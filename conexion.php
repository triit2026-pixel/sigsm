<?php
// conexion.php — conexión a la base sigsm con PDO
$config = require __DIR__ . '/config.php';
$c = $config['db'];

$dsn = "mysql:host={$c['host']};dbname={$c['nombre']};charset={$c['charset']}";
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $c['usuario'], $c['clave'], $opciones);
} catch (PDOException $e) {
    error_log('Error de conexión a la BD: ' . $e->getMessage());
    http_response_code(500);
    exit('El servicio no está disponible en este momento.');
}
