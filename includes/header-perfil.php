<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

require_once "config.php";

$mensagem = '';
$erro = '';
$id_usuario = $_SESSION["usuario_id"] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $id_usuario) {
  $nome = trim($_POST["nome"] ?? '');
  $apelido = trim($_POST["apelido"] ?? '');
  $endereco = trim($_POST["endereco"] ?? '');
  $preferencia_nome_apelido = isset($_POST["preferencia_nome_apelido"]) ? 1 : 0;

  $novo_nome_foto = null;

  if (isset($_FILES["nova_foto"]) && $_FILES["nova_foto"]["error"] === UPLOAD_ERR_OK) {
    $foto = $_FILES["nova_foto"];
    $extensao = strtolower(pathinfo($foto["name"], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extensao, $permitidas)) {
      $erro = "Formato de imagem inválido. Use JPG, JPEG, PNG ou WEBP.";
    } else {
      $pasta = "uploads/";
      if (!is_dir($pasta)) mkdir($pasta, 0755, true);

      $nomeArquivo = "foto_" . $id_usuario . "_" . time() . "." . $extensao;
      $caminho = $pasta . $nomeArquivo;

      if (move_uploaded_file($foto["tmp_name"], $caminho)) {
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

        $novo_nome_foto = $caminho;
        $_SESSION["usuario_foto"] = $caminho;
      } else {
        $erro = "Erro ao mover o arquivo.";
      }
    }
  }

  if (!$erro) {
    $query = "UPDATE usuario SET nome = ?, apelido = ?, endereco = ?, preferencia_nome_apelido = ?" . ($novo_nome_foto ? ", foto_perfil = ?" : "") . " WHERE id = ?";
    if ($novo_nome_foto) {
      $params = [$nome, $apelido, $endereco, $preferencia_nome_apelido, $novo_nome_foto, $id_usuario];
    } else {
      $params = [$nome, $apelido, $endereco, $preferencia_nome_apelido, $id_usuario];
    }

    $stmt = $conn->prepare($query);
    if ($stmt->execute($params)) {
      $_SESSION['usuario_nome'] = $nome;
      $_SESSION['usuario_apelido'] = $apelido;
      $_SESSION['usuario_endereco'] = $endereco;
      $_SESSION['usuario_preferencia_nome_apelido'] = $preferencia_nome_apelido;
      $mensagem = "Perfil atualizado com sucesso!";
    } else {
      $erro = "Erro ao atualizar perfil.";
    }
  }
}

