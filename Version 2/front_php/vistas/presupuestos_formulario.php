<?php
declare(strict_types=1);
require_once __DIR__ . '/../cliente_api.php';

$proyectos = function_exists('obtener_proyectos_para_select') ? obtener_proyectos_para_select() : [];
$mensajeError = null; $mensajeExito = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'concepto' => trim($_POST['concepto'] ?? ''),
        'monto' => (float)($_POST['monto'] ?? 0),
        'proyecto_id' => (int)($_POST['proyecto_id'] ?? 0)
    ];
    $res = crear_presupuesto($payload);
    if (isset($res['error'])) { $mensajeError = $res['error']; } 
    else { $mensajeExito = "¡Presupuesto registrado con éxito!"; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Registrar Presupuesto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require_once __DIR__ . '/navegacion.php'; ?>
<div class="container my-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white"><h5 class="mb-0">Registrar Presupuesto para Proyecto</h5></div>
        <div class="card-body">
            <?php if ($mensajeError): ?><div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div><?php endif; ?>
            <?php if ($mensajeExito): ?><div class="alert alert-success"><?= htmlspecialchars($mensajeExito) ?></div><?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Concepto del Gasto</label>
                    <input type="text" name="concepto" class="form-control" required placeholder="Ej. Compra de equipos de laboratorio">
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto ($)</label>
                    <input type="number" step="0.01" name="monto" class="form-control" required placeholder="Ej. 1500000">
                </div>
                <!-- LLAVE FORÁNEA: PROYECTO -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Proyecto Asociado (Llave Foránea)</label>
                    <select name="proyecto_id" class="form-select" required>
                        <option value="">-- Seleccione un Proyecto --</option>
                        <?php foreach ($proyectos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success px-4">Guardar Presupuesto</button>
                <a href="index.php?vista=inicio" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>