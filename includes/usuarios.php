<?php
// Funciones para la gestión de usuarios y contraseñas
require_once __DIR__ . '/funciones.php';

const CLAVE_MIN = 8;
const CLAVE_MAX = 72;   // límite real del algoritmo de hash (bcrypt), en bytes

// Contraseña temporal aleatoria, sin caracteres que se confunden al leerlos (0/O, 1/l/I)
function generar_clave_temporal(int $largo = 10): string {
    $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $max = strlen($alfabeto) - 1;
    $clave = '';
    for ($i = 0; $i < $largo; $i++) {
        $clave .= $alfabeto[random_int(0, $max)];
    }
    return $clave;
}

// Devuelve un mensaje de error, o null si la contraseña nueva es válida
function validar_clave_nueva(string $nueva, string $repetir): ?string {
    if (strlen($nueva) < CLAVE_MIN) {
        return 'La contraseña nueva debe tener al menos ' . CLAVE_MIN . ' caracteres.';
    }
    if (strlen($nueva) > CLAVE_MAX) {
        return 'La contraseña nueva no puede superar los ' . CLAVE_MAX . ' caracteres.';
    }
    if ($nueva !== $repetir) {
        return 'Las contraseñas nuevas no coinciden.';
    }
    return null;
}

function leer_campos_usuario(): array {
    return [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'email'  => trim($_POST['email'] ?? ''),
        'rol'    => $_POST['rol'] ?? '',
    ];
}

// $idActual: en edición, el id del usuario que se está editando (para que su propio correo no cuente como repetido)
function validar_usuario(PDO $pdo, array $v, int $idActual = 0): array {
    $err = [];

    if ($v['nombre'] === '' || mb_strlen($v['nombre']) > 100) {
        $err['nombre'] = 'El nombre es obligatorio y no puede superar los 100 caracteres.';
    }

    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($v['email']) > 150) {
        $err['email'] = 'Ingresa un correo válido (máximo 150 caracteres).';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE email = :e AND id <> :id");
        $stmt->execute([':e' => $v['email'], ':id' => $idActual]);
        if ((int)$stmt->fetchColumn() > 0) {
            $err['email'] = 'Ya existe un usuario con ese correo.';
        }
    }

    if (!in_array($v['rol'], ['medico', 'administrativo'], true)) {
        $err['rol'] = 'Selecciona un rol válido.';
    }

    return $err;
}

// Deja la contraseña temporal en la sesión para mostrarla UNA sola vez en usuarios.php
function mostrar_clave_temporal(string $nombre, string $email, string $clave): void {
    $_SESSION['clave_temporal'] = ['nombre' => $nombre, 'email' => $email, 'clave' => $clave];
}
