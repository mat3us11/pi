<?php
session_start();
require_once './includes/config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: perfil.php");
    exit;
}

$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$filtroNivel = isset($_GET['nivel']) ? trim($_GET['nivel']) : '';

$sql = "SELECT id, nome, email, nivel, foto_perfil 
        FROM usuario 
        WHERE 1=1";

$params = [];

if ($busca !== '') {
    $sql .= " AND (nome LIKE :busca OR email LIKE :busca)";
    $params[':busca'] = "%$busca%";
}

if ($filtroNivel !== '') {
    $sql .= " AND nivel = :nivel";
    $params[':nivel'] = $filtroNivel;
}

$sql .= " ORDER BY id ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ocultarImagemHeader = true;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./assets/css/header.css">
    <link rel="stylesheet" href="./assets/css/footer.css">
    <link rel="stylesheet" href="./assets/css/gerenciar-usuarios.css">
    <script defer src="./assets/js/modal.js"></script>
    <title>Gerenciar Usuários</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="usuarios-opcoes">
        <div class="usuarios-wrapper">
            <h2>Gerenciamento de Usuários</h2>
            <p>Aqui você pode visualizar, editar e excluir usuários cadastrados no sistema.</p>

            <form class="usuarios-filtros" method="get" action="">
                <input type="text" name="busca" placeholder="Buscar por nome ou email" value="<?= htmlspecialchars($busca) ?>">
                <select name="nivel">
                    <option value="">Todos os níveis</option>
                    <option value="admin" <?= $filtroNivel === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="usuario" <?= $filtroNivel === 'usuario' ? 'selected' : '' ?>>Usuário</option>
                </select>
                <button type="submit">Filtrar</button>
            </form>

            <div class="usuarios-tabela">
                <table>
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Nível</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php
                                    // fallback para foto padrão
                                    $foto = $usuario['foto_perfil'] ? $usuario['foto_perfil'] : './assets/img/imagem-padrao.png';
                                ?>
                                <tr>
                                    <td>
                                        <div class="usuarios-info">
                                            <img src="<?= htmlspecialchars($foto, ENT_QUOTES) ?>" alt="Foto">
                                            <?= htmlspecialchars($usuario['nome']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td><?= htmlspecialchars($usuario['nivel']) ?></td>
                                    <td>
                                        <!-- data-atributos com ENT_QUOTES para não quebrar atributos -->
                                        <button 
                                            class="usuarios-btn-acao btn-editar"
                                            data-id="<?= htmlspecialchars($usuario['id'], ENT_QUOTES) ?>"
                                            data-nome="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES) ?>"
                                            data-email="<?= htmlspecialchars($usuario['email'], ENT_QUOTES) ?>"
                                            data-nivel="<?= htmlspecialchars($usuario['nivel'], ENT_QUOTES) ?>"
                                            data-foto="<?= htmlspecialchars($foto, ENT_QUOTES) ?>"
                                        >Editar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Nenhum usuário encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal" id="modalUsuario" aria-hidden="true">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="tituloModal">
            <div class="modal-header">
                <h2 id="tituloModal">Editar Usuário</h2>
                <span class="close" id="fecharModal" role="button" aria-label="Fechar">&times;</span>
            </div>

            <div id="fotoPerfil" class="foto-perfil-grande" style="background-image: url('./assets/img/imagem-padrao.png');"></div>

            <p id="infoUsuario"></p>
            <p>Email: <span id="emailUsuario"></span></p>
            <p>Nível: <span id="nivelUsuario"></span></p>

            <div style="display:flex; gap:10px; margin-top:10px;">
                <button id="btnExcluir">Excluir Usuário</button>
                <button id="btnRedefinir">Redefinir Senha</button>
            </div>

            <p style="margin-top:15px;">Status da conta: 
                <select id="statusConta">
                    <option value="ativa">Ativa</option>
                    <option value="inativa">Inativa</option>
                </select>
            </p>

            <button class="btn-salvar" id="btnSalvar">Salvar</button>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
