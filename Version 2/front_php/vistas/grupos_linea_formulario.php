<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: grupos_linea_formulario.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Formulario de asignacion de grupos a lineas
 * de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite registrar una relacion en grupo_linea
 * seleccionando los dos registros relacionados.
 *
 * CLAVE FORANEA 1:
 *
 * grupo_linea.grupo_id
 *       ->
 * grupo_investigacion.id
 *
 * CLAVE FORANEA 2:
 *
 * grupo_linea.linea_id
 *       ->
 * linea_investigacion.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 *
 * PRIMARY KEY (grupo_id, linea_id)
 *
 * REGLAS:
 * - Las dos claves son obligatorias.
 * - Deben ser enteros positivos.
 * - Los registros referenciados deben existir.
 * - Una pareja no puede repetirse.
 * - No se permite PUT ni PATCH.
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No se conecta directamente a MariaDB.
 * - No realiza peticiones HTTP.
 * - No crea grupos ni lineas.
 * - No modifica relaciones directamente.
 *
 * RECIBE DESDE index.php:
 *
 * $opciones1: grupos de investigacion.
 * $opciones2: lineas de investigacion.
 * $valores: datos anteriores del formulario.
 * $csrf: token de seguridad.
 * $puedeGuardar: habilitacion del guardado.
 *
 * FORMULARIO:
 *
 * POST /grupo_linea/nueva
 *
 * La API recibe:
 *
 * POST /api/grupo_linea
 * =========================================================
 */


// =========================================================
// 1. EXTRAER FILAS DEL CLIENTE API
//
// El cliente puede entregar:
// - Un arreglo directo.
// - Una respuesta con "datos".
// - Una respuesta con "respuesta.datos".
//
// La vista no ejecuta peticiones HTTP.
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
// 2. CARGAR EL CATALOGO DE GRUPOS
//
// PRIMERA CLAVE FORANEA:
//
// grupo_linea.grupo_id
//          ->
// grupo_investigacion.id
// =========================================================

$listaGrupos = $extraerFilas(
    $opciones1 ?? []
);

$gruposDisponibles = [];

