<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Roteiro</title>
  <link rel="stylesheet" href="./assets/css/header.css">
  <link rel="stylesheet" href="./assets/css/footer.css">
  <link rel="stylesheet" href="./assets/css/criar-roteiro.css">
  <script defer src="./assets/js/modal.js"></script>
   <script defer src="./assets/js/criar-roteiro.js"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="container-capa">
    <div class="capa" onclick="document.getElementById('foto-capa').click()">
      <div class="placeholder">
        Faça upload da capa do seu local <i class="ph ph-upload-simple"></i>
      </div>
      <img id="preview" alt="Prévia da imagem">
      <input type="file" id="foto-capa" name="foto-capa" accept="image/*" class="hidden-file">
    </div>
  </div>

  <div class="informacoes-rota">
    <div class="informacoes-soltas">
      <label for="nome_rota">Nome da rota</label>
      <input type="text" name="nome_rota" id="nome_rota" placeholder="Nome (Obrigatório)">
    </div>

    <div class="informacoes-soltas">
      <label for="descricao_rota">Descrição</label>
      <textarea name="descricao_rota" id="descricao_rota" placeholder="Descrição da rota (Obrigatório)"></textarea>
    </div>

    <div class="informacoes-soltas">
      <label>Categorias</label>
      <div class="dropdown">
        <div class="dropdown-header" onclick="toggleDropdown()">
          <span id="dropdown-text">Selecione uma ou mais categorias</span>
          <i class="ph ph-caret-down"></i>
        </div>
        <div class="dropdown-content" id="dropdown-content">
          <label><input type="checkbox" value="cultural"> Cultural</label>
          <label><input type="checkbox" value="aventura"> Aventura</label>
          <label><input type="checkbox" value="gastronomica"> Gastronômicas</label>
          <label><input type="checkbox" value="ecologica"> Ecológicas</label>
          <label><input type="checkbox" value="citytour"> City Tour</label>
        </div>
      </div>
    </div>
  </div>

  <div class="adicionar-rota">
    <h3>Adicionar Rota</h3>

    <div class="rota-item">
      <label><i class="ph ph-arrow-circle-up"></i> Ponto de Partida</label>
      <input type="text" id="pontoPartida" name="ponto_partida" placeholder="Localização">
    </div>

    <div class="rota-item">
      <label><i class="ph ph-map-pin"></i> Destino</label>
      <input type="text" id="destino" name="destino" placeholder="Localização">
    </div>

    <div id="paradas"></div>

    <button type="button" id="btn-add-parada" class="btn-discreto">
      + Adicionar Parada
    </button>

    <button type="submit" class="btn-principal">Publicar</button>
  </div>

  <?php include 'includes/footer.php'; ?>
</body>
</html>
