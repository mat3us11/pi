<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Se não tiver foto, usar a padrão
$foto_perfil = (isset($_SESSION['usuario_foto']) && !empty($_SESSION['usuario_foto'])) 
    ? $_SESSION['usuario_foto'] 
    : './assets/img/imagem-padrao.png';
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
        <li><a href="perfil.php"><i class="ph ph-user"></i> Perfil</a></li>
      </ul>
    </div>

    <div class="right">
      <?php if (!isset($ocultarBotoesHeader) || !$ocultarBotoesHeader): ?>
        <div class="top-right">
          <?php if (isset($_SESSION['usuario_nome']) && isset($_SESSION['usuario_email'])): ?>
            <div class="usuario-logado">
              <div class="foto-perfil-header" style="background-image: url('<?php echo htmlspecialchars($foto_perfil); ?>');"><a href="/perfil.php"></a></div>
              <div class="dados-usuario">
                <p class="nome-usuario"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></p>
                <p class="email-usuario"><?php echo htmlspecialchars($_SESSION['usuario_email']); ?></p>
              </div>
            </div>
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
     <div class="foto-perfil" style="background-image: url('<?php echo htmlspecialchars($foto_perfil); ?>');"></div>
      <?php if (isset($_SESSION['usuario_nome'])): ?>
        <p class="saudacao">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</p>
      <?php else: ?>
        <a href="cadastro.php"><button class="btn btn-outline">Cadastre-se</button></a>
        <a href="login.php"><button class="btn btn-fill">Login</button></a>
      <?php endif; ?>
    </div>

    <ul class="modal-nav">
      <li><a href="./perfil.php" class="nav-link"><i class="ph ph-user"></i> Perfil</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-bicycle"></i> Passeios</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-map-trifold"></i> Roteiros</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> Histórico</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-question"></i> Dúvidas</a></li>
    </ul>
  </nav>
</div>

<?php if (!isset($ocultarImagemHeader) || !$ocultarImagemHeader): ?>
  <img class="imagem-desktop" src="./assets/img/header-index.png" alt="">
<?php endif; ?>

<script src="https://unpkg.com/@phosphor-icons/web"></script>
