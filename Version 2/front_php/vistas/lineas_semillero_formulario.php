<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: lineas_semillero_formulario.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Formulario para asignar lineas de investigacion
 * a los semilleros existentes.
 *
 * POR QUE ESTA AQUI:
 * Permite seleccionar dos registros relacionados
 * y registrar una pareja en semillero_linea.
 *
 * CLAVE FORANEA 1:
 * semillero_linea.semillero_id -> semillero.id
 *
 * CLAVE FORANEA 2:
 * semillero_linea.linea_id -> linea_investigacion.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 * PRIMARY KEY (semillero_id, linea_id)
 *
 * QUE NO HACE:
 * - No ejecuta SQL.
 * - No se conecta directamente a MariaDB.
 * - No realiza peticiones HTTP.
 * - No crea semilleros ni lineas.
 * - No modifica relaciones directamente.
 *
 * VARIABLES DEL ENRUTADOR:
 * $opciones1    : semilleros disponibles.
 * $opciones2    : lineas de investigacion.
 * $valores      : seleccion anterior.
 * $csrf         : token de seguridad.
 * $puedeGuardar : habilitacion del formulario.
 *
 * RUTA:
 * POST /semillero_linea/nueva
 *
 * API:
 * POST /api/semillero_linea
 * =========================================================
 */


// =========================================================
// 1. PREPARAR CATALOGO DE SEMILLEROS
//
// PRIMERA CLAVE FORANEA:
// semillero_linea.semillero_id -> semillero.id
// =========================================================

$semillerosDisponibles = [];

$catalogoSemilleros = $opciones1 ?? [];

