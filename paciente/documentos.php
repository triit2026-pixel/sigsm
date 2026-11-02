<?php
require __DIR__ . '/../conexion.php';

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['c'] ?? 0);

$categorias = $pdo->query(
    "SELECT DISTINCT c.id, c.nombre
     FROM categoria c
     JOIN documento d ON d.categoria_id = c.id AND d.activo = 1
     ORDER BY c.nombre"
)->fetchAll();

$sql = "SELECT d.token, d.titulo, d.descripcion, c.nombre AS categoria
        FROM documento d
        JOIN categoria c ON c.id = d.categoria_id
        WHERE d.activo = 1";
$params = [];
if ($q !== '') {
    $sql .= " AND d.titulo LIKE :q";
    $params[':q'] = '%' . addcslashes($q, '%_\\') . '%';
}
if ($cat > 0) {
    $sql .= " AND d.categoria_id = :c";
    $params[':c'] = $cat;
}
$sql .= " ORDER BY d.titulo";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documentos = $stmt->fetchAll();

$hayFiltro = ($q !== '' || $cat > 0);
$tituloPagina = 'Documentación';
require __DIR__ . '/encabezado.php';
?>
<h1 class="h3 mb-3">Documentación</h1>

<form method="get" class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-md-5">
                <input type="text" class="form-control" name="q" placeholder="Buscar por título" value="<?= e($q) ?>">
            </div>
            <div class="col-12 col-md-4">
                <select class="form-select" name="c">
                    <option value="0">Todas las categorías</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
            </div>
        </div>
        <?php if ($hayFiltro): ?>
            <div class="mt-2"><a href="documentos.php" class="small">Quitar filtros</a></div>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($documentos)): ?>
    <div class="alert alert-secondary text-center">
        <?= $hayFiltro ? 'No se encontraron documentos con esos filtros.' : 'Todavía no hay documentos disponibles.' ?>
    </div>
<?php else: ?>
    <div class="list-group shadow-sm">
        <?php foreach ($documentos as $d): ?>
            <a href="../ver.php?t=<?= e($d['token']) ?>" class="list-group-item list-group-item-action py-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="fw-semibold"><?= e($d['titulo']) ?></span>
                    <span class="badge bg-info text-dark text-wrap text-end"><?= e($d['categoria']) ?></span>
                </div>
                <?php if (!empty($d['descripcion'])): ?>
                    <div class="small text-muted mt-1"><?= e(mb_strimwidth($d['descripcion'], 0, 120, '…')) ?></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/pie.php'; ?>