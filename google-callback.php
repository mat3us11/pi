<?php
session_start();
require_once './includes/config.php';


$client_id = "315485308526-c6g22elcoge3eukt5b8bb42vf47gmsjc.apps.googleusercontent.com";
$client_secret = "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";
$redirect_uri = "http://localhost/pi/google-callback.php";

if (!isset($_GET['code'])) {
    die("Erro: código de autorização não recebido");
}

$code = $_GET['code'];


$token_data = [
    'code' => $code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($token_data),
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents('https://oauth2.googleapis.com/token', false, $context);
$token = json_decode($response, true);

if (!isset($token['access_token'])) {
    die("Erro ao obter access_token");
}

$access_token = $token['access_token'];
$user_info = file_get_contents("https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=$access_token");
$user = json_decode($user_info, true);

$nome = $user['name'] ?? '';
$email = $user['email'] ?? '';

if (!$email) {
    die("Erro ao obter informações do usuário.");
}


$stmt = $conn->prepare("SELECT id, nome FROM usuario WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION["usuario_id"] = $dados["id"];
    $_SESSION["usuario_nome"] = $dados["nome"];
    $_SESSION["usuario_email"] = $email;
} else {
    // Cria novo usuário (sem senha, pois é via Google)
    $inserir = $conn->prepare("INSERT INTO usuario (nome, email) VALUES (?, ?)");
    $inserir->execute([$nome, $email]);

    $novo_id = $conn->lastInsertId();

    $_SESSION["usuario_id"] = $novo_id;
    $_SESSION["usuario_nome"] = $nome;
    $_SESSION["usuario_email"] = $email;
}
header("Location: index.php");
exit;
