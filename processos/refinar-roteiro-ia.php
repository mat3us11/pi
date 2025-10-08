<?php
// processos/refinar-roteiro-ia.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once '../includes/config.php';
require_once '../includes/ai_gemini.php';
require_once '../includes/utils_imagens.php';

/* =========================
   Helpers
   ========================= */
function normalize_categorias($cats): array {
  $canon = ['cultural','aventura','gastronomica','ecologica','citytour'];
  $mapSyn = [
    'cultural'      => ['cultural','cultura'],
    'aventura'      => ['aventura','adrenalina','radical'],
    'gastronomica'  => ['gastronomica','gastronômica','comida','culinaria','culinária','food'],
    'ecologica'     => ['ecologica','ecológica','natureza','eco','parque','verde'],
    'citytour'      => ['citytour','city tour','turismo','city','centro','pontos turisticos','pontos turísticos','turístico','turisticos']
  ];
  $result = [];

  $tokens = [];
  if (is_array($cats)) $tokens = $cats;
  else $tokens = array_map('trim', preg_split('/[,;|]/', (string)$cats));

  $x = static function($s){
    $s = mb_strtolower($s ?? '', 'UTF-8');
    $s = strtr($s, ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
                    'é'=>'e','ê'=>'e','è'=>'e','ë'=>'e',
                    'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i',
                    'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
                    'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u',
                    'ç'=>'c']);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
  };

  foreach ($tokens as $raw) {
    if ($raw === '' || $raw === null) continue;
    $t = $x($raw);
    foreach ($mapSyn as $key => $syns) {
      foreach ($syns as $syn) {
        if ($t === $x($syn) || str_starts_with($t, $x($syn))) { $result[$key]=true; break 2; }
      }
      if ($t === $key) { $result[$key]=true; break; }
    }
  }
  return array_values(array_filter($canon, fn($c) => isset($result[$c])));
}

function mapboxForwardLocal(string $q, string $token, string $bbox): ?array {
  $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" . rawurlencode($q) . ".json"
       . "?access_token=" . urlencode($token)
       . "&autocomplete=true&limit=1&language=pt&bbox=" . $bbox . "&country=BR";
  $ch = curl_init($url);
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>12]);
  $resp = curl_exec($ch);
  if ($resp === false) { curl_close($ch); return null; }
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code !== 200) return null;
  $j = json_decode($resp, true);
  if (!isset($j['features'][0]['center'])) return null;
  $c = $j['features'][0]['center']; // [lon, lat]
  return ['lat' => (float)$c[1], 'lon' => (float)$c[0]];
}

/* =========================
   CSRF
   ========================= */
function get_post_csrf(): string {
  if (isset($_POST['csrf'])) return (string)$_POST['csrf'];
  $headers = function_exists('getallheaders') ? getallheaders() : [];
  if (!empty($headers['X-CSRF-Token'])) return (string)$headers['X-CSRF-Token'];
  if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) return (string)$_SERVER['HTTP_X_CSRF_TOKEN'];
  return '';
}
function check_csrf_multi(array $sessionKeys, string $posted): bool {
  if ($posted === '') return false;
  foreach ($sessionKeys as $k) {
    if (!empty($_SESSION[$k]) && hash_equals($_SESSION[$k], $posted)) return true;
  }
  return false;
}

if (empty($_SESSION['usuario_id'])) { http_response_code(401); exit('Login necessário.'); }

$csrfPosted = get_post_csrf();
$validKeys  = ['csrf_refine', 'csrf_ia', 'csrf_edit', 'csrf_publish'];
if (!check_csrf_multi($validKeys, $csrfPosted)) { http_response_code(400); exit('Token inválido.'); }

/* =========================
   Entrada & Modo
   ========================= */
$pedidoRefino = trim($_POST['pedido'] ?? '');
$refinarDeId  = (isset($_POST['refinar_de']) && ctype_digit((string)$_POST['refinar_de'])) ? (int)$_POST['refinar_de'] : 0;

$MAPBOX_TOKEN = getenv('MAPBOX_TOKEN') ?: '';
$BBOX_SP      = "-53.0,-25.5,-44.0,-19.0";

/* =========================
   MODO 1: ROTA EXISTENTE (refinar_de > 0)
   ========================= */
