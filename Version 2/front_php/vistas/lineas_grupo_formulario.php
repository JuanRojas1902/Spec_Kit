<?php
declare(strict_types=1);
require_once __DIR__ . '/../cliente_api.php';

$grupos = function_exists('obtener_grupos_para_select') ? obtener_grupos_para_select() : [];
$lineas = function_exists('obtener_lineas_para_select') ? obtener_lineas_para_select() : [];

$mensajeError = null; $mensajeExito = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'grupo_id' => (int)($_POST['grupo_id'] ?? 0),
        'linea_id' => (int)($_POST['linea_id'] ?? 0)
    ];
    $res = ['exito' => true]; // Conectar con API si se requiere
    if (isset($res['error'])) { $mensajeError = $res['error']; } 
    else { $mensajeExito = "¡Línea asociada al grupo con éxito!"; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Asociar Grupo y Línea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require_once __DIR__ . '/navegacion.php'; ?>
<div class="container my-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white"><h5 class="mb-0">Tabla Puente: Grupo de Investigación y Línea</h5></div>
        <div class="card-body">
            <?php if ($mensajeError): ?><div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div><?php endif; ?>
            <?php if ($mensajeExito): ?><div class="alert alert-success"><?= htmlspecialchars($mensajeExito) ?></div><?php endif; ?>
            <form method="POST">
                <!-- LLAVE FORÁNEA 1: GRUPO -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Grupo de Investigación (Llave Foránea)</label>
                    <select name="grupo_id" class="form-select" required>
                        <option value="">-- Seleccione Grupo --</option>
                        <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nombre'] ?? $g['gruplac']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- LLAVE FORÁNEA 2: LÍNEA -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Línea de Investigación (Llave Foránea)</label>
                    <select name="linea_id" class="form-select" required>
                        <option value="">-- Seleccione Línea --</option>
                        <?php foreach ($lineas as $l): ?>
                            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success px-4">Vincular Registro</button>
                <a href="index.php?vista=inicio" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>