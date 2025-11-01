<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$nivel = $_SESSION['nivel'] ?? 'user';

/* ===== Carrega os roteiros criados pelo usuário logado ===== */
$sql = "SELECT r.id, r.nome, r.descricao, r.categorias, r.capa, r.criado_em,
               u.nome AS criador
        FROM rota r
        JOIN usuario u ON r.usuario_id = u.id
        WHERE r.usuario_id = :uid
        ORDER BY r.criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':uid', $usuarioId, PDO::PARAM_INT);
$stmt->execute();
$meusRoteiros = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== Carrega as rotas em que o usuário está inscrito ===== */
$sqlInscricoes = "SELECT r.id, r.nome, r.descricao, r.categorias, r.capa, r.criado_em,
                         u.nome AS criador, ri.criado_em AS inscrito_em
                  FROM rota_inscricao ri
                  JOIN rota r ON ri.rota_id = r.id
                  JOIN usuario u ON r.usuario_id = u.id
                  WHERE ri.usuario_id = :uid
                  ORDER BY ri.criado_em DESC";
$stmtInscricoes = $conn->prepare($sqlInscricoes);
$stmtInscricoes->bindValue(':uid', $usuarioId, PDO::PARAM_INT);
$stmtInscricoes->execute();
$rotasInscritas = $stmtInscricoes->fetchAll(PDO::FETCH_ASSOC);

$temRoteirosCriados = !empty($meusRoteiros);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/header_perfil.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/perfil.css">
  <script defer src="../assets/js/modal.js"></script>
  <title>Perfil</title>
</head>
<body>
  <?php include '../includes/header-perfil.php'; ?>

<?php if ($nivel === 'admin'): ?>
  <section class="admin-panel">
    <header class="admin-panel__header">
      <div>
        <h2>Área do administrador</h2>
        <p>Use os atalhos abaixo para criar conteúdos, organizar inscrições e manter a comunidade em ordem.</p>
      </div>
    </header>
    <div class="admin-panel__grid">
      <a class="admin-card" href="./criar-roteiro.php">
        <i class="ph ph-map-trifold"></i>
        <span class="admin-card__title">Criar rota</span>
        <span class="admin-card__description">Publique novos caminhos e experiências no Campvia.</span>
      </a>
      <a class="admin-card" href="./adicionar_local.php">
        <i class="ph ph-map-pin"></i>
        <span class="admin-card__title">Adicionar local</span>
        <span class="admin-card__description">Cadastre pontos de interesse para enriquecer as rotas.</span>
      </a>
      <a class="admin-card" href="gerenciar-inscricoes.php">
        <i class="ph ph-users-three"></i>
        <span class="admin-card__title">Gerenciar inscrições</span>
        <span class="admin-card__description">Acompanhe participantes e aprove ou remova inscrições.</span>
      </a>
      <a class="admin-card" href="gerenciar_usuarios.php">
        <i class="ph ph-identification-card"></i>
        <span class="admin-card__title">Gerenciar usuários</span>
        <span class="admin-card__description">Atualize permissões e mantenha a base de usuários segura.</span>
      </a>
      <a class="admin-card" href="./gerenciar_publicacao.php">
        <i class="ph ph-megaphone"></i>
        <span class="admin-card__title">Gerenciar publicações</span>
        <span class="admin-card__description">Modere conteúdos enviados pela comunidade.</span>
      </a>
      <a class="admin-card" href="#">
        <i class="ph ph-certificate"></i>
        <span class="admin-card__title">Emitir certificados</span>
        <span class="admin-card__description">Disponibilize certificados para participantes elegíveis.</span>
      </a>
    </div>
  </section>
