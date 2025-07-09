<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script defer src="./assets/js/modal.js"></script>
  <link rel="stylesheet" href="./assets/css/login.css" />
  <title>Cadastro</title>
</head>
<body>
  <header class="cabecalho">
    <p class="logo">CAMP<span>VIA</span></p>
    <a href="#"><i class="ph ph-question"></i></a>
  </header>
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

   <!-- Modal mobile-->
<button class="hamburger" id="hamburgerBtn" aria-label="Abrir menu">
    <i class="ph ph-list" id="hamburgerIcon"></i>
</button>
<div class="mobile-modal" id="mobileModal" aria-hidden="true">
    <nav class="modal-content">
        <ul class="modal-nav">
            <!-- FIQUEI COM PREGUICA MAS AQUI É ONDE BOTA OS LINKS -->
    </nav>
</div>
</body>
</html>
