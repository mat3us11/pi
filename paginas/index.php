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

    <div class="ofertas">
        <h3>Ofertas</h3>
        <div class="visualizer">

            <div class="left">
                <i class="ph ph-arrow-left"></i>
                <div class="ofertaL">

                    </div>
            </div>

            <div class="right">
                
                    <div class="ofertaR">
                        <i class="ph ph-arrow-right"></i>
                    </div>
                    
            </div>
        </div>
    </div>


    <div class="destinos">
        <h3>Destinos mais procurados</h3>
            <div class="destinoUp">

            </div>

            <div class="destinoDown">

                <div class="destinoL">

                </div>

                <div class="destinoR">

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
            <div class="Mais1">

            </div>

            <div class="Mais2">
                
            </div>
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