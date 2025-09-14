<?php
session_start();
require_once './includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
$nivel = $_SESSION['nivel'] ?? 'user';


?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/header_perfil.css">
    <link rel="stylesheet" href="./assets/css/footer.css">
    <link rel="stylesheet" href="./assets/css/perfil.css">
    <script defer src="./assets/js/modal.js"></script>
    <title>Perfil</title>
</head>

<body>
    <?php include 'includes/header-perfil.php'; ?>
    <a href="logout.php">Sair</a>

    <?php if ($nivel === 'admin'): ?>
        <div class="admin-opcoes">
            <h2>Área do Administrador</h2>
            <ul>
                <li><a href="gerenciar_usuarios.php">👤 Gerenciar Usuários</a></li>
                <li><a href="relatorios.php">📊 Relatórios</a></li>
                <li><a href="configuracoes_site.php">⚙️ Configurações do Site</a></li>
                <li><a href="logs.php">📝 Logs do Sistema</a></li>
            </ul>
        </div>
    <?php endif; ?>

    <div class="certificado">
        <h3>Certificado</h3>
        <div class="blocos1">
            <div class="item"></div>
            <div class="item"></div>
            <div class="item"></div>
        </div>
    </div>

    <div class="favoritos">
        <h2>Favortios</h2>
        <h3>Pastas</h3>

        <div class="blocos2">
            <div class="item"></div>
            <div class="item"></div>
            <div class="item"></div>
            <div class="item"><i class="ph ph-plus"></i></div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>