<?php
// includes/ai_gemini.php
// Cliente REST para Gemini API v1 (estável) com discovery e fallback de modelos.

// === Carrega .env (se houver) ===
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
  foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
    $k = trim($k); $v = trim($v);
    if ($k && getenv($k) === false) { putenv("$k=$v"); }
  }
}

define('GOOGLE_API_KEY', getenv('GOOGLE_API_KEY') ?: '');
define('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1'); // v1 estável

const GEMINI_PREFERRED_MODELS = [
  'gemini-1.5-flash-latest', // rápido/barato
  'gemini-1.5-pro-latest',   // melhor qualidade
  'gemini-2.0-flash',        // se disponível
  'gemini-2.0-pro',          // se disponível
];

function http_json_post(string $url, array $body, int $timeout=40): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT        => $timeout,
  ]);
  $resp = curl_exec($ch);
  $err  = $resp === false ? curl_error($ch) : null;
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($resp === false) {
    error_log('Gemini cURL error: ' . $err);
    return ['ok'=>false, 'http'=>0, 'raw'=>$err, 'json'=>null];
  }
  $json = json_decode($resp, true);
  $ok = ($code >= 200 && $code < 300);
  if (!$ok) error_log("Gemini HTTP $code: $resp");
  return ['ok'=>$ok, 'http'=>$code, 'raw'=>$resp, 'json'=>$json];
}

function http_json_get(string $url, int $timeout=30): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => $timeout,
  ]);
  $resp = curl_exec($ch);
  $err  = $resp === false ? curl_error($ch) : null;
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($resp === false) {
    error_log('Gemini cURL error: ' . $err);
    return ['ok'=>false, 'http'=>0, 'raw'=>$err, 'json'=>null];
  }
  $json = json_decode($resp, true);
  $ok = ($code >= 200 && $code < 300);
  if (!$ok) error_log("Gemini HTTP $code: $resp");
  return ['ok'=>$ok, 'http'=>$code, 'raw'=>$resp, 'json'=>$json];
}

function gemini_list_models(): array {
  if (!GOOGLE_API_KEY) {
    return ['ok'=>false, 'http'=>0, 'raw'=>'GOOGLE_API_KEY ausente', 'json'=>null];
  }
  $url = GEMINI_API_BASE . '/models?key=' . urlencode(GOOGLE_API_KEY);
  return http_json_get($url);
}

function gemini_generate_raw(string $prompt, ?string $model = null): array {
  if (!GOOGLE_API_KEY) {
    return ['ok'=>false, 'http'=>0, 'raw'=>'GOOGLE_API_KEY ausente', 'json'=>null];
  }
  $chosen = $model ?: GEMINI_PREFERRED_MODELS[0];

  $url = GEMINI_API_BASE . '/models/' . rawurlencode($chosen) . ':generateContent?key=' . urlencode(GOOGLE_API_KEY);
  $payload = ['contents' => [[ 'role' => 'user', 'parts' => [['text' => $prompt]] ]]];

  $res = http_json_post($url, $payload);
  if ($res['ok']) return $res;

  if (in_array($res['http'], [400,404])) {
    foreach (GEMINI_PREFERRED_MODELS as $alt) {
      if ($alt === $chosen) continue;
      $urlAlt = GEMINI_API_BASE . '/models/' . rawurlencode($alt) . ':generateContent?key=' . urlencode(GOOGLE_API_KEY);
      $resAlt = http_json_post($urlAlt, $payload);
      if ($resAlt['ok']) { $resAlt['_chosen'] = $alt; return $resAlt; }
    }
  }
  return $res;
}

function gemini_first_text_from(array $resp): ?string {
  if (!$resp['ok'] || empty($resp['json'])) return null;
  $parts = $resp['json']['candidates'][0]['content']['parts'] ?? null;
  if (!$parts) return null;
  $out = [];
  foreach ($parts as $p) if (isset($p['text'])) $out[] = $p['text'];
  return $out ? implode("\n", $out) : null;
}
