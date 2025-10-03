<?php
// processos/publicar-rota.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once '../includes/config.php';

// CSRF
$posted = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf_publish']) || !hash_equals($_SESSION['csrf_publish'], $posted)) {
  http_response_code(400);
  exit('Token inválido.');
}
if (empty($_SESSION['usuario_id'])) { http_response_code(401); exit('Login necessário.'); }

$draft = $_SESSION['rota_draft'] ?? null;
if (!$draft) { http_response_code(400); exit('Rascunho ausente.'); }

$usuario_id    = (int)$_SESSION['usuario_id'];
$nome          = trim($_POST['nome'] ?? ($draft['nome'] ?? ''));
$descricao     = trim($_POST['descricao'] ?? ($draft['descricao'] ?? ''));
$categorias    = trim($_POST['categorias'] ?? ($draft['categorias'] ?? ''));
$ponto_partida = trim($_POST['ponto_partida'] ?? ($draft['ponto_partida'] ?? ''));
$destino       = trim($_POST['destino'] ?? ($draft['destino'] ?? ''));
$cidade_slug   = trim($_POST['cidade_slug'] ?? '');
$paradasPost   = $_POST['paradas'] ?? $draft['paradas'] ?? [];
$overwriteId   = $draft['_overwrite_id'] ?? null; // <-- se existir, faremos UPDATE

if ($nome === '' || $descricao === '' || $ponto_partida === '' || $destino === '') {
  http_response_code(400); exit('Campos obrigatórios faltando.');
}

// cidade opcional: descobre id por slug
$cidade_id = null;
if ($cidade_slug !== '') {
  $q = $conn->prepare("SELECT id FROM cidade WHERE slug = ? LIMIT 1");
  $q->execute([$cidade_slug]);
  $cid = $q->fetch(PDO::FETCH_ASSOC);
  if ($cid) $cidade_id = (int)$cid['id'];
}

// normaliza paradas
$paradasArr = [];
if (is_array($paradasPost)) {
  foreach ($paradasPost as $p) {
    $nomeP = is_array($p) ? trim((string)($p['nome'] ?? '')) : trim((string)$p);
    if ($nomeP === '') continue;
    $lat = is_array($p) && $p['lat'] !== '' ? (float)$p['lat'] : null;
    $lon = is_array($p) && $p['lon'] !== '' ? (float)$p['lon'] : null;
    $img = is_array($p) ? ($p['image_url'] ?? null) : null;
    $paradasArr[] = ['nome'=>$nomeP, 'lat'=>$lat, 'lon'=>$lon, 'image_url'=>$img];
  }
}
$paradasJson = !empty($paradasArr) ? json_encode($paradasArr, JSON_UNESCAPED_UNICODE) : null;

// upload capa (opcional)
function resolve_upload_path_pub(?string $dbPath): ?string {
  if (!$dbPath) return null; if (preg_match('#^https?://#i', $dbPath)) return null;
  $projectRoot = realpath(__DIR__ . '/..'); if ($projectRoot === false) return null;
  $p = str_replace('\\', '/', trim($dbPath)); $p = preg_replace('#^\./+#', '', $p);
  while (strpos($p, '../') === 0) { $p = substr($p, 3); }
  if (preg_match('#^/|^[A-Za-z]:/#', $p)) return $p;
  if (strpos($p, 'uploads/') === 0) return $projectRoot . '/' . $p;
  return $projectRoot . '/' . ltrim($p, '/');
}

$capaPath = $draft['capa'] ?? null;
if (isset($_FILES['capa']) && $_FILES['capa']['error'] !== UPLOAD_ERR_NO_FILE) {
  if ($_FILES['capa']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['capa']['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp); finfo_close($finfo);
    $permit = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','image/jpg'=>'jpg'];
    if (isset($permit[$mime])) {
      $dir = realpath(__DIR__ . '/../uploads/geral') ?: (__DIR__ . '/../uploads/geral');
      if (!is_dir($dir)) @mkdir($dir, 0775, true);
      $nomeArq = 'capa_' . $usuario_id . '_' . time() . '.' . $permit[$mime];
      $dst = rtrim($dir, '/\\') . '/' . $nomeArq;
      if (move_uploaded_file($_FILES['capa']['tmp_name'], $dst)) {
        $capaPath = '../uploads/geral/' . $nomeArq;
        @chmod($dst, 0644);
      }
    }
  }
}

// Decide INSERT ou UPDATE
if ($overwriteId) {
  // Atualiza a rota existente do usuário
  // Busca capa antiga para possível remoção se trocou
  $st = $conn->prepare("SELECT capa FROM rota WHERE id = ? AND usuario_id = ?");
  $st->execute([(int)$overwriteId, $usuario_id]);
  $old = $st->fetch(PDO::FETCH_ASSOC);

  $sql = "UPDATE rota SET 
            nome = :nome,
            descricao = :descricao,
            categorias = :categorias,
            ponto_partida = :ponto_partida,
            destino = :destino,
            cidade_id = :cidade_id,
            paradas = :paradas,
            capa = :capa
          WHERE id = :id AND usuario_id = :uid";
  $up = $conn->prepare($sql);
  $ok = $up->execute([
    ':nome' => $nome,
    ':descricao' => $descricao,
    ':categorias' => $categorias,
    ':ponto_partida' => $ponto_partida,
    ':destino' => $destino,
    ':cidade_id' => $cidade_id,
    ':paradas' => $paradasJson,
    ':capa' => $capaPath,
    ':id' => (int)$overwriteId,
    ':uid' => $usuario_id
  ]);
  if (!$ok) { http_response_code(500); exit('Falha ao atualizar.'); }

  // remove capa antiga se mudou e era local
  if ($capaPath && !empty($old['capa']) && $old['capa'] !== $capaPath) {
    $oldAbs = resolve_upload_path_pub($old['capa']);
    if ($oldAbs && is_file($oldAbs)) @unlink($oldAbs);
  }

  // Limpa rascunho
  unset($_SESSION['rota_draft']);

  header('Location: ../paginas/ver-rota.php?id='.(int)$overwriteId);
  exit;
} else {
  // Cria nova rota
  $sql = "INSERT INTO rota
          (usuario_id, nome, descricao, categorias, ponto_partida, destino, cidade_id, paradas, capa)
          VALUES (:usuario_id, :nome, :descricao, :categorias, :ponto_partida, :destino, :cidade_id, :paradas, :capa)";
  $ins = $conn->prepare($sql);
  $ok = $ins->execute([
    ':usuario_id' => $usuario_id,
    ':nome' => $nome,
    ':descricao' => $descricao,
    ':categorias' => $categorias,
    ':ponto_partida' => $ponto_partida,
    ':destino' => $destino,
    ':cidade_id' => $cidade_id,
    ':paradas' => $paradasJson,
    ':capa' => $capaPath
  ]);
  if (!$ok) { http_response_code(500); exit('Falha ao publicar.'); }

  $newId = (int)$conn->lastInsertId();

  unset($_SESSION['rota_draft']);
  header('Location: ../paginas/ver-rota.php?id='.$newId);
  exit;
}
