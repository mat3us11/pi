<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Redireciona para o login
    header("Location: login.php");
    exit;
}
?>


<a href="logout.php">Sair</a>
