<?php
session_start();
require_once './includes/config.php';



// Impede que o usuário acese a página depois de logado
if (isset($_SESSION["logado"]) && $_SESSION["logado"] === true) {
    header("Location: index.php");
}


// === LOGIN MANUAL (MySQL) ===
$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = $_POST["email"] ?? '';
  $senha = $_POST["senha"] ?? '';

  if (empty($email) || empty($senha)) {
    $erro = "Preencha todos os campos.";
  } else {
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuario WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
      $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
      if (password_verify($senha, $usuario["senha"])) {
        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
        $_SESSION["usuario_email"] = $email;

        $_SESSION["logado"] = true;
        header("Location: index.php");
        exit;
      } else {
        $erro = "Verifique se os campos foram preenchidos corretamente.";
      }
    } else {
      $erro = "Verifique se os campos foram preenchidos corretamente.";
    }
  }
}

// === login com  google ===
$client_id = "315485308526-c6g22elcoge3eukt5b8bb42vf47gmsjc.apps.googleusercontent.com"; // substitua aqui
$redirect_uri = "http://localhost/pi/google-callback.php";

$scope = "https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email";

$auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
  'client_id' => $client_id,
  'redirect_uri' => $redirect_uri,
  'response_type' => 'code',
  'scope' => $scope,
  'access_type' => 'offline',
  'prompt' => 'consent'
]);

// === Login com facebook ===
$fbAppId = 'SEU_APP_ID';
$redirectUri = 'http://localhost/pi/facebook-callback.php';
$scope = 'email';

$fbLoginUrl = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query([
  'client_id' => $fbAppId,
  'redirect_uri' => $redirectUri,
  'scope' => $scope,
  'response_type' => 'code',
]);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script defer src="./assets/js/modal.js"></script>
  <link rel="stylesheet" href="./assets/css/login.css" />
  <link rel="stylesheet" href="./assets/css/header.css" />
  <title>Login</title>
</head>

<body>
  <?php
  $ocultarImagemHeader = true;
  $ocultarBotoesHeader = true;
  include 'includes/header.php';
  ?>
  <div class="area-cadastro">
    <div class="cartao">
      <div class="lado-esquerdo">
        <img src="./assets/img/carro-login-cadastro.svg" alt="Imagem carro turismo" />
        <div class="texto-beneficios">
          <p><i class="ph ph-check"></i>Encontre as melhores ofertas de hospedagem e pacotes turísticos personalizados para o seu perfil.</p>
          <p><i class="ph ph-check"></i>Acompanhe e gerencie suas reservas de forma simples e rápida, em qualquer dispositivo.</p>
          <p><i class="ph ph-check"></i>Receba notificações com promoções exclusivas e alertas de preço para destinos de interesse.</p>
        </div>
      </div>

      <div class="lado-direito">
        <h3>Faça login ou crie uma conta</h3>

        <?php if (!empty($erro)): ?>
          <p style="color: red; margin-bottom: 10px;"><?php echo $erro; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="email" name="email" id="email" placeholder="Digite o endereço de e-mail" autocomplete="off" required />
          <input type="password" name="senha" id="senha" placeholder="Digite a senha" autocomplete="off" required />
          <a href="cadastro.php">
            <p class="criar-conta">Criar conta</p>
          </a>
          <button class="botao-continuar" type="submit">Continuar</button>
        </form>

        <p class="ou">ou continue com</p>

        <div class="botoes-sociais">
          <a href="<?= $auth_url ?>" class="btn-social">
            <img src="https://img.icons8.com/color/48/000000/google-logo.png" />Google
          </a>
          <button><img src="https://img.icons8.com/ios-filled/50/000000/mac-os.png" />Apple</button>
          <a href="<?= $fbLoginUrl ?>" class="btn-social">
              <img src="https://img.icons8.com/ios-filled/50/1877f2/facebook-new.png" />Facebook
          </a>
        </div>
        
      </div>
    </div>
  </div>
</body>

</html>