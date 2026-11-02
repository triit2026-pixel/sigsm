<?php
require 'conexion.php';
require 'includes/auth.php';
requerir_rol('administrativo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'crear' || $accion === 'renombrar') {
            $nombre = trim($_POST['nombre'] ?? '');
            if ($nombre === '' || mb_strlen($nombre) > 100) {
                flash('El nombre es obligatorio y no puede superar los 100 caracteres.', 'danger');
            } elseif ($accion === 'crear') {
                $pdo->prepare("INSERT INTO categoria (nombre) VALUES (:n)")->execute([':n' => $nombre]);
                flash('Categoría creada correctamente.');
            } else {
                $pdo->prepare("UPDATE categoria SET nombre = :n WHERE id = :id")
                    ->execute([':n' => $nombre, ':id' => (int)($_POST['id'] ?? 0)]);
                flash('Categoría renombrada correctamente.');
            }
        } elseif ($accion === 'alternar') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT activa FROM categoria WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $cat = $stmt->fetch();

            if ($cat) {
                if ((int)$cat['activa'] === 1) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documento WHERE categoria_id = :id AND activo = 1");
                    $stmt->execute([':id' => $id]);
                    if ((int)$stmt->fetchColumn() > 0) {
                        flash('No se puede desactivar: la categoría tiene documentos activos.', 'danger');
                    } else {
                        $pdo->prepare("UPDATE categoria SET activa = 0 WHERE id = :id")->execute([':id' => $id]);
                        flash('Categoría desactivada.');
                    }
                } else {
                    $pdo->prepare("UPDATE categoria SET activa = 1 WHERE id = :id")->execute([':id' => $id]);
                    flash('Categoría reactivada.');
                }
            }
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            flash('Ya existe una categoría con ese nombre.', 'danger');
        } else {
            error_log('categorias.php: ' . $e->getMessage());
            flash('Ocurrió un error al guardar. Inténtalo de nuevo.', 'danger');
        }
    }
    redirigir('categorias.php');
}

$categorias = $pdo->query(
    "SELECT c.*, (SELECT COUNT(*) FROM documento d WHERE d.categoria_id = c.id AND d.activo = 1) AS total
     FROM categoria c ORDER BY c.nombre"
)->fetchAll();

$tituloPagina = 'Categorías';
require 'includes/header.php';
?>
<h1 class="h3 mb-4">Categorías</h1>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="post" class="row g-2" novalidate>
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="crear">
            <div class="col-md-9">
                <input type="text" name="nombre" class="form-control" maxlength="100" placeholder="Nueva categoría (ej: Pediatría)">
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary">+ Agregar</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Documentos activos</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categorias as $c): ?>
                <tr>
                    <td style="min-width:260px">
                        <form method="post" class="d-flex gap-2" novalidate>
                            <?= csrf_campo() ?>
                            <input type="hidden" name="accion" value="renombrar">
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <input type="text" name="nombre" class="form-control form-control-sm" maxlength="100"
                                   value="<?= e($c['nombre']) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Guardar</button>
                        </form>
                    </td>
                    <td><?= (int)$c['total'] ?></td>
                    <td>
                        <span class="badge <?= $c['activa'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $c['activa'] ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <form method="post" class="d-inline">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="accion" value="alternar">
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="btn btn-sm <?= $c['activa'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                <?= $c['activa'] ? 'Desactivar' : 'Reactivar' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
