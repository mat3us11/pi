<?php
session_start();
require_once './includes/config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: perfil.php");
    exit;
}

$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$filtroNivel = isset($_GET['nivel']) ? trim($_GET['nivel']) : '';

$sql = "SELECT id, nome, email, nivel, foto_perfil 
        FROM usuario 
        WHERE 1=1";

$params = [];

if ($busca !== '') {
    $sql .= " AND (nome LIKE :busca OR email LIKE :busca)";
    $params[':busca'] = "%$busca%";
}

if ($filtroNivel !== '') {
    $sql .= " AND nivel = :nivel";
    $params[':nivel'] = $filtroNivel;
}

$sql .= " ORDER BY id ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);


$ocultarImagemHeader = true; 
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./assets/css/header.css">
    <link rel="stylesheet" href="./assets/css/footer.css">
    <link rel="stylesheet" href="./assets/css/gerenciar-usuarios.css">
    <script defer src="./assets/js/modal.js"></script>
    <title>Gerenciar Usuários</title>
</head>

<body>
    <?php include 'includes/header.php' ?>
    <div class="usuarios-opcoes">
        <div class="usuarios-wrapper">
            <h2>Gerenciamento de Usuários</h2>
            <p>Aqui você pode visualizar, editar e excluir usuários cadastrados no sistema.</p>

            <form class="usuarios-filtros" method="get" action="">
                <input type="text" name="busca" placeholder="Buscar por nome ou email" value="<?= htmlspecialchars($busca) ?>">
                <select name="nivel">
                    <option value="">Todos os níveis</option>
                    <option value="admin" <?= $filtroNivel === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="usuario" <?= $filtroNivel === 'usuario' ? 'selected' : '' ?>>Usuário</option>
                </select>
                <button type="submit">Filtrar</button>
            </form>

            <div class="usuarios-tabela">
                <table>
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Nível</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>
                                        <div class="usuarios-info">
                                            <img src="<?= $usuario['foto_perfil'] ?: './assets/img/imagem-padrao.png' ?>" alt="Foto">
                                            <?= htmlspecialchars($usuario['nome']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td><?= $usuario['nivel'] ?></td>
                                    <td>
                                        <button class="usuarios-btn-acao">Editar</button>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Nenhum usuário encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>