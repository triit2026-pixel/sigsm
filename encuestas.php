<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/encuestas.php';
requerir_rol('administrativo');

// Cambiar estado: activar / cerrar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $id    = (int)($_POST['id'] ?? 0);
    $nuevo = $_POST['estado'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT e.id, (SELECT COUNT(*) FROM encuesta_pregunta p WHERE p.encuesta_id = e.id) AS preguntas
         FROM encuesta e WHERE e.id = :id"
    );
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch();

    if (!$fila || !in_array($nuevo, ['activa', 'cerrada'], true)) {
        flash('No se pudo cambiar el estado de la encuesta.', 'danger');
    } elseif ($nuevo === 'activa' && (int)$fila['preguntas'] === 0) {
        flash('Agrega al menos una pregunta antes de activar la encuesta.', 'danger');
    } else {
        $pdo->prepare("UPDATE encuesta SET estado = :e WHERE id = :id")->execute([':e' => $nuevo, ':id' => $id]);
        flash($nuevo === 'activa' ? 'Encuesta activada: ya puede responderse con su QR.' : 'Encuesta cerrada: el QR ya no recibe respuestas.');
    }
    redirigir('encuestas.php');
}

$encuestas = $pdo->query(
    "SELECT e.*,
            (SELECT COUNT(*) FROM encuesta_pregunta p WHERE p.encuesta_id = e.id)  AS preguntas,
            (SELECT COUNT(*) FROM encuesta_respuesta r WHERE r.encuesta_id = e.id) AS respuestas
     FROM encuesta e
     ORDER BY e.fecha_actualizacion DESC, e.id DESC"
)->fetchAll();

$tituloPagina = 'Encuestas';
require 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Encuestas</h1>
    <a href="encuesta_form.php" class="btn btn-primary">+ Nueva encuesta</a>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Preguntas</th>
                    <th>Respuestas</th>
                    <th>Actualizada</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($encuestas)): ?>
                <tr><td colspan="6" class="text-center text-muted">Todavía no hay encuestas. Crea la primera con «+ Nueva encuesta».</td></tr>
            <?php else: foreach ($encuestas as $en): ?>
                <tr>
                    <td><?= e($en['titulo']) ?></td>
                    <td><?= enc_estado_badge($en['estado']) ?></td>
                    <td><?= (int)$en['preguntas'] ?></td>
                    <td><?= (int)$en['respuestas'] ?></td>
                    <td><?= e(date('d/m/Y', strtotime($en['fecha_actualizacion']))) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="encuesta_resultados.php?id=<?= (int)$en['id'] ?>" class="btn btn-sm btn-outline-success">Resultados</a>
                        <a href="qr_encuesta.php?id=<?= (int)$en['id'] ?>" class="btn btn-sm btn-outline-primary">QR</a>
                        <a href="encuesta_form.php?id=<?= (int)$en['id'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                        <?php if ($en['estado'] === 'activa'): ?>
                            <form method="post" class="d-inline"
                                  onsubmit="return confirm('¿Cerrar esta encuesta? Dejará de recibir respuestas.');">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int)$en['id'] ?>">
                                <input type="hidden" name="estado" value="cerrada">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Cerrar</button>
                            </form>
                        <?php else: ?>
                            <form method="post" class="d-inline">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int)$en['id'] ?>">
                                <input type="hidden" name="estado" value="activa">
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <?= $en['estado'] === 'cerrada' ? 'Reabrir' : 'Activar' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
