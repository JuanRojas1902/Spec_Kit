<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: areas_aplicacion_linea_formulario.php
 * CAPA: Frontend / Vista — Version 2
 * =========================================================
 *
 * QUE ES:
 * Formulario Bootstrap para asociar un area de aplicacion
 * a una linea de investigacion (tabla puente aa_linea).
 *
 * POR QUE ESTA AQUI:
 * Presenta dos desplegables alimentados por la API a traves
 * del enrutador, para evitar escribir claves manualmente.
 *
 * CLAVES FORANEAS:
 * aa_linea.area_aplicacion_id -> area_aplicacion.id
 * aa_linea.linea_id           -> linea_investigacion.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 * (area_aplicacion_id, linea_id)
 *
 * NO HACE:
 * No ejecuta SQL, no se conecta a MariaDB y no realiza
 * peticiones HTTP. El enrutador procesa el POST.
 *
 * RECIBE DE index.php:
 * $opciones1, $opciones2, $valores, $csrf, $puedeGuardar.
 * =========================================================
 */


// =========================================================
// 1. PREPARAR LAS AREAS DE APLICACION
//
// FK: aa_linea.area_aplicacion_id -> area_aplicacion.id
// =========================================================

$areasDisponibles = [];

$filasAreas = $opciones1 ?? [];

