<?php
declare(strict_types=1);

$configuracionesCatalogos = require __DIR__ . '/catalogos_config_v3.php';
$configCatalogo = $configuracionesCatalogos[$ruta ?? ''] ?? null;
if (!is_array($configCatalogo)) {
    http_response_code(404);
    echo '<div class="alert alert-danger">Catálogo no configurado.</div>';
    return;
}

// Defensa adicional en la vista: no se muestra un formulario a CONSULTA.
if (!function_exists('frontEsAdminV3') || !frontEsAdminV3()) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Solo ADMIN puede modificar este catálogo.</div>';
    return;
}

$editarCatalogo = ($modo ?? 'crear') === 'editar';
$valoresCatalogo = is_array($valores ?? null) ? $valores : [];
$claveCatalogo = $configCatalogo['clave'];
$baseCatalogo = '/' . rawurlencode((string) $ruta);
$idCatalogo = $valoresCatalogo[$claveCatalogo] ?? ($segundo ?? '');
$idCatalogo = is_scalar($idCatalogo) ? (string) $idCatalogo : '';
$accionCatalogo = $editarCatalogo
    ? $baseCatalogo . '/' . rawurlencode($idCatalogo) . '/editar'
    : $baseCatalogo . '/nuevo';
$tokenCatalogo = (string) ($csrf ?? ($_SESSION['csrf'] ?? ''));
$guardarCatalogo = ($puedeGuardar ?? true) !== false;

$escCatalogo = static function (mixed $valor): string {
    return htmlspecialchars(is_scalar($valor) ? (string) $valor : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>
<div class="d-flex justify-content-between align-items-center gap-2 mb-3">
    <h2 class="h5 mb-0"><?= $editarCatalogo ? 'Editar' : 'Crear' ?>: <?= $escCatalogo($titulo ?? 'Catálogo') ?></h2>
    <a class="btn btn-outline-secondary btn-sm" href="<?= $escCatalogo($baseCatalogo) ?>">Volver al listado</a>
</div>

<?php if (!empty($erroresValidacion)): ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($erroresValidacion as $errorCatalogo): ?>
            <div><?= $escCatalogo($errorCatalogo) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($editarCatalogo && !$guardarCatalogo): ?>
    <div class="alert alert-warning">No se pudo cargar el registro. No es posible guardar cambios.</div>
<?php endif; ?>

<form method="post" action="<?= $escCatalogo($accionCatalogo) ?>">
    <input type="hidden" name="csrf" value="<?= $escCatalogo($tokenCatalogo) ?>">
    <?php foreach ($configCatalogo['campos'] as $nombreCampo => $defCampo): ?>
        <?php
            if ($editarCatalogo && $nombreCampo === $claveCatalogo) {
                continue; // La clave primaria no se reemplaza al editar.
            }
            $valorCampo = isset($_POST[$nombreCampo]) && is_string($_POST[$nombreCampo])
                ? $_POST[$nombreCampo]
                : ($valoresCatalogo[$nombreCampo] ?? '');
            $tipoCampo = $defCampo['tipo'] ?? 'text';
            $requeridoCampo = !empty($defCampo['obligatorio']);
            $idCampo = 'catalogo_' . $nombreCampo;
        ?>
        <div class="mb-3">
            <label class="form-label" for="<?= $escCatalogo($idCampo) ?>"><?= $escCatalogo($defCampo['etiqueta'] ?? $nombreCampo) ?></label>
            <?php if ($tipoCampo === 'textarea'): ?>
                <textarea class="form-control" id="<?= $escCatalogo($idCampo) ?>" name="<?= $escCatalogo($nombreCampo) ?>" rows="3" <?= $requeridoCampo ? 'required' : '' ?>><?= $escCatalogo($valorCampo) ?></textarea>
            <?php else: ?>
                <input class="form-control" type="<?= $tipoCampo === 'number' ? 'number' : 'text' ?>"
                       id="<?= $escCatalogo($idCampo) ?>" name="<?= $escCatalogo($nombreCampo) ?>"
                       value="<?= $escCatalogo($valorCampo) ?>"
                       <?= $tipoCampo === 'number' ? 'min="1" step="1"' : '' ?>
                       <?= $requeridoCampo ? 'required' : '' ?>>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-primary" type="submit" <?= $guardarCatalogo ? '' : 'disabled' ?>><?= $editarCatalogo ? 'Guardar cambios' : 'Crear registro' ?></button>
        <a class="btn btn-outline-secondary" href="<?= $escCatalogo($baseCatalogo) ?>">Cancelar</a>
    </div>
</form>
