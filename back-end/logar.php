<?php
session_start();
require_once '../includes/config.php';



// Impede que o usuário acese a página depois de logado
// if (isset($_SESSION["logado"]) && $_SESSION["logado"] === true) {
//     header("Location: index.php");
// }


// === LOGIN MANUAL (MySQL) ===
$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = $_POST["email"] ?? '';
  $senha = $_POST["senha"] ?? '';

  if (empty($email) || empty($senha)) {
    $erro = "Preencha todos os campos.";
  } else {
    $stmt = $conn->prepare("SELECT id_usuario, nome, senha FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
      $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
      if (password_verify($senha, $usuario["senha"])) {
        $_SESSION["usuario_id"] = $usuario["id_usuario"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
        $_SESSION["usuario_email"] = $email;

        $_SESSION["logado"] = true;
        header("Location: index.php");
        exit;
      } else {
        $erro = "Verifique se os campos foram preenchidos corretamente.";
      }
    } else {
      $erro = "Verifique se os campos foram preenchidos corretamente.";
    }
  }
}

?>
