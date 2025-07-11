<?php
session_start();
require_once './includes/config.php';

$fbAppId = 'SEU_APP_ID';
$fbAppSecret = 'SEU_APP_SECRET';
$redirectUri = 'http://localhost/pi/facebook-callback.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $tokenUrl = 'https://graph.facebook.com/v18.0/oauth/access_token?' . http_build_query([
        'client_id' => $fbAppId,
        'redirect_uri' => $redirectUri,
        'client_secret' => $fbAppSecret,
        'code' => $code,
    ]);

    $response = file_get_contents($tokenUrl);
    $tokenData = json_decode($response, true);

    if (isset($tokenData['access_token'])) {
        $accessToken = $tokenData['access_token'];
        $userUrl = 'https://graph.facebook.com/me?fields=id,name,email&access_token=' . $accessToken;
        $userResponse = file_get_contents($userUrl);
        $userData = json_decode($userResponse, true);

        $nome = $userData['name'] ?? null;
        $email = $userData['email'] ?? null;

        if ($email && $nome) {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, origem_login) VALUES (?, ?, NULL, 'facebook')");
                $stmt->execute([$nome, $email]);
                $usuario_id = $pdo->lastInsertId();
            } else {
                $usuario_id = $usuario['id'];
            }
            $_SESSION['usuario_id'] = $usuario_id;
            $_SESSION['nome'] = $nome;
            $_SESSION['email'] = $email;

            header("Location: index.php"); 
            exit;
        } else {
            echo "Erro: e-mail não retornado pelo Facebook.";
        }
    } else {
        echo "Erro ao obter token de acesso.";
    }
} else {
    echo "Erro: código de autorização não recebido.";
}
