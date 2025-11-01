<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método não permitido';
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../paginas/login.php');
    exit;
}

require_once '../includes/config.php';

$idRota  = isset($_POST['id_rota']) ? (int) $_POST['id_rota'] : 0;
$acao    = $_POST['action'] ?? 'inscrever';
$csrf    = $_POST['csrf'] ?? '';
$usuario = (int) $_SESSION['usuario_id'];

if ($idRota <= 0) {
    $_SESSION['flash_inscricao_erro'] = 'Rota inválida.';
    header('Location: ../paginas/roteiro.php');
    exit;
}

if (empty($_SESSION['csrf_inscricao']) || empty($csrf) || !hash_equals($_SESSION['csrf_inscricao'], $csrf)) {
    $_SESSION['flash_inscricao_erro'] = 'Token de segurança inválido.';
    header('Location: ../paginas/ver-rota.php?id=' . $idRota);
    exit;
}

try {
    $stmt = $conn->prepare('SELECT usuario_id FROM rota WHERE id = ?');
    $stmt->execute([$idRota]);
    $rota = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rota) {
        $_SESSION['flash_inscricao_erro'] = 'Rota não encontrada.';
        header('Location: ../paginas/roteiro.php');
        exit;
    }

    if ((int) $rota['usuario_id'] === $usuario) {
        $_SESSION['flash_inscricao_erro'] = 'Você já é o criador desta rota.';
        header('Location: ../paginas/ver-rota.php?id=' . $idRota);
        exit;
    }

    if ($acao === 'cancelar') {
        $del = $conn->prepare('DELETE FROM rota_inscricao WHERE rota_id = ? AND usuario_id = ?');
        $del->execute([$idRota, $usuario]);

        if ($del->rowCount() > 0) {
            $_SESSION['flash_inscricao_sucesso'] = 'Inscrição cancelada com sucesso.';
        } else {
            $_SESSION['flash_inscricao_sucesso'] = 'Você não estava inscrito nesta rota.';
        }
    } else {
        $ins = $conn->prepare('INSERT INTO rota_inscricao (rota_id, usuario_id) VALUES (?, ?)');
        try {
            $ins->execute([$idRota, $usuario]);
            $_SESSION['flash_inscricao_sucesso'] = 'Inscrição realizada com sucesso!';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['flash_inscricao_sucesso'] = 'Você já está inscrito nesta rota.';
            } else {
                throw $e;
            }
        }
    }
} catch (PDOException $e) {
    $_SESSION['flash_inscricao_erro'] = 'Não foi possível atualizar sua inscrição.';
}

header('Location: ../paginas/ver-rota.php?id=' . $idRota);
exit;
