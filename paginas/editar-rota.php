<?php
// paginas/editar-rota.php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
require_once '../includes/config.php';

/* ---------- Helpers ---------- */
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function resolve_upload_path(?string $dbPath): ?string {
  if (!$dbPath) return null;
  if (preg_match('#^https?://#i', $dbPath)) return null;
  $projectRoot = realpath(__DIR__ . '/..'); 
  if ($projectRoot === false) return null;
  $p = str_replace('\\', '/', trim($dbPath));
  $p = preg_replace('#^\./+#', '', $p);
  while (strpos($p, '../') === 0) { $p = substr($p, 3); }
  if (preg_match('#^/|^[A-Za-z]:/#', $p)) return $p;
  if (strpos($p, 'uploads/') === 0) return $projectRoot . '/' . $p;
  return $projectRoot . '/' . ltrim($p, '/');
}

/* Normaliza categorias livres -> set canônico */
function normalize_categorias($cats): array {
  $canon = ['cultural','aventura','gastronomica','ecologica','citytour'];
  $mapSyn = [
    'cultural'      => ['cultural','cultura'],
    'aventura'      => ['aventura','adrenalina','radical'],
    'gastronomica'  => ['gastronomica','gastronômica','comida','culinaria','culinária','food'],
    'ecologica'     => ['ecologica','ecológica','natureza','eco','parque','verde'],
    'citytour'      => ['citytour','city tour','turismo','city','centro','pontos turisticos','pontos turísticos','turístico','turisticos']
  ];
  $result = [];

  $tokens = [];
  if (is_array($cats)) $tokens = $cats;
  else { $parts = preg_split('/[,;|]/', (string)$cats); $tokens = array_map('trim', $parts); }

  $xform = function($s){
    $s = mb_strtolower($s ?? '', 'UTF-8');
    $s = strtr($s, ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
                    'é'=>'e','ê'=>'e','è'=>'e','ë'=>'e',
                    'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i',
                    'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
                    'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u',
                    'ç'=>'c']);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
  };

  foreach ($tokens as $raw) {
    if ($raw === '' || $raw === null) continue;
    $t = $xform($raw);
    foreach ($mapSyn as $key => $syns) {
      foreach ($syns as $syn) {
        if ($t === $xform($syn) || str_starts_with($t, $xform($syn))) { $result[$key]=true; break 2; }
      }
      if ($t === $key) { $result[$key]=true; break; }
    }
  }
  return array_values(array_filter($canon, fn($c) => isset($result[$c])));
}

/* ---------- Auth ---------- */
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../paginas/login.php");
  exit;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  http_response_code(400);
  die("Rota inválida.");
}
$id_rota = (int) $_GET['id'];
$usuarioLogadoId = (int) $_SESSION['usuario_id'];

/* Salva contexto para o refino (fallback por sessão) */
$_SESSION['refine_ctx_id'] = $id_rota;

/* ---------- Carrega rota ---------- */
$sql = "SELECT r.id, r.usuario_id, r.nome, r.descricao, r.categorias, r.capa,
               r.ponto_partida, r.destino, r.paradas, r.criado_em,
               u.nome AS criador
        FROM rota r
        JOIN usuario u ON r.usuario_id = u.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_rota]);
$rota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rota) { http_response_code(404); die("Rota não encontrada."); }
if ($usuarioLogadoId !== (int)$rota['usuario_id']) { http_response_code(403); die("Você não tem permissão para editar esta rota."); }

/* ---------- CSRF ---------- */
if (empty($_SESSION['csrf_edit']))   $_SESSION['csrf_edit']   = bin2hex(random_bytes(16));
if (empty($_SESSION['csrf_refine'])) $_SESSION['csrf_refine'] = bin2hex(random_bytes(16));
$csrf_edit   = $_SESSION['csrf_edit'];
$csrf_refine = $_SESSION['csrf_refine'];

/* Evita cache de tokens antigos */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$erro = ''; 
$sucesso = '';

