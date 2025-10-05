<?php
require_once __DIR__ . '/../includes/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
  http_response_code(400);
  echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1'><title>Cidade</title></head><body><main class='container'><p>Slug de cidade ausente.</p></main></body></html>";
  exit;
}

$stmtCidade = $conn->prepare("SELECT id, nome, estado, slug, descricao, capa FROM cidade WHERE slug = :slug LIMIT 1");
$stmtCidade->execute([':slug' => $slug]);
$cidade = $stmtCidade->fetch(PDO::FETCH_ASSOC);
if (!$cidade) {
  http_response_code(404);
  echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1'><title>Cidade</title></head><body><main class='container'><p>Cidade não encontrada.</p></main></body></html>";
  exit;
}

/* ===== checagem de admin robusta ===== */
$nivelSessao = $_SESSION['usuario_nivel'] ?? null;
if (!$nivelSessao) {
  if (!empty($_SESSION['usuario_id'])) {
    $q = $conn->prepare("SELECT nivel FROM usuario WHERE id = :id LIMIT 1");
    $q->execute([':id' => (int)$_SESSION['usuario_id']]);
    $nivelSessao = $q->fetchColumn() ?: null;
  } elseif (!empty($_SESSION['usuario_email'])) {
    $q = $conn->prepare("SELECT nivel FROM usuario WHERE email = :email LIMIT 1");
    $q->execute([':email' => $_SESSION['usuario_email']]);
    $nivelSessao = $q->fetchColumn() ?: null;
  }
  if ($nivelSessao) $_SESSION['usuario_nivel'] = $nivelSessao;
}
$isAdmin = ($nivelSessao === 'admin');

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$mensagemOk = '';
$mensagemErro = '';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar_cidade') {
  if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $mensagemErro = 'Falha de verificação. Tente novamente.';
  } else {
    $novaDescricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : null;
    $novoCaminhoCapa = null;
    if (!empty($_FILES['capa']) && $_FILES['capa']['error'] !== UPLOAD_ERR_NO_FILE) {
      if ($_FILES['capa']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['capa']['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        $ext = '';
        if ($mime === 'image/jpeg') $ext = 'jpg';
        elseif ($mime === 'image/png') $ext = 'png';
        elseif ($mime === 'image/webp') $ext = 'webp';
        elseif ($mime === 'image/gif') $ext = 'gif';
        if ($ext) {
          $dir = realpath(__DIR__ . '/../uploads/cidades');
          if ($dir && is_dir($dir) && is_writable($dir)) {
            $nomeArq = 'capa_cidade_' . (int)$cidade['id'] . '_' . time() . '.' . $ext;
            $destAbs = $dir . DIRECTORY_SEPARATOR . $nomeArq;
            if (move_uploaded_file($tmp, $destAbs)) {
              $novoCaminhoCapa = '../uploads/cidades/' . $nomeArq;
            } else {
              $mensagemErro = 'Não foi possível salvar a imagem.';
            }
          } else {
            $mensagemErro = 'Diretório de upload indisponível.';
          }
        } else {
          $mensagemErro = 'Formato de imagem inválido.';
        }
      } else {
        $mensagemErro = 'Erro no upload da imagem.';
      }
    }
    if ($mensagemErro === '') {
      if ($novoCaminhoCapa !== null && $novaDescricao !== null) {
        $up = $conn->prepare("UPDATE cidade SET descricao = :d, capa = :c WHERE id = :id");
        $up->execute([':d' => $novaDescricao, ':c' => $novoCaminhoCapa, ':id' => (int)$cidade['id']]);
        $cidade['descricao'] = $novaDescricao;
        $cidade['capa'] = $novoCaminhoCapa;
      } elseif ($novoCaminhoCapa !== null) {
        $up = $conn->prepare("UPDATE cidade SET capa = :c WHERE id = :id");
        $up->execute([':c' => $novoCaminhoCapa, ':id' => (int)$cidade['id']]);
        $cidade['capa'] = $novoCaminhoCapa;
      } elseif ($novaDescricao !== null) {
        $up = $conn->prepare("UPDATE cidade SET descricao = :d WHERE id = :id");
        $up->execute([':d' => $novaDescricao, ':id' => (int)$cidade['id']]);
        $cidade['descricao'] = $novaDescricao;
      }
      $mensagemOk = 'Cidade atualizada com sucesso.';
    }
  }
}

