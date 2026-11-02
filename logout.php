<?php
require 'conexion.php';
require 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    actividad_cerrar($pdo);   // guarda la hora de salida
    $_SESSION = [];
    session_destroy();
}
redirigir('login.php');
