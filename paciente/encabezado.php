<?php
require_once __DIR__ . '/../includes/funciones.php';
$tituloPagina = $tituloPagina ?? 'Portal del paciente';
$esInicio = $esInicio ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina) ?> — S.I.G.S.M.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .boton-portal {
            display: block;
            padding: 1.75rem 1.25rem;
            border-radius: 1rem;
            text-decoration: none;
            text-align: center;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
        }
        .boton-portal:hover, .boton-portal:focus { color: #fff; filter: brightness(.92); }
        .boton-portal .titulo { display: block; font-size: 1.5rem; font-weight: 600; }
        .boton-portal .detalle { display: block; font-size: .95rem; opacity: .9; margin-top: .25rem; }
        .fondo-documentos { background: var(--bs-primary); }
        .fondo-encuestas { background: var(--bs-success); }
    </style>
</head>
<body class="bg-light">
<header class="bg-white border-bottom py-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="fw-semibold text-decoration-none text-body">S.I.G.S.M.</a>
        <?php if (!$esInicio): ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary">← Inicio</a>
        <?php endif; ?>
    </div>
</header>
<main class="container pb-5">