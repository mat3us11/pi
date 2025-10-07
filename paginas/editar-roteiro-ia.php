<?php
// paginas/editar-roteiro-ia.php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once '../includes/config.php';

$DEBUG = isset($_GET['dbg']) && $_GET['dbg'] == '1';

if (empty($_SESSION['usuario_id'])) { header('Location: ../paginas/login.php'); exit; }
$draft = $_SESSION['rota_draft'] ?? null;
if (!$draft) { header('Location: criar-roteiro.php'); exit; }

if (empty($_SESSION['csrf_publish'])) $_SESSION['csrf_publish'] = bin2hex(random_bytes(16));
if (empty($_SESSION['csrf_refine']))  $_SESSION['csrf_refine']  = bin2hex(random_bytes(16));
$csrfPublish = $_SESSION['csrf_publish'];
$csrfRefine  = $_SESSION['csrf_refine'];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$paradas = is_array($draft['paradas'] ?? null) ? $draft['paradas'] : [];

// Desabilita cache pra evitar token velho
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Rascunho</title>
  <link rel="stylesheet" href="../assets/css/header.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/editar-roteiros.css">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    .stop-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .stop-card{border:1px solid #e2e8f0;border-radius:12px;padding:12px;background:#fff}
    .stop-thumb{width:100%;height:140px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0}
    .stop-actions{display:flex;gap:8px;margin-top:8px}
    .xsmall{font-size:12px;color:#64748b}
    <?php if($DEBUG): ?>
    .dbg{position:fixed;bottom:8px;right:8px;z-index:9999;background:#0b1220;color:#e5e7eb;padding:10px 12px;border-radius:10px;font:12px/1.4 monospace;max-width:60vw;box-shadow:0 10px 24px rgba(0,0,0,.35)}
    .dbg strong{color:#7dd3fc}
    .dbg code{color:#a7f3d0}
    .dbg .row{margin:3px 0}
    <?php endif; ?>
  </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="wrap">
  <div class="card">
    <div class="titulo">Roteiro (rascunho)</div>
    <p class="meta">Ajuste como quiser. Você pode <strong>refinar com IA</strong> ou <strong>publicar</strong> quando estiver pronto.</p>

    <!-- PUBLICAR -->
    <form id="form-publicar" method="POST" action="../processos/publicar-rota.php" enctype="multipart/form-data" class="grid">
      <input type="hidden" name="csrf" id="csrf_publish_field" value="<?= h($csrfPublish) ?>">

      <div class="full">
        <label for="capa">Capa (opcional)</label>
        <div class="thumb">
          <img id="preview" src="<?= h($draft['capa'] ?: '../assets/img/placeholder_rosseio.png') ?>" alt="Prévia da capa">
          <input type="file" id="capa" name="capa" accept="image/*">
        </div>
      </div>

      <div class="full">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" value="<?= h($draft['nome']) ?>" required>
      </div>

      <div class="full">
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" rows="5" required><?= h($draft['descricao']) ?></textarea>
      </div>

      <div>
        <label for="categorias">Categorias</label>
        <input type="text" id="categorias" name="categorias" value="<?= h($draft['categorias']) ?>" placeholder="cultural,gastronomica,citytour">
      </div>

      <div>
        <label for="cidade_slug">Cidade (slug)</label>
        <input type="text" id="cidade_slug" name="cidade_slug" value="">
        <p class="xsmall">Se quiser sobrescrever a cidade do rascunho.</p>
      </div>

      <div class="rota-item">
        <label for="ponto_partida"><i class="ph ph-arrow-circle-up"></i> Ponto de Partida</label>
        <input type="text" id="ponto_partida" name="ponto_partida" value="<?= h($draft['ponto_partida']) ?>" required>
      </div>

      <div class="rota-item">
        <label for="destino"><i class="ph ph-map-pin"></i> Destino</label>
        <input type="text" id="destino" name="destino" value="<?= h($draft['destino']) ?>" required>
      </div>

      <div class="full">
        <label>Paradas</label>
        <div class="stop-grid" id="stops">
          <?php foreach ($paradas as $i => $p):
            $nome = $p['nome'] ?? '';
            $img  = $p['image_url'] ?? '';
            $lat  = $p['lat'] ?? '';
            $lon  = $p['lon'] ?? '';
          ?>
          <div class="stop-card">
            <?php if ($img): ?><img class="stop-thumb" src="<?= h($img) ?>" alt=""><?php endif; ?>
            <label>Nome</label>
            <input type="text" name="paradas[<?= $i ?>][nome]" value="<?= h($nome) ?>">
            <div style="display:none;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
              <div><label>Lat</label><input type="text" name="paradas[<?= $i ?>][lat]" value="<?= h($lat) ?>"></div>
              <div><label>Lon</label><input type="text" name="paradas[<?= $i ?>][lon]" value="<?= h($lon) ?>"></div>
            </div>
            <input type="hidden" name="paradas[<?= $i ?>][image_url]" value="<?= h($img) ?>">
            <div class="stop-actions">
              <button type="button" class="btn btn--ghost" onclick="removerParada(this)">Remover</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="btns" style="margin-top:8px;">
          <button type="button" class="btn btn--ghost" onclick="adicionarParada()">+ Adicionar parada</button>
        </div>
      </div>

      <div class="full btns">
        <a class="btn btn--ghost" href="criar-roteiro.php">← Voltar</a>
        <button type="submit" class="btn btn--primary">Publicar</button>
      </div>
    </form>
  </div>
  <!-- Refino com IA (gera nova versão OU atualiza a atual) -->
    <div class="card" style="margin-top:16px;">
      <div class="titulo">Refinar com IA (Gemini)</div>
      <p style="margin:6px 0 12px;">
        Por padrão, o refino vai <strong>atualizar esta mesma rota</strong> após você publicar o rascunho.
      </p>

      <form method="POST" action="../processos/refinar-roteiro-ia.php" class="grid" autocomplete="off">
        <!-- CSRF do refino -->
        <input type="hidden" name="csrf" value="<?= h($csrfRefine) ?>">
        <!-- ID da rota base -->
        <input type="hidden" name="refinar_de" value="<?= (int)($draft['id'] ?? 0) ?>">
        <!-- SINALIZA QUE O RASCUNHO DEVE SUBSTITUIR A ROTA -->
        <input type="hidden" name="overwrite" value="1">

        <div class="full">
          <label for="pedido_ia">O que melhorar / restrições</label>
          <textarea id="pedido_ia" name="pedido" rows="4" placeholder="Ex.: reduzir custo, priorizar locais ao ar livre, incluir café especial, horário entre 10h–18h"></textarea>
        </div>

        <div class="rota-item">
          <label for="ponto_partida_ia"><i class="ph ph-arrow-circle-up"></i> Ponto de Partida (base)</label>
          <input type="text" id="ponto_partida_ia" name="ponto_partida" value="<?= h($draft['ponto_partida']) ?>">
        </div>

        <div class="rota-item">
          <label for="destino_ia"><i class="ph ph-map-pin"></i> Destino (base)</label>
          <input type="text" id="destino_ia" name="destino" value="<?= h($draft['destino']) ?>">
        </div>

        <div class="full">
          <label for="categorias_ia">Categorias (opcional)</label>
         <input type="text" id="categorias_ia" name="categorias" value="<?= h($draft['categorias'] ?? '') ?>" placeholder="cultural,gastronomica,citytour">

        </div>

        <div class="full btns">
          <button type="submit" class="btn btn--primary">Gerar rascunho</button>
        </div>
      </form>
    </div>

            </div>
            <?php include '../includes/footer.php'; ?>  

<script>
  (function(){
    const input = document.getElementById('capa');
    const img   = document.getElementById('preview');
    if (!input || !img) return;
    input.addEventListener('change', () => {
      const f = input.files && input.files[0]; if (!f) return;
      const r = new FileReader(); r.onload = e => img.src = e.target.result; r.readAsDataURL(f);
    });
  })();

  function removerParada(btn){
    const card = btn.closest('.stop-card');
    if (card) card.remove();
    renumeraParadas();
  }
  function adicionarParada(){
    const grid = document.getElementById('stops');
    const i = grid.children.length;
    const html = `
      <div class="stop-card">
        <label>Nome</label>
        <input type="text" name="paradas[${i}][nome]" value="">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
          <div><label>Lat</label><input type="text" name="paradas[${i}][lat]" value=""></div>
          <div><label>Lon</label><input type="text" name="paradas[${i}][lon]" value=""></div>
        </div>
        <input type="hidden" name="paradas[${i}][image_url]" value="">
        <div class="stop-actions"><button type="button" class="btn btn--ghost" onclick="removerParada(this)">Remover</button></div>
      </div>`;
    const wrap = document.createElement('div');
    wrap.innerHTML = html;
    grid.appendChild(wrap.firstElementChild);
  }
  function renumeraParadas(){
    const grid = document.getElementById('stops');
    const cards = Array.from(grid.children);
    cards.forEach((card, idx) => {
      card.querySelectorAll('input,textarea').forEach(inp => {
        inp.name = inp.name.replace(/paradas\[\d+\]/, `paradas[${idx}]`);
      });
    });
  }
</script>

<?php if ($DEBUG): ?>
<div class="dbg">
  <div class="row"><strong>PHPSESSID:</strong> <code><?= h(session_id()) ?></code></div>
  <div class="row"><strong>csrf_refine (sessão):</strong> <code><?= h($_SESSION['csrf_refine']) ?></code></div>
  <div class="row"><strong>csrf_publish (sessão):</strong> <code><?= h($_SESSION['csrf_publish']) ?></code></div>
  <div class="row"><strong>csrf_refine (hidden):</strong> <code id="dbg_csrf_ref"><?= h($csrfRefine) ?></code></div>
  <div class="row"><strong>Action refino:</strong> <code>../processos/refinar-roteiro-ia.php</code></div>
</div>
<script>
// impede cache do hidden
document.getElementById('csrf_refine_field').value = '<?= h($csrfRefine) ?>';
</script>
<?php endif; ?>
</body>
</html>
