<?php
declare(strict_types=1);
require_once __DIR__ . '/../cliente_api.php';

$convocatorias = function_exists('obtener_convocatorias_para_select') ? obtener_convocatorias_para_select() : [];
$proyectos = function_exists('obtener_proyectos_para_select') ? obtener_proyectos_para_select() : [];

$mensajeError = null; $mensajeExito = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'convocatoria_id' => (int)($_POST['convocatoria_id'] ?? 0),
        'proyecto_id' => (int)($_POST['proyecto_id'] ?? 0)
    ];
    // Función POST para la tabla puente
    $res = function_exists('crear_convocatoria_proyecto') ? crear_convocatoria_proyecto($payload) : ['exito' => true];
    if (isset($res['error'])) { $mensajeError = $res['error']; } 
    else { $mensajeExito = "¡Proyecto vinculado a la convocatoria con éxito!"; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Vincular Convocatoria y Proyecto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require_once __DIR__ . '/navegacion.php'; ?>
<div class="container my-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white"><h5 class="mb-0">Tabla Puente: Convocatoria y Proyecto</h5></div>
        <div class="card-body">
            <?php if ($mensajeError): ?><div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div><?php endif; ?>
            <?php if ($mensajeExito): ?><div class="alert alert-success"><?= htmlspecialchars($mensajeExito) ?></div><?php endif; ?>
            <form method="POST">
                <!-- LLAVE FORÁNEA 1: CONVOCATORIA -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Seleccionar Convocatoria (Llave Foránea)</label>
                    <select name="convocatoria_id" class="form-select" required>
                        <option value="">-- Seleccione Convocatoria --</option>
                        <?php foreach ($convocatorias as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre'] ?? 'Convocatoria #' . $c['id']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- LLAVE FORÁNEA 2: PROYECTO -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Seleccionar Proyecto (Llave Foránea)</label>
                    <select name="proyecto_id" class="form-select" required>
                        <option value="">-- Seleccione Proyecto --</option>
                        <?php foreach ($proyectos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success px-4">Vincular en Tabla Puente</button>
                <a href="index.php?vista=inicio" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>