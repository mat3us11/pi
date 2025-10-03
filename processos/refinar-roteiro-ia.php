<?php
// processos/refinar-roteiro-ia.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once '../includes/config.php';
require_once '../includes/ai_gemini.php';
require_once '../includes/utils_imagens.php';

/* Normaliza categorias livres -> set canônico (mesma função do editar) */
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

  $xform = function($s){
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
    $t = $xform($raw);
    foreach ($mapSyn as $key => $syns) {
      foreach ($syns as $syn) {
        if ($t === $xform($syn) || str_starts_with($t, $xform($syn))) { $result[$key]=true; break 2; }
      }
      if ($t === $key) { $result[$key]=true; }
    }
  }
  return array_values(array_filter($canon, fn($c) => isset($result[$c])));
}

// ---------- CSRF ----------
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

$csrfPosted = get_post_csrf();
$validKeys  = ['csrf_refine', 'csrf_ia', 'csrf_edit', 'csrf_publish'];
$csrfOk     = check_csrf_multi($validKeys, $csrfPosted);

if (empty($_SESSION['usuario_id'])) { http_response_code(401); exit('Login necessário.'); }
if (!$csrfOk) { http_response_code(400); exit('Token inválido.'); }

// ---------- Entrada ----------
$pedidoRefino = trim($_POST['pedido'] ?? '');
$refinarDeId  = isset($_POST['refinar_de']) && is_numeric($_POST['refinar_de']) ? (int)$_POST['refinar_de'] : null;
$applyToForm  = isset($_POST['apply_to_form']) ? (int)$_POST['apply_to_form'] : 0;

if (!$refinarDeId) { http_response_code(400); exit('ID da rota ausente.'); }

// ---------- Carrega base ----------
$sql = "SELECT r.id, r.usuario_id, r.nome, r.descricao, r.categorias, r.capa,
               r.ponto_partida, r.destino, r.paradas, r.cidade_id
        FROM rota r
        WHERE r.id = ? AND r.usuario_id = ?";
$st = $conn->prepare($sql);
$st->execute([$refinarDeId, (int)$_SESSION['usuario_id']]);
$rota = $st->fetch(PDO::FETCH_ASSOC);

if (!$rota) { http_response_code(404); exit('Rota não encontrada.'); }

// Normaliza paradas atuais p/ prompt
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

// ---------- Config externos ----------
$MAPBOX_TOKEN = getenv('MAPBOX_TOKEN') ?: '';
$BBOX_SP      = "-53.0,-25.5,-44.0,-19.0";

// ---------- Prompt ----------
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

Pedido do usuário: \"" . $pedidoRefino . "\"
Roteiro atual (JSON): " . json_encode($estadoAtual, JSON_UNESCAPED_UNICODE);

// ---------- IA ----------
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

// ---------- Normaliza ----------
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

// Geocode opcional (Mapbox)
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

// imagens por nome
$cityForImages = $dest ?: $pp;
$imgMap = getPoiImagesBatch($conn, $nomes, $cityForImages);
foreach ($enriquecidas as &$p) { $p['image_url'] = $imgMap[$p['nome']] ?? null; }
unset($p);

// ---------- Aplica no formulário da MESMA rota ----------
$_SESSION['refine_apply'][$refinarDeId] = [
  'nome'          => $nome,
  'descricao'     => $descricao,
  'categorias'    => $cats, // já canônico
  'ponto_partida' => $pp ?: 'Não informado',
  'destino'       => $dest ?: 'Não informado',
  'paradas'       => $enriquecidas,
];

header('Location: ../paginas/editar-rota.php?id='.$refinarDeId.'&from=ia');
exit;
