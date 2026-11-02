<?php
require 'conexion.php';
require 'includes/auth.php';
requerir_login();

$config = require __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM documento WHERE id = :id");
$stmt->execute([':id' => $id]);
$doc = $stmt->fetch();

if (!$doc) {
    flash('El documento no existe.', 'danger');
    redirigir('documentos.php');
}

// Categorías activas + la actual (aunque esté inactiva)
$stmt = $pdo->prepare("SELECT id, nombre FROM categoria WHERE activa = 1 OR id = :actual ORDER BY nombre");
$stmt->execute([':actual' => $doc['categoria_id']]);
$categorias = $stmt->fetchAll();

$errores = [];
$valores = [
    'titulo'       => $doc['titulo'],
    'descripcion'  => $doc['descripcion'] ?? '',
    'categoria_id' => (int)$doc['categoria_id'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $valores = leerCamposDocumento();
    $errores = validarDocumento($pdo, $valores, (int)$doc['categoria_id']);

    $archivo   = $_FILES['archivo'] ?? null;
    $subeNuevo = hayArchivo($archivo);
    if ($err = validarPdf($archivo, false, (int)$config['max_pdf_mb'])) {
        $errores['archivo'] = $err;
    }

    if (empty($errores)) {
        $nuevoNombre = null;
        try {
            $sql = "UPDATE documento SET titulo = :titulo, descripcion = :desc, categoria_id = :cat,
                           fecha_actualizacion = NOW()";
            $params = [
                ':titulo' => $valores['titulo'],
                ':desc'   => $valores['descripcion'] !== '' ? $valores['descripcion'] : null,
                ':cat'    => $valores['categoria_id'],
                ':id'     => $id,
            ];
            if ($subeNuevo) {
                $nuevoNombre = guardarPdf($archivo);
                $sql .= ", archivo_nombre = :arch, archivo_original = :orig";
                $params[':arch'] = $nuevoNombre;
                $params[':orig'] = mb_substr(basename($archivo['name']), 0, 255);
            }
            $sql .= " WHERE id = :id";
            $pdo->prepare($sql)->execute($params);

            if ($nuevoNombre) {
                borrarPdf($doc['archivo_nombre']);   
            }
            flash('Documento editado correctamente.');
            redirigir('documentos.php');
        } catch (Throwable $e) {
            if ($nuevoNombre) {
                borrarPdf($nuevoNombre);
            }
            error_log('editar_documento.php: ' . $e->getMessage());
            $errores['general'] = 'No se pudo actualizar el documento. Inténtalo de nuevo.';
        }
    }
}

$tituloPagina  = 'Editar documento';
$esEdicion     = true;
$archivoActual = $doc['archivo_original'];
require 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <h1 class="h3 mb-4">Editar documento</h1>
        <?php require 'includes/form_documento.php'; ?>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