<?php endif; ?>

  <br><br>

  <div class="certificado">
    <h3>Certificado</h3>
    <div class="blocos1">
      <div class="item"></div>
      <div class="item"></div>
      <div class="item"></div>
    </div>
  </div>

  <br><br>

  <div class="favoritos">
    <h2>Favoritos</h2>
    <h3>Pastas</h3>
    <div class="blocos2">
      <div class="item"></div>
      <div class="item"></div>
      <div class="item"></div>
      <div class="item"><i class="ph ph-plus"></i></div>
    </div>
  </div>

  <?php if ($nivel !== 'admin'): ?>
    <div class="favoritos">
      <div class="favoritos-header">
        <h2>Rotas inscritas</h2>
      </div>

      <?php if (!empty($rotasInscritas)): ?>
        <div class="blocos2">
          <?php foreach ($rotasInscritas as $rota): ?>
            <a
              class="item rota-card"
              href="ver-rota.php?id=<?= (int) $rota['id'] ?>"
              title="<?= htmlspecialchars($rota['nome']) ?>"
              style="
                background:
                  linear-gradient(to top, rgba(17,24,39,.72), rgba(17,24,39,.15)) ,
                  url('<?= htmlspecialchars($rota['capa'] ?: '../assets/img/placeholder_rosseio.png') ?>') center/cover no-repeat;
              "
            >
              <div class="overlay">
                <div class="titulo"><?= htmlspecialchars($rota['nome']) ?></div>
                <div class="meta">
                  <?= htmlspecialchars($rota['categorias'] ?: 'Sem categoria') ?>
                  <?php if (!empty($rota['inscrito_em'])): ?>
                    • Inscrito em <?= date('d/m/Y', strtotime($rota['inscrito_em'])) ?>
                  <?php endif; ?>
                  <br>
                  Criado por <?= htmlspecialchars($rota['criador']) ?>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="blocos2">
          <div class="item rota-card rota-card--placeholder">
            <p>Você ainda não se inscreveu em nenhuma rota.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- ===== Meus Roteiros (estilizado com perfil.css) ===== -->
  <div class="favoritos">
    <div class="favoritos-header">
      <h2>Meus Roteiros</h2>
      <?php if ($nivel !== 'admin' && $temRoteirosCriados): ?>
        <a class="favoritos-link" href="gerenciar-inscricoes.php">Gerenciar inscrições</a>
      <?php endif; ?>
    </div>
    <h3>Você criou</h3>

    <?php if (!empty($meusRoteiros)): ?>
      <div class="blocos2">
        <?php foreach ($meusRoteiros as $rota): ?>
          <a
            class="item"
            href="ver-rota.php?id=<?= (int) $rota['id'] ?>"
            title="<?= htmlspecialchars($rota['nome']) ?>"
            style="
              position: relative;
              display: block;
              background:
                linear-gradient(to top, rgba(0,0,0,.45), rgba(0,0,0,0)) ,
                url('<?= htmlspecialchars($rota['capa'] ?: '../assets/img/placeholder_rosseio.png') ?>') center/cover no-repeat;
              overflow: hidden;
              border-radius: 6px;
            "
          >
            <div
              style="
                position: absolute;
                left: 10px; right: 10px; bottom: 10px;
                color: #fff;
                font-weight: 600;
                line-height: 1.2;
                text-shadow: 0 1px 2px rgba(0,0,0,.35);
              "
            >
              <div style="font-size:16px;"><?= htmlspecialchars($rota['nome']) ?></div>
              <div style="font-size:12px; opacity:.9; margin-top:2px;">
                <?= htmlspecialchars($rota['categorias'] ?: 'Sem categoria') ?>
                <?php if (!empty($rota['criado_em'])): ?>
                  • <?= date('d/m/Y', strtotime($rota['criado_em'])) ?>
                <?php endif; ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>

        <!-- bloco para criar novo roteiro -->
        <a class="item create-card" href="criar-roteiro.php">
          <i class="ph ph-plus"></i>
          <span>Novo roteiro</span>
        </a>
      </div>
    <?php else: ?>
      <div class="blocos2">
        <div class="item rota-card rota-card--placeholder">
          <p>Você ainda não criou nenhum roteiro.</p>
        </div>
        <a class="item create-card" href="criar-roteiro.php">
          <i class="ph ph-plus"></i>
          <span>Criar agora</span>
        </a>
      </div>
    <?php endif; ?>
  </div>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
