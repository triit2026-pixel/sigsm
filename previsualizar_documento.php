<?php
// Entrega el PDF de un documento SOLO a usuarios logueados (médico o administrativo).
// A diferencia de ver.php (el que abre el QR), esto:
//   - no cuenta como "escaneo" (no suma visitas),
//   - funciona también con documentos dados de baja, para poder revisarlos antes de reactivarlos.
require 'conexion.php';
require 'includes/auth.php';
requerir_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT titulo, archivo_nombre FROM documento WHERE id = :id");
$stmt->execute([':id' => $id]);
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    exit('El documento no existe.');
}

$ruta = dir_storage() . basename($doc['archivo_nombre']);
if (!is_file($ruta)) {
    http_response_code(404);
    exit('El archivo no está disponible en el servidor.');
}

// Nombre de descarga limpio, a partir del título
$nombre = trim(preg_replace('/[^A-Za-z0-9]+/', '_', (string)@iconv('UTF-8', 'ASCII//TRANSLIT', $doc['titulo'])), '_');
if ($nombre === '') {
    $nombre = 'documento';
}

// Se libera la sesión antes de enviar el archivo para no bloquear otras pestañas
session_write_close();

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $nombre . '.pdf"');
header('Content-Length: ' . filesize($ruta));
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');          // solo se puede incrustar desde este mismo sistema
header('Cache-Control: private, no-store');     // que no quede copia en cachés compartidas
readfile($ruta);
exit;
