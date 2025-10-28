<?php
session_start();
require_once '../includes/config.php';

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
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/gerenciar-usuarios.css">
    <script defer src="../assets/js/modal.js"></script>
    <title>Gerenciar Usuários</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

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
                                    $foto = $usuario['foto_perfil'] ? $usuario['foto_perfil'] : '../assets/img/imagem-padrao.png';
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
        <div class="modal-content usuario-detalhes" role="dialog" aria-modal="true" aria-labelledby="tituloModal">
            <div class="modal-header">
                <span class="close" id="fecharModal" role="button" aria-label="Fechar">&times;</span>
            </div>

            
            <h2 class="titulo-modal">Gerenciar Usuário</h2>

            <div class="geral">
                <!-- ESQUERDA -->
                <div class="content_esquerda">

                    <div class="usuario-header">
                        <div class="foto-wrapper">
                            <div id="fotoPerfil" class="foto-perfil-grande" style="background-image: url('../assets/img/imagem-padrao.png');"></div>
                        </div>

                        <div class="infoUsuario">
                            <h3 id="nomeUsuario"><span id="infoUsuario"></span></h3>
                            <p id="emailUsuario"></p>
                            <br>
                            <br>
                            
                            
                        </div>
                    </div>

                

                    <select id="nivelUsuarioSelect">
                                <option value="" id="nivelUsuario"></option>
                            <option value="usuario">Usuário comum</option>
                            <option value="admin">Administrador</option>
                    </select>

                    <div class="usuario-coluna-esquerda">
                        <h4>Informações administrativas</h4>

                        <div class="info-admin">
                        <div>
                            <p><strong>Total de rotas criadas</strong></p>
                            <p id="rotasCriadas">3 (2 ativas)</p>
                        </div>
                        <div>
                            <p><strong>Total de rotas inscritas</strong></p>
                            <p id="rotasInscritas">7</p>
                        </div>
                        </div>

                        <div class="botoes-admin">
                        <button id="btnExcluir">Excluir usuário</button>
                        <button id="btnRedefinir">Resetar senha</button>
                        </div>

                        <div class="status-conta">
                        <label for="statusConta">Status da conta</label>
                        <select id="statusConta">
                            <option value="ativa">Ativa</option>
                            <option value="inativa">Inativa</option>
                        </select>
                        </div>

                        <div class="datas-conta">
                        <p><strong>Data de cadastro:</strong> 10 de Julho de 2024</p>
                        <p><strong>Último acesso:</strong> 24 de Maio de 2025</p>
                        </div>
                    </div>

                    <button class="btn-salvar" id="btnSalvar">Salvar</button>

                </div>

                <!-- DIREITA -->
                <div class="content_direita">

                        <div class="usuario-coluna-direita">
                            <div class="rotas-section">
                            <h4>Rotas Criadas</h4>
                            <div id="rotasCriadasList" class="rotas-lista">
                                <div class="rota-item">
                                <img src="../assets/img/igreja.jpg" alt="Rota">
                                <div class="rota-info">
                                    <p class="rota-titulo">Rota da Igreja - Porangabinha</p>
                                    <p class="rota-data">16 de Janeiro de 2025</p>
                                </div>
                                <span class="status-label ativa">Ativa</span>
                                </div>

                                <div class="rota-item">
                                <img src="../assets/img/porangaba.jpg" alt="Rota">
                                <div class="rota-info">
                                    <p class="rota-titulo">Passeio em Porangaba</p>
                                    <p class="rota-data">21 de Fevereiro de 2025</p>
                                </div>
                                <span class="status-label inativa">Inativa</span>
                                </div>

                                <div class="rota-item">
                                <img src="../assets/img/zoologico.jpg" alt="Rota">
                                <div class="rota-info">
                                    <p class="rota-titulo">Passeio Animalia Park</p>
                                    <p class="rota-data">29 de Abril de 2025</p>
                                </div>
                                <span class="status-label ativa">Ativa</span>
                                </div>
                            </div>
                            </div>

                            <div class="rotas-section">
                            <h4>Rotas Inscritas</h4>
                            <div id="rotasInscritasList" class="rotas-lista">
                                <div class="rota-item">
                                <img src="../assets/img/tatui.jpg" alt="Rota">
                                <div class="rota-info">
                                    <p class="rota-titulo">Passeio cultural em Tatuí</p>
                                    <p class="rota-data">16 de Janeiro de 2025</p>
                                </div>
                                </div>

                                <div class="rota-item">
                                <img src="../assets/img/itu.jpg" alt="Rota">
                                <div class="rota-info">
                                    <p class="rota-titulo">Rolê Gastronômico em Itu</p>
                                    <p class="rota-data">14 de Maio de 2025</p>
                                </div>
                                </div>

                                <div class="rota-item">
                                <img src="../assets/img/boituva.jpg" alt="Rota">
                                <div class="rota-info">
                                    <p class="rota-titulo">Paraquedismo em Boituva</p>
                                    <p class="rota-data">29 de Abril de 2025</p>
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        </div>

                    </div>
                </div>


        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
