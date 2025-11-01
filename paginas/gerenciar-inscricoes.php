<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../includes/config.php';

$usuarioId = $_SESSION['usuario_id'] ?? null;
$nivel = $_SESSION['nivel'] ?? 'usuario';

$rotaId = filter_input(INPUT_GET, 'rota', FILTER_VALIDATE_INT);

if (!$usuarioId) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Faça login para acessar a gestão de inscrições.'
    ];
    header('Location: login.php');
    exit;
}

if (!$rotaId) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Rota inválida para gerenciamento.'
    ];
    header('Location: roteiro.php');
    exit;
}

$stmtRota = $conn->prepare('SELECT id, nome, usuario_id FROM rota WHERE id = ? LIMIT 1');
$stmtRota->execute([$rotaId]);
$rota = $stmtRota->fetch(PDO::FETCH_ASSOC);

if (!$rota) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Rota não encontrada.'
    ];
    header('Location: roteiro.php');
    exit;
}

$ehDono = ((int)$rota['usuario_id'] === (int)$usuarioId);
$ehAdmin = ($nivel === 'admin');

if (!$ehDono && !$ehAdmin) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Você não tem permissão para gerenciar as inscrições desta rota.'
    ];
    header('Location: ver-rota.php?id=' . $rota['id']);
    exit;
}

$stmtInscritos = $conn->prepare(
    'SELECT ri.usuario_id, ri.criado_em, u.nome, u.email, u.foto_perfil
     FROM rota_inscricao ri
     JOIN usuario u ON u.id = ri.usuario_id
     WHERE ri.rota_id = :rota
     ORDER BY ri.criado_em DESC'
);
$stmtInscritos->bindValue(':rota', $rotaId, PDO::PARAM_INT);
$stmtInscritos->execute();
$inscritos = $stmtInscritos->fetchAll(PDO::FETCH_ASSOC);

if (empty($_SESSION['csrf_manage_inscricao']) || !is_array($_SESSION['csrf_manage_inscricao'])) {
    $_SESSION['csrf_manage_inscricao'] = [];
}

if (empty($_SESSION['csrf_manage_inscricao'][$rotaId])) {
    $_SESSION['csrf_manage_inscricao'][$rotaId] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['csrf_manage_inscricao'][$rotaId];

$totalInscritos = count($inscritos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar inscrições - <?= htmlspecialchars($rota['nome']) ?></title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/flash.css">
    <link rel="stylesheet" href="../assets/css/gerenciar-inscricoes.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/flash.php'; ?>

<main class="gi-container">
    <nav class="gi-breadcrumb">
        <a href="ver-rota.php?id=<?= (int)$rota['id'] ?>" class="gi-link">← Voltar para a rota</a>
    </nav>

    <section class="gi-card">
        <header class="gi-card__header">
            <div>
                <h1 class="gi-title">Inscrições em "<?= htmlspecialchars($rota['nome']) ?>"</h1>
                <p class="gi-subtitle">Total de inscritos: <?= $totalInscritos ?></p>
            </div>
            <?php if ($totalInscritos > 0): ?>
                <button type="button" class="gi-btn gi-btn--ghost" disabled title="Função disponível em breve">Exportar lista</button>
            <?php endif; ?>
        </header>

        <?php if ($totalInscritos === 0): ?>
            <p class="gi-empty">Nenhum usuário inscrito por enquanto.</p>
        <?php else: ?>
            <ul class="gi-list">
                <?php foreach ($inscritos as $inscrito): ?>
                    <li class="gi-item">
                        <div class="gi-user">
                            <?php if (!empty($inscrito['foto_perfil'])): ?>
                                <img class="gi-avatar" src="<?= htmlspecialchars($inscrito['foto_perfil']) ?>" alt="Foto de <?= htmlspecialchars($inscrito['nome']) ?>">
                            <?php else: ?>
                                <div class="gi-avatar gi-avatar--placeholder" aria-hidden="true">👤</div>
                            <?php endif; ?>
                            <div>
                                <div class="gi-user__name"><?= htmlspecialchars($inscrito['nome']) ?></div>
                                <div class="gi-user__meta">
                                    <?= htmlspecialchars($inscrito['email']) ?> · Inscrito em <?= date('d/m/Y H:i', strtotime($inscrito['criado_em'])) ?>
                                </div>
                            </div>
                        </div>
                        <form class="gi-form" action="../processos/remover-inscricao.php" method="POST" onsubmit="return confirmarRemocao()">
                            <input type="hidden" name="rota_id" value="<?= (int)$rota['id'] ?>">
                            <input type="hidden" name="usuario_id" value="<?= (int)$inscrito['usuario_id'] ?>">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="gi-btn gi-btn--danger">Remover</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
<script>
function confirmarRemocao() {
    return confirm('Remover esta inscrição?');
}
</script>
</body>
</html>