foreach (is_array($filasAreas) ? $filasAreas : [] as $area) {

    if (!is_array($area)) {
        continue;
    }

    $id = filter_var(
        $area['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $nombre = trim((string) ($area['nombre'] ?? ''));

    if (!is_int($id) || $nombre === '') {
        continue;
    }

    $areasDisponibles[(string) $id] = $nombre;
}


// =========================================================
// 2. PREPARAR LAS LINEAS DE INVESTIGACION
//
// FK: aa_linea.linea_id -> linea_investigacion.id
// =========================================================

$lineasDisponibles = [];

$filasLineas = $opciones2 ?? [];

foreach (is_array($filasLineas) ? $filasLineas : [] as $linea) {

    if (!is_array($linea)) {
        continue;
    }

    $id = filter_var(
        $linea['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $nombre = trim((string) ($linea['nombre'] ?? ''));

    if (!is_int($id) || $nombre === '') {
        continue;
    }

    $lineasDisponibles[(string) $id] = $nombre;
}


// =========================================================
// 3. RECUPERAR LA SELECCION ANTERIOR
//
// Si falla una operacion, conservamos los valores
// entregados por el enrutador.
// =========================================================

$datosFormulario = isset($valores) && is_array($valores)
    ? $valores
    : [];

$idAreaSeleccionada = filter_var(
    $datosFormulario['area_aplicacion_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$idLineaSeleccionada = filter_var(
    $datosFormulario['linea_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$areaSeleccionada = is_int($idAreaSeleccionada)
    ? (string) $idAreaSeleccionada
    : '';

$lineaSeleccionada = is_int($idLineaSeleccionada)
    ? (string) $idLineaSeleccionada
    : '';


// =========================================================
// 4. PREPARAR TOKEN DE SEGURIDAD
// =========================================================

$tokenFormulario = isset($csrf) && is_string($csrf)
    ? $csrf
    : '';


// =========================================================
// 5. VERIFICAR SI SE PUEDE GUARDAR
//
// Deben existir areas y lineas disponibles.
// =========================================================

$formularioHabilitado = ($puedeGuardar ?? true) === true
    && $areasDisponibles !== []
    && $lineasDisponibles !== []
    && $tokenFormulario !== '';

?>


<!-- =====================================================
     6. ENCABEZADO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3">

        <div>

            <h2 class="h4 fw-bold mb-2">
                Nueva asignación de área a línea
            </h2>

            <p class="text-body-secondary mb-0">
                Seleccione un área de aplicación y
                una línea de investigación.
            </p>

        </div>

        <a class="btn btn-outline-secondary" href="/aa_linea">
            Volver al listado
        </a>

    </div>

</section>


<!-- =====================================================
     7. INFORMACION SOBRE LAS CLAVES FORANEAS
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Claves foráneas de aa_linea:</strong>

        <ul class="mb-0 mt-2">

            <li>
                <code>area_aplicacion_id</code>
                referencia a
                <code>area_aplicacion.id</code>.
            </li>

            <li>
                <code>linea_id</code>
                referencia a
                <code>linea_investigacion.id</code>.
            </li>

        </ul>

        <p class="mb-0 mt-2">
            Las dos claves forman una pareja única.
            No se puede registrar dos veces la misma relación.
        </p>

    </div>

</section>


<!-- =====================================================
     8. COMPROBAR LOS CATALOGOS
     ===================================================== -->

<?php if ($areasDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay áreas de aplicación disponibles
        o falló la consulta a la API.

        <a href="/area_aplicacion" class="alert-link">
            Consultar áreas de aplicación
        </a>

    </div>

<?php endif; ?>


<?php if ($lineasDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay líneas de investigación disponibles
        o falló la consulta a la API.

        <a href="/linea_investigacion" class="alert-link">
            Consultar líneas
        </a>

    </div>

<?php endif; ?>


<!-- =====================================================
     9. FORMULARIO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-3">

            <h3 class="h5 fw-bold mb-0">
                Información de la asignación
            </h3>

        </div>

        <div class="card-body p-4">

            <form method="POST" action="/aa_linea/nueva">

                <!-- Token CSRF -->

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= plantilla_escapar($tokenFormulario) ?>"
                >

                <div class="row g-4">


                    <!-- =================================
                         10. PRIMERA CLAVE FORANEA

                         area_aplicacion_id
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="area_aplicacion_id"
                            class="form-label fw-semibold"
                        >
                            Área de aplicación
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            id="area_aplicacion_id"
                            name="area_aplicacion_id"
                            class="form-select"
                            required
                            <?= $formularioHabilitado ? '' : 'disabled' ?>
                        >

                            <option value="">
                                -- Seleccione un área --
                            </option>

                            <?php foreach (
                                $areasDisponibles as $id => $nombre
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar($id) ?>"
                                    <?= $areaSeleccionada === (string) $id
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= plantilla_escapar($nombre) ?>
                                    — ID:
                                    <?= plantilla_escapar($id) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">

                            Clave foránea:
                            <code>area_aplicacion_id</code>.

                            Se enviará el identificador del área,
                            no su nombre.

                        </div>

                    </div>


                    <!-- =================================
                         11. SEGUNDA CLAVE FORANEA

                         linea_id
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="linea_id"
                            class="form-label fw-semibold"
                        >
                            Línea de investigación
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            id="linea_id"
                            name="linea_id"
                            class="form-select"
                            required
                            <?= $formularioHabilitado ? '' : 'disabled' ?>
                        >

                            <option value="">
                                -- Seleccione una línea --
                            </option>

                            <?php foreach (
                                $lineasDisponibles as $id => $nombre
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar($id) ?>"
                                    <?= $lineaSeleccionada === (string) $id
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= plantilla_escapar($nombre) ?>
                                    — ID:
                                    <?= plantilla_escapar($id) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">

                            Clave foránea:
                            <code>linea_id</code>.

                            Se enviará el identificador de la línea,
                            no su nombre.

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     12. INFORMACION DEL GUARDADO
                     ===================================== -->

                <div class="alert alert-light border mt-4">

                    <strong>¿Qué sucede al guardar?</strong>

                    <p class="small mb-0 mt-1">

                        Se registrará una nueva pareja
                        en la tabla puente

                        <code>aa_linea</code>.

                        El área de aplicación y la línea
                        de investigación conservarán sus
                        registros originales.

                    </p>

                </div>


                <!-- =====================================
                     13. BOTONES
                     ===================================== -->

                <div class="d-flex flex-wrap gap-2
                            border-top mt-4 pt-4">

                    <button
                        type="submit"
                        class="btn btn-primary"
                        <?= $formularioHabilitado ? '' : 'disabled' ?>
                    >
                        Asignar área a línea
                    </button>

                    <a
                        href="/aa_linea"
                        class="btn btn-outline-secondary"
                    >
                        Cancelar
                    </a>

                </div>

            </form>

        </div>

    </div>

</section>


<!-- =====================================================
     14. NOTA DE INTEGRIDAD REFERENCIAL
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-warning" role="note">

        <strong>Integridad referencial:</strong>

        una pareja repetida o una referencia inexistente
        puede generar <strong>HTTP 409</strong>.

        Para cambiar la relación, retire la pareja
        anterior y registre otra.

    </div>

</section>