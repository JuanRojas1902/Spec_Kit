<?php
declare(strict_types=1);

require_once __DIR__ . '/../cliente_api.php';

$mensajeError = '';
$mensajeExito = '';

// 1. Procesamiento de envíos por formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Crear Grupo
    if (isset($_POST['accion']) && $_POST['accion'] === 'crear') {
        $datos = [
            'codigo' => (int)$_POST['codigo'],
            'nombre' => trim($_POST['nombre']),
            'sigla' => trim($_POST['sigla']),
            'universidad_id' => 1
        ];

        $resultado = crear_grupo($datos);
        if (isset($resultado['error'])) {
            $mensajeError = $resultado['error'];
        } else {
            $mensajeExito = 'Grupo de investigación registrado correctamente.';
        }
    }

    // Actualizar Grupo (PUT)
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
        $codigo = (int)$_POST['codigo'];
        $datos = [
            'nombre' => trim($_POST['nombre']),
            'sigla' => trim($_POST['sigla'])
        ];

        $resultado = actualizar_grupo($codigo, $datos);
        if (isset($resultado['error'])) {
            $mensajeError = $resultado['error'];
        } else {
            $mensajeExito = 'Grupo de investigación actualizado con éxito.';
        }
    }

    // Eliminar Grupo (DELETE)
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        $codigo = (int)$_POST['codigo'];
        if (eliminar_grupo($codigo)) {
            $mensajeExito = 'Grupo de investigación eliminado correctamente.';
        } else {
            $mensajeError = 'No se pudo eliminar el grupo de investigación.';
        }
    }
}

// 2. Consultar listado desde la API
$grupos = obtener_grupos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Grupos de Investigación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require_once __DIR__ . '/navegacion.php'; ?>

<div class="container my-4">
    <h1 class="mb-4">Módulo de Grupos de Investigación</h1>

    <!-- Notificaciones -->
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

    <!-- Formulario de Registro -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">Registrar Nuevo Grupo</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="accion" value="crear">
                <div class="col-md-3">
                    <label class="form-label">Código del Grupo</label>
                    <input type="number" name="codigo" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nombre del Grupo</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sigla</label>
                    <input type="text" name="sigla" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">Guardar Grupo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Grupos -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="card-title mb-0">Listado de Grupos Registrados</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Sigla</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grupos) || isset($grupos['error_api'])): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay grupos registrados o no fue posible conectar con la API.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grupos as $g): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($g['codigo'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($g['nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($g['sigla'] ?? '') ?></td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-warning btn-sm me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditarGrupo"
                                            onclick="cargarGrupoEditar(<?= (int)$g['codigo'] ?>, '<?= htmlspecialchars($g['nombre'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($g['sigla'] ?? '', ENT_QUOTES) ?>')">
                                        Editar
                                    </button>

                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="codigo" value="<?= htmlspecialchars((string)($g['codigo'] ?? '')) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Confirma eliminar este grupo?')">Eliminar</button>
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

<!-- Modal para Edición (PUT) -->
<div class="modal fade" id="modalEditarGrupo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Editar Grupo de Investigación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="codigo" id="edit_grupo_codigo">

                    <div class="mb-3">
                        <label class="form-label">Código (No modificable)</label>
                        <input type="text" id="edit_grupo_codigo_ver" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Grupo</label>
                        <input type="text" name="nombre" id="edit_grupo_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sigla</label>
                        <input type="text" name="sigla" id="edit_grupo_sigla" class="form-control" required>
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
function cargarGrupoEditar(codigo, nombre, sigla) {
    document.getElementById('edit_grupo_codigo').value = codigo;
    document.getElementById('edit_grupo_codigo_ver').value = codigo;
    document.getElementById('edit_grupo_nombre').value = nombre;
    document.getElementById('edit_grupo_sigla').value = sigla;
}
</script>

</body>
</html>