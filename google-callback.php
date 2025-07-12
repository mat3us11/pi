<?php
session_start();
require_once './includes/config.php'; // Aqui deve ter sua conexão $conn PDO

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
$foto = $user['picture'] ?? './assets/img/imagem-padrao.png';

if (!$email) {
    die("Erro ao obter informações do usuário.");
}

// Verifica se o usuário já existe no banco
$stmt = $conn->prepare("SELECT id, nome, foto_perfil FROM usuario WHERE email = ?");
$stmt->execute([$email]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dados) {
    // Atualiza a foto somente se for diferente da atual
    if ($dados['foto_perfil'] !== $foto) {
        $stmtUpdate = $conn->prepare("UPDATE usuario SET foto_perfil = ? WHERE email = ?");
        $stmtUpdate->execute([$foto, $email]);
        $dados['foto_perfil'] = $foto;
    }

    // Atualiza sessão com dados do usuário
    $_SESSION["usuario_id"] = $dados["id"];
    $_SESSION["usuario_nome"] = $dados["nome"];
    $_SESSION["usuario_email"] = $email;
    $_SESSION["usuario_foto"] = $dados['foto_perfil'] ?? './assets/img/imagem-padrao.png';

} else {
    // Insere novo usuário no banco
    $inserir = $conn->prepare("INSERT INTO usuario (nome, email, foto_perfil) VALUES (?, ?, ?)");
    $inserir->execute([$nome, $email, $foto]);

    $novo_id = $conn->lastInsertId();

    // Salva na sessão os dados do novo usuário
    $_SESSION["usuario_id"] = $novo_id;
    $_SESSION["usuario_nome"] = $nome;
    $_SESSION["usuario_email"] = $email;
    $_SESSION["usuario_foto"] = $foto ?? './assets/img/imagem-padrao.png';
}

header("Location: index.php");
exit;
?>
