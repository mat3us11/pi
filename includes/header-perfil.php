<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$foto_perfil = (isset($_SESSION['usuario_foto']) && !empty($_SESSION['usuario_foto']))
    ? $_SESSION['usuario_foto']
    : './assets/img/imagem-padrao.png';

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$email_usuario = $_SESSION['usuario_email'] ?? '';
?>

<header class="header-perfil">
  <div class="container header-container-perfil">
    
    <!-- Topo: seta de voltar + logo -->
    <div class="perfil-top">
      <a href="index.php" class="voltar">
        <i class="ph ph-arrow-left"></i>
      </a>
      <a href="index.php">
        <p class="logo">CAMP<i class="ph ph-tipi" style="transform: rotate(180deg); display: inline-block;"></i><span>IA</span></p>
      </a>
    </div>

    <!-- Conteúdo principal do perfil -->
    <div class="perfil-conteudo">
      <div class="perfil-info">
        <div class="foto-perfil-grande" style="background-image: url('<?php echo htmlspecialchars($foto_perfil); ?>');"></div>
        <div class="dados">
          <p class="nome-usuario">Olá, <?php echo htmlspecialchars($nome_usuario); ?>!</p>
          <div class="botoes-perfil">
            <a href="#" class="btn-perfil"><i class="ph ph-pencil-simple"></i> Editar Perfil</a>
            <a href="index.php" class="btn-perfil"><i class="ph ph-house"></i> Home</a>
            <a href="#" class="btn-perfil"><i class="ph ph-bicycle"></i> Passeios</a>
            <a href="#" class="btn-perfil"><i class="ph ph-map-trifold"></i> Roteiros</a>
          </div>
        </div>
      </div>

      <!-- Informações e ícones à direita -->
      <div class="right">
        <div class="top-right">
          <div class="usuario-logado">
            <div class="dados-usuario">
            </div>
          </div>
        </div>
        <div class="icons">
          <i class="ph ph-clock-counter-clockwise"></i>
          <i class="ph ph-question"></i>
        </div>
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
        <div class="cadastro-login">
          <a href="cadastro.php"><button class="btn btn-outline">Cadastre-se</button></a>
          <a href="login.php"><button class="btn btn-fill">Login</button></a>
        </div>
      <?php endif; ?>
    </div>

    <ul class="modal-nav">
      <li><a href="perfil.php" class="nav-link"><i class="ph ph-user"></i> Perfil</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-bicycle"></i> Passeios</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-map-trifold"></i> Roteiros</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> Histórico</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-question"></i> Dúvidas</a></li>
    </ul>
  </nav>
</div>

<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script>
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mobileModal = document.getElementById('mobileModal');

  hamburgerBtn.addEventListener('click', () => {
    mobileModal.classList.toggle('active');
  });
</script>