foreach ($listaGrupos as $grupo) {

    $grupoId = filter_var(
        $grupo['id'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    if ($grupoId === false) {
        continue;
    }

    $gruplac = trim(
        (string) ($grupo['gruplac'] ?? '')
    );

    if ($gruplac === '') {
        continue;
    }

    $gruposDisponibles[(string) $grupoId] =
        $gruplac;
}


// =========================================================
// 3. CARGAR EL CATALOGO DE LINEAS
//
// SEGUNDA CLAVE FORANEA:
//
// grupo_linea.linea_id
//          ->
// linea_investigacion.id
// =========================================================

$listaLineas = $extraerFilas(
    $opciones2 ?? []
);

$lineasDisponibles = [];

foreach ($listaLineas as $linea) {

    $lineaId = filter_var(
        $linea['id'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    if ($lineaId === false) {
        continue;
    }

    $nombreLinea = trim(
        (string) ($linea['nombre'] ?? '')
    );

    if ($nombreLinea === '') {
        continue;
    }

    $lineasDisponibles[(string) $lineaId] =
        $nombreLinea;
}


// =========================================================
// 4. RECUPERAR LOS VALORES ANTERIORES
//
// Si la API devuelve un error de validacion,
// conservamos la seleccion previa cuando sea posible.
// =========================================================

$datosFormulario = [];

if (
    isset($valores) &&
    is_array($valores)
) {

    $datosFormulario = $valores;
}


// Recuperar los valores POST si index.php todavia
// no los ha incluido en $valores.

if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
) {

    foreach (['grupo_id', 'linea_id'] as $campo) {

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


// =========================================================
// 5. IDENTIFICAR LAS SELECCIONES
// =========================================================

$grupoSeleccionado = is_scalar(
    $datosFormulario['grupo_id'] ?? null
)
    ? (string) $datosFormulario['grupo_id']
    : '';


$lineaSeleccionada = is_scalar(
    $datosFormulario['linea_id'] ?? null
)
    ? (string) $datosFormulario['linea_id']
    : '';


// =========================================================
// 6. COMPROBAR QUE LAS OPCIONES EXISTEN
// =========================================================

$grupoEncontrado =
    $grupoSeleccionado !== ''
    && array_key_exists(
        $grupoSeleccionado,
        $gruposDisponibles
    );

$lineaEncontrada =
    $lineaSeleccionada !== ''
    && array_key_exists(
        $lineaSeleccionada,
        $lineasDisponibles
    );


// =========================================================
// 7. PREPARAR TOKEN CSRF
// =========================================================

$tokenFormulario = isset($csrf)
    && is_string($csrf)
        ? $csrf
        : '';


// =========================================================
// 8. COMPROBAR SI PUEDE GUARDARSE
//
// Debe existir al menos un grupo y una linea.
// =========================================================

$formularioHabilitado =
    ($puedeGuardar ?? true) === true
    && $gruposDisponibles !== []
    && $lineasDisponibles !== []
    && $tokenFormulario !== '';

?>


<!-- =====================================================
     9. ENCABEZADO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap justify-content-between
               align-items-center gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">

                Nueva asignación de grupo a línea

            </h2>

            <p class="text-body-secondary mb-0">

                Seleccione el grupo de investigación
                y la línea a la que desea asociarlo.

            </p>

        </div>

        <a
            href="/grupo_linea"
            class="btn btn-outline-secondary"
        >

            Volver al listado

        </a>

    </div>

</section>


<!-- =====================================================
     10. INFORMACION SOBRE LAS FK
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Claves foráneas:</strong>

        <ul class="mb-0 mt-2">

            <li>

                <code>grupo_id</code>

                referencia a

                <code>grupo_investigacion.id</code>.

            </li>

            <li>

                <code>linea_id</code>

                referencia a

                <code>linea_investigacion.id</code>.

            </li>

        </ul>

        <p class="mb-0 mt-2">

            La combinación de ambas claves
            identifica una relación única.

        </p>

    </div>

</section>


<!-- =====================================================
     11. VERIFICAR LOS CATALOGOS
     ===================================================== -->

<?php if ($gruposDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay grupos disponibles o no fue posible
        consultarlos desde la API.

        <a href="/grupos" class="alert-link">

            Consultar grupos

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
                action="/grupo_linea/nueva"
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
                         grupo_id
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="grupo_id"
                            class="form-label fw-semibold"
                        >

                            Grupo de investigación

                            <span class="text-danger">*</span>

                        </label>


                        <select
                            id="grupo_id"
                            name="grupo_id"
                            class="form-select"
                            required
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                        >

                            <option value="">

                                -- Seleccione un grupo --

                            </option>


                            <?php if (
                                $grupoSeleccionado !== ''
                                && !$grupoEncontrado
                            ): ?>

                                <option
                                    value=""
                                    selected
                                    disabled
                                >

                                    Grupo anterior no disponible

                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $gruposDisponibles
                                as $idGrupo => $codigoGrupo
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar(
                                        $idGrupo
                                    ) ?>"
                                    <?= (string) $idGrupo
                                        === $grupoSeleccionado
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $codigoGrupo
                                    ) ?>

                                    — ID:

                                    <?= plantilla_escapar(
                                        $idGrupo
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div class="form-text">

                            Clave foránea:
                            <code>grupo_id</code>.

                            Se enviará el identificador
                            del grupo seleccionado.

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
                                $lineaSeleccionada !== ''
                                && !$lineaEncontrada
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
                                    <?= (string) $idLinea
                                        === $lineaSeleccionada
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

                        Se registrará una nueva relación
                        en la tabla

                        <code>grupo_linea</code>.

                        No se modificará la información
                        del grupo ni de la línea.

                    </p>

                </div>


                <!-- =====================================
                     16. BOTONES DEL FORMULARIO
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

                        Asignar grupo a línea

                    </button>


                    <a
                        href="/grupo_linea"
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
     17. NOTA SOBRE CLAVE COMPUESTA
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-warning" role="note">

        <strong>Integridad referencial:</strong>

        una misma pareja grupo–línea no puede
        registrarse dos veces.

        Si ya existe, la API responderá con

        <strong>HTTP 409</strong>.

        Para cambiar una relación, retire primero
        la pareja anterior y después registre
        una nueva.

    </div>

</section>