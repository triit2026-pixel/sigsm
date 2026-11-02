<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/usuarios.php';
requerir_rol('administrativo');

$yo = usuario_actual();
$id = (int)($_GET['id'] ?? 0);
$editar = $id > 0;
$usuarioEditado = null;

if ($editar) {
    $stmt = $pdo->prepare("SELECT id, nombre, email, rol FROM usuario WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $usuarioEditado = $stmt->fetch();
    if (!$usuarioEditado) {
        flash('El usuario no existe.', 'danger');
        redirigir('usuarios.php');
    }
}

// Nadie puede cambiarse el rol a sí mismo (así siempre queda al menos un administrativo)
$esYo = $editar && (int)$usuarioEditado['id'] === (int)$yo['id'];

$errores = [];
$valores = $editar
    ? ['nombre' => $usuarioEditado['nombre'], 'email' => $usuarioEditado['email'], 'rol' => $usuarioEditado['rol']]
    : ['nombre' => '', 'email' => '', 'rol' => 'medico'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $valores = leer_campos_usuario();
    if ($esYo) {
        $valores['rol'] = $usuarioEditado['rol'];
    }
    $errores = validar_usuario($pdo, $valores, $editar ? $id : 0);

    if (empty($errores)) {
        try {
            if ($editar) {
                $pdo->prepare("UPDATE usuario SET nombre = :n, email = :e, rol = :r WHERE id = :id")
                    ->execute([':n' => $valores['nombre'], ':e' => $valores['email'], ':r' => $valores['rol'], ':id' => $id]);
                if ($esYo) {
                    $_SESSION['usuario']['nombre'] = $valores['nombre'];
                    $_SESSION['usuario']['email']  = $valores['email'];
                }
                flash('Usuario actualizado correctamente.');
            } else {
                $clave = generar_clave_temporal();
                $pdo->prepare(
                    "INSERT INTO usuario (nombre, email, password_hash, rol, debe_cambiar_clave)
                     VALUES (:n, :e, :p, :r, 1)"
                )->execute([
                    ':n' => $valores['nombre'],
                    ':e' => $valores['email'],
                    ':p' => password_hash($clave, PASSWORD_DEFAULT),
                    ':r' => $valores['rol'],
                ]);
                mostrar_clave_temporal($valores['nombre'], $valores['email'], $clave);
                flash('Usuario creado correctamente.');
            }
            redirigir('usuarios.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores['email'] = 'Ya existe un usuario con ese correo.';
            } else {
                error_log('usuario_form.php: ' . $e->getMessage());
                $errores['general'] = 'No se pudo guardar el usuario. Inténtalo de nuevo.';
            }
        }
    }
}

$tituloPagina = $editar ? 'Editar usuario' : 'Nuevo usuario';
require 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <h1 class="h3 mb-4"><?= $editar ? 'Editar usuario' : 'Nuevo usuario' ?></h1>

        <?php if (isset($errores['general'])): ?>
            <div class="alert alert-danger"><?= e($errores['general']) ?></div>
        <?php endif; ?>

        <form method="post" class="card shadow-sm" novalidate>
            <div class="card-body">
                <?= csrf_campo() ?>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre y apellido</label>
                    <input type="text" id="nombre" name="nombre" maxlength="100"
                           class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                           value="<?= e($valores['nombre']) ?>" autofocus>
                    <?php if (isset($errores['nombre'])): ?><div class="invalid-feedback"><?= e($errores['nombre']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo</label>
                    <input type="email" id="email" name="email" maxlength="150"
                           class="form-control <?= isset($errores['email']) ? 'is-invalid' : '' ?>"
                           value="<?= e($valores['email']) ?>">
                    <?php if (isset($errores['email'])): ?><div class="invalid-feedback"><?= e($errores['email']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="rol" class="form-label">Rol</label>
                    <select id="rol" name="rol" class="form-select <?= isset($errores['rol']) ? 'is-invalid' : '' ?>" <?= $esYo ? 'disabled' : '' ?>>
                        <option value="medico" <?= $valores['rol'] === 'medico' ? 'selected' : '' ?>>Médico (solo Documentos)</option>
                        <option value="administrativo" <?= $valores['rol'] === 'administrativo' ? 'selected' : '' ?>>Administrativo (acceso completo)</option>
                    </select>
                    <?php if (isset($errores['rol'])): ?><div class="invalid-feedback"><?= e($errores['rol']) ?></div><?php endif; ?>
                    <?php if ($esYo): ?><div class="form-text">No puedes cambiar tu propio rol.</div><?php endif; ?>
                </div>

                <?php if (!$editar): ?>
                    <div class="alert alert-info small mb-3">
                        Al crear el usuario se genera una contraseña temporal que verás una sola vez.
                        La persona tendrá que cambiarla en su primer ingreso.
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-end gap-2">
                    <a href="usuarios.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><?= $editar ? 'Guardar cambios' : 'Crear usuario' ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
