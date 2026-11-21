<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: lineas_semillero_lista.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Listado de relaciones entre semilleros y
 * lineas de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite consultar y retirar las asignaciones
 * registradas en la tabla puente semillero_linea.
 *
 * CLAVE FORANEA 1:
 * semillero_linea.semillero_id
 *          -> semillero.id
 *
 * CLAVE FORANEA 2:
 * semillero_linea.linea_id
 *          -> linea_investigacion.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 * PRIMARY KEY (semillero_id, linea_id)
 *
 * QUE NO HACE:
 * - No ejecuta SQL.
 * - No accede directamente a MariaDB.
 * - No realiza peticiones HTTP.
 * - No elimina semilleros ni lineas.
 * - No modifica registros por si mismo.
 *
 * VARIABLES PREPARADAS POR index.php:
 * $registros : relaciones de semillero_linea.
 * $opciones1 : catalogo de semilleros.
 * $opciones2 : catalogo de lineas.
 * $csrf      : token de seguridad.
 *
 * La plantilla principal ya incluye Bootstrap
 * y la barra de navegacion.
 * =========================================================
 */


// =========================================================
// 1. RECUPERAR LOS REGISTROS DE LA TABLA PUENTE
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
// 2. RECUPERAR EL CATALOGO DE SEMILLEROS
//
// FK: semillero_linea.semillero_id -> semillero.id
// =========================================================

$nombresSemilleros = [];

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

        if ($id === false) {
            continue;
        }

        $nombre = trim(
            (string) ($semillero['nombre'] ?? '')
        );

        if ($nombre !== '') {

            $nombresSemilleros[(string) $id] =
                $nombre;
        }
    }
}


// =========================================================
// 3. RECUPERAR EL CATALOGO DE LINEAS
//
// FK: semillero_linea.linea_id
//          -> linea_investigacion.id
// =========================================================

$nombresLineas = [];

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

        if ($id === false) {
            continue;
        }

        $nombre = trim(
            (string) ($linea['nombre'] ?? '')
        );

        if ($nombre !== '') {

            $nombresLineas[(string) $id] =
                $nombre;
        }
    }
}


// =========================================================
// 4. PREPARAR VARIABLES DEL LISTADO
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
     5. ENCABEZADO
     ===================================================== -->

<section class="mb-4">

    <div class="d-flex flex-wrap
                justify-content-between
                align-items-center gap-3">

        <div>

            <h2 class="h4 fw-bold mb-2">
                Líneas por semillero
            </h2>

            <p class="text-body-secondary mb-0">

                Consulte las líneas de investigación
                asociadas a cada semillero.

            </p>

        </div>

        <a
            href="/semillero_linea/nueva"
            class="btn btn-primary"
        >
            + Nueva asignación
        </a>

    </div>

</section>


<!-- =====================================================
     6. INFORMACION DE CLAVES FORANEAS
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

            La pareja semillero–línea debe ser única.

        </p>

    </div>

</section>


<!-- =====================================================
     7. RESUMEN
     ===================================================== -->

<section class="mb-4">

    <div class="row g-3">

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <p class="text-body-secondary mb-2">

                        Relaciones registradas

                    </p>

                    <h3 class="display-6
                               fw-bold text-primary mb-1">

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
     8. TABLA DE RELACIONES
     ===================================================== -->

