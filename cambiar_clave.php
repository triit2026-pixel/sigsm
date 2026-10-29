<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/usuarios.php';
requerir_login();

$yo = usuario_actual();
$forzado = !empty($_SESSION['usuario']['debe_cambiar_clave']);
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $actual   = $_POST['clave_actual'] ?? '';
    $nueva    = $_POST['clave_nueva'] ?? '';
    $repetir  = $_POST['clave_repetir'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM usuario WHERE id = :id");
    $stmt->execute([':id' => $yo['id']]);
    $hash = (string)$stmt->fetchColumn();

    if (!password_verify($actual, $hash)) {
        $errores['clave_actual'] = 'La contraseña actual no es correcta.';
    }
    if ($msg = validar_clave_nueva($nueva, $repetir)) {
        $errores['clave_nueva'] = $msg;
    } elseif ($nueva === $actual) {
        $errores['clave_nueva'] = 'La contraseña nueva debe ser distinta de la actual.';
    }

    if (empty($errores)) {
        try {
            $pdo->prepare("UPDATE usuario SET password_hash = :p, debe_cambiar_clave = 0 WHERE id = :id")
                ->execute([':p' => password_hash($nueva, PASSWORD_DEFAULT), ':id' => $yo['id']]);
            $_SESSION['usuario']['debe_cambiar_clave'] = 0;
            session_regenerate_id(true);
            flash('Contraseña cambiada correctamente.');
            redirigir('documentos.php');
        } catch (PDOException $e) {
            error_log('cambiar_clave.php: ' . $e->getMessage());
            $errores['general'] = 'No se pudo cambiar la contraseña. Inténtalo de nuevo.';
        }
    }
}

$tituloPagina = 'Cambiar contraseña';
require 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
        <h1 class="h3 mb-3">Cambiar contraseña</h1>

        <?php if ($forzado): ?>
            <div class="alert alert-warning">
                Por seguridad debes cambiar tu contraseña temporal antes de seguir usando el sistema.
            </div>
        <?php endif; ?>
        <?php if (isset($errores['general'])): ?>
            <div class="alert alert-danger"><?= e($errores['general']) ?></div>
        <?php endif; ?>

        <form method="post" class="card shadow-sm" novalidate>
            <div class="card-body">
                <?= csrf_campo() ?>

                <div class="mb-3">
                    <label for="clave_actual" class="form-label"><?= $forzado ? 'Contraseña temporal (la que te dieron)' : 'Contraseña actual' ?></label>
                    <input type="password" id="clave_actual" name="clave_actual" autocomplete="current-password" autofocus
                           class="form-control <?= isset($errores['clave_actual']) ? 'is-invalid' : '' ?>">
                    <?php if (isset($errores['clave_actual'])): ?><div class="invalid-feedback"><?= e($errores['clave_actual']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="clave_nueva" class="form-label">Contraseña nueva</label>
                    <input type="password" id="clave_nueva" name="clave_nueva" autocomplete="new-password"
                           class="form-control <?= isset($errores['clave_nueva']) ? 'is-invalid' : '' ?>">
                    <?php if (isset($errores['clave_nueva'])): ?>
                        <div class="invalid-feedback"><?= e($errores['clave_nueva']) ?></div>
                    <?php else: ?>
                        <div class="form-text">Mínimo <?= CLAVE_MIN ?> caracteres.</div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="clave_repetir" class="form-label">Repetir contraseña nueva</label>
                    <input type="password" id="clave_repetir" name="clave_repetir" autocomplete="new-password" class="form-control">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <?php if (!$forzado): ?><a href="documentos.php" class="btn btn-outline-secondary">Cancelar</a><?php endif; ?>
                    <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
