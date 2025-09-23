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
                <input class="input" type="search" placeholder="Estilo (cultural, aventura.... ">
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
        <div class="passeio">
            <img src="" alt="cidade">

            <div class="escrita">
                <div class="nome">
                    <h4>Boituva</h4>
                </div>

                <div class="criador">
                    <h4>Roberto</h4>
                </div>

                <div class="estilo">
                    <h4>Cultural, gastronomico</h4>
                </div>
            </div>

            <div class="botao2">
                <button><i class="ph ph-caret-right"></i></button>
            </div>
        </div>

        <div class="passeio">
            <img src="" alt="cidade">

            <div class="escrita">
                <div class="nome">
                    <h4>Boituva</h4>
                </div>

                <div class="criador">
                    <h4>Roberto</h4>
                </div>

                <div class="estilo">
                    <h4>Cultural, gastronomico</h4>
                </div>
            </div>

            <div class="botao2">
                <button><i class="ph ph-caret-right"></i></button>
            </div>
        </div>

        <div class="passeio">
            <img src="" alt="cidade">

            <div class="escrita">
                <div class="nome">
                    <h4>Boituva</h4>
                </div>

                <div class="criador">
                    <h4>Roberto</h4>
                </div>

                <div class="estilo">
                    <h4>Cultural, gastronomico</h4>
                </div>
            </div>

            <div class="botao2">
                <button><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>    

</body>