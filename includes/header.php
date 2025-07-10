<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<header>
  <div class="container">
    <div class="left">
      <div class="top-left">
        <a href="./index.php">
          <p class="logo">CAMP<i class="ph ph-tipi" style="transform: rotate(180deg); display: inline-block;"></i><span>IA</span></p>
        </a>
      </div>
      <ul class="nav-links">
        <li><a href="#"><i class="ph ph-bicycle"></i> Passeios</a></li>
        <li><a href="#"><i class="ph ph-map-trifold"></i> Roteiros de Viagem</a></li>
      </ul>
    </div>

    <div class="right">
      <?php if (!isset($ocultarBotoesHeader) || !$ocultarBotoesHeader): ?>
        <div class="top-right">
          <?php if (isset($_SESSION['usuario_nome'])): ?>
            <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
            <a href="perfil.php"><button class="btn btn-outline">Meu Perfil</button></a>
            <a href="logout.php"><button class="btn btn-fill">Sair</button></a>
          <?php else: ?>
            <a href="cadastro.php"><button class="btn btn-outline">Cadastre-se</button></a>
            <a href="login.php"><button class="btn btn-fill">Login</button></a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="icons">
        <i class="ph ph-clock-counter-clockwise"></i>
        <i class="ph ph-question"></i>
      </div>
    </div>
  </div>
</header>

<!-- Botão Hamburguer -->
<button class="hamburger" id="hamburgerBtn" aria-label="Abrir menu">
  <i class="ph ph-list" id="hamburgerIcon"></i>
</button>

<!-- Modal Mobile -->
<div class="mobile-modal" id="mobileModal" aria-hidden="true">
  <nav class="modal-conteudo">
    <div class="perfil">
      <div class="foto-perfil"></div>
      <div class="cadastro-login">
        <?php if (isset($_SESSION['usuario_nome'])): ?>
          <span style="color: white;">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
          <a href="perfil.php"><button class="btn btn-outline">Meu Perfil</button></a>
          <a href="logout.php"><button class="btn btn-fill">Sair</button></a>
        <?php else: ?>
          <a href="cadastro.php"><button class="btn btn-outline">Cadastre-se</button></a>
          <a href="login.php"><button class="btn btn-fill">Login</button></a>
        <?php endif; ?>
      </div>
    </div>
    <ul class="modal-nav">
      <li><a href="#"><button class="nav-link"><i class="ph ph-map-trifold"></i> Passeios</button></a></li>
      <li><a href="#"><button class="nav-link"><i class="ph ph-map-pin"></i> Roteiros</button></a></li>
      <li><a href="#"><button class="nav-link"><i class="ph ph-clock-clockwise"></i> Histórico</button></a></li>
      <li><a href="#"><button class="nav-link"><i class="ph ph-question"></i> Dúvidas</button></a></li>
    </ul>
  </nav>
</div>

<?php if (!isset($ocultarImagemHeader) || !$ocultarImagemHeader): ?>
  <img class="imagem-desktop" src="./assets/img/header-index.png" alt="">
<?php endif; ?>

<script src="https://unpkg.com/@phosphor-icons/web"></script>
