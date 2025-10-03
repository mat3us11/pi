<?php
// paginas/criar-roteiro.php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once '../includes/config.php';

if (empty($_SESSION['usuario_id'])) {
  header('Location: ../paginas/login.php');
  exit;
}

if (empty($_SESSION['csrf_ia'])) {
  $_SESSION['csrf_ia'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_ia'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Roteiro (IA)</title>
  <link rel="stylesheet" href="../assets/css/header.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/editar-roteiros.css">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="wrap">
  <div class="card">
    <div class="titulo">Criar roteiro com IA</div>
    <p class="meta">Descreva o que você quer e eu monto um rascunho editável antes de publicar.</p>

    <form method="POST" action="../processos/criar-roteiro-ia.php" class="grid">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

      <div class="full">
        <label for="pedido"><strong>Pedido*</strong></label>
        <textarea id="pedido" name="pedido" rows="6" required
          placeholder="Ex.: Passeio cultural e gastronômico em Sorocaba para 1 dia, começando no Centro, orçamento baixo."></textarea>
      </div>

      <div class="rota-item">
        <label for="cidade_slug"><i class="ph ph-map-trifold"></i> Cidade (slug)</label>
        <input id="cidade_slug" name="cidade_slug" type="text" placeholder="sorocaba">
      </div>

      <div class="rota-item">
        <label for="categorias"><i class="ph ph-list-checks"></i> Categorias (opcional)</label>
        <input id="categorias" name="categorias" type="text" placeholder="cultural,gastronomica,citytour">
      </div>

      <div class="rota-item">
        <label for="ponto_partida"><i class="ph ph-arrow-circle-up"></i> Ponto de partida (opcional)</label>
        <input id="ponto_partida" name="ponto_partida" type="text" placeholder="Rua X, Bairro Y">
      </div>

      <div class="rota-item">
        <label for="destino"><i class="ph ph-map-pin"></i> Destino (opcional)</label>
        <input id="destino" name="destino" type="text" placeholder="Sorocaba, SP">
      </div>

      <div class="full btns">
        <button type="submit" class="btn btn--primary">Gerar rascunho com IA</button>
        <a class="btn btn--ghost" href="roteiro.php">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
