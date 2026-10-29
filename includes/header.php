<?php
require_once __DIR__ . '/auth.php';
$tituloPagina = $tituloPagina ?? 'S.I.G.S.M.';
$usuario = usuario_actual();
$flash = flash_tomar();
$paginaActual = basename($_SERVER['SCRIPT_NAME']);
$esAdmin = es_administrativo();
// Las pantallas de alta, edición y QR forman parte de "Documentos"
$enDocumentos = in_array($paginaActual, ['documentos.php', 'alta_documento.php', 'editar_documento.php', 'qr_documento.php'], true);
$enEncuestas = in_array($paginaActual, ['encuestas.php', 'encuesta_form.php', 'encuesta_resultados.php', 'qr_encuesta.php'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina) ?> — S.I.G.S.M.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<?php if ($usuario): ?>
    <style>
        .btn-menu {
            position: fixed;
            top: 12px;
            left: 12px;
            z-index: 1030;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 10px;
            background: var(--bs-primary);
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .2);
        }
        .btn-menu:hover { background: #0b5ed7; }
        .btn-menu svg { width: 22px; height: 22px; }
        .menu-lista .nav-link {
            color: var(--bs-body-color);
            padding: .65rem .85rem;
            border-radius: .5rem;
        }
        .menu-lista .nav-link:hover { background: var(--bs-light); }
        .menu-lista .nav-link.active {
            background: var(--bs-primary-bg-subtle);
            color: var(--bs-primary-text-emphasis);
            font-weight: 600;
        }
        .contenido-principal { padding-top: 4.25rem; }
        @media print {
            .btn-menu, .offcanvas, .barra-medico { display: none !important; }
            .contenido-principal { padding-top: 0; }
        }
    </style>
<?php endif; ?>
</head>
<body class="bg-light">
<?php if ($usuario && $esAdmin): ?>
<button class="btn-menu" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
        aria-controls="menuLateral" aria-label="Abrir menú">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
        <path d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

<div class="offcanvas offcanvas-start" tabindex="-1" id="menuLateral" aria-labelledby="menuLateralTitulo" style="--bs-offcanvas-width: 290px;">
    <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h5" id="menuLateralTitulo">S.I.G.S.M.</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div class="mb-3">
            <div class="fw-semibold text-break"><?= e($usuario['nombre']) ?></div>
            <div class="small text-muted text-break mb-1"><?= e($usuario['email']) ?></div>
            <span class="badge bg-primary"><?= e(rol_etiqueta($usuario['rol'])) ?></span>
        </div>

        <nav class="nav flex-column menu-lista gap-1" aria-label="Secciones">
            <a class="nav-link <?= $enDocumentos ? 'active' : '' ?>" href="documentos.php">Documentos</a>
            <a class="nav-link <?= $paginaActual === 'categorias.php' ? 'active' : '' ?>" href="categorias.php">Categorías</a>
            <a class="nav-link <?= $enEncuestas ? 'active' : '' ?>" href="encuestas.php">Encuestas</a>
            <a class="nav-link <?= $paginaActual === 'actividad.php' ? 'active' : '' ?>" href="actividad.php">Actividad reciente</a>
        </nav>

        <form action="logout.php" method="post" class="mt-auto pt-3 border-top">
            <?= csrf_campo() ?>
            <button type="submit" class="btn btn-outline-secondary w-100">Cerrar sesión</button>
        </form>
    </div>
</div>
<?php elseif ($usuario): ?>
<?php // El médico solo usa Documentos: no tiene menú, solo su nombre, su rol y la salida ?>
<div class="container barra-medico pt-3 d-flex justify-content-end align-items-center gap-2 flex-wrap">
    <span class="small text-muted"><?= e($usuario['nombre']) ?></span>
    <span class="badge bg-success"><?= e(rol_etiqueta($usuario['rol'])) ?></span>
    <form action="logout.php" method="post" class="d-inline">
        <?= csrf_campo() ?>
        <button type="submit" class="btn btn-sm btn-outline-secondary">Salir</button>
    </form>
</div>
<?php endif; ?>
<div class="container pb-4 <?= $usuario && $esAdmin ? 'contenido-principal' : ($usuario ? 'pt-3' : '') ?>">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></div>
<?php endif; ?>
