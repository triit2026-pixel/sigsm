<?php
require 'conexion.php';
require 'includes/auth.php';
requerir_rol('administrativo');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('usuarios.php');
}
csrf_verificar();

$id     = (int)($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? '';

if (!in_array($accion, ['desactivar', 'reactivar'], true)) {
    redirigir('usuarios.php');
}

// Nadie puede desactivarse a sí mismo: así siempre queda al menos un administrativo activo
if ($id === (int)usuario_actual()['id']) {
    flash('No puedes desactivar tu propio usuario.', 'warning');
    redirigir('usuarios.php');
}

$stmt = $pdo->prepare("SELECT id FROM usuario WHERE id = :id");
$stmt->execute([':id' => $id]);
if (!$stmt->fetch()) {
    flash('El usuario no existe.', 'danger');
    redirigir('usuarios.php');
}

try {
    $pdo->prepare("UPDATE usuario SET activo = :a WHERE id = :id")
        ->execute([':a' => $accion === 'reactivar' ? 1 : 0, ':id' => $id]);
    flash($accion === 'reactivar' ? 'Usuario reactivado correctamente.' : 'Usuario desactivado correctamente.');
} catch (PDOException $e) {
    error_log('usuario_estado.php: ' . $e->getMessage());
    flash('No se pudo cambiar el estado del usuario.', 'danger');
}
redirigir('usuarios.php');
