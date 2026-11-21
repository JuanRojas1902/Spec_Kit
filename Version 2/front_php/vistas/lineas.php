<?php
declare(strict_types=1);

// Inclusión del cliente cURL para comunicación con el backend API
require_once __DIR__ . '/../cliente_api.php';

// Variables globales para la gestión de alertas de estado
$mensajeError = '';
$mensajeExito = '';

// =========================================================================
// 1. PROCESAMIENTO DE PETICIONES FORMULARIO (POST)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Acción: Crear nueva Línea de Investigación
    if (isset($_POST['accion']) && $_POST['accion'] === 'crear') {
        $datos = [
            'codigo' => (int)$_POST['codigo'],
            'nombre' => trim($_POST['nombre'])
        ];

        $resultado = crear_linea($datos);
        if (isset($resultado['error'])) {
            $mensajeError = $resultado['error'];
        } else {
            $mensajeExito = 'Línea de investigación registrada correctamente.';
        }
    }

    // Acción: Actualizar Línea de Investigación (PUT)
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
        $codigo = (int)$_POST['codigo'];
        $datos = [
            'nombre' => trim($_POST['nombre'])
        ];

        $resultado = actualizar_linea($codigo, $datos);
        if (isset($resultado['error'])) {
            $mensajeError = $resultado['error'];
        } else {
            $mensajeExito = 'Línea de investigación actualizada con éxito.';
        }
    }

    // Acción: Eliminar Línea de Investigación (DELETE)
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        $codigo = (int)$_POST['codigo'];
        if (eliminar_linea($codigo)) {
            $mensajeExito = 'Línea de investigación eliminada correctamente.';
        } else {
            $mensajeError = 'No se pudo eliminar la línea de investigación.';
        }
    }
}

// =========================================================================
// 2. OBTENCIÓN DEL LISTADO ACTUALIZADO
// =========================================================================
$lineas = obtener_lineas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Líneas de Investigación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Componente Reutilizable de Navegación -->
<?php require_once __DIR__ . '/navegacion.php'; ?>

<div class="container my-4">
    <h1 class="mb-4">Módulo de Líneas de Investigación</h1>

    <!-- Notificaciones y Banners de Alerta -->
    <?php if ($mensajeError !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensajeError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($mensajeExito !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensajeExito) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario de Registro de Nueva Línea -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Registrar Nueva Línea</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="accion" value="crear">
                <div class="col-md-4">
                    <label class="form-label">Código de la Línea</label>
                    <input type="number" name="codigo" class="form-control" placeholder="Ej. 101" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nombre de la Línea de Investigación</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej. Ingenieria de Software" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-info text-white">Guardar Línea</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Consulta y Registro de Resultados -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="card-title mb-0">Listado de Líneas Registradas</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Nombre de la Línea</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lineas) || isset($lineas['error_api'])): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No hay líneas registradas o falló la conexión con el servidor.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lineas as $l): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($l['codigo'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($l['nombre'] ?? '') ?></td>
                                <td class="text-center">
                                    <!-- Botón Editar (Invocación al Modal con paso de parámetros JavaScript) -->
                                    <button type="button" 
                                            class="btn btn-warning btn-sm me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditarLinea"
                                            onclick="cargarLineaEditar(<?= (int)$l['codigo'] ?>, '<?= htmlspecialchars($l['nombre'] ?? '', ENT_QUOTES) ?>')">
                                        Editar
                                    </button>

                                    <!-- Formulario para Solicitud de Eliminación -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="codigo" value="<?= htmlspecialchars((string)($l['codigo'] ?? '')) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Confirms eliminar esta línea de investigación?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Bootstrap para Edición de Registro (PUT) -->
<div class="modal fade" id="modalEditarLinea" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Editar Línea de Investigación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="codigo" id="edit_linea_codigo">

                    <div class="mb-3">
                        <label class="form-label">Código (No modificable)</label>
                        <input type="text" id="edit_linea_codigo_ver" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Línea</label>
                        <input type="text" name="nombre" id="edit_linea_nombre" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función JS para cargar dinámicamente los datos en el modal de edición
function cargarLineaEditar(codigo, nombre) {
    document.getElementById('edit_linea_codigo').value = codigo;
    document.getElementById('edit_linea_codigo_ver').value = codigo;
    document.getElementById('edit_linea_nombre').value = nombre;
}
</script>

</body>
</html>