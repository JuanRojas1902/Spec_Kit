<?php
declare(strict_types=1);

/**
 * ARCHIVO: front_php/vistas/docentes.php
 * QUÉ HACE: Vista del módulo Docentes con diseño responsive (tarjetas y tablas adaptativas).
 * POR QUÉ ESTÁ AQUÍ: Muestra la lista de docentes y permite su registro/retiro vía HTTP.
 */

require_once __DIR__ . '/../cliente_api.php';

$mensajeExito = null;
$mensajeError = null;

// Procesamiento de formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'crear') {
        $resultado = crear_docente([
            'cedula' => (int)$_POST['cedula'],
            'nombres' => trim($_POST['nombres']),
            'apellidos' => trim($_POST['apellidos'])
        ]);
        if (isset($resultado['exito'])) {
            $mensajeExito = "Docente registrado exitosamente.";
        } else {
            $mensajeError = $resultado['error'] ?? "No se pudo registrar el docente.";
        }
    } elseif ($_POST['accion'] === 'eliminar') {
        $cedula = (int)$_POST['cedula'];
        if (eliminar_docente($cedula)) {
            $mensajeExito = "Docente retirado del sistema.";
        } else {
            $mensajeError = "No se pudo retirar el docente.";
        }
    }
}

$docentes = obtener_docentes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Etiqueta Viewport esencial para respuesta responsive en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Docentes - Sistema de Investigación</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . '/navegacion.php'; ?>

<!-- Hero Banner Responsivo -->
<div class="container my-3">
    <div class="p-4 p-md-5 hero-banner shadow-sm text-center">
        <h1 class="display-5 fw-bold">Módulo de Docentes</h1>
        <p class="fs-5">Administración centralizada de profesores investigadores de la institución.</p>
    </div>
</div>

<div class="container my-4">

    <!-- Mensajes de Estado (Alertas) -->
    <?php if ($mensajeExito): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensajeExito) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($mensajeError): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensajeError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Formulario de Registro -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-verde-institucional text-white">
                    <h5 class="card-title mb-0 fs-6">Registrar Nuevo Docente</h5>
                </div>
                <div class="card-body">
                    <form action="index.php?vista=docentes" method="POST">
                        <input type="hidden" name="accion" value="crear">
                        
                        <div class="mb-3">
                            <label for="cedula" class="form-label font-monospace fs-7">Cédula de Ciudadanía</label>
                            <input type="number" class="form-control" id="cedula" name="cedula" required placeholder="Ej. 1017245">
                        </div>

                        <div class="mb-3">
                            <label for="nombres" class="form-label fs-7">Nombres</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required placeholder="Ej. Ana María">
                        </div>

                        <div class="mb-3">
                            <label for="apellidos" class="form-label fs-7">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required placeholder="Ej. Torres Gómez">
                        </div>

                        <button type="submit" class="btn btn-destacado w-100">Guardar Docente</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de Datos Responsive -->
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0 fs-6">Listado de Docentes Activos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($docentes['error_api'])): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-danger py-3">
                                            <?= htmlspecialchars($docentes['error_api']) ?>
                                        </td>
                                    </tr>
                                <?php elseif (empty($docentes)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            Todavía no hay docentes registrados.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($docentes as $d): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars((string)$d['cedula']) ?></td>
                                            <td><?= htmlspecialchars($d['nombres']) ?></td>
                                            <td><?= htmlspecialchars($d['apellidos']) ?></td>
                                            <td class="text-center">
                                                <form action="index.php?vista=docentes" method="POST" class="d-inline" onsubmit="return confirm('¿Desea retirar a este docente?');">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="cedula" value="<?= $d['cedula'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Retirar</button>
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
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>