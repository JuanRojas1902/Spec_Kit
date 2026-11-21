<?php
declare(strict_types=1);

// Le decimos al navegador que esta página realmente es un 404
http_response_code(404);

ob_start();
?>

<div class="row justify-content-center text-center mt-5">
    <div class="col-md-6">
        <h1 class="display-1 text-muted fw-bold">404</h1>
        <h2 class="mb-4">Página no encontrada</h2>
        <p class="lead mb-5">La ruta que intentas buscar no existe en este módulo.</p>
        <a href="/areas-de-conocimiento" class="btn btn-primary btn-lg">Volver al inicio</a>
    </div>
</div>

<?php
$contenido = ob_get_clean();
require_once __DIR__ . '/plantilla.php';