<?php
// Página pública (sin login) para que el paciente responda una encuesta anónima
require 'conexion.php';
require 'includes/encuestas.php';

$token = $_GET['t'] ?? '';
$enc = is_string($token) ? enc_cargar_por_token($pdo, $token) : null;

function pagina_no_disponible(): void {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Encuesta no disponible</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body class="bg-light"><div class="container py-5 text-center">
        <h1 class="h4">Encuesta no disponible</h1>
        <p class="text-muted">Esta encuesta ya no está disponible. Consulta con el personal del hospital.</p>
    </div></body></html>
    <?php
    exit;
}

if (!$enc || $enc['estado'] !== 'activa' || !$enc['preguntas']) {
    pagina_no_disponible();
}

$encId   = (int)$enc['id'];
$cookie  = 'enc_' . $encId;
$yaRespondio = isset($_COOKIE[$cookie]);   // anti-repetición básica: no es infalible, alcanza para satisfacción
$gracias = isset($_GET['gracias']);
$errores = [];
$valores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$yaRespondio) {
    // Campo trampa para bots: un paciente real nunca lo ve ni lo completa
    if (!empty($_POST['sitio_web'])) {
        redirigir('encuesta.php?t=' . $enc['token'] . '&gracias=1');
    }

    $valores = (isset($_POST['r']) && is_array($_POST['r'])) ? $_POST['r'] : [];
    $filas = [];   // cada elemento: [pregunta_id, texto|null, escala|null, opcion_id|null]

    foreach ($enc['preguntas'] as $pid => $p) {
        $v = $valores[$pid] ?? null;
        $oblig = (bool)$p['obligatoria'];
        $falta = 'Responde la pregunta «' . $p['texto'] . '».';

        if ($p['tipo'] === 'texto') {
            $t = is_string($v) ? trim($v) : '';
            if ($t === '') {
                if ($oblig) { $errores[] = $falta; }
            } elseif (mb_strlen($t) > ENC_MAX_TEXTO_RESPUESTA) {
                $errores[] = 'La respuesta a «' . $p['texto'] . '» no puede superar los ' . ENC_MAX_TEXTO_RESPUESTA . ' caracteres.';
            } else {
                $filas[] = [$pid, $t, null, null];
            }

        } elseif ($p['tipo'] === 'escala') {
            if ($v === null || $v === '') {
                if ($oblig) { $errores[] = $falta; }
            } elseif (is_string($v) && ctype_digit($v) && (int)$v >= 1 && (int)$v <= 5) {
                $filas[] = [$pid, null, (int)$v, null];
            } else {
                $errores[] = 'Valor no válido en «' . $p['texto'] . '».';
            }

        } else {
            $sel = is_array($v) ? $v : (($v === null || $v === '') ? [] : [$v]);
            $ids = [];
            $valido = true;
            foreach ($sel as $s) {
                if (is_string($s) && ctype_digit($s) && isset($p['opciones'][(int)$s])) {
                    $ids[(int)$s] = true;
                } else {
                    $valido = false;
                }
            }
            if (!$valido || (!$p['multiple'] && count($ids) > 1)) {
                $errores[] = 'Opción no válida en «' . $p['texto'] . '».';
            } elseif (!$ids) {
                if ($oblig) { $errores[] = $falta; }
            } else {
                foreach (array_keys($ids) as $oid) {
                    $filas[] = [$pid, null, null, $oid];
                }
            }
        }
    }

    if (!$errores && !$filas) {
        $errores[] = 'Responde al menos una pregunta para enviar la encuesta.';
    }

    if (!$errores) {
        try {
            $pdo->beginTransaction();
            // Por si la encuesta se cerró mientras el paciente la completaba
            $st = $pdo->prepare("SELECT estado FROM encuesta WHERE id = :id");
            $st->execute([':id' => $encId]);
            if ($st->fetchColumn() !== 'activa') {
                $pdo->rollBack();
                pagina_no_disponible();
            }
            $pdo->prepare("INSERT INTO encuesta_respuesta (encuesta_id) VALUES (:e)")->execute([':e' => $encId]);
            $respId = (int)$pdo->lastInsertId();
            $ins = $pdo->prepare(
                "INSERT INTO encuesta_respuesta_detalle (respuesta_id, pregunta_id, valor_texto, valor_escala, opcion_id)
                 VALUES (:r, :p, :t, :s, :o)"
            );
            foreach ($filas as [$pid, $t, $s, $o]) {
                $ins->execute([':r' => $respId, ':p' => $pid, ':t' => $t, ':s' => $s, ':o' => $o]);
            }
            $pdo->commit();
            setcookie($cookie, '1', [
                'expires'  => time() + 365 * 24 * 3600,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            redirigir('encuesta.php?t=' . $enc['token'] . '&gracias=1');
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log('encuesta.php: ' . $ex->getMessage());
            $errores[] = 'No pudimos guardar tu respuesta. Inténtalo de nuevo en unos minutos.';
        }
    }
}

function marcado(array $valores, int $pid, int $valor): bool {
    $v = $valores[$pid] ?? null;
    if (is_array($v)) { return in_array((string)$valor, $v, true); }
    return $v !== null && (string)$v === (string)$valor;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($enc['titulo']) ?> — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:600px">
<?php if ($gracias || $yaRespondio): ?>
    <div class="card shadow-sm">
        <div class="card-body p-4 text-center">
            <h1 class="h4">¡Gracias por responder!</h1>
            <p class="text-muted mb-0">
                <?= $gracias ? 'Tu opinión nos ayuda a mejorar.' : 'Ya registramos tu respuesta desde este dispositivo.' ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <form method="post" class="card shadow-sm" novalidate>
        <div class="card-body p-4">
            <h1 class="h4"><?= e($enc['titulo']) ?></h1>
            <?php if ($enc['descripcion'] !== ''): ?>
                <p class="text-muted"><?= nl2br(e($enc['descripcion'])) ?></p>
            <?php endif; ?>
            <p class="small text-muted">Esta encuesta es anónima. Las preguntas con * son obligatorias.</p>

            <?php if ($errores): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $m): ?><li><?= e($m) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div style="position:absolute; left:-9999px;" aria-hidden="true">
                <input type="text" name="sitio_web" tabindex="-1" autocomplete="off">
            </div>

            <?php foreach ($enc['preguntas'] as $pid => $p): ?>
                <fieldset class="mb-4">
                    <legend class="h6">
                        <?= e($p['texto']) ?><?= $p['obligatoria'] ? ' *' : '' ?>
                    </legend>

                    <?php if ($p['tipo'] === 'texto'): ?>
                        <textarea class="form-control" rows="3" maxlength="<?= ENC_MAX_TEXTO_RESPUESTA ?>"
                                  name="r[<?= $pid ?>]"><?= e(is_string($valores[$pid] ?? null) ? $valores[$pid] : '') ?></textarea>
                        <div class="form-text">No escribas datos personales (nombre, cédula, teléfono).</div>

                    <?php elseif ($p['tipo'] === 'escala'): ?>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php for ($n = 1; $n <= 5; $n++): ?>
                                <input type="radio" class="btn-check" name="r[<?= $pid ?>]" id="p<?= $pid ?>_<?= $n ?>"
                                       value="<?= $n ?>" <?= marcado($valores, $pid, $n) ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" style="min-width:3rem" for="p<?= $pid ?>_<?= $n ?>"><?= $n ?></label>
                            <?php endfor; ?>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-1" style="max-width:20rem">
                            <span>1 = mínimo</span><span>5 = máximo</span>
                        </div>

                    <?php else: ?>
                        <?php foreach ($p['opciones'] as $oid => $o): ?>
                            <div class="form-check">
                                <input class="form-check-input" id="p<?= $pid ?>_<?= $oid ?>"
                                       type="<?= $p['multiple'] ? 'checkbox' : 'radio' ?>"
                                       name="r[<?= $pid ?>]<?= $p['multiple'] ? '[]' : '' ?>"
                                       value="<?= $oid ?>" <?= marcado($valores, $pid, $oid) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="p<?= $pid ?>_<?= $oid ?>"><?= e($o['texto']) ?></label>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($p['multiple']): ?><div class="form-text">Puedes marcar más de una opción.</div><?php endif; ?>
                    <?php endif; ?>
                </fieldset>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary btn-lg w-100">Enviar respuestas</button>
        </div>
    </form>
<?php endif; ?>
    <p class="text-center text-muted small mt-3">Hospital de Clínicas</p>
</div>
</body>
</html>
