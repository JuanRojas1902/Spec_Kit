<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: areas_conocimiento_linea_lista.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Listado de relaciones entre areas de conocimiento
 * y lineas de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite consultar y retirar asignaciones de
 * la tabla puente ac_linea.
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
 * (area_conocimiento_id, linea_id)
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No accede directamente a MariaDB.
 * - No realiza peticiones HTTP.
 * - No elimina areas ni lineas.
 * - No modifica relaciones directamente.
 *
 * VARIABLES PREPARADAS POR index.php:
 * $registros : relaciones de ac_linea.
 * $opciones1 : areas de conocimiento.
 * $opciones2 : lineas de investigacion.
 * $csrf      : token de seguridad.
 *
 * La plantilla principal ya incluye Bootstrap.
 * =========================================================
 */


// =========================================================
// 1. PREPARAR LAS RELACIONES
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
// 2. PREPARAR LAS AREAS DE CONOCIMIENTO
//
// FK:
// ac_linea.area_conocimiento_id
//          -> area_conocimiento.id
//
// Mostraremos el campo "disciplina".
// =========================================================

$nombresAreas = [];

if (isset($opciones1) && is_array($opciones1)) {

    foreach ($opciones1 as $area) {

        if (!is_array($area)) {
            continue;
        }

        $idArea = $area['id'] ?? null;

        if (!is_string($idArea) && !is_int($idArea)) {
            continue;
        }

        $idArea = (string) $idArea;

        if ($idArea === '') {
            continue;
        }

        $disciplina = trim(
            (string) ($area['disciplina'] ?? '')
        );

        if ($disciplina === '') {
            $disciplina = 'Área ID ' . $idArea;
        }

        $nombresAreas[$idArea] = $disciplina;
    }
}


// =========================================================
// 3. PREPARAR LAS LINEAS DE INVESTIGACION
//
// FK:
// ac_linea.linea_id
//          -> linea_investigacion.id
// =========================================================

$nombresLineas = [];

if (isset($opciones2) && is_array($opciones2)) {

    foreach ($opciones2 as $linea) {

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

        if ($idLinea === false) {
            continue;
        }

        $nombre = trim(
            (string) ($linea['nombre'] ?? '')
        );

        if ($nombre !== '') {

            $nombresLineas[(string) $idLinea] =
                $nombre;
        }
    }
}


// =========================================================
// 4. VARIABLES GENERALES
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
                Áreas de conocimiento por línea
            </h2>

            <p class="text-body-secondary mb-0">
                Consulte las áreas de conocimiento
                relacionadas con las líneas de investigación.
            </p>

        </div>

        <a
            href="/ac_linea/nueva"
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
            Cada combinación de área y línea
            debe ser única.
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
                        Registros recibidos desde la API.
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
                Listado de áreas y líneas
            </h3>

            <span class="badge text-bg-primary">
                <?= $totalRelaciones ?> registros
            </span>

        </div>

        <div class="card-body p-0">


            <!-- =========================================
                 9. ERROR DE CONSULTA
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
                        href="/ac_linea"
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
                        áreas de conocimiento con líneas
                        de investigación.
                    </p>

                    <a
                        href="/ac_linea/nueva"
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
                                    Área ID (FK)
                                </th>

                                <th scope="col" class="py-3">
                                    Disciplina
                                </th>

                                <th scope="col" class="py-3">
                                    Línea ID (FK)
                                </th>

                                <th scope="col" class="py-3">
                                    Línea de investigación
                                </th>

                                <th scope="col"
                                    class="py-3 text-end pe-4">
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
                                // ac_linea.area_conocimiento_id
                                //       -> area_conocimiento.id
                                // -------------------------

                                $areaIdOriginal =
                                    $relacion['area_conocimiento_id']
                                    ?? '';

                                $areaId = is_string($areaIdOriginal)
                                    || is_int($areaIdOriginal)
                                        ? (string) $areaIdOriginal
                                        : '';

                                // Identificador numerico valido
                                // para operaciones de la API.

                                $areaIdNumerico = filter_var(
                                    $areaId,
                                    FILTER_VALIDATE_INT,
                                    [
                                        'options' => [
                                            'min_range' => 1
                                        ]
                                    ]
                                );


                                // -------------------------
                                // CLAVE FORANEA 2:
                                // ac_linea.linea_id
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
                                // Nombre de la disciplina.
                                // -------------------------

                                $nombreArea = $nombresAreas[
                                    $areaId
                                ] ?? 'Área no disponible';


                                // -------------------------
                                // Nombre de la linea.
                                // -------------------------

                                $nombreLinea = 'Línea no disponible';

                                if ($lineaId !== false) {

                                    $nombreLinea =
                                        $nombresLineas[
                                            (string) $lineaId
                                        ] ?? 'Línea no disponible';
                                }


                                // -------------------------
                                // Validar ambas claves.
                                // -------------------------

                                $clavesValidas =
                                    $areaIdNumerico !== false &&
                                    $lineaId !== false;

                                ?>

                                <tr>


                                    <!-- ID del area -->

                                    <td class="ps-4 py-3 fw-semibold">

                                        <?= plantilla_escapar(
                                            $areaId
                                        ) ?>

                                    </td>


                                    <!-- Disciplina -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombreArea
                                        ) ?>

                                    </td>


                                    <!-- ID de linea -->

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
                                                action="/ac_linea/retirar"
                                                class="m-0"
                                                onsubmit="return confirm('¿Desea retirar esta relación? El área y la línea permanecerán registradas.');"
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
                                                    name="area_conocimiento_id"
                                                    value="<?= (int) $areaIdNumerico ?>"
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
                        d-flex flex-wrap justify-content-between
                        align-items-center gap-2">

                <span class="small text-body-secondary">

                    Mostrando <?= $totalRelaciones ?>
                    relación(es).

                </span>

                <a
                    href="/ac_linea"
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
        <code>ac_linea</code>.

        El área de conocimiento y la línea de
        investigación permanecen registradas.

        Esta tabla no tiene columna
        <code>activo</code> y no permite
        editar parejas mediante PUT o PATCH.

    </div>

</section>