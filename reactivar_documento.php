<?php
require 'conexion.php';
require 'includes/auth.php';
requerir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('documentos.php');
}
csrf_verificar();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $pdo->prepare("UPDATE documento SET activo = 1 WHERE id = :id")->execute([':id' => $id]);
    flash('Documento reactivado correctamente.');
}
redirigir('documentos.php?estado=baja');
