<?php
declare(strict_types=1);

$configuracionesCatalogos = require __DIR__ . '/catalogos_config_v3.php';
$configCatalogo = $configuracionesCatalogos[$ruta ?? ''] ?? null;
if (!is_array($configCatalogo)) {
    http_response_code(404);
    echo '<div class="alert alert-danger">Catálogo no configurado.</div>';
    return;
}

$listaCatalogo = is_array($registros ?? null) ? $registros : [];
$esAdminCatalogo = function_exists('frontEsAdminV3') && frontEsAdminV3();
$claveCatalogo = $configCatalogo['clave'];
$baseCatalogo = '/' . rawurlencode((string) $ruta);
$tokenCatalogo = (string) ($csrf ?? ($_SESSION['csrf'] ?? ''));

$escCatalogo = static function (mixed $valor): string {
    if ($valor === null) {
        return '—';
    }
    if (is_bool($valor)) {
        $valor = $valor ? 'Sí' : 'No';
    } elseif (is_array($valor)) {
        $valor = json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$columnasCatalogo = [];
foreach ($listaCatalogo as $filaCatalogo) {
    if (!is_array($filaCatalogo)) {
        continue;
    }
    foreach (array_keys($filaCatalogo) as $columnaCatalogo) {
        if (!in_array($columnaCatalogo, $columnasCatalogo, true)) {
            $columnasCatalogo[] = $columnaCatalogo;
        }
    }
}
if ($columnasCatalogo === []) {
    $columnasCatalogo = array_keys($configCatalogo['campos']);
}
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h2 class="h5 mb-1"><?= $escCatalogo($titulo ?? 'Catálogo') ?></h2>
        <span class="text-muted">Registros encontrados: <?= count($listaCatalogo) ?></span>
    </div>
    <?php if ($esAdminCatalogo): ?>
        <a class="btn btn-primary" href="<?= $escCatalogo($baseCatalogo . '/nuevo') ?>">+ Nuevo registro</a>
    <?php endif; ?>
</div>

<?php if (!empty($erroresValidacion)): ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($erroresValidacion as $errorCatalogo): ?>
            <div><?= $escCatalogo($errorCatalogo) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($listaCatalogo === []): ?>
    <div class="alert alert-info">No hay registros para mostrar. Si esperabas datos, revisa los avisos de la API.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle table-sm">
            <thead class="table-light"><tr>
                <?php foreach ($columnasCatalogo as $columnaCatalogo): ?>
                    <?php $etiquetaCatalogo = $configCatalogo['campos'][$columnaCatalogo]['etiqueta'] ?? str_replace('_', ' ', (string) $columnaCatalogo); ?>
                    <th scope="col"><?= $escCatalogo($etiquetaCatalogo) ?></th>
                <?php endforeach; ?>
                <?php if ($esAdminCatalogo): ?><th scope="col">Acciones</th><?php endif; ?>
            </tr></thead>
            <tbody>
                <?php foreach ($listaCatalogo as $filaCatalogo): ?>
                    <?php if (!is_array($filaCatalogo)) { continue; } ?>
                    <tr>
                        <?php foreach ($columnasCatalogo as $columnaCatalogo): ?>
                            <td><?= $escCatalogo($filaCatalogo[$columnaCatalogo] ?? null) ?></td>
                        <?php endforeach; ?>
                        <?php if ($esAdminCatalogo): ?>
                            <td>
                                <?php $idCatalogo = isset($filaCatalogo[$claveCatalogo]) && is_scalar($filaCatalogo[$claveCatalogo]) ? (string) $filaCatalogo[$claveCatalogo] : ''; ?>
                                <?php if ($idCatalogo !== ''): ?>
                                    <?php $rutaRegistro = $baseCatalogo . '/' . rawurlencode($idCatalogo); ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a class="btn btn-outline-primary btn-sm" href="<?= $escCatalogo($rutaRegistro . '/editar') ?>">Editar</a>
                                        <form method="post" action="<?= $escCatalogo($rutaRegistro . '/retirar') ?>" onsubmit="return confirm('¿Retirar este registro?');">
                                            <input type="hidden" name="csrf" value="<?= $escCatalogo($tokenCatalogo) ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Retirar</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Sin identificador</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
