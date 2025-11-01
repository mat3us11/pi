<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../paginas/login.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$nivel = $_SESSION['nivel'] ?? 'usuario';

unset($_SESSION['flash_sucesso']);

$rotaId = isset($_POST['rota_id']) ? (int) $_POST['rota_id'] : 0;
$acao = $_POST['acao'] ?? '';
$alvoUsuarioId = isset($_POST['usuario_id']) ? (int) $_POST['usuario_id'] : $usuarioId;

if (!$rotaId || !in_array($acao, ['inscrever', 'cancelar', 'remover'], true)) {
    header('Location: ../paginas/perfil.php');
    exit;
}

try {
    $stmt = $conn->prepare('SELECT id, usuario_id, nome FROM rota WHERE id = :rota');
    $stmt->bindValue(':rota', $rotaId, PDO::PARAM_INT);
    $stmt->execute();
    $rota = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rota) {
        throw new RuntimeException('Rota não encontrada.');
    }

    $ehDono = ((int) $rota['usuario_id'] === $usuarioId);
    $ehAdmin = ($nivel === 'admin');

    switch ($acao) {
        case 'inscrever':
            $stmt = $conn->prepare(
                'INSERT INTO rota_inscricao (rota_id, usuario_id)
                 VALUES (:rota, :usuario)
                 ON DUPLICATE KEY UPDATE criado_em = criado_em'
            );
            $stmt->execute([
                ':rota' => $rotaId,
                ':usuario' => $usuarioId,
            ]);
            if (!empty($rota['nome'])) {
                $_SESSION['flash_sucesso'] = 'Parabéns! Você se inscreveu na rota "' . $rota['nome'] . '"';
            }
            break;

        case 'cancelar':
            $stmt = $conn->prepare(
                'DELETE FROM rota_inscricao WHERE rota_id = :rota AND usuario_id = :usuario'
            );
            $stmt->execute([
                ':rota' => $rotaId,
                ':usuario' => $usuarioId,
            ]);
            break;

        case 'remover':
            if (!$ehDono && !$ehAdmin) {
                throw new RuntimeException('Sem permissão para remover inscrições.');
            }

            $stmt = $conn->prepare(
                'DELETE FROM rota_inscricao WHERE rota_id = :rota AND usuario_id = :usuario'
            );
            $stmt->execute([
                ':rota' => $rotaId,
                ':usuario' => $alvoUsuarioId,
            ]);
            break;
    }
} catch (Throwable $e) {
    // Silencia o erro para o usuário final, mas garante que não haja interrupções.
}

$redirect = $_POST['redirect'] ?? ('../paginas/ver-rota.php?id=' . $rotaId);

if (preg_match('/^https?:/i', $redirect) || strpos($redirect, '\n') !== false) {
    $redirect = '../paginas/perfil.php';
}

header('Location: ' . $redirect);
exit;