<section>

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-3
                    d-flex justify-content-between
                    align-items-center flex-wrap gap-2">

            <h3 class="h5 fw-semibold mb-0">

                Listado de semilleros y líneas

            </h3>

            <span class="badge text-bg-primary">

                <?= $totalRelaciones ?> registros

            </span>

        </div>

        <div class="card-body p-0">


            <!-- =========================================
                 9. ERROR AL CARGAR LA CONSULTA
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
                        href="/semillero_linea"
                        class="btn btn-outline-primary"
                    >
                        Intentar nuevamente
                    </a>

                </div>


            <!-- =========================================
                 10. SIN REGISTROS
                 ========================================= -->

            <?php elseif ($listaRelaciones === []): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No hay relaciones registradas

                    </h4>

                    <p class="text-body-secondary">

                        Todavía no se han asignado líneas
                        de investigación a semilleros.

                    </p>

                    <a
                        href="/semillero_linea/nueva"
                        class="btn btn-primary"
                    >
                        Crear primera asignación
                    </a>

                </div>


            <!-- =========================================
                 11. MOSTRAR LAS RELACIONES
                 ========================================= -->

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-striped
                                  table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th scope="col" class="ps-4 py-3">
                                    Semillero ID (FK)
                                </th>

                                <th scope="col" class="py-3">
                                    Nombre del semillero
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
                                // CLAVE FORANEA 1:
                                // semillero_linea.semillero_id
                                //       -> semillero.id
                                // -------------------------

                                $semilleroId = filter_var(
                                    $relacion['semillero_id'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    [
                                        'options' => [
                                            'min_range' => 1
                                        ]
                                    ]
                                );


                                // -------------------------
                                // CLAVE FORANEA 2:
                                // semillero_linea.linea_id
                                //       -> linea_investigacion.id
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
                                // Obtener nombre del semillero
                                // -------------------------

                                $nombreSemillero =
                                    'No disponible';

                                if ($semilleroId !== false) {

                                    $nombreSemillero =
                                        $nombresSemilleros[
                                            (string) $semilleroId
                                        ] ?? 'Semillero no disponible';
                                }


                                // -------------------------
                                // Obtener nombre de la linea
                                // -------------------------

                                $nombreLinea = 'No disponible';

                                if ($lineaId !== false) {

                                    $nombreLinea =
                                        $nombresLineas[
                                            (string) $lineaId
                                        ] ?? 'Línea no disponible';
                                }


                                // -------------------------
                                // Validar las dos claves
                                // -------------------------

                                $clavesValidas =
                                    $semilleroId !== false &&
                                    $lineaId !== false;

                                ?>


                                <tr>

                                    <!-- ID del semillero -->

                                    <td class="ps-4 py-3 fw-semibold">

                                        <?= $semilleroId !== false
                                            ? (int) $semilleroId
                                            : 'No disponible' ?>

                                    </td>


                                    <!-- Nombre del semillero -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombreSemillero
                                        ) ?>

                                    </td>


                                    <!-- ID de la linea -->

                                    <td class="py-3">

                                        <?= $lineaId !== false
                                            ? (int) $lineaId
                                            : 'No disponible' ?>

                                    </td>


                                    <!-- Nombre de la linea -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombreLinea
                                        ) ?>

                                    </td>


                                    <!-- Retirar relacion -->

                                    <td class="py-3 text-end pe-4">

                                        <?php if (
                                            $clavesValidas &&
                                            $tokenFormulario !== ''
                                        ): ?>

                                            <form
                                                method="POST"
                                                action="/semillero_linea/retirar"
                                                class="m-0"
                                                onsubmit="return confirm('¿Desea retirar esta relación? El semillero y la línea permanecerán registrados.');"
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
                                                    name="semillero_id"
                                                    value="<?= (int) $semilleroId ?>"
                                                >


                                                <!-- Segunda FK -->

                                                <input
                                                    type="hidden"
                                                    name="linea_id"
                                                    value="<?= (int) $lineaId ?>"
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
             12. PIE DE TABLA
             ========================================= -->

        <?php if ($listaRelaciones !== []): ?>

            <div class="card-footer bg-white p-3
                        d-flex flex-wrap
                        justify-content-between
                        align-items-center gap-2">

                <span class="small text-body-secondary">

                    Mostrando <?= $totalRelaciones ?>
                    relación(es).

                </span>

                <a
                    href="/semillero_linea"
                    class="btn btn-outline-secondary btn-sm"
                >
                    Actualizar listado
                </a>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- =====================================================
     13. INFORMACION SOBRE EL RETIRO
     ===================================================== -->

<section class="mt-4">

    <div class="alert alert-warning" role="note">

        <strong>Importante:</strong>

        retirar una asignación elimina únicamente
        la pareja de la tabla puente

        <code>semillero_linea</code>.

        El semillero y la línea de investigación
        permanecen registrados.

        Esta tabla no tiene columna
        <code>activo</code>
        y no permite editar parejas con
        PUT ni PATCH.

    </div>

</section>