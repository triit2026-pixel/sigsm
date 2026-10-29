<?php
require 'conexion.php';
require 'includes/auth.php';
requerir_login();

$config = require __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT d.*, c.nombre AS categoria FROM documento d JOIN categoria c ON c.id = d.categoria_id WHERE d.id = :id");
$stmt->execute([':id' => $id]);
$doc = $stmt->fetch();

if (!$doc) {
    flash('El documento no existe.', 'danger');
    redirigir('documentos.php');
}

$urlPublica = rtrim($config['base_url'], '/') . '/ver.php?t=' . $doc['token'];

$tituloPagina = 'QR del documento';
require 'includes/header.php';
?>
<style>
    @media print {
        nav, .no-imprimir, .alert { display: none !important; }
        body { background: #fff !important; }
        .card { border: 0 !important; box-shadow: none !important; }
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm text-center">
            <div class="card-body p-4">
                <span class="badge bg-info text-dark mb-2"><?= e($doc['categoria']) ?></span>
                <h1 class="h4 mb-3"><?= e($doc['titulo']) ?></h1>

                <div id="qr" class="d-flex justify-content-center my-3"></div>

                <p class="small text-muted mb-1">Escanea el código para ver el documento</p>
                <p class="small text-break"><code><?= e($urlPublica) ?></code></p>

                <?php if (!$doc['activo']): ?>
                    <div class="alert alert-warning no-imprimir">Este documento está dado de baja: el QR no mostrará nada hasta que lo reactives.</div>
                <?php endif; ?>

                <div class="d-flex gap-2 justify-content-center mt-3 no-imprimir">
                    <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
                    <button type="button" class="btn btn-outline-primary" id="descargar">Descargar PNG</button>
                    <a href="documentos.php" class="btn btn-secondary">Volver</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const url = <?= json_encode($urlPublica) ?>;
    new QRCode(document.getElementById('qr'), {
        text: url,
        width: 280,
        height: 280,
        correctLevel: QRCode.CorrectLevel.M
    });

    document.getElementById('descargar').addEventListener('click', function () {
        const canvas = document.querySelector('#qr canvas');
        if (!canvas) { return; }
        const a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = 'qr_<?= e($doc['token']) ?>.png';
        a.click();
    });
</script>
<?php require 'includes/footer.php'; ?>
