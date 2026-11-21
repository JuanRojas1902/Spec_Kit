<?php
declare(strict_types=1);
require_once __DIR__ . '/../cliente_api.php';

// Cargamos los datos para llenar las ventanas desplegables de llaves foráneas
$grupos = function_exists('obtener_grupos_para_select') ? obtener_grupos_para_select() : [];
$docentes = function_exists('obtener_docentes_para_select') ? obtener_docentes_para_select() : [];

$mensajeError = null; 
$mensajeExito = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'grupo_id' => (int)($_POST['grupo_id'] ?? 0),
        'docente_coordinador_id' => (int)($_POST['docente_coordinador_id'] ?? 0)
    ];
    $res = function_exists('crear_semillero') ? crear_semillero($payload) : ['error' => 'Función crear_semillero no definida en cliente_api.php'];
    if (isset($res['error'])) { 
        $mensajeError = $res['error']; 
    } else { 
        $mensajeExito = "¡Semillero registrado con éxito!"; 
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo de Semilleros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require_once __DIR__ . '/navegacion.php'; ?>

<div class="container my-4" style="max-width: 800px;">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Registrar Nuevo Semillero</h5>
        </div>
        <div class="card-body">
            <?php if ($mensajeError): ?><div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div><?php endif; ?>
            <?php if ($mensajeExito): ?><div class="alert alert-success"><?= htmlspecialchars($mensajeExito) ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nombre del Semillero</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej. Semillero de IA Avanzada">
                </div>

                <!-- LLAVE FORÁNEA 1: VENTANA DESPLEGABLE DE GRUPO -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Grupo de Investigación (Llave Foránea)</label>
                    <select name="grupo_id" class="form-select" required>
                        <option value="">-- Seleccione un Grupo --</option>
                        <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['id'] ?>">
                                <?= htmlspecialchars($g['nombre'] ?? $g['gruplac'] ?? 'Grupo #' . $g['id']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- LLAVE FORÁNEA 2: VENTANA DESPLEGABLE DE DOCENTE -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Docente Coordinador (Llave Foránea)</label>
                    <select name="docente_coordinador_id" class="form-select" required>
                        <option value="">-- Seleccione un Docente --</option>
                        <?php foreach ($docentes as $d): ?>
                            <option value="<?= $d['id'] ?>">
                                <?= htmlspecialchars(($d['nombres'] ?? '') . ' ' . ($d['apellidos'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success px-4">Guardar Semillero</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>