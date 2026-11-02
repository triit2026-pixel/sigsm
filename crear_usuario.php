<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if ($argc < 4 || !in_array($argv[3], ['medico', 'administrativo'], true)) {
    exit("Uso: php crear_usuario.php \"Nombre Apellido\" correo@hospital.uy medico|administrativo\n");
}

require __DIR__ . '/conexion.php';

$nombre = trim($argv[1]);
$email  = trim($argv[2]);
$rol    = $argv[3];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("El correo no es válido.\n");
}

echo "Contraseña (mínimo 8 caracteres): ";
$clave = trim(fgets(STDIN));
if (strlen($clave) < 8) {
    exit("La contraseña es demasiado corta.\n");
}

try {
    $stmt = $pdo->prepare("INSERT INTO usuario (nombre, email, password_hash, rol) VALUES (:n, :e, :p, :r)");
    $stmt->execute([
        ':n' => $nombre,
        ':e' => $email,
        ':p' => password_hash($clave, PASSWORD_DEFAULT),
        ':r' => $rol,
    ]);
    echo ucfirst($rol) . " creado correctamente.\n";
} catch (PDOException $e) {
    echo $e->getCode() === '23000' ? "Ya existe un usuario con ese correo.\n" : "Error: " . $e->getMessage() . "\n";
}
