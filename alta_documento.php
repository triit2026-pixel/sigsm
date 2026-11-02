<?php
require 'conexion.php';
require 'includes/auth.php';
requerir_login();

$config     = require __DIR__ . '/config.php';
$categorias = $pdo->query("SELECT id, nombre FROM categoria WHERE activa = 1 ORDER BY nombre")->fetchAll();
$errores    = [];
$valores    = ['titulo' => '', 'descripcion' => '', 'categoria_id' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $valores = leerCamposDocumento();
    $errores = validarDocumento($pdo, $valores);

    $archivo = $_FILES['archivo'] ?? null;
    if ($err = validarPdf($archivo, true, (int)$config['max_pdf_mb'])) {
        $errores['archivo'] = $err;
    }

    if (empty($errores)) {
        $nombreServidor = null;
        try {
            $nombreServidor = guardarPdf($archivo);
            $stmt = $pdo->prepare(
                "INSERT INTO documento (token, titulo, descripcion, categoria_id, archivo_nombre, archivo_original, creado_por)
                 VALUES (:token, :titulo, :desc, :cat, :arch, :orig, :user)"
            );
            $stmt->execute([
                ':token'  => bin2hex(random_bytes(6)),
                ':titulo' => $valores['titulo'],
                ':desc'   => $valores['descripcion'] !== '' ? $valores['descripcion'] : null,
                ':cat'    => $valores['categoria_id'],
                ':arch'   => $nombreServidor,
                ':orig'   => mb_substr(basename($archivo['name']), 0, 255),
                ':user'   => usuario_actual()['id'],
            ]);
            $id = (int)$pdo->lastInsertId();
            flash('Documento creado correctamente. Ya puedes imprimir su QR.');
            redirigir('qr_documento.php?id=' . $id);
        } catch (Throwable $e) {
            if ($nombreServidor) {
                borrarPdf($nombreServidor);   // no dejar archivos huérfanos
            }
            error_log('alta_documento.php: ' . $e->getMessage());
            $errores['general'] = 'No se pudo guardar el documento. Inténtalo de nuevo.';
        }
    }
}

$tituloPagina = 'Nuevo documento';
$esEdicion = false;
require 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <h1 class="h3 mb-4">Nuevo documento</h1>
        <?php require 'includes/form_documento.php'; ?>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
