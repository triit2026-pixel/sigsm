<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/encuestas.php';
requerir_rol('administrativo');

$id  = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$enc = null;
$bloqueada = false;   // true cuando ya hay respuestas: solo se puede cambiar título y descripción
if ($id > 0) {
    $enc = enc_cargar($pdo, $id);
    if (!$enc) {
        flash('La encuesta no existe.', 'danger');
        redirigir('encuestas.php');
    }
    $bloqueada = enc_total_respuestas($pdo, $id) > 0;
}

$titulo      = $enc['titulo'] ?? '';
$descripcion = $enc['descripcion'] ?? '';
$preguntasForm = [];
if ($enc) {
    foreach ($enc['preguntas'] as $p) {
        $preguntasForm[] = [
            'texto'       => $p['texto'],
            'tipo'        => $p['tipo'],
            'obligatoria' => (int)$p['obligatoria'],
            'multiple'    => (int)$p['multiple'],
            'opciones'    => array_values(array_column($p['opciones'], 'texto')),
        ];
    }
}
if (!$preguntasForm) {
    $preguntasForm[] = enc_pregunta_vacia();
}
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $titulo      = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($titulo === '' || mb_strlen($titulo) > 150) {
        $errores[] = 'El título es obligatorio y no puede superar los 150 caracteres.';
    }
    if (mb_strlen($descripcion) > 500) {
        $errores[] = 'La descripción no puede superar los 500 caracteres.';
    }
    if (!$bloqueada) {
        $preguntasForm = enc_leer_preguntas_post();
        $errores = array_merge($errores, enc_validar_preguntas($preguntasForm));
        if (!$preguntasForm) {
            $preguntasForm[] = enc_pregunta_vacia();
        }
    }

    if (!$errores) {
        try {
            $pdo->beginTransaction();
            if ($id > 0) {
                // Se bloquea la fila para que nadie responda mientras se reemplazan las preguntas
                $st = $pdo->prepare("SELECT id FROM encuesta WHERE id = :id FOR UPDATE");
                $st->execute([':id' => $id]);
                $st->fetch();
                if (!$bloqueada && enc_total_respuestas($pdo, $id) > 0) {
                    throw new RuntimeException('con_respuestas');
                }
                $pdo->prepare("UPDATE encuesta SET titulo = :t, descripcion = :d WHERE id = :id")
                    ->execute([':t' => $titulo, ':d' => $descripcion, ':id' => $id]);
                if (!$bloqueada) {
                    $pdo->prepare("DELETE FROM encuesta_pregunta WHERE encuesta_id = :id")->execute([':id' => $id]);
                    enc_guardar_preguntas($pdo, $id, $preguntasForm);
                }
                $mensaje = 'Encuesta actualizada correctamente.';
            } else {
                $pdo->prepare("INSERT INTO encuesta (titulo, descripcion, token) VALUES (:t, :d, :tk)")
                    ->execute([':t' => $titulo, ':d' => $descripcion, ':tk' => enc_token_nuevo($pdo)]);
                $nuevoId = (int)$pdo->lastInsertId();
                enc_guardar_preguntas($pdo, $nuevoId, $preguntasForm);
                $mensaje = 'Encuesta creada como borrador. Actívala cuando esté lista para que funcione su QR.';
            }
            $pdo->commit();
            flash($mensaje);
            redirigir('encuestas.php');
        } catch (RuntimeException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $errores[] = 'Mientras editabas, alguien respondió esta encuesta: ya no se pueden cambiar las preguntas.';
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log('encuesta_form.php: ' . $ex->getMessage());
            $errores[] = 'Ocurrió un error al guardar. Inténtalo de nuevo.';
        }
    }
}

