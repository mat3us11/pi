<?php
// includes/ai_gemini.php
// Cliente OpenAI Responses API (2025) compatível com JSON Schema válido.
// Substitui Gemini mantendo funções gemini_generate_raw() e gemini_first_text_from().

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

define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_API_BASE', 'https://api.openai.com/v1');

const OPENAI_PREFERRED_MODELS = [
  'gpt-4o-mini',
  'gpt-4o',
  'gpt-4.1-mini',
  'gpt-4.1',
];

// === HTTP helper ===
function http_json_post(string $url, array $body, array $headers = [], int $timeout=60): array {
  $ch = curl_init($url);
  $hdrs = array_merge(['Content-Type: application/json'], $headers);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => $hdrs,
    CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => $timeout,
  ]);
  $resp = curl_exec($ch);
  $err  = $resp === false ? curl_error($ch) : null;
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($resp === false) {
    error_log('OpenAI cURL error: ' . $err);
    return ['ok'=>false, 'http'=>0, 'raw'=>$err, 'json'=>null];
  }
  $json = json_decode($resp, true);
  $ok = ($code >= 200 && $code < 300);
  if (!$ok) error_log("OpenAI HTTP $code: $resp");
  return ['ok'=>$ok, 'http'=>$code, 'raw'=>$resp, 'json'=>$json];
}

// === Principal ===
function gemini_generate_raw(string $prompt, ?string $model = null): array {
  if (!OPENAI_API_KEY) {
    return ['ok'=>false, 'http'=>0, 'raw'=>'OPENAI_API_KEY ausente', 'json'=>null];
  }

  $chosen = $model ?: OPENAI_PREFERRED_MODELS[0];
  $url = OPENAI_API_BASE . '/responses';
  $headers = ['Authorization: Bearer ' . OPENAI_API_KEY];

  // Schema de saída — agora com required completo ✅
  $schema = [
    'type' => 'object',
    'additionalProperties' => false,
    'properties' => [
      'nome' => ['type' => 'string'],
      'descricao' => ['type' => 'string'],
      'categorias' => ['type' => 'string'],
      'ponto_partida' => ['type' => 'string'],
      'destino' => ['type' => 'string'],
      'paradas' => [
        'type' => 'array',
        'minItems' => 5,
        'maxItems' => 10,
        'items' => [
          'type' => 'object',
          'additionalProperties' => false,
          'properties' => [
            'nome' => ['type' => 'string'],
            'descricao' => ['type' => 'string']
          ],
          'required' => ['nome', 'descricao']
        ]
      ]
    ],
    // 👇 Todos os campos obrigatórios
    'required' => [
      'nome',
      'descricao',
      'categorias',
      'ponto_partida',
      'destino',
      'paradas'
    ]
  ];

  $body = [
    'model' => $chosen,
    'input' => $prompt,
    'temperature' => 0.3,
    'text' => [
      'format' => [
        'name' => 'RoteiroIA',
        'type' => 'json_schema',
        'schema' => $schema
      ]
    ]
  ];

  $res = http_json_post($url, $body, $headers);
  if ($res['ok']) { $res['_chosen'] = $chosen; return $res; }

  // fallback de modelo
  if (in_array($res['http'], [400,404,422])) {
    foreach (OPENAI_PREFERRED_MODELS as $alt) {
      if ($alt === $chosen) continue;
      $body['model'] = $alt;
      $resAlt = http_json_post($url, $body, $headers);
      if ($resAlt['ok']) { $resAlt['_chosen'] = $alt; return $resAlt; }
    }
  }

  return $res;
}

// === Extrator ===
function gemini_first_text_from(array $resp): ?string {
  if (!$resp['ok'] || empty($resp['json'])) return null;
  $j = $resp['json'];

  if (isset($j['output_text']) && is_string($j['output_text'])) return $j['output_text'];

  if (isset($j['output']) && is_array($j['output'])) {
    $out = [];
    foreach ($j['output'] as $item) {
      if (isset($item['content'])) {
        foreach ($item['content'] as $c) {
          if (isset($c['text'])) $out[] = $c['text'];
        }
      } elseif (isset($item['text'])) {
        $out[] = $item['text'];
      }
    }
    if ($out) return implode("\n", $out);
  }

  return null;
}
