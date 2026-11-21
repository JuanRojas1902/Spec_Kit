<?php
declare(strict_types=1);

require_once __DIR__ . '/../cliente_api.php';

// Obtenemos los datos a través del cliente HTTP de la API
$areas = obtener_areas_conocimiento();
$errorApi = $areas['error_api'] ?? null;

// Capturamos el contenido visual para renderizarlo dentro de la plantilla base
ob_start();
?>

<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Áreas de Conocimiento</h2>

        <!-- CRITERIO P7: Si la API está apagada, la pantalla sigue en pie con su aviso y sin datos -->
        <?php if ($errorApi): ?>
            <div class="alert alert-warning" role="alert">
                <strong>Aviso:</strong> <?php echo htmlspecialchars($errorApi); ?>
            </div>
        <?php else: ?>
            <div class="mb-3">
                <a href="/areas-de-conocimiento/nuevo" class="btn btn-primary">Registrar Nueva Área</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (empty($areas)): ?>
                        <!-- Si no hay filas, se muestra un recuadro indicándolo (vacío no es error) -->
                        <p class="text-muted mb-0">Todavía no hay áreas de conocimiento registradas.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Gran Área</th>
                                        <th>Área</th>
                                        <th>Disciplina</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($areas as $area): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($area['id']); ?></code></td>
                                            <td><?php echo htmlspecialchars($area['gran_area']); ?></td>
                                            <td><?php echo htmlspecialchars($area['area']); ?></td>
                                            <td><?php echo htmlspecialchars($area['disciplina']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$contenido = ob_get_clean();
require_once __DIR__ . '/plantilla.php';