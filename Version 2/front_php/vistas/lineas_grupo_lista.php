<?php
declare(strict_types=1);

$mensaje = $_SESSION['mensaje_linea'] ?? null;
unset($_SESSION['mensaje_linea']);

$lineas = $lineas ?? [];
?>

<div class="row mb-4">
    <div class="col-12 panel-portada p-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 text-primary fw-bold">Líneas de Investigación</h1>
                <p class="text-muted mb-0">Ejes temáticos y científicos de la institución.</p>
            </div>
            <?php if (!empty($_SESSION['usuario']['roles']) && in_array('admin', $_SESSION['usuario']['roles'], true)): ?>
                <a href="/lineas-investigacion/nuevo" class="btn btn-primary">
                    + Registrar Nueva Línea
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre de la Línea</th>
                        <th>Objetivo / Descripción</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lineas)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No hay líneas de investigación registradas actualmente.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lineas as $lin): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($lin['id'] ?? $lin['id_linea'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars((string)($lin['nombre'] ?? $lin['nombre_linea'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($lin['objetivo'] ?? 'Sin descripción detallada'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <a href="/lineas-investigacion/editar?id=<?= $lin['id'] ?? $lin['id_linea'] ?? '' ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>