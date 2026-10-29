<?php
// Funciones de uso común

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function redirigir(string $url): void {
    header('Location: ' . $url);
    exit;
}

// Mensajes que se muestran una sola vez después de redirigir
function flash(string $mensaje, string $tipo = 'success'): void {
    $_SESSION['flash'] = ['mensaje' => $mensaje, 'tipo' => $tipo];
}
function flash_tomar(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// ---------- Archivos PDF ----------

function dir_storage(): string {
    return dirname(__DIR__) . '/storage/documentos/';
}

function hayArchivo(?array $archivo): bool {
    return $archivo !== null && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

// Devuelve un mensaje de error, o null si el archivo es válido
function validarPdf(?array $archivo, bool $obligatorio, int $maxMb): ?string {
    if (!hayArchivo($archivo)) {
        return $obligatorio ? 'Debes seleccionar un archivo PDF.' : null;
    }
    if (in_array($archivo['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        return 'El archivo supera el tamaño permitido por el servidor.';
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return 'Ocurrió un error al subir el archivo.';
    }
    if ($archivo['size'] > $maxMb * 1024 * 1024) {
        return "El archivo no puede superar los {$maxMb} MB.";
    }
    // Se comprueba el contenido real, no la extensión que declara el usuario
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if ($finfo->file($archivo['tmp_name']) !== 'application/pdf') {
        return 'Solo se permiten archivos PDF.';
    }
    return null;
}

// Guarda el PDF con un nombre aleatorio y devuelve ese nombre
function guardarPdf(array $archivo): string {
    $dir = dir_storage();
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $nombre = bin2hex(random_bytes(16)) . '.pdf';
    if (!move_uploaded_file($archivo['tmp_name'], $dir . $nombre)) {
        throw new RuntimeException('No se pudo guardar el archivo en el servidor.');
    }
    return $nombre;
}

function borrarPdf(string $nombre): void {
    $ruta = dir_storage() . basename($nombre);
    if (is_file($ruta)) {
        unlink($ruta);
    }
}

// ---------- Validación de documentos (compartida por alta y edición) ----------

// $categoriaPermitidaExtra: en edición, la categoría actual aunque esté inactiva
function validarDocumento(PDO $pdo, array $v, int $categoriaPermitidaExtra = 0): array {
    $err = [];

    if ($v['titulo'] === '' || mb_strlen($v['titulo']) > 150) {
        $err['titulo'] = 'El título es obligatorio y no puede superar los 150 caracteres.';
    }
    if (mb_strlen($v['descripcion']) > 500) {
        $err['descripcion'] = 'La descripción no puede superar los 500 caracteres.';
    }

    $catId = (int)$v['categoria_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categoria WHERE id = :id AND (activa = 1 OR id = :extra)");
    $stmt->execute([':id' => $catId, ':extra' => $categoriaPermitidaExtra]);
    if ($catId <= 0 || (int)$stmt->fetchColumn() === 0) {
        $err['categoria_id'] = 'Selecciona una categoría válida.';
    }

    return $err;
}

function leerCamposDocumento(): array {
    return [
        'titulo'       => trim($_POST['titulo'] ?? ''),
        'descripcion'  => trim($_POST['descripcion'] ?? ''),
        'categoria_id' => (int)($_POST['categoria_id'] ?? 0),
    ];
}
