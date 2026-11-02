<?php
// Sesión, autenticación 
require_once __DIR__ . '/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function usuario_actual(): ?array {
    return $_SESSION['usuario'] ?? null;
}

function requerir_login(): void {
    if (!usuario_actual()) {
        redirigir('login.php');
    }
    actividad_tick();
}

// ---------- Roles ----------

function rol_actual(): string {
    return usuario_actual()['rol'] ?? '';
}

function es_administrativo(): bool {
    return rol_actual() === 'administrativo';
}

function es_medico(): bool {
    return rol_actual() === 'medico';
}

function rol_etiqueta(string $rol): string {
    return $rol === 'administrativo' ? 'Administrativo' : ($rol === 'medico' ? 'Médico' : $rol);
}

function requerir_rol(string $rol): void {
    requerir_login();
    if (rol_actual() !== $rol) {
        flash('No tienes permiso para acceder a esa sección.', 'danger');
        redirigir('documentos.php');
    }
}


function ip_cliente(): string {
    return substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);
}

function actividad_iniciar(PDO $pdo, array $usuario): void {
    try {
        $pdo->prepare(
            "INSERT INTO actividad_sesion (usuario_id, email, rol, ip) VALUES (:u, :e, :r, :ip)"
        )->execute([
            ':u'  => $usuario['id'],
            ':e'  => $usuario['email'],
            ':r'  => $usuario['rol'],
            ':ip' => ip_cliente(),
        ]);
        $_SESSION['actividad_id']   = (int)$pdo->lastInsertId();
        $_SESSION['actividad_tick'] = time();
    } catch (PDOException $e) {
        error_log('actividad_iniciar: ' . $e->getMessage());
    }
}

function actividad_tick(): void {
    global $pdo;
    $id = (int)($_SESSION['actividad_id'] ?? 0);
    if ($id === 0 || !($pdo instanceof PDO)) {
        return;
    }
    $ahora = time();
    if ($ahora - (int)($_SESSION['actividad_tick'] ?? 0) < 60) {
        return;
    }
    try {
        $pdo->prepare("UPDATE actividad_sesion SET ultima_actividad = NOW() WHERE id = :id AND fecha_salida IS NULL")
            ->execute([':id' => $id]);
        $_SESSION['actividad_tick'] = $ahora;
    } catch (PDOException $e) {
        error_log('actividad_tick: ' . $e->getMessage());
    }
}

function actividad_cerrar(PDO $pdo): void {
    $id = (int)($_SESSION['actividad_id'] ?? 0);
    if ($id === 0) {
        return;
    }
    try {
        $pdo->prepare("UPDATE actividad_sesion SET fecha_salida = NOW(), ultima_actividad = NOW() WHERE id = :id AND fecha_salida IS NULL")
            ->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log('actividad_cerrar: ' . $e->getMessage());
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verificar(): void {
    $enviado = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $enviado)) {
        http_response_code(403);
        exit('Solicitud no válida. Vuelve atrás, recarga la página e inténtalo de nuevo.');
    }
}
