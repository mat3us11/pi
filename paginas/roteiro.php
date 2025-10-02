<?php
// paginas/roteiro.php
session_start();
require_once '../includes/config.php'; // conexão PDO

/* ===== Lê filtros (GET) ===== */
$q        = trim($_GET['q'] ?? '');
$tipo     = trim($_GET['tipo'] ?? '');        // texto livre (ex: "passeio", "trilha")
$duracao  = trim($_GET['duracao'] ?? '');     // opcional: tentamos casar "X dia" na descrição
$estilo   = trim($_GET['estilo'] ?? '');      // ex: "cultural,aventura"

$wheres = [];
$params = [];

/* Pesquisa geral (nome / descrição / ponto_partida / destino) */
if ($q !== '') {
  $wheres[] = '(r.nome LIKE ? OR r.descricao LIKE ? OR r.ponto_partida LIKE ? OR r.destino LIKE ?)';
  $like = '%'.$q.'%';
  array_push($params, $like, $like, $like, $like);
}

/* "Tipo" – sem campo dedicado no BD, então tratamos como busca textual em nome/descrição */
if ($tipo !== '') {
  $wheres[] = '(r.nome LIKE ? OR r.descricao LIKE ?)';
  $like = '%'.$tipo.'%';
  array_push($params, $like, $like);
}

/* "Duração" – não há coluna; tentamos achar em descrição padrões como "3 dias" / "2 dia" */
if ($duracao !== '') {
  $wheres[] = '(r.descricao REGEXP ?)';
  // transforma "3" em regex "\\b3\\s*(dia|dias)\\b"; se vier "3 dias" já funciona tbm
  $d = preg_replace('/[^0-9]/', '', $duracao);
  if ($d !== '') {
    $regex = "\\\\b{$d}\\\\s*(dia|dias)\\\\b";
  } else {
    // se não tiver número, vira busca textual mesmo
    $regex = preg_quote($duracao, '/');
  }
  $params[] = $regex;
}

/* "Estilo" – filtra por categorias CSV usando FIND_IN_SET para cada item */
$estilos = array_filter(array_map('trim', preg_split('/[,|]/', $estilo)));
foreach ($estilos as $e) {
  $wheres[] = "FIND_IN_SET(?, r.categorias)";
  $params[] = $e;
}

/* Monta SQL com filtros */
$sql = "SELECT r.id, r.nome, r.descricao, r.categorias, r.capa, r.ponto_partida, r.destino, r.criado_em,
               u.nome AS criador
        FROM rota r
        JOIN usuario u ON r.usuario_id = u.id";

if (!empty($wheres)) {
  $sql .= " WHERE " . implode(' AND ', $wheres);
}

$sql .= " ORDER BY r.criado_em DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Para preencher os campos do formulário (preserva busca) */
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Roteiros</title>
  <link rel="stylesheet" href="../assets/css/header.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/roteiro.css">
  <script defer src="../assets/js/roteiro.js"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
  <?php include '../includes/header.php' ?>

  <!-- EXPLORAR / BUSCA -->
  <form class="explore" method="GET" action="">
    <div class="up">
      <div class="roteiro">
        <input class="input" type="search" name="q" id="q" placeholder="Explore roteiros"
               value="<?= h($q) ?>" autocomplete="off">
      </div>
    </div>

    <div class="down">
      <div class="tipo">
        <input class="input" type="search" name="tipo" id="tipo" placeholder="Tipo de passeio"
               value="<?= h($tipo) ?>" autocomplete="off">
      </div>

      <div class="duracao">
        <input class="input" type="search" name="duracao" id="duracao" placeholder="Duração (ex: 3 dias)"
               value="<?= h($duracao) ?>" autocomplete="off">
      </div>

      <div class="estilo">
        <input class="input" type="search" name="estilo" id="estilo" placeholder="Estilo (cultural, aventura...)"
               value="<?= h($estilo) ?>" autocomplete="off">
      </div>
    </div>

    <div class="actions">
      <button type="submit" class="btn-search"><i class="ph ph-magnifying-glass"></i> Pesquisar</button>
      <button type="button" class="btn-clear" id="btn-clear"><i class="ph ph-x"></i> Limpar</button>
    </div>
  </form>

  <!-- CTA Criar -->
  <div class="criar">
    <h2>Deseja criar seu próprio roteiro?</h2>
    <a href="criar-roteiro.php">
      <button type="button">Criar novo roteiro</button>
    </a>
  </div>

  <!-- Lista de Roteiros -->
  <div class="prontos">
    <h2>Roteiros prontos</h2>

    <?php if (count($rotas) > 0): ?>
      <?php foreach ($rotas as $rota): 
        $capa = !empty($rota['capa']) ? $rota['capa'] : '../assets/img/placeholder.jpg';
      ?>
        <article class="passeio">
          <img src="<?= h($capa) ?>" alt="<?= h($rota['nome']) ?>">

          <div class="escrita">
            <div class="nome">
              <h4 title="<?= h($rota['nome']) ?>"><?= h($rota['nome']) ?></h4>
            </div>

            <div class="criador">
              <h4><?= h($rota['criador']) ?></h4>
            </div>

            <div class="estilo">
              <h4 title="<?= h($rota['categorias']) ?>"><?= h($rota['categorias']) ?></h4>
            </div>

            <?php if (!empty($rota['ponto_partida']) || !empty($rota['destino'])): ?>
              <div class="trajeto">
                <span class="from" title="<?= h($rota['ponto_partida']) ?>"><?= h($rota['ponto_partida']) ?></span>
                <span class="sep">→</span>
                <span class="to" title="<?= h($rota['destino']) ?>"><?= h($rota['destino']) ?></span>
              </div>
            <?php endif; ?>
          </div>

          <div class="botao2">
            <a href="ver-rota.php?id=<?= (int)$rota['id'] ?>" class="go">
              <button type="button" aria-label="Ver roteiro">
                <i class="ph ph-caret-right"></i>
              </button>
            </a>
          </div>
        </article>
      <?php endforeach; ?>

    <?php else: ?>
      <div class="no-results">
        <i class="ph ph-map-pin"></i>
        <p>Nenhum roteiro encontrado com os filtros atuais.</p>
        <button type="button" class="btn-clear" id="btn-clear-2"><i class="ph ph-arrow-counter-clockwise"></i> Limpar filtros</button>
      </div>
    <?php endif; ?>
  </div>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
