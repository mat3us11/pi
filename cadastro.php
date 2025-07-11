<?php
// === LINK DO GOOGLE LOGIN ===
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
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script defer src="./assets/js/modal.js"></script>
  <link rel="stylesheet" href="./assets/css/cadastro.css" />
  <link rel="stylesheet" href="./assets/css/header.css" />
  <title>Cadastro</title>
</head>

<body>
  <?php
  $ocultarImagemHeader = true;
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
        <h3>Crie uma conta ou faça login</h3>

        <form action="cadastrar.php" method="POST" style="width: 100%;">
          <input type="text" id="nome" name="nome" placeholder="Digite o seu nome" required />
          <input type="text" id="email" name="email" placeholder="Digite o endereço de e-mail" required />
          <input type="password" id="senha" name="senha" placeholder="Digite a senha" required />
          <a href="login.php">
            <p class="criar-conta">Fazer login</p>
          </a>
          <button class="botao-continuar" type="submit">Continuar</button>
        </form>

        <p class="ou">ou continue com</p>

        <div class="botoes-sociais">
          <a href="<?= $auth_url ?>" class="btn-social">
            <img src="https://img.icons8.com/color/48/000000/google-logo.png" />Google
          </a>
          <button><img src="https://img.icons8.com/ios-filled/50/000000/mac-os.png" />Apple</button>
          <button><img src="https://img.icons8.com/ios-filled/50/1877f2/facebook-new.png" />Facebook</button>
        </div>
      </div>

    </div>
  </div>


</body>

</html>