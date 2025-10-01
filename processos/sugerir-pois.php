<?php
// processos/sugerir-pois.php
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once '../includes/config.php';
require_once '../includes/utils_imagens.php';
require_once '../includes/utils_overpass.php';

$city  = trim($_GET['city'] ?? '');
$cats  = trim($_GET['cats'] ?? '');
$limit = (int)($_GET['limit'] ?? 40);

if ($city === '') {
  http_response_code(400);
  echo json_encode(['error'=>'Parâmetro "city" é obrigatório.']);
  exit;
}

$styles = array_filter(array_map('trim', explode(',', $cats)));
try {
  $pois = fetchPoisByCity($conn, $city, $styles, max(10, min($limit, 80)));
  if (empty($pois)) {
    echo json_encode(['city'=>$city, 'count'=>0, 'pois'=>[], 'info'=>'empty']);
    exit;
  }

  // imagens em lote
  $names = array_column($pois, 'name');
  $imgMap = getPoiImagesBatch($conn, $names, $city);

  $list = [];
  foreach ($pois as $p) {
    $list[] = [
      'nome'      => $p['name'],
      'lat'       => $p['lat'],
      'lon'       => $p['lon'],
      'categoria' => $p['category'],
      'image_url' => $imgMap[$p['name']] ?? null
    ];
  }

  echo json_encode(['city'=>$city, 'count'=>count($list), 'pois'=>$list], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error'=>'Falha ao obter POIs', 'detail'=>$e->getMessage()]);
}
