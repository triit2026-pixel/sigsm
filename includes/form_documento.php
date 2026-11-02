<?php
/* Formulario compartido por alta y edición.
   Variables esperadas: $valores, $errores, $categorias, $esEdicion, $archivoActual (solo edición) */
?>
<?php if (!empty($errores['general'])): ?>
    <div class="alert alert-danger"><?= e($errores['general']) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" novalidate>
            <?= csrf_campo() ?>

            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" maxlength="150"
                       value="<?= e($valores['titulo']) ?>">
                <?php if (!empty($errores['titulo'])): ?>
                    <div class="text-danger small"><?= e($errores['titulo']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Categoría</label>
                <select name="categoria_id" class="form-select">
                    <option value="">— Selecciona —</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"
                            <?= (int)$valores['categoria_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errores['categoria_id'])): ?>
                    <div class="text-danger small"><?= e($errores['categoria_id']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción <span class="text-muted">(opcional)</span></label>
                <textarea name="descripcion" class="form-control" rows="3" maxlength="500"><?= e($valores['descripcion']) ?></textarea>
                <?php if (!empty($errores['descripcion'])): ?>
                    <div class="text-danger small"><?= e($errores['descripcion']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Archivo PDF</label>
                <?php if ($esEdicion): ?>
                    <div class="form-text mb-1">
                        Actual: <strong><?= e($archivoActual) ?></strong>. Sube otro solo si quieres reemplazarlo
                        (el QR seguirá funcionando igual).
                    </div>
                <?php endif; ?>
                <input type="file" name="archivo" class="form-control" accept="application/pdf">
                <?php if (!empty($errores['archivo'])): ?>
                    <div class="text-danger small"><?= e($errores['archivo']) ?></div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between">
                <a href="documentos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Guardar documento' ?></button>
            </div>
        </form>
    </div>
</div>
