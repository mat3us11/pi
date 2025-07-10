<?php
session_start();
require_once './includes/config.php';

$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? '';
    $senha = $_POST["senha"] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        // Agora buscamos o nome também
        $stmt = $conn->prepare("SELECT id, nome, senha FROM usuario WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($senha, $usuario["senha"])) {
                $_SESSION["usuario_id"] = $usuario["id"];
                $_SESSION["usuario_nome"] = $usuario["nome"]; // pega do banco
                $_SESSION["usuario_email"] = $email;
                header("Location: index.php"); // Redireciona para a página inicial
                exit;
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            $erro = "Usuário não encontrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script defer src="./assets/js/modal.js"></script>
  <link rel="stylesheet" href="./assets/css/login.css" />
  <link rel="stylesheet" href="./assets/css/header.css" />
  <title>Login</title>
</head>

<body>
  <?php
    $ocultarImagemHeader = true;
    $ocultarBotoesHeader = true;
    include 'includes/header.php';
  ?>
  <div class="area-cadastro">
    <div class="cartao">
      <div class="lado-esquerdo">
        <img src="./assets/img/carro-login-cadastro.svg" alt="Imagem carro turismo" />
        <div class="texto-beneficios">
          <p><i class="ph ph-check"></i>Encontre as melhores ofertas de hospedagem e pacotes turísticos personalizados para o seu perfil.</p>
          <p><i class="ph ph-check"></i>Acompanhe e gerencie suas reservas de forma simples e rápida, em qualquer dispositivo.</p>
          <p><i class="ph ph-check"></i>Receba notificações com promoções exclusivas e alertas de preço para destinos de interesse.</p>
        </div>
      </div>

      <div class="lado-direito">
        <h3>Faça login ou crie uma conta</h3>

        <?php if (!empty($erro)): ?>
          <p style="color: red; margin-bottom: 10px;"><?php echo $erro; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="email" name="email" id="email" placeholder="Digite o endereço de e-mail" required />
          <input type="password" name="senha" id="senha" placeholder="Digite a senha" required />
          <button class="botao-continuar" type="submit">Continuar</button>
        </form>

        <a href="cadastro.php">
          <p class="criar-conta">Criar conta</p>
        </a>
        <p class="ou">ou continue com</p>
        <div class="botoes-sociais">
          <button><img src="https://img.icons8.com/color/48/000000/google-logo.png" />Google</button>
          <button><img src="https://img.icons8.com/ios-filled/50/000000/mac-os.png" />Apple</button>
          <button><img src="https://img.icons8.com/ios-filled/50/1877f2/facebook-new.png" />Facebook</button>
        </div>
      </div>
    </div>
  </div>


</body>

</html>
