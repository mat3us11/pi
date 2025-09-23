<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script defer src="./assets/js/modal.js"></script>
  <link rel="stylesheet" href="../assets/css/historico.css" />
  <link rel="stylesheet" href="../assets/css/header.css" />
  <link rel="stylesheet" href="../assets/css/footer.css" />
  <title>Cadastro</title>
</head>

<?php 
    include '../includes/header.php';
?>

<body>
    <div class="main">
        <h3>Histórico</h3>

        <div class="menu">
            <div class="passeios">
                <h4>Passeios</h4>
            </div>

            <div class="rotas">
                <h4>Rotas</h4>
            </div>

            <div class="pesquisa">
                <h4>Pesquisa</h4>
            </div>
        </div>

        <div class="especifi">

            <div class="filtro">
                <h4 class="iconi">Filtro<i class="ph ph-funnel"></i></h4>
            </div>

            <div class="cidade">
                <h4>Cidade</h4>
            </div>

            <div class="categoria">
                <h4>Categoria</h4>
            </div>

            <div class="data">
                <h4 class="espaco">Data</h4>
            </div>
            
        </div>

        <div class="historico">
            <div class="img">
                <img src="" alt="A">
            </div>

            <div class="cidade">
                <h4>Porangaba</h4>
            </div>
            
            <div class="categoria">
                <h4>Ecológica</h4>
            </div>

            <div class="data">
                <h4 class="espaco">Jan 10,  2025 - Jan 25, 2025</h4>
            </div>

            <div class="bloco">
                <i class="ph ph-caret-right"></i>
            </div>
        </div>
    </div>

    
</body>
<?php 
    include '../includes/footer.php';
?>