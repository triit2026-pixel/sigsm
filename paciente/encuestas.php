<?php
require __DIR__ . '/../conexion.php';

$q = trim($_GET['q'] ?? '');
$a = $_GET['a'] ?? '';
$general = ($a === 'g');
$areaId = (is_string($a) && ctype_digit($a)) ? (int)$a : 0;

$areas = $pdo->query(
    "SELECT DISTINCT c.id, c.nombre
     FROM categoria c
     JOIN encuesta e ON e.categoria_id = c.id AND e.estado = 'activa'
     ORDER BY c.nombre"
)->fetchAll();
$hayGeneral = (int)$pdo->query(
    "SELECT COUNT(*) FROM encuesta WHERE estado = 'activa' AND categoria_id IS NULL"
)->fetchColumn() > 0;

$sql = "SELECT e.id, e.token, e.titulo, e.descripcion, c.nombre AS area
        FROM encuesta e
        LEFT JOIN categoria c ON c.id = e.categoria_id
        WHERE e.estado = 'activa'
          AND EXISTS (SELECT 1 FROM encuesta_pregunta p WHERE p.encuesta_id = e.id)";
$params = [];
if ($q !== '') {
    $sql .= " AND e.titulo LIKE :q";
    $params[':q'] = '%' . addcslashes($q, '%_\\') . '%';
}
if ($general) {
    $sql .= " AND e.categoria_id IS NULL";
} elseif ($areaId > 0) {
    $sql .= " AND e.categoria_id = :a";
    $params[':a'] = $areaId;
}
$sql .= " ORDER BY e.titulo";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$encuestas = $stmt->fetchAll();

$hayFiltro = ($q !== '' || $general || $areaId > 0);
$tituloPagina = 'Encuestas';
require __DIR__ . '/encabezado.php';
?>
<h1 class="h3 mb-3">Encuestas</h1>

<form method="get" class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-md-5">
                <input type="text" class="form-control" name="q" placeholder="Buscar por nombre" value="<?= e($q) ?>">
            </div>
            <div class="col-12 col-md-4">
                <select class="form-select" name="a">
                    <option value="">Todas las áreas</option>
                    <?php if ($hayGeneral): ?>
                        <option value="g" <?= $general ? 'selected' : '' ?>>General</option>
                    <?php endif; ?>
                    <?php foreach ($areas as $ar): ?>
                        <option value="<?= (int)$ar['id'] ?>" <?= $areaId === (int)$ar['id'] ? 'selected' : '' ?>><?= e($ar['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
            </div>
        </div>
        <?php if ($hayFiltro): ?>
            <div class="mt-2"><a href="encuestas.php" class="small">Quitar filtros</a></div>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($encuestas)): ?>
    <div class="alert alert-secondary text-center">
        <?= $hayFiltro ? 'No se encontraron encuestas con esos filtros.' : 'Por ahora no hay encuestas disponibles.' ?>
    </div>
<?php else: ?>
    <div class="list-group shadow-sm">
        <?php foreach ($encuestas as $en): ?>
            <?php $respondida = isset($_COOKIE['enc_' . (int)$en['id']]); ?>
            <a href="../encuesta.php?t=<?= e($en['token']) ?>" class="list-group-item list-group-item-action py-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="fw-semibold"><?= e($en['titulo']) ?></span>
                    <span class="badge bg-info text-dark text-wrap text-end"><?= $en['area'] ? e($en['area']) : 'General' ?></span>
                </div>
                <?php if ($en['descripcion'] !== ''): ?>
                    <div class="small text-muted mt-1"><?= e(mb_strimwidth($en['descripcion'], 0, 120, '…')) ?></div>
                <?php endif; ?>
                <?php if ($respondida): ?>
                    <span class="badge bg-success mt-2">Ya respondida desde este dispositivo</span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/pie.php'; ?>