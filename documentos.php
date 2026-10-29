<?php
require 'conexion.php';
require 'includes/auth.php';
requerir_login();

$config = require __DIR__ . '/config.php';

$q        = trim($_GET['q'] ?? '');
$cat      = (int)($_GET['categoria'] ?? 0);
$estado   = $_GET['estado'] ?? 'activos';
if (!in_array($estado, ['activos', 'baja', 'todos'], true)) {
    $estado = 'activos';
}
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = (int)$config['por_pagina'];

$where  = [];
$params = [];
if ($estado === 'activos') { $where[] = 'd.activo = 1'; }
if ($estado === 'baja')    { $where[] = 'd.activo = 0'; }
if ($q !== '') {
    $where[] = 'd.titulo LIKE :q';
    $params[':q'] = '%' . $q . '%';
}
if ($cat > 0) {
    $where[] = 'd.categoria_id = :cat';
    $params[':cat'] = $cat;
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM documento d $sqlWhere");
$stmt->execute($params);
$total   = (int)$stmt->fetchColumn();
$paginas = max(1, (int)ceil($total / $porPagina));
$pagina  = min($pagina, $paginas);
$offset  = ($pagina - 1) * $porPagina;

$stmt = $pdo->prepare(
    "SELECT d.*, c.nombre AS categoria
     FROM documento d
     JOIN categoria c ON c.id = d.categoria_id
     $sqlWhere
     ORDER BY d.fecha_actualizacion DESC, d.id DESC
     LIMIT $porPagina OFFSET $offset"
);
$stmt->execute($params);
$documentos = $stmt->fetchAll();

$categorias = $pdo->query("SELECT id, nombre FROM categoria ORDER BY nombre")->fetchAll();

function urlPagina(int $n): string {
    return 'documentos.php?' . http_build_query(array_merge($_GET, ['pagina' => $n]));
}

$tituloPagina = 'Documentos';
require 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Documentos</h1>
    <a href="alta_documento.php" class="btn btn-primary">+ Nuevo documento</a>
</div>

<form method="get" class="card shadow-sm mb-3">
    <div class="card-body row g-2">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Buscar por título" value="<?= e($q) ?>">
        </div>
        <div class="col-md-3">
            <select name="categoria" class="form-select">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="estado" class="form-select">
                <option value="activos" <?= $estado === 'activos' ? 'selected' : '' ?>>Activos</option>
                <option value="baja"    <?= $estado === 'baja' ? 'selected' : '' ?>>Dados de baja</option>
                <option value="todos"   <?= $estado === 'todos' ? 'selected' : '' ?>>Todos</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>Actualizado</th>
                    <th>Escaneos</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($documentos)): ?>
                <tr><td colspan="5" class="text-center text-muted">No hay documentos para mostrar.</td></tr>
            <?php else: foreach ($documentos as $d): ?>
                <tr>
                    <td>
                        <?= e($d['titulo']) ?>
                        <?php if (!$d['activo']): ?><span class="badge bg-secondary ms-1">Baja</span><?php endif; ?>
                    </td>
                    <td><span class="badge bg-info text-dark"><?= e($d['categoria']) ?></span></td>
                    <td><?= e(date('d/m/Y', strtotime($d['fecha_actualizacion']))) ?></td>
                    <td><?= (int)$d['visitas'] ?></td>
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal" data-bs-target="#modalPrevia"
                                data-url="previsualizar_documento.php?id=<?= (int)$d['id'] ?>"
                                data-titulo="<?= e($d['titulo']) ?>">Ver</button>
                        <a href="qr_documento.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-outline-primary">QR</a>
                        <a href="editar_documento.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                        <?php if ($d['activo']): ?>
                            <form action="baja_documento.php" method="post" class="d-inline"
                                  onsubmit="return confirm('¿Dar de baja este documento? Su QR dejará de funcionar.');">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Baja</button>
                            </form>
                        <?php else: ?>
                            <form action="reactivar_documento.php" method="post" class="d-inline">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-success">Reactivar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

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
<!-- Previsualización del documento -->
<div class="modal fade" id="modalPrevia" tabindex="-1" aria-labelledby="modalPreviaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5 text-break" id="modalPreviaTitulo">Vista previa</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="marcoPrevia" title="Vista previa del documento"
                        style="width:100%; height:75vh; border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <a id="abrirPrevia" href="#" target="_blank" rel="noopener" class="btn btn-outline-primary">Abrir en una pestaña nueva</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var modal = document.getElementById('modalPrevia');
    var marco = document.getElementById('marcoPrevia');
    modal.addEventListener('show.bs.modal', function (ev) {
        var btn = ev.relatedTarget;
        document.getElementById('modalPreviaTitulo').textContent = btn.getAttribute('data-titulo');
        document.getElementById('abrirPrevia').href = btn.getAttribute('data-url');
        marco.src = btn.getAttribute('data-url');
    });
    // Al cerrar se vacía el visor para que no siga cargado
    modal.addEventListener('hidden.bs.modal', function () {
        marco.src = 'about:blank';
    });
})();
</script>
<?php require 'includes/footer.php'; ?>
