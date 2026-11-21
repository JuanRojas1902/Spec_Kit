<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: areas_conocimiento_linea_formulario.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Formulario para relacionar areas de conocimiento
 * con lineas de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite crear una pareja en la tabla puente ac_linea
 * utilizando dos desplegables.
 *
 * CLAVE FORANEA 1:
 * ac_linea.area_conocimiento_id
 *          -> area_conocimiento.id
 *
 * CLAVE FORANEA 2:
 * ac_linea.linea_id
 *          -> linea_investigacion.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 * PRIMARY KEY (area_conocimiento_id, linea_id)
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No se conecta directamente con MariaDB.
 * - No realiza peticiones HTTP.
 * - No crea areas ni lineas de investigacion.
 * - No modifica relaciones por si mismo.
 *
 * DATOS RECIBIDOS DE index.php:
 * $opciones1: areas de conocimiento.
 * $opciones2: lineas de investigacion.
 * $valores: seleccion anterior.
 * $csrf: token de seguridad.
 * $puedeGuardar: permiso para guardar.
 *
 * RUTA DEL FORMULARIO:
 * POST /ac_linea/nueva
 *
 * DESTINO EN LA API:
 * POST /api/ac_linea
 * =========================================================
 */


// =========================================================
// 1. PREPARAR LAS AREAS DE CONOCIMIENTO
//
// PRIMERA CLAVE FORANEA:
//
// ac_linea.area_conocimiento_id
//             ->
// area_conocimiento.id
//
// Mostraremos la disciplina y el area asociada,
// pero enviaremos el identificador.
// =========================================================

$areasDisponibles = [];

$catalogoAreas = $opciones1 ?? [];

