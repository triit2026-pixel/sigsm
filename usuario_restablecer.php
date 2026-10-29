<?php
require 'conexion.php';
require 'includes/auth.php';
require 'includes/usuarios.php';
requerir_rol('administrativo');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('usuarios.php');
}
csrf_verificar();

$id = (int)($_POST['id'] ?? 0);

if ($id === (int)usuario_actual()['id']) {
    flash('Para cambiar tu propia contraseña usa la opción "Cambiar mi contraseña".', 'warning');
    redirigir('usuarios.php');
}

$stmt = $pdo->prepare("SELECT id, nombre, email FROM usuario WHERE id = :id");
$stmt->execute([':id' => $id]);
$u = $stmt->fetch();
if (!$u) {
    flash('El usuario no existe.', 'danger');
    redirigir('usuarios.php');
}

try {
    $clave = generar_clave_temporal();
    $pdo->prepare("UPDATE usuario SET password_hash = :p, debe_cambiar_clave = 1 WHERE id = :id")
        ->execute([':p' => password_hash($clave, PASSWORD_DEFAULT), ':id' => $id]);
    mostrar_clave_temporal($u['nombre'], $u['email'], $clave);
    flash('Contraseña restablecida correctamente.');
} catch (PDOException $e) {
    error_log('usuario_restablecer.php: ' . $e->getMessage());
    flash('No se pudo restablecer la contraseña. Inténtalo de nuevo.', 'danger');
}
redirigir('usuarios.php');
