<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../includes/config.php';

$usuarioId = $_SESSION['usuario_id'] ?? null;
$nivel = $_SESSION['nivel'] ?? 'usuario';

$rotaId = filter_input(INPUT_POST, 'rota_id', FILTER_VALIDATE_INT);
$alvoId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
$token = $_POST['csrf'] ?? '';

if (empty($_SESSION['csrf_manage_inscricao']) || !is_array($_SESSION['csrf_manage_inscricao'])) {
    $_SESSION['csrf_manage_inscricao'] = [];
}

$redirect = '../paginas/roteiro.php';
if ($rotaId) {
    $redirect = '../paginas/gerenciar-inscricoes.php?rota=' . $rotaId;
}

if (!$usuarioId) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Faça login para continuar.'
    ];
    header('Location: ../paginas/login.php');
    exit;
}

if (!$rotaId || !$alvoId) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Dados de inscrição inválidos.'
    ];
    header('Location: ' . $redirect);
    exit;
}

if (empty($_SESSION['csrf_manage_inscricao'][$rotaId]) || !hash_equals($_SESSION['csrf_manage_inscricao'][$rotaId], $token)) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Não foi possível validar sua solicitação.'
    ];
    header('Location: ' . $redirect);
    exit;
}

$stmtRota = $conn->prepare('SELECT usuario_id, nome FROM rota WHERE id = ? LIMIT 1');
$stmtRota->execute([$rotaId]);
$rota = $stmtRota->fetch(PDO::FETCH_ASSOC);

if (!$rota) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Rota não encontrada.'
    ];
    header('Location: ../paginas/roteiro.php');
    exit;
}

$ehDono = ((int)$rota['usuario_id'] === (int)$usuarioId);
$ehAdmin = ($nivel === 'admin');

if (!$ehDono && !$ehAdmin) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Você não pode gerenciar inscrições desta rota.'
    ];
    header('Location: ../paginas/ver-rota.php?id=' . $rotaId);
    exit;
}

$stmtInscricao = $conn->prepare('SELECT 1 FROM rota_inscricao WHERE rota_id = ? AND usuario_id = ?');
$stmtInscricao->execute([$rotaId, $alvoId]);
if (!$stmtInscricao->fetchColumn()) {
    $_SESSION['flash'] = [
        'type' => 'info',
        'message' => 'Inscrição já removida.'
    ];
    header('Location: ' . $redirect);
    exit;
}

$stmtRemover = $conn->prepare('DELETE FROM rota_inscricao WHERE rota_id = ? AND usuario_id = ?');
$stmtRemover->execute([$rotaId, $alvoId]);

$_SESSION['csrf_manage_inscricao'][$rotaId] = bin2hex(random_bytes(16));

$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'Inscrição removida com sucesso.',
    'details' => 'O participante foi retirado do roteiro "' . $rota['nome'] . '".'
];

header('Location: ' . $redirect);
exit;
