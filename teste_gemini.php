<?php
require __DIR__ . '/includes/ai_gemini.php';
$prompt = "Responda apenas em JSON: {\"ok\":true,\"msg\":\"teste\"}";
$res = gemini_generate_raw($prompt, 'gemini-1.5-flash');
header('Content-Type: text/plain; charset=utf-8');
var_dump($res['http']);
echo "\n\nRAW:\n".$res['raw'];