$porPagina = 12;
$pagina = max(1, intval($_GET['p'] ?? 1));
$offset = ($pagina - 1) * $porPagina;

$stmtRoteiros = $conn->prepare("
  SELECT r.id, r.nome, r.descricao, r.categorias, r.ponto_partida, r.destino, r.cidade_id, r.capa, r.criado_em,
         u.nome AS autor_nome
  FROM rota r
  LEFT JOIN usuario u ON u.id = r.usuario_id
  WHERE r.cidade_id = :cid
  ORDER BY r.criado_em DESC
  LIMIT :limit OFFSET :offset
");
$stmtRoteiros->bindValue(':cid', (int)$cidade['id'], PDO::PARAM_INT);
$stmtRoteiros->bindValue(':limit', $porPagina, PDO::PARAM_INT);
$stmtRoteiros->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtRoteiros->execute();
$roteiros = $stmtRoteiros->fetchAll(PDO::FETCH_ASSOC);

$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM rota WHERE cidade_id = :cid");
$stmtTotal->execute([':cid' => (int)$cidade['id']]);
$totalDireto = (int)$stmtTotal->fetchColumn();

$fallbackUsado = false;
if ($totalDireto === 0) {
  $like = '%' . $cidade['nome'] . '%';
  $stmtFallback = $conn->prepare("
    SELECT r.id, r.nome, r.descricao, r.categorias, r.ponto_partida, r.destino, r.cidade_id, r.capa, r.criado_em,
           u.nome AS autor_nome
    FROM rota r
    LEFT JOIN usuario u ON u.id = r.usuario_id
    WHERE (r.destino LIKE :like OR r.ponto_partida LIKE :like)
    ORDER BY r.criado_em DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmtFallback->bindValue(':like', $like, PDO::PARAM_STR);
  $stmtFallback->bindValue(':limit', $porPagina, PDO::PARAM_INT);
  $stmtFallback->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmtFallback->execute();
  $roteiros = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);
  $stmtCount = $conn->prepare("SELECT COUNT(*) FROM rota WHERE (destino LIKE :like OR ponto_partida LIKE :like)");
  $stmtCount->execute([':like' => $like]);
  $totalDireto = (int)$stmtCount->fetchColumn();
  $fallbackUsado = true;
}
$totalPaginas = max(1, ceil($totalDireto / $porPagina));

function capaRoteiro(?string $capa): string {
  return ($capa && trim($capa) !== '') ? $capa : '../assets/img/placeholder_rosseio.png';
}

$topAttractions = [];
try {
  $stmtParadas = $conn->prepare("SELECT paradas FROM rota WHERE cidade_id = :cid LIMIT 200");
  $stmtParadas->execute([':cid' => (int)$cidade['id']]);
  $count = [];
  while ($row = $stmtParadas->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($row['paradas'])) {
      $json = json_decode($row['paradas'], true);
      if (is_array($json)) {
        foreach ($json as $p) {
          $p = trim(trim($p, "\"' "));
          if ($p !== '') $count[$p] = ($count[$p] ?? 0) + 1;
        }
      }
    }
  }
  arsort($count);
  $topAttractions = array_slice(array_keys($count), 0, 3);
} catch (Throwable $e) {}

