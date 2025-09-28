<?php
session_start();
require_once '../includes/config.php'; // conexão PDO

$sql = "SELECT r.id, r.nome, r.descricao, r.categorias, r.capa, r.localidade, u.nome AS criador
        FROM passeios r
        JOIN usuario u ON r.usuario_id = u.id
        ORDER BY r.criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$passeios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/passeios.css">
    <script defer src="./assets/js/modal.js"></script>
    <title>Passeios</title>
</head>

<body>
    
    <?php include '../includes/header.php' ?>

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

    <div class="destino">
        <h3>Destinos por perto</h3>

        <div class="blocos">
            <div class="left">
            <?php
// Supondo que $passeios é o array com todos os passeios
if (count($passeios) > 0) {
    $passeio = $passeios[0]; // pega o primeiro passeio
    ?>
    <img src="<?= htmlspecialchars($passeio['capa']) ?>" alt="Capa do passeio" />
    <?php
} else {
    echo "<p>Nenhum passeio encontrado.</p>";
}
?>

            </div>

            <div class="right">
            <?php
// Supondo que $passeios é o array com todos os passeios
if (count($passeios) > 0) {
    $passeio = $passeios[1]; // pega o primeiro passeio
    ?>
    <img src="<?= htmlspecialchars($passeio['capa']) ?>" alt="Capa do passeio" />
    <?php
} else {
    echo "<p>Nenhum passeio encontrado.</p>";
}
?>
            </div>
        </div>
    </div>

    <div class="help">
        <h3>Nós podemos te ajudar</h3>

        <div class="ajudas">
            <div class="atracoes">
                <div class="texto">
                        <h5><i class="ph ph-balloon"></i>Conheça as principais atrações</h5>

                    <span class="spam">Curta o melhor no destino escolhido: atrações, passeios, museus e muito mais</span>
                </div>
            </div>

            <div class="reserva">
                <div class="texto">
                    <h5><i class="ph ph-calendar-check"></i>Rapidez e flexibilidade</h5>

                    <span class="spam">Reserve hoteis on-line em minutos, com cancelamento grátis em muitas atrações</span>
                </div>
            </div>

            <div class="aqui">
                <div class="texto">
                        <h5><i class="ph ph-headset"></i>Ajuda sempre que precisar</h5>

                    <span class="spam">A equipe de Apoio ao Cliente está aqui para te ajudar 24 horas por dia</span>
                </div>
            </div>
        </div>
    </div>

    <div class="explore">
        <h3>Explore seus destinos</h3>
        <div class="local">
            <div class="cidade">
                <h4>Boituva</h4>
            </div>

            <div class="cidade">
                <h4>Botucatu</h4>
            </div>

            <div class="cidade">
                <h4>Brotas</h4>
            </div>

            <div class="cidade">
                <h4>Cesário&nbsp;Lange</h4>
            </div>

            <div class="cidade">
                <h4>Tatuí</h4>
            </div>

            <div class="cidade">
                <h4>Aparecida</h4>
            </div>

            <div class="cidade">
                <h4>Cunha</h4>
            </div>

            <div class="cidade">
                <h4>Itu</h4>
            </div>

            <div class="cidade">
                <h4>Olímpia</h4>
            </div>

            <div class="cidade">
                <h4>Águas&nbsp;de&nbsp;Lindóia</h4>
            </div>
        </div>

        <div class="destaques">
            <div class="up">
                <div class="quadrado"></div>
                <div class="quadrado"></div>
                <div class="quadrado"></div>
                <div class="quadrado"></div>
            </div>

            <div class="down">
                <div class="quadrado"></div>
                <div class="quadrado"></div>
                <div class="quadrado"></div>
                <div class="quadrado"></div>
            </div>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>     
</body>