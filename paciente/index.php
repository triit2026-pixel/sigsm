<?php
$tituloPagina = 'Bienvenido';
$esInicio = true;
require __DIR__ . '/encabezado.php';
?>
<div class="text-center mb-4">
    <h1 class="h3 mb-1">Bienvenido</h1>
    <p class="text-muted mb-0">Elige qué quieres consultar</p>
</div>

<div class="row g-3 justify-content-center">
    <div class="col-12 col-md-5">
        <a href="documentos.php" class="boton-portal fondo-documentos">
            <span class="titulo">Documentación</span>
            <span class="detalle">Guías, indicaciones y material informativo</span>
        </a>
    </div>
    <div class="col-12 col-md-5">
        <a href="encuestas.php" class="boton-portal fondo-encuestas">
            <span class="titulo">Encuestas</span>
            <span class="detalle">Cuéntanos tu experiencia en el hospital</span>
        </a>
    </div>
</div>
<?php require __DIR__ . '/pie.php'; ?>