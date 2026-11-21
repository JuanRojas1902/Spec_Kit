<?php
declare(strict_types=1);
require_once __DIR__ . '/../cliente_api.php';

// Cargamos los catálogos para las llaves foráneas
$tiposProducto = function_exists('obtener_tipos_producto_para_select') ? obtener_tipos_producto_para_select() : [];
$proyectos = function_exists('obtener_proyectos_para_select') ? obtener_proyectos_para_select() : [];

$mensajeError = null;
$mensajeExito = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'tipo_id' => (int)($_POST['tipo_id'] ?? 0),         // Llave foránea 1
        'proyecto_id' => (int)($_POST['proyecto_id'] ?? 0) // Llave foránea 2
    ];

    $res = crear_producto($payload); 
    if (isset($res['error'])) {
        $mensajeError = $res['error'];
    } else {
        $mensajeExito = "¡Producto de resultado registrado con éxito!";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Producto de Resultado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require_once __DIR__ . '/navegacion.php'; ?>

<div class="container my-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Registrar Producto de Resultado</h5>
        </div>
        <div class="card-body">
            <?php if ($mensajeError): ?><div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div><?php endif; ?>
            <?php if ($mensajeExito): ?><div class="alert alert-success"><?= htmlspecialchars($mensajeExito) ?></div><?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Título del Producto</label>
                    <input type="text" name="titulo" class="form-control" required placeholder="Ej. Artículo Indexado en Scopus">
                </div>

                <!-- LLAVE FORÁNEA 1: TIPO DE PRODUCTO -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Tipo de Producto (Llave Foránea)</label>
                    <select name="tipo_id" class="form-select" required>
                        <option value="">-- Seleccione el Tipo --</option>
                        <?php foreach ($tiposProducto as $tp): ?>
                            <option value="<?= $tp['id'] ?>"><?= htmlspecialchars($tp['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- LLAVE FORÁNEA 2: PROYECTO ASOCIADO -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Proyecto Asociado (Llave Foránea)</label>
                    <select name="proyecto_id" class="form-select" required>
                        <option value="">-- Seleccione el Proyecto --</option>
                        <?php foreach ($proyectos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success px-4">Guardar Producto</button>
                <a href="index.php?vista=inicio" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>