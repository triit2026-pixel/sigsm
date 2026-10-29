<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/usuarios.php';
requerir_rol('administrativo');

$yo = usuario_actual();

// Contraseña temporal recién generada: se muestra una sola vez
$claveTemporal = $_SESSION['clave_temporal'] ?? null;
unset($_SESSION['clave_temporal']);

$usuarios = $pdo->query(
    "SELECT u.id, u.nombre, u.email, u.rol, u.activo, u.debe_cambiar_clave,
            (SELECT MAX(a.fecha_ingreso) FROM actividad_sesion a WHERE a.usuario_id = u.id) AS ultimo_ingreso
     FROM usuario u
     ORDER BY u.activo DESC, u.nombre"
)->fetchAll();

$tituloPagina = 'Usuarios';
require 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Usuarios</h1>
    <a href="usuario_form.php" class="btn btn-primary">+ Nuevo usuario</a>
</div>

<?php if ($claveTemporal): ?>
    <div class="alert alert-warning" role="alert">
        <h2 class="h6 mb-1">Contraseña temporal de <?= e($claveTemporal['nombre']) ?> (<?= e($claveTemporal['email']) ?>)</h2>
        <p class="small mb-2">
            Cópiala ahora: <strong>no se vuelve a mostrar</strong>. Pásasela a la persona; el sistema le pedirá
            cambiarla en su primer ingreso.
        </p>
        <div class="input-group" style="max-width: 420px;">
            <input type="text" id="claveTemporal" class="form-control font-monospace"
                   value="<?= e($claveTemporal['clave']) ?>" readonly>
            <button type="button" class="btn btn-outline-dark" id="copiarClave">Copiar</button>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Último ingreso</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): $esYo = (int)$u['id'] === (int)$yo['id']; ?>
                <tr>
                    <td>
                        <?= e($u['nombre']) ?>
                        <?php if ($esYo): ?><span class="badge bg-light text-dark border ms-1">Tú</span><?php endif; ?>
                    </td>
                    <td class="text-break"><?= e($u['email']) ?></td>
                    <td>
                        <span class="badge <?= $u['rol'] === 'administrativo' ? 'bg-primary' : 'bg-success' ?>">
                            <?= e(rol_etiqueta($u['rol'])) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['activo']): ?>
                            <span class="badge bg-success-subtle text-success-emphasis">Activo</span>
                            <?php if ($u['debe_cambiar_clave']): ?>
                                <span class="badge bg-warning text-dark" title="Todavía no cambió su contraseña temporal">Cambio pendiente</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-secondary">Desactivado</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted">
                        <?= $u['ultimo_ingreso'] ? e(date('d/m/Y H:i', strtotime($u['ultimo_ingreso']))) : 'Nunca' ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="usuario_form.php?id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                        <?php if (!$esYo): ?>
                            <form action="usuario_restablecer.php" method="post" class="d-inline"
                                  onsubmit="return confirm('¿Restablecer la contraseña de este usuario? Se generará una temporal y tendrá que cambiarla al ingresar.');">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Restablecer contraseña</button>
                            </form>
                            <?php if ($u['activo']): ?>
                                <form action="usuario_estado.php" method="post" class="d-inline"
                                      onsubmit="return confirm('¿Desactivar este usuario? No podrá ingresar al sistema.');">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <input type="hidden" name="accion" value="desactivar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Desactivar</button>
                                </form>
                            <?php else: ?>
                                <form action="usuario_estado.php" method="post" class="d-inline">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <input type="hidden" name="accion" value="reactivar">
                                    <button type="submit" class="btn btn-sm btn-outline-success">Reactivar</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($claveTemporal): ?>
<script>
document.getElementById('copiarClave').addEventListener('click', function () {
    var campo = document.getElementById('claveTemporal');
    var boton = this;
    campo.select();
    var listo = function () {
        boton.textContent = 'Copiada';
        setTimeout(function () { boton.textContent = 'Copiar'; }, 2000);
    };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(campo.value).then(listo);
    } else {
        // Sitio por http en la red local: el portapapeles moderno no está disponible
        try { if (document.execCommand('copy')) { listo(); } } catch (e) {}
    }
});
</script>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
