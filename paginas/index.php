<?php
session_start();
require_once '../includes/config.php'; // conexão PDO

// Busca os passeios com PDO
$sql = "SELECT r.id, r.nome, r.descricao, r.categorias, r.capa, r.localidade, u.nome AS criador
        FROM passeios r
        JOIN usuario u ON r.usuario_id = u.id
        ORDER BY r.criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$passeios = $stmt->fetchAll(PDO::FETCH_ASSOC); // aqui criamos o array certo
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <script defer src="../assets/js/modal.js"></script>
    <title>Home</title>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="pesquisa-home">
        <div class="coluna1">
            <input class="input1" type="search" placeholder="Para onde você vai?">
        </div>

        <div class="coluna2">
            <input class="input2" type="text" id="data" placeholder="Quando você vai?" onfocus="this.type='date'">

            <input class="input3" type="search" placeholder="Quem vai com você?">
        </div>

        <div class="coluna3">
            <button>Pesquisar</button>
        </div>
    </div>


    <div class="destinos">
        <h3>Destinos mais procurados</h3>

        <!-- Primeira imagem -->
        <div class="destinoUp">
            <?php if (!empty($passeios[0])): ?>
                <img src="<?= htmlspecialchars($passeios[0]['capa']) ?>" alt="Capa do passeio">
            <?php else: ?>
                <p>Nenhum passeio encontrado.</p>
            <?php endif; ?>
        </div>

        <div class="destinoDown">
            <!-- Segunda imagem -->
            <div class="destinoL">
                <?php if (!empty($passeios[1])): ?>
                    <img src="<?= htmlspecialchars($passeios[1]['capa']) ?>" alt="Capa do passeio">
                <?php else: ?>
                    <p>Nenhum passeio encontrado.</p>
                <?php endif; ?>
            </div>

            <!-- Terceira imagem -->
            <div class="destinoR">
                <?php if (!empty($passeios[2])): ?>
                    <img src="<?= htmlspecialchars($passeios[2]['capa']) ?>" alt="Capa do passeio">
                <?php else: ?>
                    <p>Nenhum passeio encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="Mais">
        <h3>Conheça o interior</h3>
        <div class="MaisUp">
            <div class="up1">
                <div class="item"></div>
                <div class="item"></div>
                <div class="item"></div>
            </div>

            <div class="up2">
                <div class="item"></div>
                <div class="item"></div>
                <div class="item"></div>
            </div>
        </div>

        <div class="MaisDown">
            <div class="Mais1"></div>
            <div class="Mais2"></div>
        </div>
    </div>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        document.getElementById('data').onblur = function() {
            if (this.value == '') {
                this.type = 'text';
            }
        }
    </script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
