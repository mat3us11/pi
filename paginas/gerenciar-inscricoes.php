<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once '../includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$nivel = $_SESSION['nivel'] ?? 'usuario';
$rotaSelecionada = isset($_GET['rota']) ? (int) $_GET['rota'] : null;

$filtroProprietario = '';
$paramsLista = [];

if ($nivel !== 'admin') {
    $filtroProprietario = 'WHERE usuario_id = :uid';
    $paramsLista[':uid'] = $usuarioId;
}

$sqlRotasDisponiveis = 'SELECT id, nome FROM rota ' . $filtroProprietario . ' ORDER BY nome ASC';
$stmtRotasDisponiveis = $conn->prepare($sqlRotasDisponiveis);
$stmtRotasDisponiveis->execute($paramsLista);
$rotasDisponiveis = $stmtRotasDisponiveis->fetchAll(PDO::FETCH_ASSOC);

$temPermissao = $nivel === 'admin' || !empty($rotasDisponiveis);

$paramsDetalhes = [];
$whereDetalhes = [];

if ($nivel !== 'admin') {
    $whereDetalhes[] = 'r.usuario_id = :uid';
    $paramsDetalhes[':uid'] = $usuarioId;
}

if ($rotaSelecionada) {
    $whereDetalhes[] = 'r.id = :rotaId';
    $paramsDetalhes[':rotaId'] = $rotaSelecionada;
}

$whereClause = '';
if ($whereDetalhes) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereDetalhes);
}

$sqlDetalhes = "SELECT r.id, r.nome, r.categorias, r.capa, r.criado_em, u.nome AS criador,
                       ri.usuario_id AS inscrito_id, ri.criado_em AS inscrito_em,
                       inscrito.nome AS inscrito_nome, inscrito.email AS inscrito_email
                FROM rota r
                JOIN usuario u ON r.usuario_id = u.id
                LEFT JOIN rota_inscricao ri ON ri.rota_id = r.id
                LEFT JOIN usuario inscrito ON ri.usuario_id = inscrito.id
                $whereClause
                ORDER BY r.nome ASC, inscrito.nome ASC";
$stmtDetalhes = $conn->prepare($sqlDetalhes);
$stmtDetalhes->execute($paramsDetalhes);
$rows = $stmtDetalhes->fetchAll(PDO::FETCH_ASSOC);

$rotasAgrupadas = [];
foreach ($rows as $row) {
    $rotaId = (int) $row['id'];
    if (!isset($rotasAgrupadas[$rotaId])) {
        $rotasAgrupadas[$rotaId] = [
            'id' => $rotaId,
            'nome' => $row['nome'],
            'categorias' => $row['categorias'],
            'capa' => $row['capa'],
            'criador' => $row['criador'],
            'inscritos' => [],
        ];
    }

    if (!empty($row['inscrito_id'])) {
        $rotasAgrupadas[$rotaId]['inscritos'][] = [
            'id' => (int) $row['inscrito_id'],
            'nome' => $row['inscrito_nome'],
            'email' => $row['inscrito_email'],
            'data' => $row['inscrito_em'],
        ];
    }
}

$redirectAtual = $_SERVER['REQUEST_URI'] ?? 'gerenciar-inscricoes.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciar inscrições</title>
  <link rel="stylesheet" href="../assets/css/header_perfil.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/gerenciar-inscricoes.css">
  <script defer src="../assets/js/modal.js"></script>
</head>
<body>
  <?php include '../includes/header-perfil.php'; ?>

  <main class="gi-container">
    <div class="gi-header">
      <div>
        <h1>Gerenciar inscrições</h1>
        <p class="gi-subtitle">Visualize quem está participando das suas rotas e remova inscrições se necessário.</p>
      </div>

      <?php if ($nivel === 'admin' || count($rotasDisponiveis) > 1): ?>
        <form method="GET" class="gi-filter">
          <label for="rota">Filtrar por rota</label>
          <select id="rota" name="rota">
            <option value="">Todas</option>
            <?php foreach ($rotasDisponiveis as $rotaFiltro):
              $idFiltro = (int) $rotaFiltro['id'];
            ?>
              <option value="<?= $idFiltro ?>" <?= ($rotaSelecionada === $idFiltro) ? 'selected' : '' ?>>
                <?= htmlspecialchars($rotaFiltro['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="gi-btn">Aplicar</button>
          <?php if ($rotaSelecionada): ?>
            <a class="gi-btn gi-btn--ghost" href="gerenciar-inscricoes.php">Limpar</a>
          <?php endif; ?>
        </form>
      <?php endif; ?>
    </div>

    <?php if (!$temPermissao && $nivel !== 'admin'): ?>
      <p class="gi-empty">Você ainda não criou nenhuma rota para gerenciar inscrições.</p>
    <?php elseif (empty($rotasAgrupadas)): ?>
      <p class="gi-empty">Nenhuma rota encontrada para os filtros aplicados.</p>
    <?php else: ?>
      <?php foreach ($rotasAgrupadas as $rota): ?>
        <section class="gi-card">
          <header class="gi-card__header">
            <div>
              <h2><?= htmlspecialchars($rota['nome']) ?></h2>
              <p class="gi-card__meta">
                <?= htmlspecialchars($rota['categorias'] ?: 'Sem categoria definida') ?> ·
                <?= count($rota['inscritos']) ?> inscrito(s)
              </p>
            </div>
            <div class="gi-card__actions">
              <a class="gi-btn gi-btn--outline" href="ver-rota.php?id=<?= (int) $rota['id'] ?>">Ver rota</a>
            </div>
          </header>

          <?php if (!empty($rota['inscritos'])): ?>
            <div class="gi-table-wrapper">
              <table class="gi-table">
                <thead>
                  <tr>
                    <th>Participante</th>
                    <th>E-mail</th>
                    <th>Inscrito em</th>
                    <th class="gi-col-acoes">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rota['inscritos'] as $inscrito): ?>
                    <tr>
                      <td><?= htmlspecialchars($inscrito['nome'] ?? 'Usuário removido') ?></td>
                      <td><?= htmlspecialchars($inscrito['email'] ?? '—') ?></td>
                      <td><?= $inscrito['data'] ? date('d/m/Y H:i', strtotime($inscrito['data'])) : '—' ?></td>
                      <td class="gi-col-acoes">
                        <form method="POST" action="../processos/inscricao-rota.php" onsubmit="return confirm('Remover este participante da rota?');">
                          <input type="hidden" name="acao" value="remover">
                          <input type="hidden" name="rota_id" value="<?= (int) $rota['id'] ?>">
                          <input type="hidden" name="usuario_id" value="<?= (int) $inscrito['id'] ?>">
                          <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectAtual, ENT_QUOTES, 'UTF-8') ?>">
                          <button type="submit" class="gi-btn gi-btn--danger">Remover</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="gi-empty">Nenhum inscrito ainda. Compartilhe sua rota para atrair participantes!</p>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
