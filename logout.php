<?php
session_start(); // Inicia a sessão

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Apaga o cookie da sessão para garantir que o navegador esqueça
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

// Redireciona para a página de login ou home depois do logout
header("Location: login.php");
exit;
?>
