<?php
session_start();
require_once '../includes/config.php';

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
    <link rel="stylesheet" href="../assets/css/header_perfil.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/perfil.css">
    <script defer src="../assets/js/modal.js"></script>
    <title>Perfil</title>
</head>

<body>
    <?php include '../includes/header-perfil.php'; ?>

    <?php if ($nivel === 'admin'): ?>
        <br><br>
        <div class="admin">
            <div class="admin-opcoes">
            <h2>Área do Administrador</h2>
            <ul>
                <div class="opcoes-cima">
                    <li><a href="#">Criar Rota</a></li>
                    <li><a href="./adicionar_local.php">Adcionar local</a></li>
                    <li><a href="#">Emitir Certificados</a></li>
                </div>
                <div class="opcoes-baixo">
                    <li><a href="#">Gerenciar Inscrições</a></li>
                    <li><a href="gerenciar_usuarios.php">Gerenciar Usuários</a></li>
                    <li><a href="#">Gerenciar Publicações</a></li>
                </div>
            </ul>
        </div>
        </div>
    <?php endif; ?>


    <br><br>

    <div class="certificado">
        <h3>Certificado</h3>
        <div class="blocos1">
            <div class="item"></div>
            <div class="item"></div>
            <div class="item"></div>
        </div>
    </div>

    <br><br>

    <div class="favoritos">
        <h2>Favoritos</h2>
        <h3>Pastas</h3>

        <div class="blocos2">
            <div class="item"></div>
            <div class="item"></div>
            <div class="item"></div>
            <div class="item"><i class="ph ph-plus"></i></div>
        </div>

    </div>

    
    


    <?php include '../includes/footer.php'; ?>
</body>

</html>