// Puxar dados para mostrar no formulário
$stmt = $conn->prepare("SELECT nome, apelido, endereco, foto_perfil, preferencia_nome_apelido FROM usuario WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$foto_perfil = $usuario['foto_perfil'] ?? './assets/img/imagem-padrao.png';
$nome_usuario = $usuario['nome'] ?? 'Usuário';
$email_usuario = $_SESSION['usuario_email'] ?? '';
$apelido_usuario = $usuario['apelido'] ?? '';
$endereco_usuario = $usuario['endereco'] ?? '';
$preferencia_nome_apelido = $usuario['preferencia_nome_apelido'] ?? 0;
?>

<header class="header-perfil">
  <div class="container header-container-perfil">
    <div class="perfil-top">
      <a href="index.php">
        <p class="logo">CAMP<i class="ph ph-tipi" style="transform: rotate(180deg); display: inline-block;"></i><span>IA</span></p>
      </a>
    </div>

    <div class="perfil-conteudo">
      <div class="perfil-info">
        <div class="foto-perfil-grande" style="background-image: url('<?php echo htmlspecialchars($foto_perfil); ?>');"></div>
        <div class="dados">
          <p class="nome-usuario">Olá, 
            <?php 
              echo ($preferencia_nome_apelido && !empty($apelido_usuario))
                ? htmlspecialchars($apelido_usuario)
                : htmlspecialchars($nome_usuario);
            ?>!
          </p>
          <div class="botoes-perfil">
            <a href="#" id="btn-editar-perfil" class="btn-perfil"><i class="ph ph-pencil-simple"></i> Editar Perfil</a>
            <a href="index.php" class="btn-perfil"><i class="ph ph-house"></i> Home</a>
            <a href="./passeios.php" class="btn-perfil"><i class="ph ph-bicycle"></i> Passeios</a>
            <a href="#" class="btn-perfil"><i class="ph ph-map-trifold"></i> Roteiros</a>
          </div>
        </div>
      </div>

      <div class="right"></div>
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
        <p class="saudacao">Olá, <?php 
  echo ($preferencia_nome_apelido && !empty($apelido_usuario)) 
    ? htmlspecialchars($apelido_usuario) 
    : htmlspecialchars($nome_usuario); 
?>!</p>

      <?php else: ?>
        <a href="cadastro.php"><button class="btn btn-outline">Cadastre-se</button></a>
        <a href="login.php"><button class="btn btn-fill">Login</button></a>
      <?php endif; ?>
    </div>

    <ul class="modal-nav">
      <li><a href="./perfil.php" class="nav-link"><i class="ph ph-user"></i> Perfil</a></li>
      <li><a href="./passeios.php" class="nav-link"><i class="ph ph-bicycle"></i> Passeios</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-map-trifold"></i> Roteiros</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-clock-counter-clockwise"></i> Histórico</a></li>
      <li><a href="#" class="nav-link"><i class="ph ph-question"></i> Dúvidas</a></li>
    </ul>
  </nav>
</div>

<div id="modal-editar" class="modal">
  <div class="modal-editar">
    <h3>Editar Perfil</h3>
    <span class="fechar" id="fechar-modal">&times;</span>

    <form method="POST" enctype="multipart/form-data">
      <div class="editar">
        <div class="foto">
          <div class="foto-perfil-grande" style="background-image: url('<?php echo htmlspecialchars($foto_perfil); ?>');"></div>
          <label for="nova_foto" class="custom-file-label">Upload Imagem</label>
          <input type="file" id="nova_foto" name="nova_foto" accept="image/*" class="hidden-file">
        </div>

        <div class="editarleft">
          <label for="nome">Nome</label>
          <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome_usuario); ?>" required>

          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email_usuario); ?>" disabled>
        </div>

        <div class="editarright">
          <label for="endereco">Endereço</label>
          <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($endereco_usuario); ?>">

           <label for="apelido">Apelido</label>
          <input type="text" id="apelido" name="apelido" value="<?php echo htmlspecialchars($apelido_usuario); ?>">

          <label>
            <input type="checkbox" name="preferencia_nome_apelido" value="1" <?php echo ($preferencia_nome_apelido) ? 'checked' : ''; ?>>
            Prefiro ser chamado pelo apelido
          </label>
        </div>
      </div>

      <button class="confirmar" type="submit">CONFIRMAR</button>
    </form>
  </div>
</div>

<div class="quest">
        <img src="./assets/img/moçadebraçoesabertosaoarlivre.jpg" alt="imagem">

        <div class="questDown">
          <h4>O que é importante na hora de viajar?</h4>

          <h5>Responda a 5 perguntas simples sobre as suas preferências e nos ajude a personalizar a sua proxima viagem</h5>

          <button>Começar</button>
        </div>
    </div>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script>
const btnEditar = document.getElementById('btn-editar-perfil');
const modalEditar = document.getElementById('modal-editar');
const fecharModal = document.getElementById('fechar-modal');

btnEditar.addEventListener('click', e => {
  e.preventDefault();
  modalEditar.style.display = 'block';
});

fecharModal.addEventListener('click', () => {
  modalEditar.style.display = 'none';
});

window.addEventListener('click', e => {
  if (e.target === modalEditar) {
    modalEditar.style.display = 'none';
  }
});
</script>



</script>