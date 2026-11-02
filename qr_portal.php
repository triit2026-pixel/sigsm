<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/url_base.php';
requerir_rol('administrativo');

$config = require __DIR__ . '/config.php';
$urlPortal = url_base($config) . '/paciente/';

$tituloPagina = 'QR del portal del paciente';
require 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm text-center">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Portal del paciente</h1>
                <p class="text-muted">Hospital de Clínicas</p>

                <div id="qr" class="d-flex justify-content-center my-3"></div>

                <p class="mb-3">Escanea el código para ver la documentación y responder encuestas</p>

                <div class="d-print-none text-start">
                    <label class="form-label small" for="direccion">Dirección del portal (la podés cambiar y se regenera el QR)</label>
                    <input type="text" class="form-control mb-2" id="direccion" value="<?= e($urlPortal) ?>">
                    <div id="aviso-local" class="alert alert-warning small" hidden>
                        Esta dirección solo funciona en esta computadora. Para que abra desde un celular, entra al sistema por su dirección pública o pega aquí la dirección pública del portal, por ejemplo
                        <code>https://algo.trycloudflare.com/sigsm/sigsm/paciente/</code>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-3 flex-wrap">
                        <button type="button" class="btn btn-primary" id="descargar">Descargar PNG</button>
                        <button type="button" class="btn btn-outline-secondary" id="imprimir">Imprimir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const campo = document.getElementById('direccion');
    const aviso = document.getElementById('aviso-local');
    const qr = new QRCode(document.getElementById('qr'), {
        text: campo.value,
        width: 280,
        height: 280,
        correctLevel: QRCode.CorrectLevel.M
    });

    function actualizar() {
        const texto = campo.value.trim();
        aviso.hidden = !/^https?:\/\/(localhost|127\.0\.0\.1|\[::1\])([:\/]|$)/i.test(texto);
        if (texto !== '') {
            qr.clear();
            qr.makeCode(texto);
        }
    }

    campo.addEventListener('input', actualizar);
    actualizar();

    document.getElementById('descargar').addEventListener('click', function () {
        const canvas = document.querySelector('#qr canvas');
        if (!canvas) { return; }
        const a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = 'qr_portal_paciente.png';
        a.click();
    });

    document.getElementById('imprimir').addEventListener('click', function () {
        window.print();
    });
</script>
<?php require 'includes/footer.php'; ?>