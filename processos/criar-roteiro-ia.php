<?php
// processos/criar-roteiro-ia.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once '../includes/config.php';
require_once '../includes/ai_gemini.php';
require_once '../includes/utils_imagens.php';
require_once '../includes/utils_overpass.php'; 

if (empty($_SESSION['usuario_id'])) { http_response_code(401); exit('Login necessário.'); }

// CSRF
if (!hash_equals($_SESSION['csrf_ia'] ?? '', $_POST['csrf'] ?? '')) {
  http_response_code(400); exit('Token inválido.');
}

$MAPBOX_TOKEN = getenv('MAPBOX_TOKEN') ?: '';
$BBOX_SP      = "-53.0,-25.5,-44.0,-19.0";

$pedido        = trim($_POST['pedido'] ?? '');
$cidadeSlug    = trim($_POST['cidade_slug'] ?? '');
$categorias    = trim($_POST['categorias'] ?? '');
$pontoPartida  = trim($_POST['ponto_partida'] ?? '');
$destino       = trim($_POST['destino'] ?? '');

if ($pedido === '') { http_response_code(400); exit('Descreva o que você quer no campo "pedido".'); }

// resolve cidade_id
$cidadeId = null;
if ($cidadeSlug !== '') {
  $st = $conn->prepare('SELECT id FROM cidade WHERE slug = ? LIMIT 1');
  $st->execute([$cidadeSlug]);
  $cidadeId = $st->fetchColumn() ?: null;
}

// prompt
$prompt = <<<PROMPT
Você é um planejador de viagens especializado no interior de São Paulo. Gere um ROTEIRO em JSON ESTRITO:

{
  "nome": "string curta",
  "descricao": "1 parágrafo",
  "categorias": "lista separada por vírgula",
  "ponto_partida": "endereço ou referência",
  "destino": "cidade/bairro/ponto final",
  "paradas": [
    {"nome": "string", "descricao": "1-2 linhas"},
    {"nome": "string", "descricao": " ... "}
  ]
}

Regras:
- Se o usuário passou categorias, respeite.
- Use locais plausíveis.
- 5 a 10 paradas.
- pt-BR.

Dados do usuário:
- Pedido: "{$pedido}"
- Categorias preferidas: "{$categorias}"
- Ponto de partida sugerido: "{$pontoPartida}"
- Destino sugerido: "{$destino}"
PROMPT;

$res  = gemini_generate_raw($prompt, null); // deixa o helper escolher melhor modelo
$text = gemini_first_text_from($res);
if (!$text) {
  http_response_code(502);
  echo 'Não consegui gerar agora. HTTP=' . (int)$res['http'] . '. ';
  echo $res['raw'] ? htmlspecialchars(substr($res['raw'],0,400)) : '';
  exit;
}

// extrai JSON
$clean = trim(preg_replace('/```json|```/i', '', $text));
$data  = json_decode($clean, true);
if (!$data && preg_match('/\{.*\}/s', $clean, $m)) { $data = json_decode($m[0], true); }
if (!$data || !isset($data['nome'], $data['descricao'], $data['paradas'])) {
  http_response_code(502);
  echo 'Formato inesperado da IA.'; exit;
}

$nome         = mb_substr((string)$data['nome'], 0, 200);
$descricao    = (string)($data['descricao'] ?? '');
$cats         = (string)($data['categorias'] ?? $categorias);
$pp           = (string)($data['ponto_partida'] ?? $pontoPartida);
$dest         = (string)($data['destino'] ?? $destino);
$paradasArray = $data['paradas'] ?? [];

// nomes
$nomes = [];
foreach ($paradasArray as $p) {
  if (is_array($p) && isset($p['nome'])) $nomes[] = trim((string)$p['nome']);
  elseif (is_string($p)) $nomes[] = trim($p);
}
$nomes = array_values(array_filter(array_unique($nomes)));

// geocoding (se token)
function mapboxForwardLocal(string $q, string $token, string $bbox): ?array {
  $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" . rawurlencode($q) . ".json?access_token=" . urlencode($token) . "&autocomplete=true&limit=1&language=pt&bbox=" . $bbox . "&country=BR";
  $ch = curl_init($url);
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>12]);
  $resp = curl_exec($ch);
  if ($resp === false) { curl_close($ch); return null; }
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code !== 200) return null;
  $j = json_decode($resp, true);
  if (!isset($j['features'][0]['center'])) return null;
  $center = $j['features'][0]['center']; // [lon, lat]
  return ['lat' => (float)$center[1], 'lon' => (float)$center[0]];
}

$enriquecidas = [];
foreach ($nomes as $nm) {
  $lat = null; $lon = null;
  if ($MAPBOX_TOKEN) {
    $coords = mapboxForwardLocal($nm . ' ' . ($dest ?: $pp), $MAPBOX_TOKEN, $BBOX_SP);
    if ($coords) { $lat = $coords['lat']; $lon = $coords['lon']; }
  }
  $enriquecidas[] = ['nome'=>$nm, 'lat'=>$lat, 'lon'=>$lon];
}

// imagens
$cityForImages = $dest ?: $pp;
$imgMap = getPoiImagesBatch($conn, $nomes, $cityForImages);
foreach ($enriquecidas as &$p) { $p['image_url'] = $imgMap[$p['nome']] ?? null; }
unset($p);

// guarda rascunho em sessão (não publica)
$_SESSION['rota_draft'] = [
  'usuario_id'    => (int)$_SESSION['usuario_id'],
  'nome'          => $nome,
  'descricao'     => $descricao,
  'categorias'    => $cats,
  'ponto_partida' => $pp ?: 'Não informado',
  'destino'       => $dest ?: 'Não informado',
  'cidade_id'     => $cidadeId,
  'paradas'       => $enriquecidas,
  'capa'          => null,
  // guardar JSON rico para salvar depois em roteiro_ai
  '_ia_json'      => $data,
];

header('Location: ../paginas/editar-roteiro-ia.php');
exit;