if ($refinarDeId > 0) {
  $st = $conn->prepare("SELECT id, usuario_id, nome, descricao, categorias, ponto_partida, destino, paradas, cidade_id
                        FROM rota WHERE id = ? AND usuario_id = ?");
  $st->execute([$refinarDeId, (int)$_SESSION['usuario_id']]);
  $rota = $st->fetch(PDO::FETCH_ASSOC);
  if (!$rota) { http_response_code(404); exit('Rota não encontrada.'); }

  // prepara paradas p/ prompt
  $paradasBase = [];
  if (!empty($rota['paradas'])) {
    $tmp = json_decode($rota['paradas'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
      foreach ($tmp as $p) {
        if (is_array($p))      $paradasBase[] = ['nome' => (string)($p['nome'] ?? '')];
        elseif (is_string($p)) $paradasBase[] = ['nome' => $p];
      }
    }
  }

  $estadoAtual = [
    'nome'          => (string)$rota['nome'],
    'descricao'     => (string)$rota['descricao'],
    'categorias'    => (string)$rota['categorias'],
    'ponto_partida' => (string)$rota['ponto_partida'],
    'destino'       => (string)$rota['destino'],
    'paradas'       => $paradasBase,
  ];

  $prompt = "Refine o roteiro abaixo conforme o pedido do usuário. 
Responda APENAS em JSON válido no formato:
{
  \"nome\": string,
  \"descricao\": string,
  \"categorias\": string|array,
  \"ponto_partida\": string,
  \"destino\": string,
  \"paradas\": [{\"nome\": string}, ...]
}

Pedido do usuário: \"{$pedidoRefino}\"
Roteiro atual (JSON): " . json_encode($estadoAtual, JSON_UNESCAPED_UNICODE);

  $res  = gemini_generate_raw($prompt, null);
  $text = gemini_first_text_from($res);
  if (!$text) {
    $_SESSION['flash_refine_err'] = 'Não foi possível refinar agora.';
    header('Location: ../paginas/editar-rota.php?id='.$refinarDeId);
    exit;
  }

  $clean = trim(preg_replace('/```json|```/i', '', $text));
  $data  = json_decode($clean, true);
  if (!$data && preg_match('/\{.*\}/s', $clean, $m)) { $data = json_decode($m[0], true); }
  if (!$data || !isset($data['nome'], $data['descricao'], $data['paradas'])) {
    $_SESSION['flash_refine_err'] = 'Resposta inesperada da IA.';
    header('Location: ../paginas/editar-rota.php?id='.$refinarDeId);
    exit;
  }

  // Normaliza
  $nome         = mb_substr((string)$data['nome'], 0, 200);
  $descricao    = (string)($data['descricao'] ?? '');
  $catsRaw      = $data['categorias'] ?? ($rota['categorias'] ?? '');
  $catsList     = normalize_categorias($catsRaw);
  $cats         = implode(',', $catsList);

  $pp           = (string)($data['ponto_partida'] ?? ($rota['ponto_partida'] ?? ''));
  $dest         = (string)($data['destino'] ?? ($rota['destino'] ?? ''));
  $paradasArray = $data['paradas'] ?? [];

  $nomes = [];
  foreach ($paradasArray as $p) {
    if (is_array($p) && isset($p['nome'])) $nomes[] = trim((string)$p['nome']);
    elseif (is_string($p)) $nomes[] = trim($p);
  }
  $nomes = array_values(array_filter(array_unique($nomes)));

  $enriquecidas = [];
  if (!empty($nomes)) {
    foreach ($nomes as $nm) {
      $lat = null; $lon = null;
      if ($MAPBOX_TOKEN) {
        $coords = mapboxForwardLocal($nm . ' ' . ($dest ?: $pp), $MAPBOX_TOKEN, $BBOX_SP);
        if ($coords) { $lat = $coords['lat']; $lon = $coords['lon']; }
      }
      $enriquecidas[] = ['nome'=>$nm, 'lat'=>$lat, 'lon'=>$lon];
    }
  }

  $cityForImages = $dest ?: $pp;
  $imgMap = getPoiImagesBatch($conn, $nomes, $cityForImages);
  foreach ($enriquecidas as &$p) { $p['image_url'] = $imgMap[$p['nome']] ?? null; }
  unset($p);

  // Aplica no formulário da MESMA rota
  $_SESSION['refine_apply'][$refinarDeId] = [
    'nome'          => $nome,
    'descricao'     => $descricao,
    'categorias'    => $cats,
    'ponto_partida' => $pp ?: 'Não informado',
    'destino'       => $dest ?: 'Não informado',
    'paradas'       => $enriquecidas,
  ];

  header('Location: ../paginas/editar-rota.php?id='.$refinarDeId.'&from=ia');
  exit;
}

/* =========================
   MODO 2: RASCUNHO (sem id, antes de publicar)
   Usa $_SESSION['rota_draft']
   ========================= */
$draft = $_SESSION['rota_draft'] ?? null;
if (!$draft) {
  // não há rascunho — volta para criar
  header('Location: ../paginas/criar-roteiro.php');
  exit;
}

// Permitir que o usuário passe overrides base (opcionais) no form de refino do rascunho:
if (isset($_POST['ponto_partida']) && $_POST['ponto_partida'] !== '') $draft['ponto_partida'] = trim($_POST['ponto_partida']);
if (isset($_POST['destino']) && $_POST['destino'] !== '')           $draft['destino']       = trim($_POST['destino']);
if (isset($_POST['categorias']) && $_POST['categorias'] !== '')     $draft['categorias']    = implode(',', normalize_categorias($_POST['categorias']));

// Monta estado atual para o prompt
$paradasBase = [];
if (!empty($draft['paradas']) && is_array($draft['paradas'])) {
  foreach ($draft['paradas'] as $p) {
    if (is_array($p))      $paradasBase[] = ['nome' => (string)($p['nome'] ?? '')];
    elseif (is_string($p)) $paradasBase[] = ['nome' => $p];
  }
}

$estadoAtual = [
  'nome'          => (string)($draft['nome'] ?? ''),
  'descricao'     => (string)($draft['descricao'] ?? ''),
  'categorias'    => (string)($draft['categorias'] ?? ''),
  'ponto_partida' => (string)($draft['ponto_partida'] ?? ''),
  'destino'       => (string)($draft['destino'] ?? ''),
  'paradas'       => $paradasBase,
];

$prompt = "Refine o roteiro abaixo conforme o pedido do usuário. 
Responda APENAS em JSON válido no formato:
{
  \"nome\": string,
  \"descricao\": string,
  \"categorias\": string|array,
  \"ponto_partida\": string,
  \"destino\": string,
  \"paradas\": [{\"nome\": string}, ...]
}

Pedido do usuário: \"{$pedidoRefino}\"
Roteiro atual (JSON): " . json_encode($estadoAtual, JSON_UNESCAPED_UNICODE);

// IA
$res  = gemini_generate_raw($prompt, null);
$text = gemini_first_text_from($res);
if (!$text) {
  $_SESSION['flash_refine_err'] = 'Não foi possível refinar agora.';
  header('Location: ../paginas/editar-roteiro-ia.php');
  exit;
}

$clean = trim(preg_replace('/```json|```/i', '', $text));
$data  = json_decode($clean, true);
if (!$data && preg_match('/\{.*\}/s', $clean, $m)) { $data = json_decode($m[0], true); }

if (!$data || !isset($data['nome'], $data['descricao'], $data['paradas'])) {
  $_SESSION['flash_refine_err'] = 'Resposta inesperada da IA.';
  header('Location: ../paginas/editar-roteiro-ia.php');
  exit;
}

// Normaliza
$nome         = mb_substr((string)$data['nome'], 0, 200);
$descricao    = (string)($data['descricao'] ?? '');
$catsRaw      = $data['categorias'] ?? ($draft['categorias'] ?? '');
$catsList     = normalize_categorias($catsRaw);
$cats         = implode(',', $catsList);

$pp           = (string)($data['ponto_partida'] ?? ($draft['ponto_partida'] ?? ''));
$dest         = (string)($data['destino'] ?? ($draft['destino'] ?? ''));
$paradasArray = $data['paradas'] ?? [];

$nomes = [];
foreach ($paradasArray as $p) {
  if (is_array($p) && isset($p['nome'])) $nomes[] = trim((string)$p['nome']);
  elseif (is_string($p)) $nomes[] = trim($p);
}
$nomes = array_values(array_filter(array_unique($nomes)));

$enriquecidas = [];
if (!empty($nomes)) {
  foreach ($nomes as $nm) {
    $lat = null; $lon = null;
    if ($MAPBOX_TOKEN) {
      $coords = mapboxForwardLocal($nm . ' ' . ($dest ?: $pp), $MAPBOX_TOKEN, $BBOX_SP);
      if ($coords) { $lat = $coords['lat']; $lon = $coords['lon']; }
    }
    $enriquecidas[] = ['nome'=>$nm, 'lat'=>$lat, 'lon'=>$lon];
  }
}

// imagens
$cityForImages = $dest ?: $pp;
$imgMap = getPoiImagesBatch($conn, $nomes, $cityForImages);
foreach ($enriquecidas as &$p) { $p['image_url'] = $imgMap[$p['nome']] ?? null; }
unset($p);

// Atualiza rascunho na sessão (substitui os campos)
$_SESSION['rota_draft'] = [
  'usuario_id'    => (int)$_SESSION['usuario_id'],
  'nome'          => $nome,
  'descricao'     => $descricao,
  'categorias'    => $cats,
  'ponto_partida' => $pp ?: 'Não informado',
  'destino'       => $dest ?: 'Não informado',
  'cidade_id'     => $draft['cidade_id'] ?? null,
  'paradas'       => $enriquecidas,
  'capa'          => $draft['capa'] ?? null,
  '_ia_json'      => $data,
];

header('Location: ../paginas/editar-roteiro-ia.php?from=ia');
exit;
