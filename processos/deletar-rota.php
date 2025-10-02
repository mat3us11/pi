<?php
// processos/deletar-rota.php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
require_once '../includes/config.php';

function resolve_upload_path(?string $dbPath): ?string {
  if (!$dbPath) return null;

  // URL? (não apaga)
  if (preg_match('#^https?://#i', $dbPath)) {
    return null;
  }

  // raiz do projeto: .../seu-projeto
  $projectRoot = realpath(__DIR__ . '/..'); // "processos" -> sobe p/ raiz do projeto
  if ($projectRoot === false) return null;

  // Normaliza separadores e remove "./"
  $p = str_replace('\\', '/', trim($dbPath));
  $p = preg_replace('#^\./+#', '', $p);

  // Remove "../" do início, se houver (p/ evitar subir para fora da raiz)
  while (strpos($p, '../') === 0) {
    $p = substr($p, 3);
  }

  // Se path já é absoluto no SO e existe, pode retornar direto
  if (preg_match('#^/|^[A-Za-z]:/#', $p)) {
    return $p;
  }

  // Se começar com "uploads", monta absoluto
  if (strpos($p, 'uploads/') === 0) {
    $abs = $projectRoot . '/' . $p;
    return $abs;
  }

  // Última tentativa: tentar o caminho do jeito que veio, relativo à raiz
  $fallback = $projectRoot . '/' . ltrim($p, '/');
  return $fallback;
}

/* ---------- Regras básicas ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die('Método não permitido.');
}

$id_rota = isset($_POST['id_rota']) ? (int)$_POST['id_rota'] : 0;
$csrf    = $_POST['csrf'] ?? '';

if (!$id_rota || !$csrf || !hash_equals($_SESSION['csrf_delete'] ?? '', $csrf)) {
  http_response_code(400);
  die('Requisição inválida.');
}

if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../paginas/login.php');
  exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];

/* ---------- Checa dono + pega capa ---------- */
$st = $conn->prepare("SELECT usuario_id, capa FROM rota WHERE id = ?");
$st->execute([$id_rota]);
$row = $st->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  http_response_code(404);
  die('Rota não encontrada.');
}
if ((int)$row['usuario_id'] !== $usuario_id) {
  http_response_code(403);
  die('Você não tem permissão para excluir esta rota.');
}

$capaDbPath = $row['capa'] ?? null;

/* ---------- Exclui do banco ---------- */
$del = $conn->prepare("DELETE FROM rota WHERE id = ? LIMIT 1");
$del->execute([$id_rota]);

// Evita reuso do token
unset($_SESSION['csrf_delete']);

/* ---------- Tenta remover o arquivo físico da capa ---------- */
if (!empty($capaDbPath)) {
  // Monta caminhos candidatos
  $candidates = [];

  // 1) Normalizado seguro dentro do projeto
  $resolved = resolve_upload_path($capaDbPath);
  if ($resolved) $candidates[] = $resolved;

  // 2) Se por acaso veio absoluto real e difere do resolved
  $real = realpath($capaDbPath);
  if ($real && (!in_array($real, $candidates, true))) {
    $candidates[] = $real;
  }

  // 3) Se veio com prefixo "../", tentar também sem os dois pontos
  if (strpos($capaDbPath, '../') === 0) {
    $noDots = substr($capaDbPath, 3);
    $maybe = resolve_upload_path($noDots);
    if ($maybe && (!in_array($maybe, $candidates, true))) {
      $candidates[] = $maybe;
    }
  }

  foreach ($candidates as $path) {
    if ($path && is_file($path) && file_exists($path)) {
      @unlink($path);
      break;
    }
  }
}

header('Location: ../paginas/roteiro.php?msg=excluida');
exit;
