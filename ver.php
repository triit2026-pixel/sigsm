<?php
require 'conexion.php';
require 'includes/funciones.php';

$token = $_GET['t'] ?? '';

$doc = false;
if (preg_match('/^[a-f0-9]{12}$/', $token)) {
    $stmt = $pdo->prepare(
        "SELECT d.id, d.titulo, d.descripcion, d.archivo_nombre, d.fecha_actualizacion, c.nombre AS categoria
         FROM documento d
         JOIN categoria c ON c.id = d.categoria_id
         WHERE d.token = :t AND d.activo = 1"
    );
    $stmt->execute([':t' => $token]);
    $doc = $stmt->fetch();
}

if (!$doc) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documento no disponible</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-light"><div class="container py-5 text-center">
        <h1 class="h4">Documento no disponible</h1>
        <p class="text-muted">Este documento ya no está disponible. Consulta con el personal del hospital.</p>
    </div></body></html>
    <?php
    exit;
}

// ---- Entrega del PDF ----
if (isset($_GET['pdf'])) {
    $ruta = dir_storage() . basename($doc['archivo_nombre']);
    if (!is_file($ruta)) {
        http_response_code(404);
        exit('Archivo no disponible.');
    }
    $nombre = trim(preg_replace('/[^A-Za-z0-9]+/', '_', (string)@iconv('UTF-8', 'ASCII//TRANSLIT', $doc['titulo'])), '_');
    if ($nombre === '') {
        $nombre = 'documento';
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $nombre . '.pdf"');
    header('Content-Length: ' . filesize($ruta));
    header('X-Content-Type-Options: nosniff');
    readfile($ruta);
    exit;
}

// ---- Página para el paciente (cuenta un escaneo) ----
$pdo->prepare("UPDATE documento SET visitas = visitas + 1 WHERE id = :id")->execute([':id' => $doc['id']]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($doc['titulo']) ?> — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:560px">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <span class="badge bg-info text-dark mb-2"><?= e($doc['categoria']) ?></span>
            <h1 class="h4"><?= e($doc['titulo']) ?></h1>
            <?php if (!empty($doc['descripcion'])): ?>
                <p class="text-muted"><?= nl2br(e($doc['descripcion'])) ?></p>
            <?php endif; ?>
            <p class="small text-muted">Actualizado el <?= e(date('d/m/Y', strtotime($doc['fecha_actualizacion']))) ?></p>
            <a href="ver.php?t=<?= e($token) ?>&pdf=1" class="btn btn-primary btn-lg w-100">Ver documento (PDF)</a>
        </div>
    </div>
    <p class="text-center text-muted small mt-3">Hospital de Clínicas</p>
</div>
</body>
</html>
