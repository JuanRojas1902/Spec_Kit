<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: ods_linea_formulario.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Formulario para relacionar un Objetivo de Desarrollo
 * Sostenible (ODS) con una linea de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite crear una pareja en la tabla puente
 * ods_linea mediante dos desplegables de Bootstrap.
 *
 * CLAVE FORANEA 1:
 * ods_linea.ods_id
 *       -> objetivo_desarrollo_sostenible.id
 *
 * CLAVE FORANEA 2:
 * ods_linea.linea_id
 *       -> linea_investigacion.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 * PRIMARY KEY (ods_id, linea_id)
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No se conecta directamente con MariaDB.
 * - No realiza peticiones HTTP.
 * - No crea objetivos ni lineas.
 * - No modifica relaciones existentes.
 *
 * VARIABLES RECIBIDAS DESDE index.php:
 *
 * $opciones1    : catalogo de ODS.
 * $opciones2    : catalogo de lineas.
 * $valores      : seleccion anterior.
 * $csrf         : token de seguridad.
 * $puedeGuardar : habilitacion del formulario.
 *
 * RUTA DEL FORMULARIO:
 * POST /ods_linea/nueva
 *
 * DESTINO DE LA API:
 * POST /api/ods_linea
 * =========================================================
 */


// =========================================================
// 1. PREPARAR EL CATALOGO DE OBJETIVOS ODS
//
// PRIMERA CLAVE FORANEA:
//
// ods_linea.ods_id
//       ->
// objetivo_desarrollo_sostenible.id
//
// Se muestra el nombre, pero se envia el ID.
// =========================================================

$odsDisponibles = [];

$catalogoODS = $opciones1 ?? [];

