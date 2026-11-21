<?php
declare(strict_types=1);
require_once __DIR__ . '/../cliente_api.php';

// Cargamos los datos para las llaves foráneas
$grupos = function_exists('obtener_grupos_para_select') ? obtener_grupos_para_select() : [];

$mensajeError = null;
$mensajeExito = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
        'estado' => trim($_POST['estado'] ?? ''),
        'grupo_id' => (int)($_POST['grupo_id'] ?? 0) // Llave foránea seleccionada
    ];

    $res = crear_proyecto($payload); 
    if (isset($res['error'])) {
        $mensajeError = $res['error'];
    } else {
        $mensajeExito = "¡Proyecto registrado con éxito!";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Proyecto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require_once __DIR__ . '/navegacion.php'; ?>

<div class="container my-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Registrar Nuevo Proyecto de Investigación</h5>
        </div>
        <div class="card-body">
            <?php if ($mensajeError): ?><div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div><?php endif; ?>
            <?php if ($mensajeExito): ?><div class="alert alert-success"><?= htmlspecialchars($mensajeExito) ?></div><?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Título del Proyecto</label>
                    <input type="text" name="titulo" class="form-control" required placeholder="Ej. Sistema IoT para agricultura">
                </div>

                <div class="mb-3">
                    <label class="form-label">Fecha de Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="">Seleccione estado...</option>
                        <option value="En Ejecución">En Ejecución</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>

                <!-- LLAVE FORÁNEA: GRUPO DE INVESTIGACIÓN (MENÚ DESPLEGABLE) -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Grupo de Investigación Responsable (Llave Foránea)</label>
                    <select name="grupo_id" class="form-select" required>
                        <option value="">-- Seleccione un Grupo --</option>
                        <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['id'] ?>">
                                <?= htmlspecialchars($g['nombre'] ?? $g['gruplac'] ?? 'Grupo #' . $g['id']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success px-4">Guardar Proyecto</button>
                <a href="index.php?vista=inicio" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>