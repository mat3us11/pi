<?php
session_start();
require_once '../includes/config.php';

$stmt = $conn->prepare("
  SELECT id, nome, estado, slug, descricao, capa
  FROM cidade
  ORDER BY RAND()
  LIMIT 32
");
$stmt->execute();
$cidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

function ph($src){
  return (!empty($src)) ? htmlspecialchars($src) : '../assets/img/placeholder_rosseio.png';
}

$ofertas   = array_slice($cidades, 0, 2);
$destaques = array_slice($cidades, 2, 3);
$interior  = array_slice($cidades, 5, 12);
$slider    = array_slice($cidades, 17);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Home</title>
  <link rel="stylesheet" href="../assets/css/header.css" />
  <link rel="stylesheet" href="../assets/css/footer.css" />
  <link rel="stylesheet" href="../assets/css/index.css" />
  <script defer src="../assets/js/modal.js"></script>
</head>
<body>
  <?php include '../includes/header.php'; ?>

  <section class="hero">
    <div class="container-index hero__inner">
      <h1 class="hero__title">Encontre seu próximo destino</h1>
    </div>
  </section>

  <div class="pesquisa-home">
    <div class="coluna1">
      <input class="input1" type="search" placeholder="Para onde você vai?">
    </div>
    <div class="coluna2">
      <input class="input2" type="text" id="data" placeholder="Quando você vai?" onfocus="this.type='date'">
      <input class="input3" type="search" placeholder="Quem vai com você?">
    </div>
    <div class="coluna3">
      <button>Pesquisar</button>
    </div>
  </div>

  <section class="home-sec">
    <h3 class="home-sec__title">Cidades em destaque</h3>
    <?php if (empty($ofertas)): ?>
      <p class="home-sec__empty container-index">Sem cidades para exibir.</p>
    <?php else: ?>
      <div class="ofertas-grid container-index">
        <?php foreach ($ofertas as $c): ?>
          <a class="city-card city-card--xl" href="cidade.php?slug=<?= urlencode($c['slug']) ?>">
            <img loading="lazy" src="<?= ph($c['capa']) ?>" alt="Capa de <?= htmlspecialchars($c['nome']) ?>">
            <span class="city-chip"><?= htmlspecialchars($c['nome']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="home-sec">
    <h3 class="home-sec__title">Destinos mais procurados</h3>
    <?php if (empty($destaques)): ?>
      <p class="home-sec__empty container-index">Sem cidades para exibir.</p>
    <?php else: ?>
      <div class="destinos-grid container-index">
        <?php foreach ($destaques as $c): ?>
          <a class="city-card city-card--md" href="cidade.php?slug=<?= urlencode($c['slug']) ?>">
            <img loading="lazy" src="<?= ph($c['capa']) ?>" alt="Capa de <?= htmlspecialchars($c['nome']) ?>">
            <span class="city-chip"><?= htmlspecialchars($c['nome']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="home-sec">
    <h3 class="home-sec__title">Conheça o interior</h3>
    <?php if (empty($interior)): ?>
      <p class="home-sec__empty container-index">Sem cidades para exibir.</p>
    <?php else: ?>
      <div class="interior-grid container-index">
        <?php foreach ($interior as $c): ?>
          <a class="city-card city-card--sm" href="cidade.php?slug=<?= urlencode($c['slug']) ?>">
            <img loading="lazy" src="<?= ph($c['capa']) ?>" alt="Capa de <?= htmlspecialchars($c['nome']) ?>">
            <span class="city-chip city-chip--sm"><?= htmlspecialchars($c['nome']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <?php if (!empty($slider)): ?>
  <section class="home-sec mobile-slider">
    <h3 class="home-sec__title container-index">Outras cidades</h3>
    <div class="container-index">
      <div class="extra-strip">
        <?php foreach ($slider as $c): ?>
          <a class="strip-item" href="cidade.php?slug=<?= urlencode($c['slug']) ?>">
            <img loading="lazy" src="<?= ph($c['capa']) ?>" alt="Capa de <?= htmlspecialchars($c['nome']) ?>">
            <span><?= htmlspecialchars($c['nome']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script>
    const data = document.getElementById('data');
    if (data) data.onblur = function(){ if (!this.value) this.type='text'; }
  </script>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
