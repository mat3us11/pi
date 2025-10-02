<?php
// paginas/ver-rota.php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
require_once '../includes/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Rota inválida.");
}

$id_rota = (int)$_GET['id'];

/* Buscar rota */
$sql = "SELECT r.id, r.usuario_id, r.nome, r.descricao, r.categorias, r.capa,
               r.ponto_partida, r.destino, r.paradas, r.criado_em,
               u.nome AS criador
        FROM rota r
        JOIN usuario u ON r.usuario_id = u.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_rota]);
$rota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rota) die("Rota não encontrada.");

/* Paradas (compat: strings antigas x objetos novos) */
$paradas = [];
if (!empty($rota['paradas'])) {
  $tmp = json_decode($rota['paradas'], true);
  if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
    $paradas = $tmp;
  } else {
    // legado: lista simples
    $paradas = (array)$tmp;
  }
}

$usuarioLogadoId = $_SESSION['usuario_id'] ?? null;
$ehDono = $usuarioLogadoId && ((int)$usuarioLogadoId === (int)$rota['usuario_id']);

$capa = !empty($rota['capa']) ? $rota['capa'] : '../assets/img/placeholder.jpg';

$chipsCategorias = [];
if (!empty($rota['categorias'])) {
  $chipsCategorias = array_filter(array_map('trim', preg_split('/,|\|/', $rota['categorias'])));
}

/* Token simples para exclusão */
if (empty($_SESSION['csrf_delete'])) {
  $_SESSION['csrf_delete'] = bin2hex(random_bytes(16));
}
$csrf_delete = $_SESSION['csrf_delete'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($rota['nome']) ?> - Roteiro</title>

<link rel="stylesheet" href="../assets/css/header.css">
<link rel="stylesheet" href="../assets/css/footer.css">
<link rel="stylesheet" href="../assets/css/ver-rota.css">
<script defer src="../assets/js/modal.js"></script>
</head>
<body>
<?php include '../includes/header.php'; ?>

<main class="vr-container">
  <nav class="vr-breadcrumb">
    <a href="roteiro.php" class="vr-link">← Voltar</a>
  </nav>

  <!-- HERO -->
  <section class="vr-hero">
    <div class="vr-hero__media">
      <img src="<?= htmlspecialchars($capa) ?>" alt="Capa da rota">
    </div>
    <div class="vr-hero__content">
      <h1 class="vr-title"><?= htmlspecialchars($rota['nome']) ?></h1>
      <div class="vr-meta">
        <span class="vr-meta__item"><strong>Criador:</strong> <?= htmlspecialchars($rota['criador']) ?></span>
        <?php if (!empty($rota['criado_em'])): ?>
          <span class="vr-meta__dot">•</span>
          <span class="vr-meta__item"><?= date('d/m/Y', strtotime($rota['criado_em'])) ?></span>
        <?php endif; ?>
      </div>

      <?php if (!empty($chipsCategorias)): ?>
        <div class="vr-chips">
          <?php foreach ($chipsCategorias as $cat): ?>
            <span class="vr-chip"><?= htmlspecialchars($cat) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="vr-actions">
        <?php if ($ehDono): ?>
          <a class="btn btn--primary" href="editar-rota.php?id=<?= (int)$rota['id'] ?>">✎ Editar</a>
          <form class="inline" action="../processos/deletar-rota.php" method="POST" onsubmit="return confirmarExclusao()">
            <input type="hidden" name="id_rota" value="<?= (int)$rota['id'] ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf_delete) ?>">
            <button type="submit" class="btn btn--danger">🗑️ Excluir</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- INFO RÁPIDA -->
  <section class="vr-cards">
    <article class="vr-card">
      <h3 class="vr-card__title">Descrição</h3>
      <p class="vr-card__text"><?= nl2br(htmlspecialchars($rota['descricao'])) ?></p>
    </article>

    <div class="vr-info-grid">
      <article class="vr-card">
        <h3 class="vr-card__title">Ponto de Partida</h3>
        <p class="vr-card__text"><?= htmlspecialchars($rota['ponto_partida'] ?: '—') ?></p>
      </article>
      <article class="vr-card">
        <h3 class="vr-card__title">Destino</h3>
        <p class="vr-card__text"><?= htmlspecialchars($rota['destino'] ?: '—') ?></p>
      </article>
    </div>
  </section>

  <!-- PARADAS -->
  <?php if (!empty($paradas)): ?>
  <section class="vr-card">
    <h2 class="vr-card__title">Paradas</h2>

    <ol class="vr-stops">
      <?php foreach ($paradas as $idx => $p):
        $nome = is_array($p) ? ($p['nome'] ?? '') : (string)$p;
        $img  = is_array($p) ? ($p['image_url'] ?? '') : '';
        $lat  = is_array($p) ? ($p['lat'] ?? null) : null;
        $lon  = is_array($p) ? ($p['lon'] ?? null) : null;
        $maps = ($lat && $lon) ? "https://www.google.com/maps/search/?api=1&query={$lat},{$lon}" : null;
      ?>
      <li class="vr-stop">
        <div class="vr-stop__index"><?= $idx + 1 ?></div>
        <div class="vr-stop__body">
          <div class="vr-stop__header">
            <?php if ($maps): ?>
              <a class="vr-stop__title" href="<?= htmlspecialchars($maps) ?>" target="_blank" rel="noopener">
                <?= htmlspecialchars($nome) ?>
              </a>
            <?php else: ?>
              <span class="vr-stop__title"><?= htmlspecialchars($nome) ?></span>
            <?php endif; ?>
          </div>
          <?php if ($img): ?>
            <div class="vr-stop__media">
              <img src="<?= htmlspecialchars($img) ?>" alt="Foto de <?= htmlspecialchars($nome) ?>">
            </div>
          <?php endif; ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>
  </section>
  <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

<script>
  function confirmarExclusao() {
    return confirm('Tem certeza que deseja excluir este roteiro? Esta ação não pode ser desfeita.');
  }
</script>
</body>
</html>
