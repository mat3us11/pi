<?php
// paginas/criar-roteiro.php
session_start();
require_once '../includes/config.php';
require_once '../includes/utils_imagens.php';
require_once '../includes/utils_overpass.php';

/* ===== Token anti-reenvio (CSRf/simple re-submit) ===== */
if (empty($_SESSION['form_token'])) {
  $_SESSION['form_token'] = bin2hex(random_bytes(16));
}

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../paginas/login.php");
  exit;
}

$MAPBOX_TOKEN = getenv('MAPBOX_TOKEN') ?: '';
$BBOX_SP = "-53.0,-25.5,-44.0,-19.0";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* Throttle 3s */
    if (isset($_SESSION['last_submit_at']) && (time() - $_SESSION['last_submit_at']) < 3) {
      header("Location: roteiro.php?msg=aguarde");
      exit;
    }
    $_SESSION['last_submit_at'] = time();

    /* Valida token (consumível) */
    $token = $_POST['form_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['form_token'], $token)) {
      http_response_code(400);
      die("Formulário inválido. Recarregue a página.");
    }
    unset($_SESSION['form_token']);

    $usuario_id    = $_SESSION['usuario_id'];
    $nome          = trim($_POST['nome_rota'] ?? '');
    $descricao     = trim($_POST['descricao_rota'] ?? '');
    $ponto_partida = trim($_POST['ponto_partida'] ?? '');
    $destino       = trim($_POST['destino'] ?? '');
    $categorias    = isset($_POST['categorias']) ? implode(',', $_POST['categorias']) : '';

    /* ===== Upload capa ===== */
    $capa = null;
    if (!empty($_FILES['foto_capa']['name']) && $_FILES['foto_capa']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_capa']['name'], PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext);
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif','jfif'])) $ext = 'jpg';
        $dir = realpath(__DIR__ . '/../uploads/geral');
        if (!$dir) { @mkdir(__DIR__ . '/../uploads/geral', 0775, true); $dir = realpath(__DIR__ . '/../uploads/geral'); }
        $nomeArquivo = 'capa_' . $usuario_id . '_' . time() . '.' . $ext;
        $destAbs = $dir . DIRECTORY_SEPARATOR . $nomeArquivo;
        $destRel = '../uploads/geral/' . $nomeArquivo;
        if (move_uploaded_file($_FILES['foto_capa']['tmp_name'], $destAbs)) {
          @chmod($destAbs, 0644);
          $capa = $destRel;
        }
    }

    /* ===== Paradas ===== */
    $paradasPost = $_POST['paradas'] ?? [];
    if (!is_array($paradasPost)) $paradasPost = [];
    $cityForImages = $destino ?: $ponto_partida;

    // 1) Normaliza (preferindo JSON do front com lat/lon)
    $norm = [];
    foreach ($paradasPost as $raw) {
      $item = json_decode($raw, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($item) && !empty($item['nome'])) {
        $norm[] = [
          'nome' => trim((string)$item['nome']),
          'lat'  => isset($item['lat']) ? (float)$item['lat'] : null,
          'lon'  => isset($item['lon']) ? (float)$item['lon'] : null
        ];
      } else {
        $name = trim((string)$raw);
        if ($name !== '') $norm[] = ['nome'=>$name, 'lat'=>null, 'lon'=>null];
      }
    }

    // 2) Geocode só se faltarem coords e houver token
    if ($MAPBOX_TOKEN) {
      foreach ($norm as &$p) {
        if ($p['lat'] === null || $p['lon'] === null) {
          $coords = mapboxForward($p['nome'], $MAPBOX_TOKEN, $BBOX_SP);
          if ($coords) { $p['lat'] = $coords['lat']; $p['lon'] = $coords['lon']; }
        }
      }
      unset($p);
    }

    // 3) Imagens em lote
    $names = array_column($norm, 'nome');
    $imgMap = getPoiImagesBatch($conn, $names, $cityForImages);

    // 4) Monta final
    $paradasEnriquecidas = [];
    foreach ($norm as $p) {
      $paradasEnriquecidas[] = [
        'nome' => $p['nome'],
        'lat'  => $p['lat'],
        'lon'  => $p['lon'],
        'image_url' => $imgMap[$p['nome']] ?? null
      ];
    }
    $paradasJson = !empty($paradasEnriquecidas)
      ? json_encode($paradasEnriquecidas, JSON_UNESCAPED_UNICODE)
      : null;

    /* ===== Insert ===== */
    $sql = "INSERT INTO rota
            (usuario_id, nome, descricao, categorias, ponto_partida, destino, paradas, capa)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([$usuario_id, $nome, $descricao, $categorias, $ponto_partida, $destino, $paradasJson, $capa]);
        header("Location: roteiro.php?sucesso=1");
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Erro ao salvar rota: " . htmlspecialchars($e->getMessage());
        exit;
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
  <link rel="stylesheet" href="../assets/css/sugerir-pois.css">
  <script defer src="../assets/js/modal.js"></script>
  <script defer src="../assets/js/criar-roteiro.js"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
  <?php include '../includes/header.php'; ?>

  <form action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="form_token" value="<?= htmlspecialchars($_SESSION['form_token']) ?>">

    <div class="container-capa">
      <div class="capa" onclick="document.getElementById('foto-capa').click()">
        <div class="placeholder">Faça upload da capa do seu local <i class="ph ph-upload-simple"></i></div>
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
        <button type="button" class="btn-discreto" id="btn-sugerir-pois" style="margin-top:.5rem">🔎 Sugerir locais (OSM)</button>
      </div>

      <div id="paradas"></div>

      <button type="button" id="btn-add-parada" class="btn-discreto">+ Adicionar Parada</button>
      <button type="submit" class="btn-principal">Publicar</button>
    </div>
  </form>

  <!-- Modal: Sugerir locais -->
<div id="modal-sugestoes" class="modal" style="display:none">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Locais sugeridos</h3>
      <button type="button" class="close-modal" id="close-modal" aria-label="Fechar">×</button>
    </div>
    <div class="modal-body">
      <div id="sugestoes-list">
        <!-- “Carregando...” / grid de cards aparece aqui pelo JS -->
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-discreto" id="close-modal-2">Fechar</button>
      <button type="button" class="btn-prim" id="add-selecionados">Adicionar selecionados</button>
    </div>
  </div>
</div>

<script>
  // garante que o segundo botão "Fechar" funcione
  (function(){
    const modal = document.getElementById('modal-sugestoes');
    const close1 = document.getElementById('close-modal');
    const close2 = document.getElementById('close-modal-2');
    function closeModal(){ modal.style.display = 'none'; }
    if (close2) close2.addEventListener('click', closeModal);
    // close1 é tratado no arquivo criar-roteiro.js também
  })();
</script>


  <?php include '../includes/footer.php'; ?>
</body>
</html>
