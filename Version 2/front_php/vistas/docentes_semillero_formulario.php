<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: docentes_semillero_formulario.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Formulario para relacionar docentes con semilleros.
 *
 * POR QUE ESTA AQUI:
 * Permite registrar una pareja en la tabla puente
 * participa_semillero, mediante dos desplegables.
 *
 * CLAVE FORANEA 1:
 * participa_semillero.docente_cedula
 *                   -> docente.cedula
 *
 * CLAVE FORANEA 2:
 * participa_semillero.semillero_id
 *                   -> semillero.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 * PRIMARY KEY (docente_cedula, semillero_id)
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No se conecta directamente a MariaDB.
 * - No realiza peticiones HTTP.
 * - No crea nuevos docentes ni semilleros.
 * - No edita relaciones existentes.
 *
 * DATOS PREPARADOS POR index.php:
 * $opciones1 : docentes.
 * $opciones2 : semilleros.
 * $valores   : datos previamente escritos.
 * $csrf      : token de seguridad.
 * $puedeGuardar : habilitacion del formulario.
 *
 * OPERACION:
 * POST /participa_semillero/nueva
 *
 * La API recibira:
 * POST /api/participa_semillero
 * =========================================================
 */


// =========================================================
// 1. EXTRAER FILAS DE LAS RESPUESTAS
//
// El cliente API puede entregar una lista directa
// o una respuesta con "datos" y "respuesta".
// =========================================================

$extraerFilas = static function (mixed $origen): array {

    for ($nivel = 0; $nivel < 5; $nivel++) {

        if (!is_array($origen)) {
            return [];
        }

        if (array_is_list($origen)) {

            return array_values(
                array_filter($origen, 'is_array')
            );
        }

        if (
            isset($origen['respuesta']) &&
            is_array($origen['respuesta'])
        ) {

            $origen = $origen['respuesta'];
            continue;
        }

        if (
            isset($origen['datos']) &&
            is_array($origen['datos'])
        ) {

            $origen = $origen['datos'];
            continue;
        }

        return [];
    }

    return [];
};


// =========================================================
// 2. CARGAR DOCENTES DISPONIBLES
//
// CLAVE FORANEA:
// participa_semillero.docente_cedula
//                 -> docente.cedula
// =========================================================

$listaDocentes = $extraerFilas(
    $opciones1 ?? []
);

$docentesDisponibles = [];

