<?php
session_start();
require_once './includes/config.php'; // conexão PDO

$sql = "SELECT r.id, r.nome, r.descricao, r.categorias, r.capa, u.nome AS criador
        FROM rota r
        JOIN usuario u ON r.usuario_id = u.id
        ORDER BY r.criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$rotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/header.css">
    <link rel="stylesheet" href="./assets/css/footer.css">
    <link rel="stylesheet" href="./assets/css/roteiro.css">
    <script defer src="./assets/js/modal.js"></script>
    <title>Roteiro</title>
</head>

<body>

    <?php include 'includes/header.php' ?>

    <div class="explore">
        <div class="up">
            <div class="roteiro">
                <input class="input" type="search" placeholder="Explore roteiros">
            </div>
        </div>

        <div class="down">
            <div class="tipo">
                <input class="input" type="search" placeholder="Tipo de passeio">
            </div>

            <div class="duracao">
                <input class="input" type="search" placeholder="Duração">
            </div>

            <div class="estilo">
                <input class="input" type="search" placeholder="Estilo (cultural, aventura....)">
            </div>
        </div>

        <button>Pesquisar</button>
    </div>

    <div class="criar">
        <h2>Deseja criar seu próprio roteiro?</h2>
        <button><a href="criar-roteiro.php">Criar novo roteiro</a></button>
    </div>

    <div class="prontos">
        <h2>Roteiros prontos</h2>

        <?php if (count($rotas) > 0): ?>
            <?php foreach ($rotas as $rota): ?>
                <div class="passeio">
                    <img src="<?= htmlspecialchars($rota['capa'] ?: './assets/img/placeholder.jpg') ?>"
                        alt="<?= htmlspecialchars($rota['nome']) ?>">

                    <div class="escrita">
                        <div class="nome">
                            <h4><?= htmlspecialchars($rota['nome']) ?></h4>
                        </div>

                        <div class="criador">
                            <h4><?= htmlspecialchars($rota['criador']) ?></h4>
                        </div>

                        <div class="estilo">
                            <h4><?= htmlspecialchars($rota['categorias']) ?></h4>
                        </div>
                    </div>

                    <div class="botao2">
                        <a href="ver-rota.php?id=<?= $rota['id'] ?>">
                            <button><i class="ph ph-caret-right"></i></button>
                        </a>
                    </div>
                </div>


<?php endforeach; ?>
<?php else: ?>
    <p>Nenhum roteiro cadastrado ainda.</p>
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    function toggleDetalhes(id) {
        const detalhes = document.getElementById("detalhes-" + id);
        if (detalhes.style.display === "none") {
            detalhes.style.display = "block";
        } else {
            detalhes.style.display = "none";
        }
    }
</script>
</body>

</html>