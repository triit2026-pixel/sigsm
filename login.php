<?php
require 'conexion.php';
require 'includes/auth.php';

if (usuario_actual()) {
    redirigir('documentos.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $email = trim($_POST['email'] ?? '');
    $clave = $_POST['clave'] ?? '';

    $stmt = $pdo->prepare("SELECT id, nombre, email, password_hash, rol FROM usuario WHERE email = :e AND activo = 1");
    $stmt->execute([':e' => $email]);
    $u = $stmt->fetch();

    if (!$u || !password_verify($clave, $u['password_hash'])) {
        $error = 'Correo o contraseña incorrectos.';
    } elseif (!in_array($u['rol'], ['medico', 'administrativo'], true)) {
        $error = 'Tu usuario no tiene un rol asignado. Consulta con un administrativo.';
    } else {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'     => (int)$u['id'],
            'nombre' => $u['nombre'],
            'email'  => $u['email'],
            'rol'    => $u['rol'],
        ];
        actividad_iniciar($pdo, $_SESSION['usuario']);
        redirigir('documentos.php');
    }
}

$tituloPagina = 'Ingresar';
require 'includes/header.php';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Instrument+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    :root {
        --lg-tinta: #12303a;
        --lg-petroleo: #0f5560;
        --lg-petroleo-oscuro: #0a3d46;
        --lg-menta: #dcefe9;
        --lg-papel: #f5f8f7;
        --lg-borde: #d3dedc;
        --lg-texto-suave: #5b6f73;
        --lg-error: #b3261e;
    }

    .login-split {
        position: fixed;
        inset: 0;
        display: flex;
        font-family: 'Instrument Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        color: var(--lg-tinta);
    }

    .login-imagen {
        position: relative;
        flex: 0 0 58%;
        background: url('hp_imagen.jpg') center / cover no-repeat;
        display: flex;
        align-items: flex-end;
    }
    .login-imagen::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(180deg, rgba(10, 61, 70, 0.10) 0%, rgba(10, 61, 70, 0.35) 45%, rgba(8, 40, 47, 0.88) 100%);
    }
    .login-imagen .leyenda {
        position: relative;
        padding: 3rem 3.5rem;
        max-width: 38rem;
        color: #fff;
    }
    .login-imagen .leyenda .hospital {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 600;
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        line-height: 1.15;
        letter-spacing: -0.01em;
        margin: 0 0 .75rem;
    }
    .login-imagen .leyenda p {
        margin: 0;
        font-size: 1.02rem;
        line-height: 1.55;
        color: rgba(255, 255, 255, .86);
    }

    .login-form {
        flex: 0 0 42%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background-color: var(--lg-papel);
        overflow-y: auto;
    }
    .login-form .contenido {
        width: 100%;
        max-width: 400px;
        margin: auto;
    }

    .login-marca {
        display: flex;
        align-items: center;
        gap: .85rem;
        margin-bottom: 2rem;
    }
    .login-marca .sello {
        flex: 0 0 auto;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: var(--lg-petroleo);
        display: grid;
        place-items: center;
        box-shadow: 0 6px 16px rgba(15, 85, 96, .28);
    }
    .login-marca .sello svg { width: 26px; height: 26px; }
    .login-marca .siglas {
        font-weight: 600;
        font-size: 1.05rem;
        letter-spacing: .04em;
        color: var(--lg-petroleo);
        line-height: 1.1;
    }
    .login-marca .siglas small {
        display: block;
        font-weight: 400;
        letter-spacing: 0;
        font-size: .85rem;
        color: var(--lg-texto-suave);
        margin-top: .15rem;
    }

    .login-form h1 {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 600;
        font-size: 1.9rem;
        line-height: 1.2;
        letter-spacing: -0.01em;
        margin: 0 0 .4rem;
        text-align: left;
    }
    .login-form .bajada {
        color: var(--lg-texto-suave);
        margin: 0 0 1.75rem;
        font-size: .98rem;
    }

    .login-form .card {
        border: 1px solid var(--lg-borde);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 18px 40px -22px rgba(15, 85, 96, .35);
    }
    .login-form .card-body { padding: 1.75rem; }

    .login-form .form-label {
        font-weight: 600;
        font-size: .9rem;
        margin-bottom: .4rem;
        color: var(--lg-tinta);
    }
    .login-form .form-control {
        border: 1px solid var(--lg-borde);
        border-radius: 10px;
        padding: .7rem .9rem;
        font-size: 1rem;
        background-color: #fbfdfc;
        transition: border-color .15s, box-shadow .15s, background-color .15s;
    }
    .login-form .form-control:focus {
        background-color: #fff;
        border-color: var(--lg-petroleo);
        box-shadow: 0 0 0 .25rem rgba(15, 85, 96, .18);
    }

    .campo-clave { position: relative; }
    .campo-clave .form-control { padding-right: 3rem; }
    .campo-clave .ver-clave {
        position: absolute;
        top: 50%;
        right: .35rem;
        transform: translateY(-50%);
        width: 2.4rem;
        height: 2.4rem;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: var(--lg-texto-suave);
        display: grid;
        place-items: center;
        cursor: pointer;
    }
    .campo-clave .ver-clave:hover { color: var(--lg-petroleo); background: var(--lg-menta); }
    .campo-clave .ver-clave:focus-visible { outline: 2px solid var(--lg-petroleo); outline-offset: 1px; }
    .campo-clave .ver-clave svg { width: 20px; height: 20px; }

    .login-form .btn-primary {
        background-color: var(--lg-petroleo);
        border-color: var(--lg-petroleo);
        border-radius: 10px;
        padding: .75rem 1rem;
        font-weight: 600;
        font-size: 1rem;
        transition: background-color .15s, transform .05s;
    }
    .login-form .btn-primary:hover,
    .login-form .btn-primary:focus-visible {
        background-color: var(--lg-petroleo-oscuro);
        border-color: var(--lg-petroleo-oscuro);
    }
    .login-form .btn-primary:focus-visible { box-shadow: 0 0 0 .25rem rgba(15, 85, 96, .3); }
    .login-form .btn-primary:active { transform: translateY(1px); }
    .login-form .btn-primary[disabled] { opacity: .8; }

    .login-form .alert-danger {
        display: flex;
        align-items: flex-start;
        gap: .6rem;
        border: 1px solid #f0c7c4;
        border-left: 4px solid var(--lg-error);
        background: #fdf2f1;
        color: #7a1a15;
        border-radius: 10px;
        font-size: .93rem;
        padding: .75rem .9rem;
    }
    .login-form .alert-danger svg { flex: 0 0 auto; width: 18px; height: 18px; margin-top: .1rem; color: var(--lg-error); }

    .login-pie {
        margin-top: 1.5rem;
        text-align: center;
        font-size: .85rem;
        color: var(--lg-texto-suave);
    }

    /* Entrada única al cargar */
    @media (prefers-reduced-motion: no-preference) {
        .login-form .contenido { animation: lg-entrada .5s ease-out both; }
        @keyframes lg-entrada {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: none; }
        }
    }

    @media (max-width: 991.98px) {
        .login-imagen { flex-basis: 45%; }
        .login-form   { flex-basis: 55%; }
        .login-imagen .leyenda { padding: 2rem; }
    }
    @media (max-width: 767.98px) {
        .login-imagen { display: none; }
        .login-form   { flex: 1 1 100%; padding: 1.25rem; }
        .login-form .card-body { padding: 1.4rem; }
        .login-form h1 { font-size: 1.6rem; }
    }
