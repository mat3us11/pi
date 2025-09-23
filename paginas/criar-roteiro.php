<?php
session_start();
require_once '../includes/config.php'; // aqui fica sua conexão com o banco (variável $conn)

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../paginas/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $nome = $_POST['nome_rota'] ?? '';
    $descricao = $_POST['descricao_rota'] ?? '';
    $ponto_partida = $_POST['ponto_partida'] ?? '';
    $destino = $_POST['destino'] ?? '';
    $categorias = isset($_POST['categorias']) ? implode(',', $_POST['categorias']) : '';
    $paradas = isset($_POST['paradas']) ? json_encode($_POST['paradas']) : null;
    
    $capa = null;
    if (isset($_FILES['foto_capa']) && $_FILES['foto_capa']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto_capa']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'capa_' . $usuario_id . '_' . time() . '.' . $ext;
        $destinoUpload = '../uploads/geral' . $nomeArquivo;

        if (move_uploaded_file($_FILES['foto_capa']['tmp_name'], $destinoUpload)) {
            $capa = $destinoUpload;
        }
    }
    $sql = "INSERT INTO rota 
            (usuario_id, nome, descricao, categorias, ponto_partida, destino, paradas, capa)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([$usuario_id, $nome, $descricao, $categorias, $ponto_partida, $destino, $paradas, $capa]);
        header("Location: roteiro.php?sucesso=1");
        exit;
    } catch (PDOException $e) {
        echo "Erro ao salvar rota: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Roteiro</title>
  <link rel="stylesheet" href="../assets/css/header.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/criar-roteiro.css">
  <script defer src="../assets/js/modal.js"></script>
  <script defer src="../assets/js/criar-roteiro.js"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
  <?php include '../includes/header.php'; ?>

  <!-- Formulário -->
  <form action="" method="POST" enctype="multipart/form-data">
    <div class="container-capa">
      <div class="capa" onclick="document.getElementById('foto-capa').click()">
        <div class="placeholder">
          Faça upload da capa do seu local <i class="ph ph-upload-simple"></i>
        </div>
        <img id="preview" alt="Prévia da imagem">
        <input type="file" id="foto-capa" name="foto_capa" accept="image/*" class="hidden-file">
      </div>
    </div>

    <div class="informacoes-rota">
      <div class="informacoes-soltas">
        <label for="nome_rota">Nome da rota</label>
        <input type="text" name="nome_rota" id="nome_rota" placeholder="Nome (Obrigatório)" required>
      </div>

      <div class="informacoes-soltas">
        <label for="descricao_rota">Descrição</label>
        <textarea name="descricao_rota" id="descricao_rota" placeholder="Descrição da rota (Obrigatório)" required></textarea>
      </div>

      <div class="informacoes-soltas">
        <label>Categorias</label>
        <div class="dropdown">
          <div class="dropdown-header" onclick="toggleDropdown()">
            <span id="dropdown-text">Selecione uma ou mais categorias</span>
            <i class="ph ph-caret-down"></i>
          </div>
          <div class="dropdown-content" id="dropdown-content">
            <label><input type="checkbox" name="categorias[]" value="cultural"> Cultural</label>
            <label><input type="checkbox" name="categorias[]" value="aventura"> Aventura</label>
            <label><input type="checkbox" name="categorias[]" value="gastronomica"> Gastronômica</label>
            <label><input type="checkbox" name="categorias[]" value="ecologica"> Ecológica</label>
            <label><input type="checkbox" name="categorias[]" value="citytour"> City Tour</label>
          </div>
        </div>
      </div>
    </div>

    <div class="adicionar-rota">
      <h3>Adicionar Rota</h3>

      <div class="rota-item">
        <label><i class="ph ph-arrow-circle-up"></i> Ponto de Partida</label>
        <input type="text" id="pontoPartida" name="ponto_partida" placeholder="Localização" required>
      </div>

      <div class="rota-item">
        <label><i class="ph ph-map-pin"></i> Destino</label>
        <input type="text" id="destino" name="destino" placeholder="Localização" required>
      </div>

      <div id="paradas"></div>

      <button type="button" id="btn-add-parada" class="btn-discreto">
        + Adicionar Parada
      </button>

      <button type="submit" class="btn-principal">Publicar</button>
    </div>
  </form>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
