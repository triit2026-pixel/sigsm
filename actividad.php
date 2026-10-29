<?php
require 'conexion.php';
require 'includes/auth.php';
requerir_rol('administrativo');

$config = require __DIR__ . '/config.php';


const MINUTOS_SESION_ACTIVA = 15;


$fecha = trim($_GET['fecha'] ?? '');
$desde = trim($_GET['hora_desde'] ?? '');
$hasta = trim($_GET['hora_hasta'] ?? '');

if ($fecha !== '') {
    $ok = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $m) && checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
    if (!$ok) { $fecha = ''; }
}
$horaValida = fn(string $h): bool => (bool)preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $h);
if ($desde !== '' && !$horaValida($desde)) { $desde = ''; }
if ($hasta !== '' && !$horaValida($hasta)) { $hasta = ''; }

$where  = [];
$params = [];
if ($fecha !== '') {
    $where[] = 'DATE(a.fecha_ingreso) = :fecha';
    $params[':fecha'] = $fecha;
}
if ($desde !== '') {
    $where[] = 'TIME(a.fecha_ingreso) >= :desde';
    $params[':desde'] = $desde . ':00';
}
if ($hasta !== '') {
    $where[] = 'TIME(a.fecha_ingreso) <= :hasta';
    $params[':hasta'] = $hasta . ':59';
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$pagina    = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = (int)$config['por_pagina'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM actividad_sesion a $sqlWhere");
$stmt->execute($params);
$total   = (int)$stmt->fetchColumn();
$paginas = max(1, (int)ceil($total / $porPagina));
$pagina  = min($pagina, $paginas);
$offset  = ($pagina - 1) * $porPagina;

$stmt = $pdo->prepare(
    "SELECT a.*, TIMESTAMPDIFF(MINUTE, a.ultima_actividad, NOW()) AS minutos_inactiva
     FROM actividad_sesion a
     $sqlWhere
     ORDER BY a.fecha_ingreso DESC, a.id DESC
     LIMIT $porPagina OFFSET $offset"
);
$stmt->execute($params);
$sesiones = $stmt->fetchAll();

function urlPagina(int $n): string {
    return 'actividad.php?' . http_build_query(array_merge($_GET, ['pagina' => $n]));
}

function fmtSalida(string $ingreso, string $salida): string {
    $f = strtotime($salida);
    return date('Y-m-d', strtotime($ingreso)) === date('Y-m-d', $f) ? date('H:i', $f) : date('d/m/Y H:i', $f);
}

$hayFiltros = ($fecha !== '' || $desde !== '' || $hasta !== '');
$tituloPagina = 'Actividad reciente';
require 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Actividad reciente</h1>
    <span class="text-muted small"><?= $total ?> <?= $total === 1 ? 'ingreso' : 'ingresos' ?></span>
</div>

<form method="get" class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6 col-md-4 col-lg-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" id="fecha" name="fecha" class="form-control" value="<?= e($fecha) ?>">
            </div>
        </div>
        <div class="row g-3 mt-0 align-items-end">
            <div class="col-6 col-md-3 col-lg-2">
                <label for="hora_desde" class="form-label">Hora de entrada, desde</label>
                <input type="time" id="hora_desde" name="hora_desde" class="form-control" value="<?= e($desde) ?>">
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label for="hora_hasta" class="form-label">Hora de entrada, hasta</label>
                <input type="time" id="hora_hasta" name="hora_hasta" class="form-control" value="<?= e($hasta) ?>">
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                <?php if ($hayFiltros): ?>
                    <a href="actividad.php" class="btn btn-outline-secondary">Limpiar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Ingreso</th>
                    <th>Salida</th>
                    <th>Dirección IP</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($sesiones)): ?>
                <tr><td colspan="5" class="text-center text-muted">No hay ingresos para mostrar.</td></tr>
            <?php else: foreach ($sesiones as $s): ?>
                <tr>
                    <td class="text-break"><?= e($s['email']) ?></td>
                    <td>
                        <span class="badge <?= $s['rol'] === 'administrativo' ? 'bg-primary' : 'bg-success' ?>">
                            <?= e(rol_etiqueta($s['rol'])) ?>
                        </span>
                    </td>
                    <td class="text-nowrap"><?= e(date('d/m/Y H:i', strtotime($s['fecha_ingreso']))) ?></td>
                    <td class="text-nowrap">
                        <?php if ($s['fecha_salida'] !== null): ?>
                            <?= e(fmtSalida($s['fecha_ingreso'], $s['fecha_salida'])) ?>
                        <?php elseif ((int)$s['minutos_inactiva'] < MINUTOS_SESION_ACTIVA): ?>
                            <span class="badge bg-success-subtle text-success-emphasis">En sesión</span>
                        <?php else: ?>
                            <?= e(fmtSalida($s['fecha_ingreso'], $s['ultima_actividad'])) ?>
                            <span class="text-muted" title="No cerró sesión con el botón Salir; se muestra su última actividad.">*</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= e($s['ip']) ?>
                        <?php if (in_array($s['ip'], ['::1', '127.0.0.1'], true)): ?>
                            <span class="text-muted small">(este equipo)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="small text-muted mt-2 mb-0">* No cerró sesión con el botón Salir (cerró el navegador o se venció la sesión): se muestra su última actividad.</p>

<?php if ($paginas > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e(urlPagina($pagina - 1)) ?>">Anterior</a>
            </li>
            <li class="page-item disabled"><span class="page-link">Página <?= $pagina ?> de <?= $paginas ?></span></li>
            <li class="page-item <?= $pagina >= $paginas ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e(urlPagina($pagina + 1)) ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