</style>

<div class="login-split">
    <div class="login-imagen" role="img" aria-label="Hospital de Clínicas">
        <div class="leyenda">
        </div>
    </div>

    <div class="login-form">
        <div class="contenido">
            <h1>S.I.G.S.M. — Gestión de documentación</h1>
            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                            </svg>
                            <span><?= e($error) ?></span>
                        </div>
                    <?php endif; ?>
                    <form method="post" novalidate id="form-login">
                        <?= csrf_campo() ?>
                        <div class="mb-3">
                            <label class="form-label" for="email">Correo</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= e($email) ?>" autocomplete="username" autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="clave">Contraseña</label>
                            <div class="campo-clave">
                                <input type="password" id="clave" name="clave" class="form-control" autocomplete="current-password">
                                <button type="button" class="ver-clave" id="ver-clave" aria-label="Mostrar contraseña" aria-pressed="false">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btn-ingresar">Ingresar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var clave = document.getElementById('clave');
    var boton = document.getElementById('ver-clave');
    var form  = document.getElementById('form-login');
    var envio = document.getElementById('btn-ingresar');

    boton.addEventListener('click', function () {
        var visible = clave.type === 'text';
        clave.type = visible ? 'password' : 'text';
        boton.setAttribute('aria-pressed', visible ? 'false' : 'true');
        boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });

    form.addEventListener('submit', function () {
        envio.disabled = true;
        envio.textContent = 'Ingresando…';
    });
})();
</script>
<?php require 'includes/footer.php'; ?>