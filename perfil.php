<?php
session_start();
require_once './includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem = '';
$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["nova_foto"])) {
    $id_usuario = $_SESSION["usuario_id"];
    $foto = $_FILES["nova_foto"];

    if ($foto["error"] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($foto["name"], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extensao, $permitidas)) {
            $erro = "Formato de imagem inválido. Use JPG, JPEG, PNG ou WEBP.";
        } else {
            $pasta = "uploads/";
            if (!is_dir($pasta)) {
                mkdir($pasta, 0755, true);
            }

            $nomeArquivo = "foto_" . $id_usuario . "_" . time() . "." . $extensao;
            $caminho = $pasta . $nomeArquivo;
            if (move_uploaded_file($foto["tmp_name"], $caminho)) {
                // Busca a foto anterior
                $stmt = $conn->prepare("SELECT foto_perfil FROM usuario WHERE id = ?");
                $stmt->execute([$id_usuario]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                $foto_antiga = $usuario['foto_perfil'] ?? '';
                if (
                    $foto_antiga &&
                    strpos($foto_antiga, 'uploads/') === 0 &&
                    file_exists($foto_antiga) &&
                    $foto_antiga !== './assets/img/imagem-padrao.png'
                ) {
                    unlink($foto_antiga);
                }

                $stmt = $conn->prepare("UPDATE usuario SET foto_perfil = ? WHERE id = ?");
                $stmt->execute([$caminho, $id_usuario]);
                $_SESSION["usuario_foto"] = $caminho;

                $mensagem = "Foto atualizada com sucesso!";
            } else {
                $erro = "Erro ao mover o arquivo.";
            }
        }
    } else {
        $erro = "Erro no upload da imagem.";
    }
}
?>

<h3>Alterar foto de perfil</h3>

<?php if (!empty($mensagem)) echo "<p style='color: green;'>$mensagem</p>"; ?>
<?php if (!empty($erro)) echo "<p style='color: red;'>$erro</p>"; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="nova_foto" accept="image/*" required>
    <button type="submit">Atualizar Foto</button>
</form>

<br>
<a href="logout.php">Sair</a>
