<?php
session_start();
require_once './includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
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

</body>

</html>
<br>