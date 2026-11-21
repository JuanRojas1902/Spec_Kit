<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Investigación - Áreas de Conocimiento</title>
    <!-- CSS local descargado en publico/ (Criterio: sin dependencias de CDN externos) -->
    <link rel="stylesheet" href="/publico/bootstrap.min.css">
</>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/areas-de-conocimiento">Investigación</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/areas-de-conocimiento">Áreas de Conocimiento</a>
                <a class="nav-link" href="/areas-de-conocimiento/nuevo">Nueva Área</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Aquí se inyecta el contenido específico de cada vista -->
        <?php echo $contenido ?? ''; ?>
    </div>
</body>
</html>