foreach ($listaDocentes as $docente) {

    $cedula = filter_var(
        $docente['cedula'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    if ($cedula === false) {
        continue;
    }

    $nombres = trim(
        (string) ($docente['nombres'] ?? '')
    );

    $apellidos = trim(
        (string) ($docente['apellidos'] ?? '')
    );

    $nombreCompleto = trim(
        $nombres . ' ' . $apellidos
    );

    if ($nombreCompleto === '') {
        $nombreCompleto = 'Docente';
    }

    // El indice es la clave primaria del docente.
    $docentesDisponibles[(string) $cedula] =
        $nombreCompleto;
}


// =========================================================
// 3. CARGAR SEMILLEROS DISPONIBLES
//
// CLAVE FORANEA:
// participa_semillero.semillero_id
//                 -> semillero.id
// =========================================================

$listaSemilleros = $extraerFilas(
    $opciones2 ?? []
);

$semillerosDisponibles = [];

foreach ($listaSemilleros as $semillero) {

    $idSemillero = filter_var(
        $semillero['id'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    if ($idSemillero === false) {
        continue;
    }

    $nombreSemillero = trim(
        (string) ($semillero['nombre'] ?? '')
    );

    if ($nombreSemillero === '') {
        $nombreSemillero = 'Semillero';
    }

    // El indice es la clave primaria del semillero.
    $semillerosDisponibles[
        (string) $idSemillero
    ] = $nombreSemillero;
}


// =========================================================
// 4. RECUPERAR DATOS PREVIAMENTE ESCRITOS
//
// Si ocurre un error 409 o 422, conservamos las
// opciones seleccionadas cuando siguen disponibles.
// =========================================================

$datosFormulario = [];

if (isset($valores) && is_array($valores)) {

    $datosFormulario = $valores;
}


// Si el formulario fue enviado por POST y el
// enrutador no preparo $valores, recuperamos
// solamente las dos claves del formulario.

if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
) {

    foreach (
        ['docente_cedula', 'semillero_id'] as $campo
    ) {

        if (
            !isset($datosFormulario[$campo]) &&
            isset($_POST[$campo]) &&
            is_scalar($_POST[$campo])
        ) {

            $datosFormulario[$campo] =
                $_POST[$campo];
        }
    }
}


$docenteSeleccionado = is_scalar(
    $datosFormulario['docente_cedula'] ?? null
)
    ? (string) $datosFormulario['docente_cedula']
    : '';


$semilleroSeleccionado = is_scalar(
    $datosFormulario['semillero_id'] ?? null
)
    ? (string) $datosFormulario['semillero_id']
    : '';


// =========================================================
// 5. VERIFICAR SI LAS SELECCIONES SIGUEN EXISTIENDO
// =========================================================

$docenteEncontrado = $docenteSeleccionado !== ''
    && array_key_exists(
        $docenteSeleccionado,
        $docentesDisponibles
    );

$semilleroEncontrado = $semilleroSeleccionado !== ''
    && array_key_exists(
        $semilleroSeleccionado,
        $semillerosDisponibles
    );


// =========================================================
// 6. PREPARAR TOKEN CSRF
// =========================================================

$tokenFormulario = isset($csrf)
    && is_string($csrf)
        ? $csrf
        : '';


// =========================================================
// 7. VERIFICAR SI PODEMOS GUARDAR
//
// Debe existir al menos un docente y un semillero.
// =========================================================

$formularioHabilitado =
    ($puedeGuardar ?? true) === true
    && $docentesDisponibles !== []
    && $semillerosDisponibles !== []
    && $tokenFormulario !== '';

?>


<!-- =====================================================
     8. ENCABEZADO
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap justify-content-between
               align-items-center gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">

                Nueva asignación de docente a semillero

            </h2>

            <p class="text-body-secondary mb-0">

                Seleccione un docente y un semillero
                existentes para registrar su relación.

            </p>

        </div>

        <a
            href="/participa_semillero"
            class="btn btn-outline-secondary"
        >
            Volver al listado
        </a>

    </div>

</section>


<!-- =====================================================
     9. INFORMACION SOBRE LAS CLAVES FORANEAS
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Claves foráneas:</strong>

        <ul class="mb-0 mt-2">

            <li>
                <code>docente_cedula</code>
                referencia a
                <code>docente.cedula</code>.
            </li>

            <li>
                <code>semillero_id</code>
                referencia a
                <code>semillero.id</code>.
            </li>

        </ul>

        <p class="mb-0 mt-2">

            La pareja docente–semillero debe ser única.
            No se pueden registrar relaciones duplicadas.

        </p>

    </div>

</section>


<!-- =====================================================
     10. VALIDAR LA DISPONIBILIDAD DE LOS CATALOGOS
     ===================================================== -->

<?php if ($docentesDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay docentes disponibles o no fue posible
        consultar el catálogo desde la API.

        <a
            href="/docentes"
            class="alert-link"
        >
            Consultar docentes
        </a>

    </div>

<?php endif; ?>


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


<!-- =====================================================
     11. FORMULARIO PRINCIPAL
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
                action="/participa_semillero/nueva"
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
                         12. DOCENTE
                         PRIMERA CLAVE FORANEA
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="docente_cedula"
                            class="form-label fw-semibold"
                        >

                            Docente

                            <span class="text-danger">*</span>

                        </label>

                        <select
                            id="docente_cedula"
                            name="docente_cedula"
                            class="form-select"
                            required
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                        >

                            <option value="">
                                -- Seleccione un docente --
                            </option>


                            <?php if (
                                $docenteSeleccionado !== ''
                                && !$docenteEncontrado
                            ): ?>

                                <option
                                    value=""
                                    selected
                                    disabled
                                >
                                    El docente anterior
                                    ya no está disponible
                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $docentesDisponibles
                                as $cedula => $nombre
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar(
                                        $cedula
                                    ) ?>"
                                    <?= (string) $cedula
                                        === $docenteSeleccionado
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $nombre
                                    ) ?>

                                    — Cédula:

                                    <?= plantilla_escapar(
                                        $cedula
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div class="form-text">

                            Clave foránea:
                            <code>docente_cedula</code>.

                            El formulario enviará la cédula,
                            no el nombre del docente.

                        </div>

                    </div>


                    <!-- =================================
                         13. SEMILLERO
                         SEGUNDA CLAVE FORANEA
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="semillero_id"
                            class="form-label fw-semibold"
                        >

                            Semillero

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
                                $semilleroSeleccionado !== ''
                                && !$semilleroEncontrado
                            ): ?>

                                <option
                                    value=""
                                    selected
                                    disabled
                                >
                                    El semillero anterior
                                    ya no está disponible
                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $semillerosDisponibles
                                as $idSemillero => $nombre
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar(
                                        $idSemillero
                                    ) ?>"
                                    <?= (string) $idSemillero
                                        === $semilleroSeleccionado
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $nombre
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

                            El formulario enviará el ID,
                            no el nombre del semillero.

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     14. EXPLICACION DE LA OPERACION
                     ===================================== -->

                <div class="alert alert-light border mt-4">

                    <h4 class="h6 fw-bold">

                        ¿Qué sucede al guardar?

                    </h4>

                    <p class="small mb-0">

                        Se registrará una relación en la tabla

                        <code>participa_semillero</code>.

                        El docente y el semillero conservarán
                        sus datos originales.

                    </p>

                </div>


                <!-- =====================================
                     15. BOTONES
                     ===================================== -->

                <div
                    class="d-flex flex-wrap gap-2
                           border-top mt-4 pt-4"
                >

                    <button
                        type="submit"
                        class="btn btn-primary"
                        <?= $formularioHabilitado
                            ? ''
                            : 'disabled' ?>
                    >

                        Asignar docente al semillero

                    </button>


                    <a
                        href="/participa_semillero"
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
     16. NOTA DE INTEGRIDAD REFERENCIAL
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-warning" role="note">

        <strong>Importante:</strong>

        las dos claves forman una clave primaria compuesta.

        Si intenta registrar una relación que ya existe,
        la API responderá con

        <strong>HTTP 409</strong>.

        Para cambiar la pareja, retire primero
        la relación anterior y después registre
        la nueva asignación.

    </div>

</section>