if (is_array($catalogoSemilleros)) {

    foreach ($catalogoSemilleros as $semillero) {

        if (!is_array($semillero)) {
            continue;
        }

        $id = filter_var(
            $semillero['id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if (!is_int($id)) {
            continue;
        }

        $nombre = $semillero['nombre'] ?? '';

        if (!is_string($nombre)) {
            continue;
        }

        $nombre = trim($nombre);

        if ($nombre === '') {
            continue;
        }

        $semillerosDisponibles[(string) $id] = $nombre;
    }
}


// =========================================================
// 2. PREPARAR CATALOGO DE LINEAS
//
// SEGUNDA CLAVE FORANEA:
// semillero_linea.linea_id
//          -> linea_investigacion.id
// =========================================================

$lineasDisponibles = [];

$catalogoLineas = $opciones2 ?? [];

if (is_array($catalogoLineas)) {

    foreach ($catalogoLineas as $linea) {

        if (!is_array($linea)) {
            continue;
        }

        $id = filter_var(
            $linea['id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if (!is_int($id)) {
            continue;
        }

        $nombre = $linea['nombre'] ?? '';

        if (!is_string($nombre)) {
            continue;
        }

        $nombre = trim($nombre);

        if ($nombre === '') {
            continue;
        }

        $lineasDisponibles[(string) $id] = $nombre;
    }
}


// =========================================================
// 3. CONSERVAR LOS DATOS DEL FORMULARIO
//
// Si la API responde con un error, index.php
// conserva las claves dentro de $valores.
// =========================================================

$datosFormulario = [];

if (isset($valores) && is_array($valores)) {

    $datosFormulario = $valores;
}

$semilleroSeleccionado = '';

$lineaSeleccionada = '';

if (
    isset($datosFormulario['semillero_id']) &&
    is_scalar($datosFormulario['semillero_id'])
) {

    $semilleroSeleccionado =
        (string) $datosFormulario['semillero_id'];
}

if (
    isset($datosFormulario['linea_id']) &&
    is_scalar($datosFormulario['linea_id'])
) {

    $lineaSeleccionada =
        (string) $datosFormulario['linea_id'];
}


// =========================================================
// 4. VERIFICAR LAS SELECCIONES
// =========================================================

$semilleroEncontrado =
    $semilleroSeleccionado !== '' &&
    array_key_exists(
        $semilleroSeleccionado,
        $semillerosDisponibles
    );

$lineaEncontrada =
    $lineaSeleccionada !== '' &&
    array_key_exists(
        $lineaSeleccionada,
        $lineasDisponibles
    );


// =========================================================
// 5. PREPARAR TOKEN CSRF
// =========================================================

$tokenFormulario = isset($csrf) && is_string($csrf)
    ? $csrf
    : '';


// =========================================================
// 6. VERIFICAR SI SE PUEDE GUARDAR
//
// Debe existir al menos un semillero y una linea.
// =========================================================

$formularioHabilitado =
    ($puedeGuardar ?? true) === true &&
    $semillerosDisponibles !== [] &&
    $lineasDisponibles !== [] &&
    $tokenFormulario !== '';

?>


<!-- =====================================================
     7. ENCABEZADO
     ===================================================== -->

<section class="mb-4">

    <div class="d-flex flex-wrap
                justify-content-between
                align-items-center gap-3">

        <div>

            <h2 class="h4 fw-bold mb-2">
                Nueva asignación de semillero a línea
            </h2>

            <p class="text-body-secondary mb-0">
                Seleccione el semillero y la línea
                de investigación que desea relacionar.
            </p>

        </div>

        <a
            href="/semillero_linea"
            class="btn btn-outline-secondary"
        >
            Volver al listado
        </a>

    </div>

</section>


<!-- =====================================================
     8. INFORMACION SOBRE LAS FK
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Claves foráneas:</strong>

        <ul class="mb-0 mt-2">

            <li>
                <code>semillero_id</code>
                referencia a
                <code>semillero.id</code>.
            </li>

            <li>
                <code>linea_id</code>
                referencia a
                <code>linea_investigacion.id</code>.
            </li>

        </ul>

        <p class="mb-0 mt-2">
            Las dos claves forman una relación única.
        </p>

    </div>

</section>


<!-- =====================================================
     9. VALIDAR CATALOGOS DISPONIBLES
     ===================================================== -->

<?php if ($semillerosDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay semilleros disponibles o no fue posible
        consultar el catálogo desde la API.

        <a
            href="/semilleros"
            class="alert-link"
        >
            Consultar semilleros
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
            Consultar líneas
        </a>

    </div>

<?php endif; ?>


<!-- =====================================================
     10. FORMULARIO PRINCIPAL
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
                action="/semillero_linea/nueva"
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
                         11. PRIMERA CLAVE FORANEA
                         semillero_id
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="semillero_id"
                            class="form-label fw-semibold"
                        >

                            Semillero de investigación

                            <span class="text-danger">*</span>

                        </label>


                        <select
                            id="semillero_id"
                            name="semillero_id"
                            class="form-select"
                            required
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                        >

                            <option value="">
                                -- Seleccione un semillero --
                            </option>


                            <?php if (
                                $semilleroSeleccionado !== '' &&
                                !$semilleroEncontrado
                            ): ?>

                                <option
                                    value=""
                                    selected
                                    disabled
                                >
                                    Semillero anterior no disponible
                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $semillerosDisponibles
                                as $idSemillero => $nombreSemillero
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar(
                                        $idSemillero
                                    ) ?>"
                                    <?= (string) $idSemillero ===
                                        $semilleroSeleccionado
                                            ? 'selected'
                                            : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $nombreSemillero
                                    ) ?>

                                    — ID:

                                    <?= plantilla_escapar(
                                        $idSemillero
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div class="form-text">

                            Clave foránea:
                            <code>semillero_id</code>.

                            Se enviará el ID del semillero.

                        </div>

                    </div>


                    <!-- =================================
                         12. SEGUNDA CLAVE FORANEA
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

                            Se enviará el ID de la línea.

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     13. INFORMACION DE LA OPERACION
                     ===================================== -->

                <div class="alert alert-light border mt-4">

                    <h4 class="h6 fw-bold">
                        ¿Qué sucede al guardar?
                    </h4>

                    <p class="small mb-0">

                        Se registrará una nueva pareja
                        en la tabla

                        <code>semillero_linea</code>.

                        No se modificarán los registros
                        del semillero ni de la línea.

                    </p>

                </div>


                <!-- =====================================
                     14. BOTONES DEL FORMULARIO
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
                        Asignar semillero a línea
                    </button>


                    <a
                        href="/semillero_linea"
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
     15. NOTA DE INTEGRIDAD
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-warning" role="note">

        <strong>Integridad referencial:</strong>

        una misma pareja semillero–línea no puede
        registrarse dos veces.

        Si la pareja ya existe, la API responderá
        con <strong>HTTP 409</strong>.

        Para cambiar una relación, debe retirar
        la pareja anterior y registrar otra.

    </div>

</section>