<?php
// includes/utils_overpass.php

/* ===== HTTP cURL ===== */
function http_post_json(string $url, array $fields, int $timeout = 30): ?array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => http_build_query($fields),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT => $timeout,
  ]);
  $res = curl_exec($ch);
  if ($res === false) {
    error_log("Overpass curl error: " . curl_error($ch));
    curl_close($ch);
    return null;
  }
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code < 200 || $code >= 300) {
    error_log("Overpass HTTP $code: " . substr($res,0,500));
    return null;
  }
  $json = json_decode($res, true);
  return (json_last_error() === JSON_ERROR_NONE) ? $json : null;
}

function overpassRequest(string $query, int $timeoutSec = 60): ?array {
  // espelho mais leve
  return http_post_json("https://overpass.kumi.systems/api/interpreter", ['data'=>$query], $timeoutSec);
}

/* ===== Filtros por categoria (somente nodes para ser mais rápido) ===== */
function overpassCategoryFilters(array $cats): string {
  $map = [
    'gastronomica' => ['amenity~"^(restaurant|cafe|bar|fast_food|pub)$"'],
    'cultural'     => ['tourism~"^(museum|gallery)$"','amenity~"^(theatre|library)$"'],
    'citytour'     => ['tourism~"^(attraction|information|viewpoint)$"','historic~".+"'],
    'ecologica'    => ['leisure~"^(park|nature_reserve)$"','tourism~"^(viewpoint|picnic_site)$"'],
    'aventura'     => ['sport~".+"','leisure~"^(pitch|track|fitness_centre)$"'],
  ];
  if (empty($cats)) $cats = ['gastronomica','cultural','citytour'];
  $parts = [];
  foreach ($cats as $c) {
    if (!isset($map[$c])) continue;
    foreach ($map[$c] as $expr) {
      $parts[] = "node(area.a)[$expr];";
    }
  }
  if (empty($parts)) $parts = ['node(area.a)[amenity~"^(restaurant|cafe)$"];','node(area.a)[tourism~"^(attraction|museum)$"];'];
  return implode("\n  ", $parts);
}

/* ===== Busca POIs por cidade ===== */
function fetchPoisByCity(PDO $conn, string $cityFullName, array $styles = [], int $limit = 60): array {
  require_once __DIR__ . '/utils_imagens.php';

  $cityOnly = trim(explode(',', $cityFullName)[0] ?? $cityFullName);
  $filters = overpassCategoryFilters($styles);

  // 1) Área administrativa
  $query = <<<QL
[out:json][timeout:30];
area["boundary"="administrative"]["name"="$cityOnly"]["admin_level"~"^(7|8)$"]->.a;
(
  $filters
);
out center tags;
QL;

  $data = overpassRequest($query);

  // 2) Fallback por raio (around), 5km do centro obtido via Mapbox
  if ((!$data || empty($data['elements'])) && ($token = getenv('MAPBOX_TOKEN'))) {
    $geo = mapboxForward($cityFullName, $token);
    if ($geo) {
      $lat = $geo['lat']; $lon = $geo['lon'];
      $radius = 5000; // 5 km
      $q2 = <<<QL
[out:json][timeout:30];
(
  node[amenity](around:$radius,$lat,$lon);
  node[tourism](around:$radius,$lat,$lon);
);
out center tags;
QL;
      $data = overpassRequest($q2);
    }
  }

  $out = [];
  if (!empty($data['elements'])) {
    foreach ($data['elements'] as $el) {
      $tags = $el['tags'] ?? [];
      $name = $tags['name'] ?? null;
      $lat = $el['lat'] ?? ($el['center']['lat'] ?? null);
      $lon = $el['lon'] ?? ($el['center']['lon'] ?? null);
      if (!$lat || !$lon) continue;

      $cat = 'other';
      foreach (['amenity','tourism','shop','leisure','historic','sport'] as $k) {
        if (!empty($tags[$k])) { $cat = $k . ':' . $tags[$k]; break; }
      }
      if (!$name) {
        $pretty = explode(':', $cat)[1] ?? 'Ponto';
        $name = ucfirst(str_replace('_',' ', $pretty));
      }

      $out[] = ['name'=>$name, 'lat'=>(float)$lat, 'lon'=>(float)$lon, 'category'=>$cat, 'tags'=>$tags];
    }
  }

  usort($out, fn($a,$b)=> strcmp($a['name'], $b['name']));
  if ($limit>0) $out = array_slice($out, 0, $limit);
  return $out;
}
