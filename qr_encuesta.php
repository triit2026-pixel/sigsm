<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/encuestas.php';
requerir_rol('administrativo');

$config = require __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id, titulo, token, estado FROM encuesta WHERE id = :id");
$stmt->execute([':id' => $id]);
$enc = $stmt->fetch();

if (!$enc) {
    flash('La encuesta no existe.', 'danger');
    redirigir('encuestas.php');
}

$urlPublica = rtrim($config['base_url'], '/') . '/encuesta.php?t=' . $enc['token'];

$tituloPagina = 'QR de la encuesta';
require 'includes/header.php';
?>
<style>
    @media print {
        nav, .no-imprimir, .alert, .btn-menu, .offcanvas { display: none !important; }
        body { background: #fff !important; }
        .card { border: 0 !important; box-shadow: none !important; }
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm text-center">
            <div class="card-body p-4">
                <span class="badge bg-info text-dark mb-2">Encuesta</span>
                <h1 class="h4 mb-3"><?= e($enc['titulo']) ?></h1>

                <div id="qr" class="d-flex justify-content-center my-3"></div>

                <p class="small text-muted mb-1">Escanea el código para responder la encuesta</p>
                <p class="small text-break"><code><?= e($urlPublica) ?></code></p>

                <?php if ($enc['estado'] !== 'activa'): ?>
                    <div class="alert alert-warning no-imprimir">
                        Esta encuesta está en estado «<?= e(ENC_ESTADOS[$enc['estado']] ?? $enc['estado']) ?>»:
                        el QR no recibirá respuestas hasta que la actives desde el listado de encuestas.
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-2 justify-content-center mt-3 no-imprimir">
                    <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
                    <button type="button" class="btn btn-outline-primary" id="descargar">Descargar PNG</button>
                    <a href="encuestas.php" class="btn btn-secondary">Volver</a>
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
        a.download = 'qr_encuesta_<?= e($enc['token']) ?>.png';
        a.click();
    });
</script>
<?php require 'includes/footer.php'; ?>
