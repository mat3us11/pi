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
    // era uma string/JSON antigo -> virar lista simples
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

<div class="main-teste33">
  <div class="voltar">
    <a class="btn btn--ghost" href="roteiro.php">← Voltar</a>
  </div>

  <!-- HEADER DA ROTA -->
  <section class="rota-header">
    <div class="rota-header__img">
      <img src="<?= htmlspecialchars($capa) ?>" alt="Capa da rota">
    </div>
    <div class="rota-header__info">
      <h1><?= htmlspecialchars($rota['nome']) ?></h1>
      <div class="meta">
        <span><strong>Criador:</strong> <?= htmlspecialchars($rota['criador']) ?></span>
        <?php if (!empty($rota['criado_em'])): ?>
          <span>• <?= date('d/m/Y', strtotime($rota['criado_em'])) ?></span>
        <?php endif; ?>
      </div>

      <?php if (!empty($chipsCategorias)): ?>
        <div class="chips">
          <?php foreach ($chipsCategorias as $cat): ?>
            <span class="chip"><?= htmlspecialchars($cat) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="rota-header__actions">
        <?php if ($ehDono): ?>
          <a class="btn btn--primary" href="editar-rota.php?id=<?= (int)$rota['id'] ?>">✎ Editar roteiro</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- CONTEÚDO -->
  <main class="rota-content">
    <section class="card">
      <h2 class="section-title">Descrição</h2>
      <p class="descricao"><?= nl2br(htmlspecialchars($rota['descricao'])) ?></p>
    </section>

    <section class="info-grid">
      <div class="card info-card">
        <div class="info-card__body">
          <h3>Ponto de Partida</h3>
          <p><?= htmlspecialchars($rota['ponto_partida'] ?: '—') ?></p>
        </div>
      </div>

      <div class="card info-card">
        <div class="info-card__body">
          <h3>Destino</h3>
          <p><?= htmlspecialchars($rota['destino'] ?: '—') ?></p>
        </div>
      </div>
    </section>

    <?php if (!empty($paradas)): ?>
    <section class="card">
      <h2 class="section-title">Paradas</h2>
      <ol class="timeline">
        <?php foreach ($paradas as $p):
          $nome = is_array($p) ? ($p['nome'] ?? '') : (string)$p;
          $img  = is_array($p) ? ($p['image_url'] ?? '') : '';
          $lat  = is_array($p) ? ($p['lat'] ?? null) : null;
          $lon  = is_array($p) ? ($p['lon'] ?? null) : null;
          $maps = ($lat && $lon) ? "https://www.google.com/maps/search/?api=1&query={$lat},{$lon}" : null;
        ?>
          <li class="timeline__item">
            <div class="timeline__point"></div>
            <div class="timeline__content">
              <div class="timeline__title">
                <?php if ($maps): ?>
                  <a href="<?= htmlspecialchars($maps) ?>" target="_blank" rel="noopener">
                    <?= htmlspecialchars($nome) ?>
                  </a>
                <?php else: ?>
                  <?= htmlspecialchars($nome) ?>
                <?php endif; ?>
              </div>
              <?php if ($img): ?>
                <div class="timeline__thumb" style="margin-top:.5rem">
                  <img src="<?= htmlspecialchars($img) ?>"
                       alt="Foto de <?= htmlspecialchars($nome) ?>"
                       style="max-width:240px;border-radius:8px;object-fit:cover">
                </div>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    </section>
    <?php endif; ?>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
