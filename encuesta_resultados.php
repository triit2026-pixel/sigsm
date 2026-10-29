<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/encuestas.php';
requerir_rol('administrativo');

$id  = (int)($_GET['id'] ?? 0);
$enc = enc_cargar($pdo, $id);
if (!$enc) {
    flash('La encuesta no existe.', 'danger');
    redirigir('encuestas.php');
}

// ---- Filtro por fechas (opcional) ----
$desde = enc_fecha_valida($_GET['fecha_desde'] ?? '');
$hasta = enc_fecha_valida($_GET['fecha_hasta'] ?? '');
$filtroSql = '';
$params    = [];
if ($desde !== '') { $filtroSql .= ' AND r.fecha >= :desde'; $params[':desde'] = $desde . ' 00:00:00'; }
if ($hasta !== '') { $filtroSql .= ' AND r.fecha <= :hasta'; $params[':hasta'] = $hasta . ' 23:59:59'; }

$stmt = $pdo->prepare("SELECT COUNT(*) FROM encuesta_respuesta r WHERE r.encuesta_id = :enc $filtroSql");
$stmt->execute($params + [':enc' => $id]);
$total = (int)$stmt->fetchColumn();

// ---- Resultados por pregunta ----
$resultados = [];
foreach ($enc['preguntas'] as $pid => $p) {
    $base = "FROM encuesta_respuesta_detalle d
             JOIN encuesta_respuesta r ON r.id = d.respuesta_id
             WHERE d.pregunta_id = :p $filtroSql";
    $pp = $params + [':p' => $pid];
    $res = ['respondieron' => 0];

    if ($p['tipo'] === 'texto') {
        $stmt = $pdo->prepare("SELECT d.valor_texto, r.fecha $base AND d.valor_texto IS NOT NULL ORDER BY r.fecha DESC, d.id DESC LIMIT 200");
        $stmt->execute($pp);
        $res['textos'] = $stmt->fetchAll();
        $stmt = $pdo->prepare("SELECT COUNT(*) $base AND d.valor_texto IS NOT NULL");
        $stmt->execute($pp);
        $res['respondieron'] = (int)$stmt->fetchColumn();

    } elseif ($p['tipo'] === 'escala') {
        $stmt = $pdo->prepare("SELECT d.valor_escala AS v, COUNT(*) AS c $base AND d.valor_escala IS NOT NULL GROUP BY d.valor_escala");
        $stmt->execute($pp);
        $conteo = array_fill(1, 5, 0);
        foreach ($stmt->fetchAll() as $f) { $conteo[(int)$f['v']] = (int)$f['c']; }
        $n = array_sum($conteo);
        $suma = 0;
        foreach ($conteo as $v => $c) { $suma += $v * $c; }
        $res['conteo'] = $conteo;
        $res['respondieron'] = $n;
        $res['promedio'] = $n > 0 ? $suma / $n : null;

    } else {
        $stmt = $pdo->prepare("SELECT d.opcion_id AS o, COUNT(*) AS c $base AND d.opcion_id IS NOT NULL GROUP BY d.opcion_id");
        $stmt->execute($pp);
        $conteo = [];
        foreach ($stmt->fetchAll() as $f) { $conteo[(int)$f['o']] = (int)$f['c']; }
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT d.respuesta_id) $base AND d.opcion_id IS NOT NULL");
        $stmt->execute($pp);
        $res['conteo'] = $conteo;
        $res['respondieron'] = (int)$stmt->fetchColumn();
    }
    $resultados[$pid] = $res;
}

$qs = http_build_query(array_filter(['id' => $id, 'fecha_desde' => $desde, 'fecha_hasta' => $hasta]));

$tituloPagina = 'Resultados';
require 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1"><?= e($enc['titulo']) ?></h1>
        <?= enc_estado_badge($enc['estado']) ?>
    </div>
    <div class="d-flex gap-2">
        <?php if ($total > 0): ?>
            <a href="encuesta_exportar.php?<?= e($qs) ?>" class="btn btn-outline-success">Exportar CSV</a>
        <?php endif; ?>
        <a href="encuestas.php" class="btn btn-secondary">Volver</a>
    </div>
</div>

<form method="get" class="card shadow-sm mb-3">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label mb-1">Desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="<?= e($desde) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label mb-1">Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="<?= e($hasta) ?>">
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        </div>
        <div class="col-md-2 d-grid">
            <a href="encuesta_resultados.php?id=<?= (int)$id ?>" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="alert alert-primary">
    <strong><?= $total ?></strong> <?= $total === 1 ? 'respuesta recibida' : 'respuestas recibidas' ?>
    <?= ($desde !== '' || $hasta !== '') ? 'en el período filtrado' : '' ?>.
</div>

<?php if ($total === 0): ?>
    <p class="text-muted">Todavía no hay respuestas para mostrar.</p>
<?php else: foreach ($enc['preguntas'] as $pid => $p): $r = $resultados[$pid]; ?>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h6 mb-1"><?= e($p['texto']) ?></h2>
            <div class="small text-muted mb-3">
                <?= e(ENC_TIPOS[$p['tipo']]) ?> · respondieron <?= (int)$r['respondieron'] ?> de <?= $total ?>
            </div>

            <?php if ($p['tipo'] === 'texto'): ?>
                <?php if (empty($r['textos'])): ?>
                    <p class="text-muted mb-0">Sin comentarios.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($r['textos'] as $t): ?>
                            <li class="list-group-item">
                                <?= nl2br(e($t['valor_texto'])) ?>
                                <div class="small text-muted"><?= e(date('d/m/Y H:i', strtotime($t['fecha']))) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($r['respondieron'] > 200): ?>
                        <p class="small text-muted mt-2 mb-0">Se muestran los 200 comentarios más recientes; el CSV trae todos.</p>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif ($p['tipo'] === 'escala'): ?>
                <?php if ($r['promedio'] === null): ?>
                    <p class="text-muted mb-0">Sin respuestas.</p>
                <?php else: ?>
                    <p class="mb-3"><span class="fs-4 fw-semibold"><?= e(number_format($r['promedio'], 1, ',', '')) ?></span> promedio (de 1 a 5)</p>
                    <?php foreach ($r['conteo'] as $valor => $c):
                        $pct = $r['respondieron'] > 0 ? round($c * 100 / $r['respondieron']) : 0; ?>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span style="width:1.5rem"><?= $valor ?></span>
                            <div class="progress flex-grow-1" style="height:1.1rem">
                                <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%"></div>
                            </div>
                            <span class="small text-muted text-nowrap" style="width:6rem"><?= $c ?> (<?= $pct ?>%)</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            <?php else: ?>
                <?php foreach ($p['opciones'] as $oid => $o):
                    $c = $r['conteo'][$oid] ?? 0;
                    $pct = $r['respondieron'] > 0 ? round($c * 100 / $r['respondieron']) : 0; ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small">
                            <span><?= e($o['texto']) ?></span>
                            <span class="text-muted"><?= $c ?> (<?= $pct ?>%)</span>
                        </div>
                        <div class="progress" style="height:.9rem">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($p['multiple']): ?>
                    <p class="small text-muted mb-0">Se podía elegir más de una opción, por eso los porcentajes pueden sumar más de 100 %.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; endif; ?>
<?php require 'includes/footer.php'; ?>
