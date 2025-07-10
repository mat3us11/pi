<?php
require_once './includes/config.php'; // conexão com o banco

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome  = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Validação simples
    if (empty($nome) || empty($email) || empty($senha)) {
        echo "Preencha todos os campos.";
        exit;
    }

    // Verifica se e-mail já existe
    $stmt = $conn->prepare("SELECT id FROM usuario WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo "Este e-mail já está cadastrado.";
        exit;
    }

    // Criptografa a senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere o novo usuário com nome
    $stmt = $conn->prepare("INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)");
    $resultado = $stmt->execute([$nome, $email, $senha_hash]);

    if ($resultado) {
        header("Location: login.php");
        exit;
    } else {
        echo "Erro ao cadastrar.";
    }
}
?>