// Dibuja el bloque de una pregunta ($i es el índice; '__i__' en la plantilla del botón «Agregar»)
function bloque_pregunta($i, array $p): string {
    ob_start(); ?>
    <div class="card mb-3 pregunta-bloque">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="num-pregunta">Pregunta</strong>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" data-accion="subir" aria-label="Subir pregunta">↑</button>
                    <button type="button" class="btn btn-outline-secondary" data-accion="bajar" aria-label="Bajar pregunta">↓</button>
                    <button type="button" class="btn btn-outline-danger" data-accion="quitar">Quitar</button>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-8">
                    <label class="form-label">Texto de la pregunta</label>
                    <input type="text" class="form-control" maxlength="300"
                           name="preguntas[<?= e($i) ?>][texto]" value="<?= e($p['texto']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo de respuesta</label>
                    <select class="form-select campo-tipo" name="preguntas[<?= e($i) ?>][tipo]">
                        <?php foreach (ENC_TIPOS as $clave => $etiqueta): ?>
                            <option value="<?= e($clave) ?>" <?= $p['tipo'] === $clave ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" value="1" id="ob_<?= e($i) ?>"
                       name="preguntas[<?= e($i) ?>][obligatoria]" <?= $p['obligatoria'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="ob_<?= e($i) ?>">Respuesta obligatoria</label>
            </div>
            <div class="zona-opciones mt-3" <?= $p['tipo'] !== 'opcion' ? 'hidden' : '' ?>>
                <label class="form-label">Opciones (una por línea, entre <?= ENC_MIN_OPCIONES ?> y <?= ENC_MAX_OPCIONES ?>)</label>
                <textarea class="form-control" rows="4"
                          name="preguntas[<?= e($i) ?>][opciones]"><?= e(implode("\n", $p['opciones'])) ?></textarea>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" value="1" id="mu_<?= e($i) ?>"
                           name="preguntas[<?= e($i) ?>][multiple]" <?= $p['multiple'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="mu_<?= e($i) ?>">Permitir elegir varias respuestas</label>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

$tituloPagina = $enc ? 'Editar encuesta' : 'Nueva encuesta';
require 'includes/header.php';
?>
<h1 class="h3 mb-4"><?= e($tituloPagina) ?></h1>

<?php if ($errores): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errores as $m): ?><li><?= e($m) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" novalidate>
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="titulo">Título de la encuesta</label>
                <input type="text" class="form-control" id="titulo" name="titulo" maxlength="150" value="<?= e($titulo) ?>">
            </div>
            <div>
                <label class="form-label" for="descripcion">Descripción (opcional)</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" maxlength="500"><?= e($descripcion) ?></textarea>
            </div>
        </div>
    </div>

    <h2 class="h5 mb-3">Preguntas</h2>

<?php if ($bloqueada): ?>
    <div class="alert alert-info">
        Esta encuesta ya tiene respuestas, por eso solo puedes cambiar el título y la descripción.
        Si necesitas otras preguntas, crea una encuesta nueva (así los resultados no se mezclan).
    </div>
    <?php foreach ($preguntasForm as $n => $p): ?>
        <div class="card mb-2">
            <div class="card-body py-2">
                <strong><?= $n + 1 ?>. <?= e($p['texto']) ?></strong>
                <span class="badge bg-light text-dark border ms-1"><?= e(ENC_TIPOS[$p['tipo']]) ?></span>
                <?php if ($p['obligatoria']): ?><span class="badge bg-warning text-dark">Obligatoria</span><?php endif; ?>
                <?php if ($p['tipo'] === 'opcion'): ?>
                    <div class="small text-muted mt-1">
                        <?= $p['multiple'] ? 'Varias respuestas' : 'Una sola respuesta' ?>:
                        <?= e(implode(' · ', $p['opciones'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div id="preguntas">
        <?php foreach ($preguntasForm as $i => $p) { echo bloque_pregunta($i, $p); } ?>
    </div>
    <template id="tpl-pregunta"><?= bloque_pregunta('__i__', enc_pregunta_vacia()) ?></template>
    <button type="button" class="btn btn-outline-primary mb-4" id="agregar">+ Agregar pregunta</button>
<?php endif; ?>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="encuestas.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php if (!$bloqueada): ?>
<script>
(function () {
    const cont = document.getElementById('preguntas');
    const tpl  = document.getElementById('tpl-pregunta').innerHTML;
    let contador = <?= (int)count($preguntasForm) ?>;

    function renumerar() {
        cont.querySelectorAll('.pregunta-bloque').forEach(function (b, i) {
            b.querySelector('.num-pregunta').textContent = 'Pregunta ' + (i + 1);
        });
    }
    function ajustarTipo(b) {
        b.querySelector('.zona-opciones').hidden = (b.querySelector('.campo-tipo').value !== 'opcion');
    }

    document.getElementById('agregar').addEventListener('click', function () {
        const d = document.createElement('div');
        d.innerHTML = tpl.replace(/__i__/g, String(contador++));
        const b = d.firstElementChild;
        cont.appendChild(b);
        ajustarTipo(b);
        renumerar();
        b.querySelector('input[type=text]').focus();
    });

    cont.addEventListener('click', function (ev) {
        const btn = ev.target.closest('[data-accion]');
        if (!btn) { return; }
        const b = btn.closest('.pregunta-bloque');
        const a = btn.dataset.accion;
        if (a === 'quitar') {
            b.remove();
        } else if (a === 'subir' && b.previousElementSibling) {
            cont.insertBefore(b, b.previousElementSibling);
        } else if (a === 'bajar' && b.nextElementSibling) {
            cont.insertBefore(b.nextElementSibling, b);
        }
        renumerar();
    });

    cont.addEventListener('change', function (ev) {
        if (ev.target.classList.contains('campo-tipo')) {
            ajustarTipo(ev.target.closest('.pregunta-bloque'));
        }
    });

    renumerar();
})();
</script>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
