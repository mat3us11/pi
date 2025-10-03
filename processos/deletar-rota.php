<?php
// processos/deletar-rota.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../includes/config.php';

/**
 * Retorna caminho absoluto da raiz do projeto (pasta onde ficam /paginas, /processos, /uploads etc.)
 */
function project_root(): ?string {
  $root = realpath(__DIR__ . '/..');
  return $root !== false ? str_replace('\\', '/', $root) : null;
}

/**
 * Resolve um caminho salvo no banco para um caminho absoluto, SEM abrir brecha de path traversal.
 * - URLs (http/https) => null (não apagamos)
 * - Caminhos relativos tipo "../uploads/geral/..." ou "uploads/geral/..." => resolvidos dentro do projeto
 * - Caminhos absolutos no SO => retornamos, mas a checagem de pasta permitida virá depois
 */
function resolve_upload_path_safe(?string $dbPath): ?string {
  if (!$dbPath) return null;

  // Não apagamos URLs remotas
  if (preg_match('#^https?://#i', $dbPath)) return null;

  $root = project_root();
  if (!$root) return null;

  // Normaliza separadores e remove "./"
  $p = str_replace('\\', '/', trim($dbPath));
  $p = preg_replace('#^\./+#', '', $p);

  // Remove "../" do início para evitar escapar da raiz por concatenação ingênua
  while (strpos($p, '../') === 0) { $p = substr($p, 3); }

  // Se for absoluto, mantemos; se for relativo, tornamos absoluto a partir da raiz do projeto
  if (preg_match('#^/|^[A-Za-z]:/#', $p)) {
    $abs = $p;
  } else {
    $abs = $root . '/' . ltrim($p, '/');
  }

  // Normaliza realpath se existir; se não existir, retorna mesmo assim (deixamos a checagem para depois)
  $real = realpath($abs);
  $final = $real ? str_replace('\\', '/', $real) : str_replace('\\', '/', $abs);

  return $final;
}

/** Garante que o arquivo está dentro da pasta uploads/geral do projeto */
function is_inside_uploads_geral(string $absPath): bool {
  $root = project_root();
  if (!$root) return false;
  $uploadsGeral = $root . '/uploads/geral/';

  // Normaliza
  $absPath = str_replace('\\', '/', $absPath);
  $uploadsGeral = rtrim(str_replace('\\', '/', $uploadsGeral), '/') . '/';

  // Usa realpath quando possível pra evitar falsos positivos
  $realAbs = realpath($absPath);
  if ($realAbs) $absPath = str_replace('\\', '/', $realAbs);

  return strpos($absPath, $uploadsGeral) === 0;
}

/* ---------- Regras básicas ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método não permitido.');
}
if (empty($_SESSION['usuario_id'])) {
  header('Location: ../paginas/login.php');
  exit;
}

$id_rota = isset($_POST['id_rota']) ? (int)$_POST['id_rota'] : 0;
$csrf    = $_POST['csrf'] ?? '';

if ($id_rota <= 0 || !$csrf || !hash_equals($_SESSION['csrf_delete'] ?? '', $csrf)) {
  http_response_code(400);
  exit('Requisição inválida.');
}

/* ---------- Checa dono + pega capa ---------- */
$st = $conn->prepare("SELECT usuario_id, capa FROM rota WHERE id = ?");
$st->execute([$id_rota]);
$row = $st->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  http_response_code(404);
  exit('Rota não encontrada.');
}
if ((int)$row['usuario_id'] !== (int)$_SESSION['usuario_id']) {
  http_response_code(403);
  exit('Você não tem permissão para excluir esta rota.');
}

$capaDbPath = $row['capa'] ?? null;

/* ---------- Exclui do banco ---------- */
$del = $conn->prepare("DELETE FROM rota WHERE id = ? LIMIT 1");
$del->execute([$id_rota]);

// invalida token para evitar reuso
unset($_SESSION['csrf_delete']);

/* ---------- Remove a capa física (se aplicável) ---------- */
if (!empty($capaDbPath)) {
  $candidate = resolve_upload_path_safe($capaDbPath);

  // Só apaga se for arquivo dentro de /uploads/geral/
  if ($candidate && is_file($candidate) && is_inside_uploads_geral($candidate)) {
    @unlink($candidate);
  }

  // OBS: Se você também salva capas em ../uploads/geral (com prefixo "../"),
  // resolve_upload_path_safe já cobre, pois normalizamos para absoluto
}

header('Location: ../paginas/roteiro.php?msg=excluida');
exit;