/* ====== Retorno do refino: aplica sugestões no formulário ====== */
$aplicouIA = false;
if (!empty($_SESSION['refine_apply'][$id_rota]) && isset($_GET['from']) && $_GET['from'] === 'ia') {
  $ia = $_SESSION['refine_apply'][$id_rota];

  $rota['nome']          = $ia['nome']          ?? $rota['nome'];
  $rota['descricao']     = $ia['descricao']     ?? $rota['descricao'];
  if (isset($ia['categorias'])) {
    $norm = normalize_categorias($ia['categorias']);
    $rota['categorias'] = implode(',', $norm);
  }
  $rota['ponto_partida'] = $ia['ponto_partida'] ?? $rota['ponto_partida'];
  $rota['destino']       = $ia['destino']       ?? $rota['destino'];

  if (isset($ia['paradas']) && is_array($ia['paradas'])) {
    $rota['paradas'] = json_encode($ia['paradas'], JSON_UNESCAPED_UNICODE);
  }

  $aplicouIA = true;
}

/* ---------- Salvar (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf_edit'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(400); die('Token inválido.');
  }

  $nome           = trim($_POST['nome_rota'] ?? '');
  $descricao      = trim($_POST['descricao_rota'] ?? '');
  $ponto_partida  = trim($_POST['ponto_partida'] ?? '');
  $destino        = trim($_POST['destino'] ?? '');

  $categoriasPost = isset($_POST['categorias']) ? (array)$_POST['categorias'] : [];
  $categoriasNorm = normalize_categorias($categoriasPost);
  $categorias     = implode(',', $categoriasNorm);

  // Paradas simples (mantém compat)
  $paradasArr = isset($_POST['paradas']) ? (array)$_POST['paradas'] : [];
  $paradasArr = array_map(function($p){
    if (is_array($p)) {
      $nome = trim((string)($p['nome'] ?? ''));
      if ($nome === '') return null;
      return [
        'nome' => $nome,
        'lat'  => ($p['lat'] === '' ? null : (isset($p['lat']) ? (float)$p['lat'] : null)),
        'lon'  => ($p['lon'] === '' ? null : (isset($p['lon']) ? (float)$p['lon'] : null)),
        'image_url' => $p['image_url'] ?? null
      ];
    }
    $nome = trim((string)$p);
    if ($nome === '') return null;
    return ['nome'=>$nome, 'lat'=>null, 'lon'=>null, 'image_url'=>null];
  }, $paradasArr);
  $paradasArr = array_values(array_filter($paradasArr, fn($x) => !is_null($x)));
  $paradasJson = !empty($paradasArr) ? json_encode($paradasArr, JSON_UNESCAPED_UNICODE) : null;

  if ($nome === '' || $descricao === '' || $ponto_partida === '' || $destino === '') {
    $erro = "Preencha nome, descrição, ponto de partida e destino.";
  }

  $capaPath = $rota['capa'];
  $oldCapa  = $rota['capa'];

  if (!$erro && isset($_FILES['foto_capa']) && $_FILES['foto_capa']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['foto_capa']['error'] !== UPLOAD_ERR_OK) {
      $erro = "Erro no upload da imagem (código {$_FILES['foto_capa']['error']}).";
    } else {
      $tmp  = $_FILES['foto_capa']['tmp_name'];
      $size = (int)$_FILES['foto_capa']['size'];
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime  = finfo_file($finfo, $tmp);
      finfo_close($finfo);

      $permitidos = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','image/jpg'=>'jpg'];
      if (!isset($permitidos[$mime])) {
        $erro = "Formato de imagem inválido. Use JPG, PNG, WEBP ou GIF.";
      } elseif ($size > 6*1024*1024) {
        $erro = "A imagem deve ter no máximo 6MB.";
      } else {
        $dir = __DIR__ . '/../uploads/geral';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        if (!is_dir($dir) || !is_writable($dir)) {
          $erro = "Pasta de upload não disponível.";
        } else {
          $ext = $permitidos[$mime];
          $nomeArquivo = 'capa_' . $usuarioLogadoId . '_' . time() . '.' . $ext;
          $destinoUpload = rtrim($dir, '/\\') . '/' . $nomeArquivo;

          if (!move_uploaded_file($_FILES['foto_capa']['tmp_name'], $destinoUpload)) {
            $erro = "Não foi possível salvar a imagem enviada.";
          } else {
            $oldAbs = resolve_upload_path($oldCapa);
            if ($oldAbs && is_file($oldAbs) && file_exists($oldAbs)) { @unlink($oldAbs); }
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
      $rota['nome'] = $nome;
      $rota['descricao'] = $descricao;
      $rota['categorias'] = $categorias;
      $rota['ponto_partida'] = $ponto_partida;
      $rota['destino'] = $destino;
      $rota['paradas'] = $paradasJson;
      $rota['capa'] = $capaPath;

      if (!empty($_SESSION['refine_apply'][$id_rota])) {
        unset($_SESSION['refine_apply'][$id_rota]);
      }
    } else {
      $erro = "Não foi possível salvar as alterações. Tente novamente.";
    }
  }
}

/* ---------- Valores p/ form ---------- */
$capaAtual = !empty($rota['capa']) ? $rota['capa'] : '../assets/img/placeholder_rosseio.png';
$categoriasMarcadas = normalize_categorias((string)$rota['categorias']);