$capaCidade = $cidade['capa'] ?: '../assets/img/placeholder_rosseio.png';
$googleLink = "https://www.google.com/search?q=" . urlencode($cidade['nome'] . " " . $cidade['estado']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($cidade['nome']) ?> — Campvia</title>
  <link rel="stylesheet" href="../assets/css/header.css" />
  <link rel="stylesheet" href="../assets/css/footer.css" />
  <link rel="stylesheet" href="../assets/css/cidades.css" />
</head>
<body>
  <?php $ocultarImagemHeader = true; include __DIR__ . '/../includes/header.php'; ?>

  <main class="cidade-wrap">
    <section class="cidade-hero">
      <img class="hero-img" src="<?= htmlspecialchars($capaCidade) ?>" alt="Capa de <?= htmlspecialchars($cidade['nome']) ?>">
      <div class="hero-grad"></div>
      <div class="hero-content">
        <h1 class="hero-title">
          <?= htmlspecialchars($cidade['nome']) ?>
          <span class="hero-uf"><?= htmlspecialchars($cidade['estado']) ?></span>
        </h1>
        <div class="hero-chips">
          <a class="chip chip-link" href="<?= htmlspecialchars($googleLink) ?>" target="_blank" rel="noopener">Ver no Google</a>
        </div>
      </div>
      <?php if ($isAdmin): ?>
        <button class="btn-admin-toggle" type="button" onclick="document.querySelector('.admin-panel').classList.toggle('open')">Editar cidade</button>
      <?php endif; ?>
    </section>

    <?php if ($isAdmin): ?>
      <section class="admin-panel">
        <?php if ($mensagemOk): ?><div class="alert ok"><?= htmlspecialchars($mensagemOk) ?></div><?php endif; ?>
        <?php if ($mensagemErro): ?><div class="alert err"><?= htmlspecialchars($mensagemErro) ?></div><?php endif; ?>
        <form class="admin-form" method="post" enctype="multipart/form-data">
          <input type="hidden" name="acao" value="salvar_cidade">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <div class="form-row">
            <label class="form-label">Nova capa</label>
            <input class="form-file" type="file" name="capa" accept="image/*">
          </div>
          <div class="form-row">
            <label class="form-label">Descrição</label>
            <textarea class="form-textarea" name="descricao" rows="5" placeholder="Descrição da cidade..."><?= htmlspecialchars($cidade['descricao'] ?? '') ?></textarea>
          </div>
          <div class="form-actions">
            <button class="btn-primary" type="submit">Salvar alterações</button>
          </div>
        </form>
      </section>
    <?php endif; ?>

    <section class="cidade-body">
      <?php if (!empty($cidade['descricao'])): ?>
        <div class="bloco">
          <h2 class="sec">Sobre o destino</h2>
          <p class="texto"><?= nl2br(htmlspecialchars($cidade['descricao'])) ?></p>
        </div>
      <?php endif; ?>

      <?php if (!empty($topAttractions)): ?>
        <div class="bloco">
          <h3 class="sec">Principais atrações</h3>
          <ul class="lista-bullets">
            <?php foreach ($topAttractions as $a): ?>
              <li><?= htmlspecialchars($a) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="bloco">
        <h2 class="sec">Roteiros disponíveis no destino</h2>

        <?php if ($fallbackUsado): ?>
          <p class="nota">* Mostrando por correspondência no destino/ponto de partida contendo “<?= htmlspecialchars($cidade['nome']) ?>”.</p>
        <?php endif; ?>

        <?php if (empty($roteiros)): ?>
          <p class="texto">Nenhum roteiro encontrado nesta cidade ainda.</p>
        <?php else: ?>
          <div class="cards">
            <?php foreach ($roteiros as $r): ?>
              <a class="card" href="ver-rota.php?id=<?= (int)$r['id'] ?>">
                <div class="card-thumb">
                  <img src="<?= htmlspecialchars(capaRoteiro($r['capa'])) ?>" alt="Capa do roteiro <?= htmlspecialchars($r['nome']) ?>">
                </div>
                <div class="card-info">
                  <h4 class="card-title"><?= htmlspecialchars($r['nome']) ?></h4>
                  <p class="card-sub">
                    <?php if (!empty($r['autor_nome'])): ?><?= htmlspecialchars($r['autor_nome']) ?><?php endif; ?>
                    <?php if (!empty($r['categorias'])): ?><span class="dot"></span> <?= htmlspecialchars($r['categorias']) ?><?php endif; ?>
                  </p>
                </div>
                <span class="card-arrow">›</span>
              </a>
            <?php endforeach; ?>
          </div>

          <?php if ($totalPaginas > 1): ?>
            <nav class="paginacao">
              <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a class="page <?= $i === $pagina ? 'is-active' : '' ?>" href="?slug=<?= urlencode($slug) ?>&p=<?= $i ?>"><?= $i ?></a>
              <?php endfor; ?>
            </nav>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
