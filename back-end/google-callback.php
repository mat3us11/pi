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
$foto_google = $user['picture'] ?? './assets/img/imagem-padrao.png';

if (!$email) {
    die("Erro ao obter informações do usuário.");
}

// Verifica se o usuário já existe no banco
$stmt = $conn->prepare("SELECT id, nome, foto_perfil FROM usuario WHERE email = ?");
$stmt->execute([$email]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dados) {
    $foto_atual = $dados['foto_perfil'];

    // Verifica se a foto atual é personalizada (não é a mesma do Google)
    $foto_final = ($foto_atual !== $foto_google && !empty($foto_atual)) ? $foto_atual : $foto_google;

    // Atualiza no banco se necessário
    if ($foto_atual !== $foto_final) {
        $stmtUpdate = $conn->prepare("UPDATE usuario SET foto_perfil = ? WHERE email = ?");
        $stmtUpdate->execute([$foto_final, $email]);
    }

    // Atualiza sessão
    $_SESSION["usuario_id"] = $dados["id"];
    $_SESSION["usuario_nome"] = $dados["nome"];
    $_SESSION["usuario_email"] = $email;
    $_SESSION["usuario_foto"] = $foto_final ?? './assets/img/imagem-padrao.png';
} else {
    // Novo usuário
    $inserir = $conn->prepare("INSERT INTO usuario (nome, email, foto_perfil) VALUES (?, ?, ?)");
    $inserir->execute([$nome, $email, $foto_google]);

    $novo_id = $conn->lastInsertId();

    $_SESSION["usuario_id"] = $novo_id;
    $_SESSION["usuario_nome"] = $nome;
    $_SESSION["usuario_email"] = $email;
    $_SESSION["usuario_foto"] = $foto_google ?? './assets/img/imagem-padrao.png';
}

header("Location: index.php");
exit;