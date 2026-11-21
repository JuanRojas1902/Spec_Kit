<?php

declare(strict_types=1);

/**
 * ARCHIVO: areas_aplicacion_linea_lista.php
 * CAPA: Frontend / Vista — Version 2
 *
 * QUE ES:
 * Listado de relaciones de la tabla puente aa_linea.
 *
 * POR QUE ESTA AQUI:
 * Muestra las areas de aplicacion relacionadas con lineas
 * de investigacion y permite retirar una pareja existente.
 *
 * CLAVES FORANEAS Y CLAVE PRIMARIA COMPUESTA:
 * aa_linea.area_aplicacion_id -> area_aplicacion.id
 * aa_linea.linea_id           -> linea_investigacion.id
 * PRIMARY KEY (area_aplicacion_id, linea_id)
 *
 * QUE NO HACE:
 * No ejecuta SQL, no accede a MariaDB, no llama a la API
 * y no elimina las entidades relacionadas.
 *
 * VARIABLES DEL ENRUTADOR:
 * $registros:  parejas de aa_linea.
 * $opciones1: areas de aplicacion.
 * $opciones2: lineas de investigacion.
 * $csrf:      token de seguridad.
 */

// 1. Preparar las parejas recibidas de index.php.
$listaRelaciones = [];
foreach (($registros ?? []) as $fila) {
    if (is_array($fila)) {
        $listaRelaciones[] = $fila;
    }
}

// 2. Resolver la primera FK: area_aplicacion_id.
$nombresAreas = [];
foreach (($opciones1 ?? []) as $area) {
    if (!is_array($area)) {
        continue;
    }
    $id = filter_var(
        $area['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    if (is_int($id)) {
        $nombresAreas[(string) $id] = (string) ($area['nombre'] ?? '');
    }
}

// 3. Resolver la segunda FK: linea_id.
$nombresLineas = [];
foreach (($opciones2 ?? []) as $linea) {
    if (!is_array($linea)) {
        continue;
    }
    $id = filter_var(
        $linea['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    if (is_int($id)) {
        $nombresLineas[(string) $id] = (string) ($linea['nombre'] ?? '');
    }
}

$totalRelaciones = count($listaRelaciones);
$tokenFormulario = isset($csrf) && is_string($csrf) ? $csrf : '';
$hayErrores = isset($erroresValidacion)
    && is_array($erroresValidacion)
    && $erroresValidacion !== [];
?>

<section class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="h4 fw-bold mb-2">Áreas de aplicación por línea</h2>
            <p class="text-body-secondary mb-0">
                Consulte las áreas de aplicación asociadas a las líneas de investigación.
            </p>
        </div>
        <a class="btn btn-primary" href="/aa_linea/nueva">+ Nueva asignación</a>
    </div>
</section>

<section class="mb-4">
    <div class="alert alert-info" role="note">
        <strong>Claves foráneas:</strong>
        <code>area_aplicacion_id</code> referencia a
        <code>area_aplicacion.id</code>, y
        <code>linea_id</code> referencia a
        <code>linea_investigacion.id</code>.
        Las dos claves forman una pareja única.
    </div>
</section>

<section class="mb-4">
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-body-secondary mb-2">Relaciones registradas</p>
                    <p class="display-6 fw-bold text-primary mb-0">
                        <?= $totalRelaciones ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h3 class="h5 fw-semibold mb-0">Listado de áreas y líneas</h3>
            <span class="badge text-bg-primary">
                <?= $totalRelaciones ?> registros
            </span>
        </div>
        <div class="card-body p-0">
            <?php if ($listaRelaciones === [] && $hayErrores): ?>
                <div class="text-center p-5">
                    <h4 class="h5">No fue posible cargar el listado</h4>
                    <p class="text-body-secondary">Revise el mensaje de error mostrado arriba.</p>
                    <a class="btn btn-outline-primary" href="/aa_linea">Intentar nuevamente</a>
                </div>
            <?php elseif ($listaRelaciones === []): ?>
                <div class="text-center p-5">
                    <h4 class="h5">No hay relaciones registradas</h4>
                    <p class="text-body-secondary">
                        Todavía no existen asociaciones entre áreas de aplicación y líneas.
                    </p>
                    <a class="btn btn-primary" href="/aa_linea/nueva">Crear primera asignación</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4 py-3">Área ID (FK)</th>
                                <th scope="col" class="py-3">Área de aplicación</th>
                                <th scope="col" class="py-3">Línea ID (FK)</th>
                                <th scope="col" class="py-3">Línea de investigación</th>
                                <th scope="col" class="py-3 text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listaRelaciones as $relacion): ?>
                                <?php
                                // FK 1: aa_linea.area_aplicacion_id -> area_aplicacion.id.
                                $areaId = filter_var(
                                    $relacion['area_aplicacion_id'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    ['options' => ['min_range' => 1]]
                                );
                                // FK 2: aa_linea.linea_id -> linea_investigacion.id.
                                $lineaId = filter_var(
                                    $relacion['linea_id'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    ['options' => ['min_range' => 1]]
                                );
                                $clavesValidas = is_int($areaId) && is_int($lineaId);
                                $nombreArea = is_int($areaId)
                                    ? ($nombresAreas[(string) $areaId] ?? 'Área no disponible')
                                    : 'Área no disponible';
                                $nombreLinea = is_int($lineaId)
                                    ? ($nombresLineas[(string) $lineaId] ?? 'Línea no disponible')
                                    : 'Línea no disponible';
                                ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-semibold">
                                        <?= is_int($areaId) ? $areaId : 'No disponible' ?>
                                    </td>
                                    <td class="py-3"><?= plantilla_escapar($nombreArea) ?></td>
                                    <td class="py-3">
                                        <?= is_int($lineaId) ? $lineaId : 'No disponible' ?>
                                    </td>
                                    <td class="py-3"><?= plantilla_escapar($nombreLinea) ?></td>
                                    <td class="py-3 text-end pe-4">
                                        <?php if ($clavesValidas && $tokenFormulario !== ''): ?>
                                            <form
                                                method="POST"
                                                action="/aa_linea/retirar"
                                                class="m-0"
                                                onsubmit="return confirm('¿Retirar esta relación? El área y la línea permanecerán registradas.');"
                                            >
                                                <input type="hidden" name="csrf"
                                                       value="<?= plantilla_escapar($tokenFormulario) ?>">
                                                <input type="hidden" name="area_aplicacion_id"
                                                       value="<?= $areaId ?>">
                                                <input type="hidden" name="linea_id"
                                                       value="<?= $lineaId ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    Retirar relación
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="small text-body-secondary">Acción no disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($listaRelaciones !== []): ?>
            <div class="card-footer bg-white p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="small text-body-secondary">
                    Mostrando <?= $totalRelaciones ?> relación(es).
                </span>
                <a class="btn btn-outline-secondary btn-sm" href="/aa_linea">Actualizar listado</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="mt-4">
    <div class="alert alert-warning" role="note">
        <strong>Importante:</strong> retirar una relación elimina únicamente la pareja
        de <code>aa_linea</code>. El área y la línea permanecen registradas.
        Esta tabla no tiene columna <code>activo</code> y no admite PUT ni PATCH.
    </div>
</section>