$paradas = $rota['paradas'] ? json_decode($rota['paradas'], true) : [];
if (is_array($paradas)) {
  $paradas = array_values(array_map(function($p){
    if (is_array($p)) return (string)($p['nome'] ?? '');
    return (string)$p;
  }, $paradas));
} else { $paradas = []; }
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
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

  <?php include '../includes/header.php'; ?>

  <div class="wrap">
    <div class="card">
      <div class="titulo">Editar Roteiro</div>
      <div class="meta">
        Criado por <?= h($rota['criador']) ?>
        <?php if(!empty($rota['criado_em'])): ?> • <?= date('d/m/Y', strtotime($rota['criado_em'])) ?><?php endif; ?>
      </div>

      <?php if ($sucesso): ?><div class="alert alert--ok"><?= h($sucesso) ?></div><?php endif; ?>
      <?php if ($erro): ?><div class="alert alert--err"><?= h($erro) ?></div><?php endif; ?>
      <?php if ($aplicouIA && !$erro): ?>
        <div class="alert alert--ok">Sugestões da IA foram aplicadas ao formulário. Revise e clique em <strong>Salvar alterações</strong>.</div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="grid">
        <input type="hidden" name="csrf" value="<?= h($csrf_edit) ?>">

        <div class="full">
          <label>Capa</label>
          <div class="thumb">
            <img id="preview" src="<?= h($capaAtual) ?>" alt="Prévia da capa">
            <input type="file" id="foto-capa" name="foto_capa" accept="image/*">
          </div>
        </div>

        <div class="full">
          <label for="nome_rota">Nome da rota</label>
          <input type="text" name="nome_rota" id="nome_rota" value="<?= h($rota['nome']) ?>" placeholder="Nome (Obrigatório)" required>
        </div>

        <div class="full">
          <label for="descricao_rota">Descrição</label>
          <textarea name="descricao_rota" id="descricao_rota" placeholder="Descrição da rota (Obrigatório)" required><?= h($rota['descricao']) ?></textarea>
        </div>

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
              <label><input type="checkbox" name="categorias[]" value="<?= h($val) ?>" <?= $checked ?>> <?= h($label) ?></label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="rota-item">
          <label for="pontoPartida"><i class="ph ph-arrow-circle-up"></i> Ponto de Partida</label>
          <input type="text" id="pontoPartida" name="ponto_partida" value="<?= h($rota['ponto_partida']) ?>" placeholder="Localização" required>
        </div>

        <div class="rota-item">
          <label for="destino"><i class="ph ph-map-pin"></i> Destino</label>
          <input type="text" id="destino" name="destino" value="<?= h($rota['destino']) ?>" placeholder="Localização" required>
        </div>

        <div class="full">
          <label>Paradas</label>
          <div id="paradas">
            <?php if (!empty($paradas)): ?>
              <?php foreach ($paradas as $i => $p): ?>
                <div class="rota-item">
                  <input type="text" id="parada<?= $i+1 ?>" name="paradas[]" value="<?= h($p) ?>" placeholder="Localização">
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

        <div class="full btns">
          <a href="ver-rota.php?id=<?= (int)$rota['id'] ?>" class="btn btn--ghost">Cancelar</a>
          <button type="submit" class="btn btn--primary">Salvar alterações</button>
        </div>
      </form>
    </div>

    <!-- Refino com IA (aplica no mesmo formulário desta rota) -->
    <div class="card" style="margin-top:16px;">
      <div class="titulo">Refinar com IA</div>
      <p style="margin:6px 0 12px;">
        O refino <strong>aplica as sugestões diretamente neste formulário</strong>. Depois é só clicar em <strong>Salvar alterações</strong>.
      </p>

      <form method="POST" action="../processos/refinar-roteiro-ia.php" class="grid" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= h($csrf_refine) ?>">
        <input type="hidden" name="refinar_de" value="<?= (int)$rota['id'] ?>">
        <input type="hidden" name="apply_to_form" value="1">

        <div class="full">
          <label for="pedido_ia">O que melhorar / restrições</label>
          <textarea id="pedido_ia" name="pedido" rows="4" placeholder="Ex.: reduzir custo, priorizar locais ao ar livre, incluir café especial, horário entre 10h–18h"></textarea>
        </div>

        <div class="rota-item">
          <label for="ponto_partida_ia"><i class="ph ph-arrow-circle-up"></i> Ponto de Partida (base)</label>
          <input type="text" id="ponto_partida_ia" name="ponto_partida" value="<?= h($rota['ponto_partida']) ?>">
        </div>

        <div class="rota-item">
          <label for="destino_ia"><i class="ph ph-map-pin"></i> Destino (base)</label>
          <input type="text" id="destino_ia" name="destino" value="<?= h($rota['destino']) ?>">
        </div>

        <div class="full">
          <label for="categorias_ia">Categorias (opcional)</label>
          <input type="text" id="categorias_ia" name="categorias" value="<?= h(implode(',', $categoriasMarcadas)) ?>" placeholder="cultural,gastronomica,citytour">
        </div>

        <div class="full btns">
          <button type="submit" class="btn btn--primary">Refinar e preencher formulário</button>
        </div>
      </form>
    </div>

    <div class="btns" style="margin-top:10px;">
      <a class="btn btn--ghost" href="ver-rota.php?id=<?= (int)$rota['id'] ?>">← Voltar à rota</a>
    </div>
  </div>

<div id="loading-overlay" class="loading-overlay" aria-hidden="true">
  <div class="loading-box" role="status" aria-live="assertive">
    <div class="spinner" aria-hidden="true"></div>
    <p>Refinando seu roteiro com IA...</p>
  </div>
</div>


<script>
  (function () {
    const form = document.querySelector('form[action="../processos/refinar-roteiro-ia.php"]');
    const overlay = document.getElementById('loading-overlay');
    const submitBtn = form?.querySelector('button[type="submit"]');

    if (!form || !overlay) return;

    form.addEventListener('submit', function () {
      if (!form.checkValidity()) return;

      overlay.classList.add('is-active');
      document.body.classList.add('loading');

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.dataset.originalText = submitBtn.textContent;
        submitBtn.textContent = 'Gerando...';
      }
    });

    window.addEventListener('pageshow', function (e) {
      if (e.persisted) {
        overlay.classList.remove('is-active');
        document.body.classList.remove('loading');
        if (submitBtn && submitBtn.dataset.originalText) {
          submitBtn.disabled = false;
          submitBtn.textContent = submitBtn.dataset.originalText;
        }
      }
    });
  })();
</script>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
