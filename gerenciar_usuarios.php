<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel'] !== 'admin') {
    // Redireciona para o perfil ou página inicial
    header("Location: perfil.php");
    exit;
}

// A partir daqui só entra quem for admin
?>
