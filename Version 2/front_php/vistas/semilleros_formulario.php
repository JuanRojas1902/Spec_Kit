<?php
declare(strict_types=1);

$esEdicion = ($modo ?? 'crear') === 'editar';
$tituloFormulario = $esEdicion ? 'Editar Semillero' : 'Registrar Nuevo Semillero';
$accionFormulario = $esEdicion ? '/semilleros/actualizar' : '/semilleros/crear';

$valores = $valores ?? [];
$errores = $errores ?? [];
$grupos = $grupos ?? [];
?>

<div class="row mb-4">
    <div class="col-12 panel-portada p-4 shadow-sm">
        <h1 class="h3 mb-1 text-primary fw-bold"><?= $tituloFormulario ?></h1>
        <p class="text-muted mb-0">Complete la información requerida para el semillero.</p>
    </div>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            <?php foreach ($errores as $err): ?>
                <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form action="<?= $accionFormulario ?>" method="POST" class="needs-validation" novalidate>
            
            <?php if ($esEdicion): ?>
                <input type="hidden" name="accion" value="completo">
            <?php endif; ?>

            <div class="row g-3">
                <!-- ID del Semillero -->
                <div class="col-md-3">
                    <label for="id" class="form-label">ID del Semillero</label>
                    <input type="number" class="form-control" id="id" name="id" 
                           value="<?= htmlspecialchars((string)($valores['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" 
                           required <?= $esEdicion ? 'readonly' : '' ?>>
                    <div class="form-text">Identificador numérico único.</div>
                </div>

                <!-- Nombre -->
                <div class="col-md-9">
                    <label for="nombre" class="form-label">Nombre del Semillero</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars((string)($valores['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <!-- CLAVE FORÁNEA: SELECT DE GRUPOS DE INVESTIGACIÓN -->
                <div class="col-md-6">
                    <label for="grupo_investigacion" class="form-label">Grupo de Investigación Patrocinador</label>
                    <select class="form-select" id="grupo_investigacion" name="grupo_investigacion" required>
                        <option value="">-- Seleccione un grupo --</option>
                        <?php 
                        $grupoActual = $valores['grupo_investigacion'] ?? '';
                        foreach ($grupos as $g): 
                            $idGrupo = $g['id'] ?? '';
                            $nombreGrupo = $g['nombre'] ?? 'Grupo #' . $idGrupo;
                            $seleccionado = ((string)$grupoActual === (string)$idGrupo) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars((string)$idGrupo, ENT_QUOTES, 'UTF-8') ?>" <?= $seleccionado ?>>
                                <?= htmlspecialchars((string)$nombreGrupo, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Grupo al cual se adscribe el semillero.</div>
                </div>

                <!-- Fecha de Creación -->
                <div class="col-md-6">
                    <label for="fecha_creacion" class="form-label">Fecha de Creación</label>
                    <input type="date" class="form-control" id="fecha_creacion" name="fecha_creacion" 
                           value="<?= htmlspecialchars((string)($valores['fecha_creacion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Mision -->
                <div class="col-md-12">
                    <label for="mision" class="form-label">Misión</label>
                    <textarea class="form-control" id="mision" name="mision" rows="3"><?= htmlspecialchars((string)($valores['mision'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <!-- Vision -->
                <div class="col-md-12">
                    <label for="vision" class="form-label">Visión</label>
                    <textarea class="form-control" id="vision" name="vision" rows="3"><?= htmlspecialchars((string)($valores['vision'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="/semilleros" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Semillero</button>
            </div>
        </form>
    </div>
</div>