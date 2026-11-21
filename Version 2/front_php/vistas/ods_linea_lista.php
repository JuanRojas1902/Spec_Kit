<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: ods_linea_lista.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Pantalla para consultar las relaciones entre
 * Objetivos de Desarrollo Sostenible (ODS)
 * y lineas de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite visualizar y retirar las parejas
 * registradas en la tabla puente ods_linea.
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
 * - No accede directamente a MariaDB.
 * - No realiza peticiones HTTP.
 * - No elimina objetivos ni lineas.
 * - No modifica registros directamente.
 *
 * VARIABLES RECIBIDAS DESDE index.php:
 *
 * $registros : relaciones de ods_linea.
 * $opciones1 : catalogo de ODS.
 * $opciones2 : catalogo de lineas.
 * $csrf      : token de seguridad.
 *
 * La plantilla principal ya contiene Bootstrap.
 * =========================================================
 */


// =========================================================
// 1. RECUPERAR LAS RELACIONES ODS-LINEA
// =========================================================

$listaRelaciones = [];

if (isset($registros) && is_array($registros)) {

    foreach ($registros as $registro) {

        if (is_array($registro)) {

            $listaRelaciones[] = $registro;
        }
    }
}


// =========================================================
// 2. PREPARAR EL CATALOGO DE ODS
//
// PRIMERA CLAVE FORANEA:
//
// ods_linea.ods_id
//       ->
// objetivo_desarrollo_sostenible.id
//
// Mostraremos el nombre del objetivo.
// =========================================================

$nombresODS = [];

