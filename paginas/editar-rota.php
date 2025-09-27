<?php
session_start();
require_once '../includes/config.php';

// 🔒 Bloqueia não logados
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../paginas/login.php");
  exit;
}

// Verifica ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  http_response_code(400);
  die("Rota inválida.");
}

$id_rota = (int) $_GET['id'];
$usuarioLogadoId = (int) $_SESSION['usuario_id'];

/* ===== Carrega rota e valida ownership ===== */
$sql = "SELECT r.id, r.usuario_id, r.nome, r.descricao, r.categorias, r.capa,
               r.ponto_partida, r.destino, r.paradas, r.criado_em,
               u.nome AS criador
        FROM rota r
        JOIN usuario u ON r.usuario_id = u.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_rota]);
$rota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rota) {
  http_response_code(404);
  die("Rota não encontrada.");
}
if ($usuarioLogadoId !== (int)$rota['usuario_id']) {
  http_response_code(403);
  die("Você não tem permissão para editar esta rota.");
}

$erro = '';
$sucesso = '';

/* ===== Salvar (POST) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome           = trim($_POST['nome_rota'] ?? '');
  $descricao      = trim($_POST['descricao_rota'] ?? '');
  $ponto_partida  = trim($_POST['ponto_partida'] ?? '');
  $destino        = trim($_POST['destino'] ?? '');
  $categorias     = isset($_POST['categorias']) ? implode(',', (array)$_POST['categorias']) : '';
  $paradasArr     = isset($_POST['paradas']) ? (array)$_POST['paradas'] : [];
  $paradasArr     = array_values(array_filter(array_map('trim', $paradasArr)));
  $paradasJson    = !empty($paradasArr) ? json_encode($paradasArr, JSON_UNESCAPED_UNICODE) : null;

  if ($nome === '' || $descricao === '' || $ponto_partida === '' || $destino === '') {
    $erro = "Preencha nome, descrição, ponto de partida e destino.";
  }

  // Upload da capa (opcional)
  $capaPath = $rota['capa']; // mantém atual por padrão
  if (!$erro && isset($_FILES['foto_capa']) && $_FILES['foto_capa']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['foto_capa']['error'] !== UPLOAD_ERR_OK) {
      $erro = "Erro no upload da imagem (código {$_FILES['foto_capa']['error']}).";
    } else {
      $tmp  = $_FILES['foto_capa']['tmp_name'];
      $size = (int)$_FILES['foto_capa']['size'];
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime  = finfo_file($finfo, $tmp);
      finfo_close($finfo);

      $permitidos = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
      if (!isset($permitidos[$mime])) {
        $erro = "Formato de imagem inválido. Use JPG, PNG ou WEBP.";
      } elseif ($size > 4*1024*1024) {
        $erro = "A imagem deve ter no máximo 4MB.";
      } else {
        // pasta de upload (mesma ideia da sua criação, corrigindo a barra que faltava)
        $dir = realpath(__DIR__ . '/../uploads/geral');
        if ($dir === false) {
          $tentativa = __DIR__ . '/../uploads/geral';
          if (!is_dir($tentativa)) mkdir($tentativa, 0775, true);
          $dir = realpath($tentativa);
        }
        if ($dir === false) {
          $erro = "Falha ao preparar pasta de upload.";
        } else {
          $ext = $permitidos[$mime];
          $nomeArquivo = 'capa_' . $usuarioLogadoId . '_' . time() . '.' . $ext;
          $destinoUpload = $dir . '/' . $nomeArquivo; // <- com barra

          if (!move_uploaded_file($_FILES['foto_capa']['tmp_name'], $destinoUpload)) {
            $erro = "Não foi possível salvar a imagem enviada.";
          } else {
            // caminho relativo para servir (ajuste conforme sua estrutura)
            $capaPath = '../uploads/geral/' . $nomeArquivo;
          }
        }
      }
    }
  }

  if (!$erro) {
    $upd = "UPDATE rota SET
              nome = :nome,
              descricao = :descricao,
              categorias = :categorias,
              ponto_partida = :ponto_partida,
              destino = :destino,
              paradas = :paradas,
              capa = :capa
            WHERE id = :id AND usuario_id = :uid";
    $st = $conn->prepare($upd);
    $ok = $st->execute([
      ':nome'           => $nome,
      ':descricao'      => $descricao,
      ':categorias'     => $categorias,
      ':ponto_partida'  => $ponto_partida,
      ':destino'        => $destino,
      ':paradas'        => $paradasJson,
      ':capa'           => $capaPath,
      ':id'             => $id_rota,
      ':uid'            => $usuarioLogadoId
    ]);

    if ($ok) {
      $sucesso = "Rota atualizada com sucesso!";
      // reflete no array $rota para reexibir no form
      $rota['nome'] = $nome;
      $rota['descricao'] = $descricao;
      $rota['categorias'] = $categorias;
      $rota['ponto_partida'] = $ponto_partida;
      $rota['destino'] = $destino;
      $rota['paradas'] = $paradasJson;
      $rota['capa'] = $capaPath;
    } else {
      $erro = "Não foi possível salvar as alterações. Tente novamente.";
    }
  }
}

/* ===== Valores para o form ===== */
$capaAtual = !empty($rota['capa']) ? $rota['capa'] : '../assets/img/placeholder.jpg';
$categoriasMarcadas = array_filter(array_map('trim', explode(',', (string)$rota['categorias'])));
$paradas = $rota['paradas'] ? json_decode($rota['paradas'], true) : [];
if (!is_array($paradas)) $paradas = [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Roteiro</title>

  <link rel="stylesheet" href="../assets/css/header.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/editar-roteiros.css">
  <script defer src="../assets/js/editar-roteiros.js"></script>
  <!-- você pode usar o mesmo CSS do criar, ou deixar estes estilos leves -->

  <script defer src="../assets/js/modal.js"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
  <?php include '../includes/header.php'; ?>

  <div class="wrap">
    <div class="card">
      <div class="titulo">Editar Roteiro</div>
      <div class="meta">Criado por <?= htmlspecialchars($rota['criador']) ?> <?php if(!empty($rota['criado_em'])): ?> • <?= date('d/m/Y', strtotime($rota['criado_em'])) ?><?php endif; ?></div>

      <?php if ($sucesso): ?><div class="alert alert--ok"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>
      <?php if ($erro): ?><div class="alert alert--err"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="grid">
        <!-- Capa -->
        <div class="full">
          <label>Capa</label>
          <div class="thumb">
            <img id="preview" src="<?= htmlspecialchars($capaAtual) ?>" alt="Prévia da capa">
            <input type="file" id="foto-capa" name="foto_capa" accept="image/*">
          </div>
        </div>

        <!-- Nome -->
        <div class="full">
          <label for="nome_rota">Nome da rota</label>
          <input type="text" name="nome_rota" id="nome_rota" value="<?= htmlspecialchars($rota['nome']) ?>" placeholder="Nome (Obrigatório)" required>
        </div>

        <!-- Descrição -->
        <div class="full">
          <label for="descricao_rota">Descrição</label>
          <textarea name="descricao_rota" id="descricao_rota" placeholder="Descrição da rota (Obrigatório)" required><?= htmlspecialchars($rota['descricao']) ?></textarea>
        </div>

        <!-- Categorias (dropdown igual ao criar) -->
        <div class="full">
          <label>Categorias</label>
          <div class="dropdown">
            <div class="dropdown-header" onclick="toggleDropdown()">
              <span id="dropdown-text">Selecione uma ou mais categorias</span>
              <i class="ph ph-caret-down"></i>
            </div>
            <div class="dropdown-content" id="dropdown-content">
              <?php
                $catalogo = [
                  'cultural' => 'Cultural',
                  'aventura' => 'Aventura',
                  'gastronomica' => 'Gastronômica',
                  'ecologica' => 'Ecológica',
                  'citytour' => 'City Tour',
                ];
                foreach ($catalogo as $val => $label):
                  $checked = in_array($val, $categoriasMarcadas) ? 'checked' : '';
              ?>
              <label><input type="checkbox" name="categorias[]" value="<?= $val ?>" <?= $checked ?>> <?= $label ?></label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Ponto de partida -->
        <div class="rota-item">
          <label for="pontoPartida"><i class="ph ph-arrow-circle-up"></i> Ponto de Partida</label>
          <input type="text" id="pontoPartida" name="ponto_partida" value="<?= htmlspecialchars($rota['ponto_partida']) ?>" placeholder="Localização" required>
        </div>

        <!-- Destino -->
        <div class="rota-item">
          <label for="destino"><i class="ph ph-map-pin"></i> Destino</label>
          <input type="text" id="destino" name="destino" value="<?= htmlspecialchars($rota['destino']) ?>" placeholder="Localização" required>
        </div>

        <!-- Paradas -->
        <div class="full">
          <label>Paradas</label>
          <div id="paradas">
            <?php if (!empty($paradas)): ?>
              <?php foreach ($paradas as $i => $p): ?>
                <div class="rota-item">
                  <input type="text" id="parada<?= $i+1 ?>" name="paradas[]" value="<?= htmlspecialchars($p) ?>" placeholder="Localização">
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="rota-item">
                <input type="text" id="parada1" name="paradas[]" placeholder="Localização">
              </div>
            <?php endif; ?>
          </div>

          <button type="button" id="btn-add-parada" class="btn btn--ghost" style="margin-top:8px;">+ Adicionar Parada</button>
        </div>

        <!-- Ações -->
        <div class="full btns">
          <a href="ver-rota.php?id=<?= (int)$rota['id'] ?>" class="btn btn--ghost">Cancelar</a>
          <button type="submit" class="btn btn--primary">Salvar alterações</button>
        </div>
      </form>
    </div>

    <div class="btns" style="margin-top:10px;">
      <a class="btn btn--ghost" href="ver-rota.php?id=<?= (int)$rota['id'] ?>">← Voltar à rota</a>
    </div>
  </div>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
