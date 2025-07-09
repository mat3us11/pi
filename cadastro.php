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
        <input type="text" id="email" placeholder="Digite o endereço de e-mail" />
        <input type="password" id="senha" placeholder="Digite a senha" />
        <a href="login.php"><p class="criar-conta">Fazer login</p></a>
        <button class="botao-continuar">Continuar</button>
        <p class="ou">ou continue com</p>
        <div class="botoes-sociais">
          <button><img src="https://img.icons8.com/color/48/000000/google-logo.png"/>Google</button>
          <button><img src="https://img.icons8.com/ios-filled/50/000000/mac-os.png"/>Apple</button>
          <button><img src="https://img.icons8.com/ios-filled/50/1877f2/facebook-new.png"/>Facebook</button>
        </div>
      </div>
    </div>
  </div>

 
</body>
</html>