if (isset($opciones1) && is_array($opciones1)) {

    foreach ($opciones1 as $objetivo) {

        if (!is_array($objetivo)) {
            continue;
        }

        $odsId = filter_var(
            $objetivo['id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if (!is_int($odsId)) {
            continue;
        }

        $nombre = trim(
            (string) ($objetivo['nombre'] ?? '')
        );

        if ($nombre !== '') {

            $nombresODS[(string) $odsId] = $nombre;
        }
    }
}


// =========================================================
// 3. PREPARAR EL CATALOGO DE LINEAS
//
// SEGUNDA CLAVE FORANEA:
//
// ods_linea.linea_id
//       ->
// linea_investigacion.id
// =========================================================

$nombresLineas = [];

if (isset($opciones2) && is_array($opciones2)) {

    foreach ($opciones2 as $linea) {

        if (!is_array($linea)) {
            continue;
        }

        $lineaId = filter_var(
            $linea['id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if (!is_int($lineaId)) {
            continue;
        }

        $nombre = trim(
            (string) ($linea['nombre'] ?? '')
        );

        if ($nombre !== '') {

            $nombresLineas[(string) $lineaId] = $nombre;
        }
    }
}


// =========================================================
// 4. PREPARAR LAS VARIABLES GENERALES
// =========================================================

$totalRelaciones = count($listaRelaciones);

$tokenFormulario = isset($csrf) && is_string($csrf)
    ? $csrf
    : '';

$hayErrores = isset($erroresValidacion)
    && is_array($erroresValidacion)
    && $erroresValidacion !== [];

?>


<!-- =====================================================
     5. ENCABEZADO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div class="d-flex flex-wrap
                justify-content-between
                align-items-center gap-3">

        <div>

            <h2 class="h4 fw-bold mb-2">

                ODS por línea de investigación

            </h2>

            <p class="text-body-secondary mb-0">

                Consulte los Objetivos de Desarrollo
                Sostenible relacionados con las líneas
                de investigación.

            </p>

        </div>

        <a
            href="/ods_linea/nueva"
            class="btn btn-primary"
        >

            + Nueva asignación

        </a>

    </div>

</section>


<!-- =====================================================
     6. INFORMACION SOBRE LAS CLAVES FORANEAS
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

            Cada pareja ODS–línea debe ser única.

        </p>

    </div>

</section>


<!-- =====================================================
     7. TARJETA DE RESUMEN
     ===================================================== -->

<section class="mb-4">

    <div class="row g-3">

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <p class="text-body-secondary mb-2">

                        Relaciones registradas

                    </p>

                    <h3 class="display-6 fw-bold
                               text-primary mb-1">

                        <?= $totalRelaciones ?>

                    </h3>

                    <p class="small text-body-secondary mb-0">

                        Asignaciones recibidas desde la API.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     8. TABLA PRINCIPAL
     ===================================================== -->

<section>

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-3
                    d-flex flex-wrap justify-content-between
                    align-items-center gap-2">

            <h3 class="h5 fw-semibold mb-0">

                Listado de objetivos y líneas

            </h3>

            <span class="badge text-bg-primary">

                <?= $totalRelaciones ?> registros

            </span>

        </div>


        <div class="card-body p-0">


            <!-- =========================================
                 9. ERROR AL CONSULTAR LA API
                 ========================================= -->

            <?php if (
                $listaRelaciones === [] &&
                $hayErrores
            ): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No se pudo cargar el listado

                    </h4>

                    <p class="text-body-secondary">

                        Revise los mensajes de error
                        que aparecen en la página.

                    </p>

                    <a
                        href="/ods_linea"
                        class="btn btn-outline-primary"
                    >

                        Intentar nuevamente

                    </a>

                </div>


            <!-- =========================================
                 10. LISTADO VACIO
                 ========================================= -->

            <?php elseif ($listaRelaciones === []): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No hay relaciones registradas

                    </h4>

                    <p class="text-body-secondary">

                        Todavía no se han relacionado
                        objetivos de desarrollo sostenible
                        con líneas de investigación.

                    </p>

                    <a
                        href="/ods_linea/nueva"
                        class="btn btn-primary"
                    >

                        Crear primera asignación

                    </a>

                </div>


            <!-- =========================================
                 11. MOSTRAR LOS REGISTROS
                 ========================================= -->

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-striped
                                  table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th scope="col" class="ps-4 py-3">
                                    ODS ID (FK)
                                </th>

                                <th scope="col" class="py-3">
                                    Nombre del ODS
                                </th>

                                <th scope="col" class="py-3">
                                    Línea ID (FK)
                                </th>

                                <th scope="col" class="py-3">
                                    Línea de investigación
                                </th>

                                <th
                                    scope="col"
                                    class="py-3 text-end pe-4"
                                >
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $listaRelaciones as $relacion
                            ): ?>

                                <?php

                                // -------------------------
                                // PRIMERA CLAVE FORANEA:
                                //
                                // ods_linea.ods_id
                                //       ->
                                // objetivo_desarrollo_sostenible.id
                                // -------------------------

                                $odsId = filter_var(
                                    $relacion['ods_id'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    [
                                        'options' => [
                                            'min_range' => 1
                                        ]
                                    ]
                                );


                                // -------------------------
                                // SEGUNDA CLAVE FORANEA:
                                //
                                // ods_linea.linea_id
                                //       ->
                                // linea_investigacion.id
                                // -------------------------

                                $lineaId = filter_var(
                                    $relacion['linea_id'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    [
                                        'options' => [
                                            'min_range' => 1
                                        ]
                                    ]
                                );


                                // -------------------------
                                // IDENTIFICAR EL NOMBRE
                                // DEL OBJETIVO ODS
                                // -------------------------

                                $nombreODS = 'ODS no disponible';

                                if (is_int($odsId)) {

                                    $nombreODS =
                                        $nombresODS[
                                            (string) $odsId
                                        ] ?? 'ODS no disponible';
                                }


                                // -------------------------
                                // IDENTIFICAR EL NOMBRE
                                // DE LA LINEA
                                // -------------------------

                                $nombreLinea =
                                    'Línea no disponible';

                                if (is_int($lineaId)) {

                                    $nombreLinea =
                                        $nombresLineas[
                                            (string) $lineaId
                                        ] ?? 'Línea no disponible';
                                }


                                // -------------------------
                                // VALIDAR LAS CLAVES
                                // -------------------------

                                $clavesValidas =
                                    is_int($odsId) &&
                                    is_int($lineaId);

                                ?>


                                <tr>


                                    <!-- Identificador del ODS -->

                                    <td class="ps-4 py-3 fw-semibold">

                                        <?= is_int($odsId)
                                            ? $odsId
                                            : 'No disponible' ?>

                                    </td>


                                    <!-- Nombre del ODS -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombreODS
                                        ) ?>

                                    </td>


                                    <!-- Identificador de linea -->

                                    <td class="py-3">

                                        <?= is_int($lineaId)
                                            ? $lineaId
                                            : 'No disponible' ?>

                                    </td>


                                    <!-- Nombre de la linea -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombreLinea
                                        ) ?>

                                    </td>


                                    <!-- Retirar la relacion -->

                                    <td class="py-3 text-end pe-4">

                                        <?php if (
                                            $clavesValidas &&
                                            $tokenFormulario !== ''
                                        ): ?>

                                            <form
                                                method="POST"
                                                action="/ods_linea/retirar"
                                                class="m-0"
                                                onsubmit="return confirm('¿Desea retirar esta relación? El ODS y la línea no serán eliminados.');"
                                            >

                                                <!-- Token CSRF -->

                                                <input
                                                    type="hidden"
                                                    name="csrf"
                                                    value="<?= plantilla_escapar(
                                                        $tokenFormulario
                                                    ) ?>"
                                                >


                                                <!-- Primera FK -->

                                                <input
                                                    type="hidden"
                                                    name="ods_id"
                                                    value="<?= $odsId ?>"
                                                >


                                                <!-- Segunda FK -->

                                                <input
                                                    type="hidden"
                                                    name="linea_id"
                                                    value="<?= $lineaId ?>"
                                                >


                                                <button
                                                    type="submit"
                                                    class="btn btn-outline-danger btn-sm"
                                                >

                                                    Retirar relación

                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <span class="small text-body-secondary">

                                                Acción no disponible

                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>


        <!-- =========================================
             12. PIE DE LA TABLA
             ========================================= -->

        <?php if ($listaRelaciones !== []): ?>

            <div class="card-footer bg-white p-3
                        d-flex flex-wrap justify-content-between
                        align-items-center gap-2">

                <span class="small text-body-secondary">

                    Mostrando <?= $totalRelaciones ?>
                    relación(es).

                </span>

                <a
                    href="/ods_linea"
                    class="btn btn-outline-secondary btn-sm"
                >

                    Actualizar listado

                </a>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- =====================================================
     13. INFORMACION SOBRE LA ELIMINACION
     ===================================================== -->

<section class="mt-4">

    <div class="alert alert-warning" role="note">

        <strong>Importante:</strong>

        retirar una asignación elimina únicamente
        la pareja registrada en

        <code>ods_linea</code>.

        El Objetivo de Desarrollo Sostenible y la línea
        de investigación permanecen en sus tablas.

        Esta tabla no tiene columna

        <code>activo</code>

        y no permite editar relaciones mediante
        PUT ni PATCH.

    </div>

</section>