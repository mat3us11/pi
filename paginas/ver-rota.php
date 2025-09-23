<?php
session_start();
require_once '../includes/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Rota inválida.");
}

$id_rota = (int)$_GET['id'];

// Buscar rota no banco com info do usuário
$sql = "SELECT r.*, u.nome AS criador
        FROM rota r
        JOIN usuario u ON r.usuario_id = u.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_rota]);
$rota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rota) {
    die("Rota não encontrada.");
}

// Transformar JSON de paradas em array
$paradas = $rota['paradas'] ? json_decode($rota['paradas'], true) : [];
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
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="rota-detalhe" style="width: 70%; margin: 30px auto; padding: 20px; background: #f5f5f5; border-radius: 12px;">
    <a class="voltar-rota" href="roteiro.php">← Voltar para roteiros</a>
    <h2><?= htmlspecialchars($rota['nome']) ?></h2>
    <p><strong>Criador:</strong> <?= htmlspecialchars($rota['criador']) ?></p>
    <p><strong>Categorias:</strong> <?= htmlspecialchars($rota['categorias']) ?></p>
    <img src="<?= htmlspecialchars($rota['capa'] ?: '../assets/img/placeholder.jpg') ?>" alt="Capa" style="width:100%; max-height:300px; object-fit:cover; border-radius: 8px; margin: 15px 0;">
    <p><strong>Descrição:</strong></p>
    <p><?= nl2br(htmlspecialchars($rota['descricao'])) ?></p>

    <p><strong>Ponto de Partida:</strong> <?= htmlspecialchars($rota['ponto_partida']) ?></p>
    <p><strong>Destino:</strong> <?= htmlspecialchars($rota['destino']) ?></p>

    <?php if(count($paradas) > 0): ?>
        <p><strong>Paradas:</strong></p>
        <ul>
            <?php foreach($paradas as $p): ?>
                <li><?= htmlspecialchars($p) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>



<?php include '../includes/footer.php'; ?>
</body>
</html>
