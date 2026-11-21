<?php
/**
 * Archivo: puentes_formulario.php
 * Capa: Front-End / Vista
 * 
 * Qué es: Formulario con dos listas desplegables para asignar una nueva pareja en una tabla puente.
 * Por qué está hier: Permite elegir los elementos correspondientes mediante desplegables con nombres,
 * sin digitar códigos a ciegas.
 * Qué NO hace: No valida reglas relacionales complejas (de eso se encarga la API y la base de datos).
 */

declare(strict_types=1);

$tituloModulo = ucwords(str_replace('_', ' ', $recursoPuente));
?>
<div class="mb-3">
    <a href="/<?= $recursoPuente ?>" class="btn btn-outline-secondary btn-sm">&larr; Volver al Listado</a>
</div>

<div class="card shadow-sm border mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title mb-0">Asignar en: <?= htmlspecialchars($tituloModulo, ENT_QUOTES, 'UTF-8') ?></h3>
    </div>
    <div class="card-body">
        <?php if (!empty($erroresValidacion)): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Por favor corrija los siguientes motivos:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($erroresValidacion as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/<?= $recursoPuente ?>/nueva" method="POST">
            <div class="mb-3">
                <label for="campo1" class="form-label"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $nombreCampo1)), ENT_QUOTES, 'UTF-8') ?> *</label>
                <select class="form-select" id="campo1" name="<?= $nombreCampo1 ?>" required>
                    <option value="">Seleccione una opción...</option>
                    <?php foreach ($opciones1 as $opt1): ?>
                        <option value="<?= $opt1[$idCampo1] ?>">
                            <?= htmlspecialchars($opt1[$textoCampo1] ?? $opt1[$idCampo1], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="campo2" class="form-label"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $nombreCampo2)), ENT_QUOTES, 'UTF-8') ?> *</label>
                <select class="form-select" id="campo2" name="<?= $nombreCampo2 ?>" required>
                    <option value="">Seleccione una opción...</option>
                    <?php foreach ($opciones2 as $opt2): ?>
                        <option value="<?= $opt2[$idCampo2] ?>">
                            <?= htmlspecialchars($opt2[$textoCampo2] ?? $opt2[$idCampo2], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="/<?= $recursoPuente ?>" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Asignar Pareja</button>
            </div>
        </form>
    </div>
</div>