if (is_array($catalogoODS)) {

    foreach ($catalogoODS as $objetivo) {

        if (!is_array($objetivo)) {
            continue;
        }

        $idODS = filter_var(
            $objetivo['id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if (!is_int($idODS)) {
            continue;
        }

        $nombreODS = trim(
            (string) ($objetivo['nombre'] ?? '')
        );

        if ($nombreODS === '') {
            continue;
        }

        $odsDisponibles[(string) $idODS] = $nombreODS;
    }
}


// =========================================================
// 2. PREPARAR EL CATALOGO DE LINEAS
//
// SEGUNDA CLAVE FORANEA:
//
// ods_linea.linea_id
//       ->
// linea_investigacion.id
// =========================================================

$lineasDisponibles = [];

$catalogoLineas = $opciones2 ?? [];

if (is_array($catalogoLineas)) {

    foreach ($catalogoLineas as $linea) {

        if (!is_array($linea)) {
            continue;
        }

        $idLinea = filter_var(
            $linea['id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if (!is_int($idLinea)) {
            continue;
        }

        $nombreLinea = trim(
            (string) ($linea['nombre'] ?? '')
        );

        if ($nombreLinea === '') {
            continue;
        }

        $lineasDisponibles[(string) $idLinea] =
            $nombreLinea;
    }
}


// =========================================================
// 3. RECUPERAR LOS VALORES DEL FORMULARIO
//
// Si ocurre un error 409 o 422, index.php conserva
// los identificadores seleccionados.
// =========================================================

$datosFormulario = [];

if (isset($valores) && is_array($valores)) {

    $datosFormulario = $valores;
}


// =========================================================
// 4. IDENTIFICAR EL ODS SELECCIONADO
// =========================================================

$odsSeleccionado = '';

if (
    isset($datosFormulario['ods_id']) &&
    is_scalar($datosFormulario['ods_id'])
) {

    $odsSeleccionado =
        (string) $datosFormulario['ods_id'];
}


// =========================================================
// 5. IDENTIFICAR LA LINEA SELECCIONADA
// =========================================================

$lineaSeleccionada = '';

if (
    isset($datosFormulario['linea_id']) &&
    is_scalar($datosFormulario['linea_id'])
) {

    $lineaSeleccionada =
        (string) $datosFormulario['linea_id'];
}


// =========================================================
// 6. COMPROBAR QUE LAS SELECCIONES EXISTEN
// =========================================================

$odsEncontrado =
    $odsSeleccionado !== '' &&
    array_key_exists(
        $odsSeleccionado,
        $odsDisponibles
    );

$lineaEncontrada =
    $lineaSeleccionada !== '' &&
    array_key_exists(
        $lineaSeleccionada,
        $lineasDisponibles
    );


// =========================================================
// 7. PREPARAR EL TOKEN DE SEGURIDAD
// =========================================================

$tokenFormulario = isset($csrf) && is_string($csrf)
    ? $csrf
    : '';


// =========================================================
// 8. VERIFICAR SI SE PUEDE GUARDAR
//
// Deben existir objetivos y lineas disponibles.
// =========================================================

$formularioHabilitado =
    ($puedeGuardar ?? true) === true &&
    $odsDisponibles !== [] &&
    $lineasDisponibles !== [] &&
    $tokenFormulario !== '';

?>


<!-- =====================================================
     9. ENCABEZADO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3">

        <div>

            <h2 class="h4 fw-bold mb-2">

                Nueva asignación de ODS a línea

            </h2>

            <p class="text-body-secondary mb-0">

                Seleccione un Objetivo de Desarrollo
                Sostenible y una línea de investigación
                para registrar su relación.

            </p>

        </div>

        <a
            href="/ods_linea"
            class="btn btn-outline-secondary"
        >

            Volver al listado

        </a>

    </div>

</section>


<!-- =====================================================
     10. INFORMACION DE LAS CLAVES FORANEAS
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Claves foráneas:</strong>

        <ul class="mb-0 mt-2">

            <li>

                <code>ods_id</code>

                referencia a

                <code>objetivo_desarrollo_sostenible.id</code>.

            </li>

            <li>

                <code>linea_id</code>

                referencia a

                <code>linea_investigacion.id</code>.

            </li>

        </ul>

        <p class="mb-0 mt-2">

            Ambas claves son obligatorias
            y forman una pareja única.

        </p>

    </div>

</section>


<!-- =====================================================
     11. COMPROBAR DISPONIBILIDAD DE LOS CATALOGOS
     ===================================================== -->

<?php if ($odsDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay Objetivos de Desarrollo Sostenible
        disponibles o no fue posible consultar su
        catálogo desde la API.

        <a
            href="/objetivo_desarrollo_sostenible"
            class="alert-link"
        >

            Consultar objetivos ODS

        </a>

    </div>

<?php endif; ?>


<?php if ($lineasDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay líneas de investigación disponibles
        o no fue posible consultar su catálogo.

        <a
            href="/linea_investigacion"
            class="alert-link"
        >

            Consultar líneas de investigación

        </a>

    </div>

<?php endif; ?>


<!-- =====================================================
     12. FORMULARIO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-3">

            <h3 class="h5 fw-bold mb-0">

                Información de la asignación

            </h3>

        </div>


        <div class="card-body p-4">

            <form
                method="POST"
                action="/ods_linea/nueva"
            >

                <!-- Token CSRF -->

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= plantilla_escapar(
                        $tokenFormulario
                    ) ?>"
                >


                <div class="row g-4">


                    <!-- =================================
                         13. PRIMERA CLAVE FORANEA
                         ods_id
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="ods_id"
                            class="form-label fw-semibold"
                        >

                            Objetivo de Desarrollo Sostenible

                            <span class="text-danger">*</span>

                        </label>


                        <select
                            id="ods_id"
                            name="ods_id"
                            class="form-select"
                            required
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                        >

                            <option value="">

                                -- Seleccione un ODS --

                            </option>


                            <?php if (
                                $odsSeleccionado !== '' &&
                                !$odsEncontrado
                            ): ?>

                                <option
                                    value=""
                                    selected
                                    disabled
                                >

                                    ODS anterior no disponible

                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $odsDisponibles
                                as $idODS => $nombreODS
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar(
                                        $idODS
                                    ) ?>"
                                    <?= (string) $idODS ===
                                        $odsSeleccionado
                                            ? 'selected'
                                            : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $nombreODS
                                    ) ?>

                                    — ID:

                                    <?= plantilla_escapar(
                                        $idODS
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div class="form-text">

                            Primera clave foránea:

                            <code>ods_id</code>.

                            Se enviará el identificador
                            del objetivo seleccionado,
                            no su nombre.

                        </div>

                    </div>


                    <!-- =================================
                         14. SEGUNDA CLAVE FORANEA
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
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                        >

                            <option value="">

                                -- Seleccione una línea --

                            </option>


                            <?php if (
                                $lineaSeleccionada !== '' &&
                                !$lineaEncontrada
                            ): ?>

                                <option
                                    value=""
                                    selected
                                    disabled
                                >

                                    Línea anterior no disponible

                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $lineasDisponibles
                                as $idLinea => $nombreLinea
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar(
                                        $idLinea
                                    ) ?>"
                                    <?= (string) $idLinea ===
                                        $lineaSeleccionada
                                            ? 'selected'
                                            : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $nombreLinea
                                    ) ?>

                                    — ID:

                                    <?= plantilla_escapar(
                                        $idLinea
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div class="form-text">

                            Segunda clave foránea:

                            <code>linea_id</code>.

                            Se enviará el identificador
                            de la línea de investigación.

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     15. INFORMACION SOBRE EL GUARDADO
                     ===================================== -->

                <div class="alert alert-light border mt-4">

                    <h4 class="h6 fw-bold">

                        ¿Qué sucede al guardar?

                    </h4>

                    <p class="small mb-0">

                        Se registrará una relación
                        en la tabla puente

                        <code>ods_linea</code>.

                        No se modificará la información
                        del Objetivo de Desarrollo
                        Sostenible ni de la línea
                        de investigación.

                    </p>

                </div>


                <!-- =====================================
                     16. BOTONES
                     ===================================== -->

                <div class="d-flex flex-wrap gap-2
                            border-top mt-4 pt-4">

                    <button
                        type="submit"
                        class="btn btn-primary"
                        <?= $formularioHabilitado
                            ? ''
                            : 'disabled' ?>
                    >

                        Asignar ODS a línea

                    </button>


                    <a
                        href="/ods_linea"
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
     17. NOTA SOBRE INTEGRIDAD REFERENCIAL
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-warning" role="note">

        <strong>Integridad referencial:</strong>

        no se permite registrar dos veces
        la misma pareja ODS–línea.

        Si la pareja ya existe, la API responderá
        con <strong>HTTP 409</strong>.

        Para cambiar una relación, primero
        debe retirar la pareja anterior
        y después registrar una nueva.

    </div>

</section>