if (is_array($catalogoAreas)) {

    foreach ($catalogoAreas as $area) {

        if (!is_array($area)) {
            continue;
        }

        $idArea = filter_var(
            $area['id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if (!is_int($idArea)) {
            continue;
        }

        $disciplina = trim(
            (string) ($area['disciplina'] ?? '')
        );

        $nombreArea = trim(
            (string) ($area['area'] ?? '')
        );

        if ($disciplina === '') {
            $disciplina = 'Área de conocimiento';
        }

        $etiqueta = $disciplina;

        if (
            $nombreArea !== '' &&
            $nombreArea !== $disciplina
        ) {

            $etiqueta .= ' — ' . $nombreArea;
        }

        $areasDisponibles[(string) $idArea] =
            $etiqueta;
    }
}


// =========================================================
// 2. PREPARAR LAS LINEAS DE INVESTIGACION
//
// SEGUNDA CLAVE FORANEA:
//
// ac_linea.linea_id
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
// 3. RECUPERAR VALORES ANTERIORES
//
// Si ocurre un error 409 o 422, el enrutador
// prepara $valores para conservar lo seleccionado.
// =========================================================

$datosFormulario = [];

if (isset($valores) && is_array($valores)) {

    $datosFormulario = $valores;
}


// =========================================================
// 4. IDENTIFICAR EL AREA SELECCIONADA
// =========================================================

$areaSeleccionada = '';

if (
    isset($datosFormulario['area_conocimiento_id']) &&
    is_scalar($datosFormulario['area_conocimiento_id'])
) {

    $areaSeleccionada =
        (string) $datosFormulario['area_conocimiento_id'];
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
// 6. COMPROBAR LAS SELECCIONES
// =========================================================

$areaEncontrada =
    $areaSeleccionada !== '' &&
    array_key_exists(
        $areaSeleccionada,
        $areasDisponibles
    );

$lineaEncontrada =
    $lineaSeleccionada !== '' &&
    array_key_exists(
        $lineaSeleccionada,
        $lineasDisponibles
    );


// =========================================================
// 7. PREPARAR TOKEN CSRF
// =========================================================

$tokenFormulario = isset($csrf) && is_string($csrf)
    ? $csrf
    : '';


// =========================================================
// 8. VERIFICAR SI SE PUEDE GUARDAR
//
// Deben existir areas y lineas disponibles.
// =========================================================

$formularioHabilitado =
    ($puedeGuardar ?? true) === true &&
    $areasDisponibles !== [] &&
    $lineasDisponibles !== [] &&
    $tokenFormulario !== '';

?>


<!-- =====================================================
     9. ENCABEZADO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div class="d-flex flex-wrap
                justify-content-between
                align-items-center gap-3">

        <div>

            <h2 class="h4 fw-bold mb-2">

                Nueva asignación de área a línea

            </h2>

            <p class="text-body-secondary mb-0">

                Seleccione un área de conocimiento
                y una línea de investigación
                para registrar su relación.

            </p>

        </div>

        <a
            href="/ac_linea"
            class="btn btn-outline-secondary"
        >

            Volver al listado

        </a>

    </div>

</section>


<!-- =====================================================
     10. INFORMACION SOBRE CLAVES FORANEAS
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Claves foráneas:</strong>

        <ul class="mb-0 mt-2">

            <li>

                <code>area_conocimiento_id</code>

                referencia a

                <code>area_conocimiento.id</code>.

            </li>

            <li>

                <code>linea_id</code>

                referencia a

                <code>linea_investigacion.id</code>.

            </li>

        </ul>

        <p class="mb-0 mt-2">

            La pareja formada por ambas claves
            debe ser única.

        </p>

    </div>

</section>


<!-- =====================================================
     11. COMPROBAR LOS CATALOGOS
     ===================================================== -->

<?php if ($areasDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay áreas de conocimiento disponibles
        o no fue posible consultar su catálogo.

        <a
            href="/area_conocimiento"
            class="alert-link"
        >

            Consultar áreas de conocimiento

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
                action="/ac_linea/nueva"
            >

                <!-- Token de seguridad -->

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
                         area_conocimiento_id
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="area_conocimiento_id"
                            class="form-label fw-semibold"
                        >

                            Área de conocimiento

                            <span class="text-danger">*</span>

                        </label>


                        <select
                            id="area_conocimiento_id"
                            name="area_conocimiento_id"
                            class="form-select"
                            required
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                        >

                            <option value="">

                                -- Seleccione un área --

                            </option>


                            <?php if (
                                $areaSeleccionada !== '' &&
                                !$areaEncontrada
                            ): ?>

                                <option
                                    value=""
                                    selected
                                    disabled
                                >

                                    Área anterior no disponible

                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $areasDisponibles
                                as $idArea => $nombreArea
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar(
                                        $idArea
                                    ) ?>"
                                    <?= (string) $idArea ===
                                        $areaSeleccionada
                                            ? 'selected'
                                            : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $nombreArea
                                    ) ?>

                                    — ID:

                                    <?= plantilla_escapar(
                                        $idArea
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div class="form-text">

                            Clave foránea:
                            <code>area_conocimiento_id</code>.

                            Se enviará el ID del área,
                            no su disciplina.

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

                            Clave foránea:
                            <code>linea_id</code>.

                            Se enviará el identificador
                            de la línea seleccionada.

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     15. INFORMACION DE LA OPERACION
                     ===================================== -->

                <div class="alert alert-light border mt-4">

                    <h4 class="h6 fw-bold">

                        ¿Qué sucede al guardar?

                    </h4>

                    <p class="small mb-0">

                        Se registrará una nueva pareja
                        en la tabla

                        <code>ac_linea</code>.

                        El área de conocimiento y la línea
                        conservarán sus datos originales.

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

                        Asignar área a línea

                    </button>


                    <a
                        href="/ac_linea"
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
     17. NOTA DE INTEGRIDAD REFERENCIAL
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-warning" role="note">

        <strong>Integridad referencial:</strong>

        una misma pareja área–línea no puede
        registrarse dos veces.

        Si la pareja ya existe, la API responderá
        con <strong>HTTP 409</strong>.

        Para cambiar una relación, retire primero
        la pareja anterior y después registre otra.

    </div>

</section>