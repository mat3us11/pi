<?php
// includes/utils_imagens.php
// Requer $conn (PDO) no arquivo que importar.

function cacheGetImage(PDO $conn, string $poiKey): ?string {
  $st = $conn->prepare("SELECT image_url FROM poi_images_cache WHERE poi_key=? AND expires_at>NOW() LIMIT 1");
  $st->execute([$poiKey]);
  $row = $st->fetch();
  return $row['image_url'] ?? null;
}

function cachePutImage(PDO $conn, string $poiKey, string $imageUrl, int $days=7): void {
  $st = $conn->prepare(
    "INSERT INTO poi_images_cache (poi_key, image_url, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))
     ON DUPLICATE KEY UPDATE image_url=VALUES(image_url), expires_at=VALUES(expires_at)"
  );
  $st->execute([$poiKey, $imageUrl, $days]);
}

function wikimediaThumb(string $title, int $size=600, string $lang='pt'): ?string {
  foreach ([$lang, 'en'] as $lg) {
    $url = "https://{$lg}.wikipedia.org/w/api.php?action=query&titles="
         . rawurlencode($title) . "&prop=pageimages&format=json&pithumbsize={$size}";
    $ctx = stream_context_create(['http'=>['timeout'=>7]]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) continue;
    $data = json_decode($json, true);
    foreach (($data['query']['pages'] ?? []) as $p) {
      if (!empty($p['thumbnail']['source'])) return $p['thumbnail']['source'];
    }
  }
  return null;
}

/* ======== Lote (mais rápido) ======== */
function wikimediaThumbsBatch(array $titles, int $size=600, string $lang='pt'): array {
  $titles = array_values(array_unique(array_filter(array_map('trim', $titles))));
  if (!$titles) return [];
  $chunks = array_chunk($titles, 40);
  $out = [];
  foreach ($chunks as $chunk) {
    $url = "https://{$lang}.wikipedia.org/w/api.php?action=query&prop=pageimages&format=json&pithumbsize={$size}&titles="
         . rawurlencode(implode('|', $chunk));
    $ctx = stream_context_create(['http'=>['timeout'=>7]]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) continue;
    $data = json_decode($json, true);
    foreach (($data['query']['pages'] ?? []) as $p) {
      $title = $p['title'] ?? null;
      $thumb = $p['thumbnail']['source'] ?? null;
      if ($title && $thumb) $out[$title] = $thumb;
    }
  }
  return $out;
}

function getPoiImagesBatch(PDO $conn, array $poiNames, string $cityName): array {
  $poiNames = array_values(array_unique(array_filter(array_map('trim', $poiNames))));
  if (!$poiNames) return [];
  $result = [];
  $toFetch = [];

  foreach ($poiNames as $name) {
    $key = $name . '|' . $cityName;
    $cached = cacheGetImage($conn, $key);
    if ($cached) $result[$name] = $cached; else $toFetch[] = $name;
  }
  if ($toFetch) {
    $pt = wikimediaThumbsBatch($toFetch, 600, 'pt');
    $rest = array_values(array_diff($toFetch, array_keys($pt)));
    $en = $rest ? wikimediaThumbsBatch($rest, 600, 'en') : [];

    foreach ($toFetch as $name) {
      $img = $pt[$name] ?? $en[$name] ?? null;
      if (!$img && $cityName) {
        $cityImg = wikimediaThumb($cityName, 600, 'pt') ?: wikimediaThumb($cityName, 600, 'en');
        $img = $cityImg ?: null;
      }
      if ($img) {
        $result[$name] = $img;
        cachePutImage($conn, $name.'|'.$cityName, $img, 7);
      }
    }
  }
  return $result; // nome -> url
}

/* ======== Mapbox geocode ======== */
function mapboxForward(string $query, string $token, string $bbox="-53.0,-25.5,-44.0,-19.0"): ?array {
  $query = trim($query);
  if ($query === '' || $token === '') return null;
  $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" . rawurlencode($query)
       . ".json?access_token=" . urlencode($token) . "&limit=1&language=pt&country=BR&bbox=" . urlencode($bbox);
  $ctx = stream_context_create(['http'=>['timeout'=>7]]);
  $json = @file_get_contents($url, false, $ctx);
  if (!$json) return null;
  $data = json_decode($json, true);
  $f = $data['features'][0] ?? null;
  if (!$f || empty($f['center'])) return null;
  return ['lat'=>$f['center'][1], 'lon'=>$f['center'][0]];
}
