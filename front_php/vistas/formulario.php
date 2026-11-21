<?php
declare(strict_types=1);

require_once __DIR__ . '/../cliente_api.php';

$errores = [];
$exito = false;

// Estado inicial del formulario (vacío)
$datosFormulario = [
    'id' => '',
    'gran_area' => '',
    'area' => '',
    'disciplina' => ''
];

// Si el usuario presionó el botón de guardar (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CRITERIO P6: Guardamos lo que el usuario escribió para volver a rellenar los inputs si hay error
    $datosFormulario['id']         = trim($_POST['id'] ?? '');
    $datosFormulario['gran_area']  = trim($_POST['gran_area'] ?? '');
    $datosFormulario['area']       = trim($_POST['area'] ?? '');
    $datosFormulario['disciplina'] = trim($_POST['disciplina'] ?? '');

    // Llamamos a la API a través de nuestra función pura
    $respuesta = crear_area_conocimiento($datosFormulario);

    if (isset($respuesta['error_api'])) {
        $errores[] = $respuesta['error_api'];
    } elseif (isset($respuesta['errores'])) {
        $errores = $respuesta['errores']; // Errores de validación (422)
    } else {
        // Si todo salió bien, redirigimos a la lista
        header('Location: /areas-de-conocimiento');
        exit;
    }
}

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="mb-4">Registrar Área de Conocimiento</h2>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="/areas-de-conocimiento/nuevo" method="POST">
                    
                    <div class="mb-3">
                        <label for="id" class="form-label">Código (ID)</label>
                        <!-- value="..." garantiza que lo escrito no se pierda al fallar (Criterio P6) -->
                        <input type="text" class="form-control" id="id" name="id" value="<?php echo htmlspecialchars($datosFormulario['id']); ?>" maxlength="6" required>
                        <div class="form-text">Máximo 6 caracteres alfanuméricos (Ej: 1A01).</div>
                    </div>

                    <div class="mb-3">
                        <label for="gran_area" class="form-label">Gran Área</label>
                        <input type="text" class="form-control" id="gran_area" name="gran_area" value="<?php echo htmlspecialchars($datosFormulario['gran_area']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="area" class="form-label">Área</label>
                        <input type="text" class="form-control" id="area" name="area" value="<?php echo htmlspecialchars($datosFormulario['area']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="disciplina" class="form-label">Disciplina</label>
                        <input type="text" class="form-control" id="disciplina" name="disciplina" value="<?php echo htmlspecialchars($datosFormulario['disciplina']); ?>" required>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="/areas-de-conocimiento" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                        <!-- CRITERIO P5: Botones que hablan el idioma del usuario, sin jerga de bases de datos -->
                        <button type="submit" name="accion" value="parcial" class="btn btn-info text-white">Guardar solo lo que cambié</button>
                        <button type="submit" name="accion" value="completo" class="btn btn-primary">Guardar la ficha completa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean();
require_once __DIR__ . '/plantilla.php';