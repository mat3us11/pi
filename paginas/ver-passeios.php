<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once '../includes/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Passeio inválido.");
}

$id_passeio = (int)$_GET['id'];

/* Buscar passeio */
$sql = "SELECT r.id, r.usuario_id, r.nome, r.descricao, r.categorias, r.capa,
               r.localidade, r.criado_em, u.nome AS criador
        FROM passeios r
        JOIN usuario u ON r.usuario_id = u.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_passeio]);
$passeio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$passeio) {
    die("Passeio não encontrado.");
}

/* Normaliza capa */
$capa = !empty($passeio['capa']) ? $passeio['capa'] : '../assets/img/placeholder.jpg';

/* Quebra categorias em chips */
$chipsCategorias = [];
if (!empty($passeio['categorias'])) {
    $chipsCategorias = array_filter(array_map('trim', preg_split('/,|\|/', $passeio['categorias'])));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($passeio['nome']) ?> - Passeio</title>

<link rel="stylesheet" href="../assets/css/header.css">
<link rel="stylesheet" href="../assets/css/footer.css">
<link rel="stylesheet" href="../assets/css/ver-passeios.css">
<script defer src="../assets/js/modal.js"></script>
</head>
<body>
<?php include '../includes/header.php'; ?>

<section class="rota-header">
  <div class="rota-header__img">
    <img src="<?= htmlspecialchars($capa) ?>" alt="Capa do passeio">
  </div>
  <div class="rota-header__info">
    <h1><?= htmlspecialchars($passeio['nome']) ?></h1>
    <div class="meta">
      <span><strong>Criador:</strong> <?= htmlspecialchars($passeio['criador']) ?></span>
      <?php if (!empty($passeio['criado_em'])): ?>
        <span>• <?= date('d/m/Y', strtotime($passeio['criado_em'])) ?></span>
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
      <a class="btn btn--ghost" href="index.php">← Voltar</a>
    </div>
  </div>
</section>

<main class="rota-content">
  <section class="card">
    <h2 class="section-title">Descrição</h2>
    <p class="descricao"><?= nl2br(htmlspecialchars($passeio['descricao'])) ?></p>
  </section>

  <section class="card info-card">
    <div class="info-card__body">
      <h3>Localidade</h3>
      <p><?= htmlspecialchars($passeio['localidade'] ?: '—') ?></p>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
