<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../includes/config.php';

$usuarioId = $_SESSION['usuario_id'] ?? null;
$rotaId = filter_input(INPUT_POST, 'rota_id', FILTER_VALIDATE_INT);
$token = $_POST['csrf'] ?? '';
$redirect = '../paginas/roteiro.php';

if ($rotaId) {
    $redirect = '../paginas/ver-rota.php?id=' . $rotaId;
}

if (!$usuarioId) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Faça login para se inscrever em um roteiro.'
    ];
    header('Location: ../paginas/login.php');
    exit;
}

if (!$rotaId) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Rota inválida para inscrição.'
    ];
    header('Location: ' . $redirect);
    exit;
}

if (empty($_SESSION['csrf_subscribe']) || !hash_equals($_SESSION['csrf_subscribe'], $token)) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Não foi possível validar sua solicitação. Tente novamente.'
    ];
    header('Location: ' . $redirect);
    exit;
}

$_SESSION['csrf_subscribe'] = bin2hex(random_bytes(16));

$stmtRota = $conn->prepare('SELECT nome FROM rota WHERE id = ? LIMIT 1');
$stmtRota->execute([$rotaId]);
$rota = $stmtRota->fetch(PDO::FETCH_ASSOC);

if (!$rota) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'A rota escolhida não foi encontrada.'
    ];
    header('Location: ../paginas/roteiro.php');
    exit;
}

try {
    $stmt = $conn->prepare('INSERT INTO rota_inscricao (rota_id, usuario_id) VALUES (:rota_id, :usuario_id)');
    $stmt->execute([
        ':rota_id' => $rotaId,
        ':usuario_id' => $usuarioId
    ]);

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Inscrição confirmada!',
        'details' => 'Você agora participa do roteiro "' . $rota['nome'] . '".'
    ];
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $_SESSION['flash'] = [
            'type' => 'info',
            'message' => 'Você já está inscrito neste roteiro.'
        ];
    } else {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Não foi possível concluir sua inscrição. Tente novamente mais tarde.'
        ];
    }
}

header('Location: ' . $redirect);
exit;
