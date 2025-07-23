<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$mensagem = '';
$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["nova_foto"])) {
  $id_usuario = $_SESSION["usuario_id"];
  $foto = $_FILES["nova_foto"];

  if ($foto["error"] === UPLOAD_ERR_OK) {
    $extensao = strtolower(pathinfo($foto["name"], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extensao, $permitidas)) {
      $erro = "Formato de imagem inválido. Use JPG, JPEG, PNG ou WEBP.";
    } else {
      $pasta = "uploads/";
      if (!is_dir($pasta)) {
        mkdir($pasta, 0755, true);
      }

      $nomeArquivo = "foto_" . $id_usuario . "_" . time() . "." . $extensao;
      $caminho = $pasta . $nomeArquivo;
      if (move_uploaded_file($foto["tmp_name"], $caminho)) {
        // Busca a foto anterior
        $stmt = $conn->prepare("SELECT foto_perfil FROM usuario WHERE id = ?");
        $stmt->execute([$id_usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $foto_antiga = $usuario['foto_perfil'] ?? '';
        if (
          $foto_antiga &&
          strpos($foto_antiga, 'uploads/') === 0 &&
          file_exists($foto_antiga) &&
          $foto_antiga !== './assets/img/imagem-padrao.png'
        ) {
          unlink($foto_antiga);
        }

        $stmt = $conn->prepare("UPDATE usuario SET foto_perfil = ? WHERE id = ?");
        $stmt->execute([$caminho, $id_usuario]);
        $_SESSION["usuario_foto"] = $caminho;

        $mensagem = "Foto atualizada com sucesso!";
      } else {
        $erro = "Erro ao mover o arquivo.";
      }
    }
  } else {
    $erro = "Erro no upload da imagem.";
  }
}

$foto_perfil = (isset($_SESSION['usuario_foto']) && !empty($_SESSION['usuario_foto']))
  ? $_SESSION['usuario_foto']
  : './assets/img/imagem-padrao.png';

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$email_usuario = $_SESSION['usuario_email'] ?? '';
?>

<header class="header-perfil">
  <div class="container header-container-perfil">

    <div class="perfil-top">
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
            <a href="#" id="btn-editar-perfil" class="btn-perfil"><i class="ph ph-pencil-simple"></i> Editar Perfil</a>
            <a href="index.php" class="btn-perfil"><i class="ph ph-house"></i> Home</a>
            <a href="#" class="btn-perfil"><i class="ph ph-bicycle"></i> Passeios</a>
            <a href="#" class="btn-perfil"><i class="ph ph-map-trifold"></i> Roteiros</a>
          </div>
        </div>
      </div>

      <!-- Informações e ícones à direita -->
      <div class="right">
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
  <nav class="modal-conteudo-mobile">
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


<!-- Modal de Edição de Perfil -->
<div id="modal-editar" class="modal">
  <div class="modal-conteudo">
    <h3>Editar Perfil</h3>

    <div class="editar">
    <span class="fechar" id="fechar-modal">&times;</span>

      <div class="foto">
        <div class="foto-perfil-grande" style="background-image: url('<?php echo htmlspecialchars($foto_perfil); ?>');"></div>
      
        <?php if (!empty($mensagem)) echo "<p style='color: green;'>$mensagem</p>"; ?>
        <?php if (!empty($erro)) echo "<p style='color: red;'>$erro</p>"; ?>

        <form method="POST" enctype="multipart/form-data">

          <input type="file" name="nova_foto" accept="image/*" required>
          <button class="botao" type="submit">Atualizar</button>

        </div>

          <div class="editarleft">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome">

            <label for="apelido">Apelido</label>
            <input type="text" id="apelido" name="apelido" >


            <label for="email">E-mail</label>
            <input type="email" id="email" name="email">

          </div>

          <div class="editarright">
            <label for="sobrenome">Sobrenome</label>
            <input type="text" id="sobrenome" name="sobrenome">


            <label for="telefone">Número de Telefone</label>
            <input type="tel" id="telefone" name="telefone">


            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco">
          </div>

        </div>

        <button class="confirmar">CONFIRMAR</button>

          
      </form>
  </div>
</div>

<div class="quest">
        <img src="assets/img/moçadebraçoesabertosaoarlivre.jpg" alt="imagem">

        <div class="questDown">
          <h4>O que é importante na hora de viajar?</h4>

          <h5>Responda a 5 perguntas simples sobre as suas preferências e nos ajude a personalizar a sua proxima viagem</h5>

          <button>Começar</button>
        </div>
    </div>

<script src="https://unpkg.com/@phosphor-icons/web"></script>
</script>