<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script defer src="./assets/js/modal.js"></script>
  <link rel="stylesheet" href="../assets/css/duvidas.css" />
  <link rel="stylesheet" href="../assets/css/header.css" />
  <link rel="stylesheet" href="../assets/css/footer.css" />
  <title>Cadastro</title>
</head>


<body>


<?php 
    include '../includes/header.php';
?>

  <div class="titulo">
    <h2>Dúvidas Frequentes</h2>
    
  </div>

  <label id="assunto" for="assuntos"></label>
  
  <select id="assuntos">
    <option value="">Selecione um assunto</option>
    <option value="rotas">Como criar Rotas?</option>
    <option value="perfil">Como editar meu perfil?</option>
    <option value="reservas">Onde posso acompanhar minhas reservas e histórico de viagens?</option>
    <option value="integracao">A Campvia oferece integração com transporte no destino?</option>
  </select>

  <div id="resposta"></div>


  <?php include '../includes/footer.php'; ?>
  
  <script>
    const select = document.getElementById('assuntos');
    const resposta = document.getElementById('resposta');

    const respostas = {
      rotas: "Para criar rotas, vá até o menu 'Rotas' e clique em 'Nova Rota'. Preencha as informações e salve.",
      perfil: "Você pode editar seu perfil acessando o menu 'Minha Conta' e clicando em 'Editar Perfil'.",
      reservas: "Acompanhe suas reservas e histórico de viagens na seção 'Minhas Viagens'.",
      integracao: "Sim, a Campvia oferece integração com alguns serviços locais de transporte. Verifique a disponibilidade no seu destino."
    };

    select.addEventListener('change', function() {
      const valorSelecionado = select.value;
      if (respostas[valorSelecionado]) {
        resposta.textContent = respostas[valorSelecionado];
        resposta.style.display = 'block';
      } else {
        resposta.style.display = 'none';
      }
    });
  </script>

</body>
</html>