<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: perfil.php");
    exit;
}

// A partir daqui só entra quem for admin

?>
