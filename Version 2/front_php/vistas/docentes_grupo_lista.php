<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: docentes_grupo_lista.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Pantalla de consulta de la tabla puente participa_grupo.
 *
 * POR QUE ESTA AQUI:
 * Permite consultar que docentes participan
 * en que grupos de investigacion.
 *
 * CLAVES FORANEAS:
 *
 * 1. participa_grupo.docente_cedula
 *                  ->
 *    docente.cedula
 *
 * 2. participa_grupo.grupo_id
 *                  ->
 *    grupo_investigacion.id
 *
 * CLAVE PRIMARIA:
 *
 * PRIMARY KEY (docente_cedula, grupo_id)
 *
 * Las dos claves forman una pareja unica.
 *
 * OPERACIONES:
 *
 * GET:    consultar relaciones.
 * POST:   crear una nueva pareja.
 * DELETE: retirar una pareja existente.
 *
 * PUT y PATCH no estan permitidos.
 *
 * IMPORTANTE:
 * Retirar una relacion NO elimina al docente
 * ni retira al grupo de investigacion.
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No se conecta directamente a MariaDB.
 * - No hace peticiones HTTP.
 * - No modifica los registros directamente.
 *
 * DATOS PREPARADOS POR index.php:
 *
 * $registros: relaciones de participa_grupo.
 * $opciones1: catalogo de docentes.
 * $opciones2: catalogo de grupos.
 * $csrf: token para formularios POST.
 * =========================================================
 */


// =========================================================
// 1. NORMALIZAR LOS LISTADOS RECIBIDOS
//
// cliente_api.php devuelve respuestas con "datos".
// Esta funcion permite utilizar el arreglo resultante
// sin realizar nuevas llamadas HTTP desde la vista.
// =========================================================

$extraerFilas = static function (mixed $origen): array {

    if (!is_array($origen)) {
        return [];
    }

    // Respuesta completa del cliente HTTP.
    if (
        isset($origen['respuesta']['datos']) &&
        is_array($origen['respuesta']['datos'])
    ) {
        $origen = $origen['respuesta']['datos'];
    }

    // Resultado normalizado del cliente HTTP.
    elseif (
        isset($origen['datos']) &&
        is_array($origen['datos'])
    ) {
        $origen = $origen['datos'];
    }

    $filas = [];

    foreach ($origen as $fila) {

        if (is_array($fila)) {
            $filas[] = $fila;
        }
    }

    return $filas;
};


// =========================================================
// 2. RECUPERAR LAS RELACIONES DOCENTE-GRUPO
// =========================================================

$listaRelaciones = $extraerFilas(
    $registros ?? []
);


// =========================================================
// 3. RECUPERAR LOS DOCENTES
//
// La clave foranea docente_cedula corresponde
// a la clave primaria cedula de la tabla docente.
// =========================================================

$listaDocentes = $extraerFilas(
    $opciones1 ?? []
);


// =========================================================
// 4. RECUPERAR LOS GRUPOS
//
// La clave foranea grupo_id corresponde
// al id de grupo_investigacion.
// =========================================================

$listaGrupos = $extraerFilas(
    $opciones2 ?? []
);


// =========================================================
// 5. CREAR UN DICCIONARIO DE DOCENTES
//
// Ejemplo:
// 1017245 => "Ana Torres Gómez"
//
// Esto evita mostrar solamente una cedula.
// =========================================================

$nombresDocentes = [];

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

    if ($nombreCompleto !== '') {

        $nombresDocentes[(string) $cedula] =
            $nombreCompleto;
    }
}


// =========================================================
// 6. CREAR UN DICCIONARIO DE GRUPOS
//
// Ejemplo:
// 1 => "COL0001"
//
// La tabla grupo_investigacion no tiene nombre:
// utiliza el campo gruplac.
// =========================================================

$codigosGrupos = [];

foreach ($listaGrupos as $grupo) {

    $idGrupo = filter_var(
        $grupo['id'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    if ($idGrupo === false) {
        continue;
    }

    $gruplac = trim(
        (string) ($grupo['gruplac'] ?? '')
    );

    if ($gruplac !== '') {

        $codigosGrupos[(string) $idGrupo] =
            $gruplac;
    }
}


// =========================================================
// 7. CONTAR LAS RELACIONES
// =========================================================

$totalRelaciones = count($listaRelaciones);


// =========================================================
// 8. TOKEN DE SEGURIDAD
// =========================================================

$tokenFormulario = isset($csrf) &&
    is_string($csrf)
        ? $csrf
        : '';


// =========================================================
// 9. ERRORES DE CONSULTA
// =========================================================

$hayErrores = isset($erroresValidacion)
    && is_array($erroresValidacion)
    && $erroresValidacion !== [];

?>


<!-- =====================================================
     10. ENCABEZADO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap
               justify-content-between
               align-items-center gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">

                Docentes por grupo

            </h2>

            <p class="text-body-secondary mb-0">

                Consulte los docentes asociados a
                los grupos de investigación.

            </p>

        </div>


        <!-- Crear nueva relacion -->

        <a
            href="/participa_grupo/nueva"
            class="btn btn-primary"
        >

            + Nueva asignación

        </a>

    </div>

</section>


<!-- =====================================================
     11. EXPLICACION DE LAS CLAVES FORANEAS
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Relaciones de esta tabla:</strong>

        <ul class="mb-0 mt-2">

            <li>

                <code>docente_cedula</code>

                hace referencia a

                <code>docente.cedula</code>.

            </li>

            <li>

                <code>grupo_id</code>

                hace referencia a

                <code>grupo_investigacion.id</code>.

            </li>

        </ul>

        <p class="mb-0 mt-2">

            Las dos claves identifican una
            asignación única.

        </p>

    </div>

</section>


<!-- =====================================================
     12. RESUMEN DE ASIGNACIONES
     ===================================================== -->

<section class="mb-4">

    <div class="row g-3">

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <p class="text-body-secondary mb-2">

                        Asignaciones registradas

                    </p>

                    <h3 class="display-6 fw-bold text-primary mb-1">

                        <?= $totalRelaciones ?>

                    </h3>

                    <p class="text-body-secondary small mb-0">

                        Relaciones recibidas desde la API.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     13. TABLA DE RELACIONES
     ===================================================== -->

<section>

    <div class="card border-0 shadow-sm">

        <div
            class="card-header bg-white p-3
                   d-flex flex-wrap
                   justify-content-between
                   align-items-center gap-2"
        >

            <h3 class="h5 fw-semibold mb-0">

                Listado de participaciones

            </h3>

            <span class="badge text-bg-primary">

                <?= $totalRelaciones ?> registros

            </span>

        </div>


        <div class="card-body p-0">


            <!-- =========================================
                 14. LISTADO VACIO
                 ========================================= -->

            <?php if (
                $listaRelaciones === [] &&
                !$hayErrores
            ): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No hay asignaciones registradas

                    </h4>

                    <p class="text-body-secondary">

                        No se encontraron relaciones
                        entre docentes y grupos.

                    </p>

                    <a
                        href="/participa_grupo/nueva"
                        class="btn btn-primary"
                    >

                        Crear primera asignación

                    </a>

                </div>


            <!-- =========================================
                 15. ERROR AL CARGAR RELACIONES
                 ========================================= -->

            <?php elseif (
                $listaRelaciones === [] &&
                $hayErrores
            ): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No se pudo cargar el listado

                    </h4>

                    <p class="text-body-secondary">

                        Revise los mensajes de error
                        que aparecen arriba.

                    </p>

                    <a
                        href="/participa_grupo"
                        class="btn btn-outline-primary"
                    >

                        Intentar nuevamente

                    </a>

                </div>


            <!-- =========================================
                 16. TABLA PRINCIPAL
                 ========================================= -->

            <?php else: ?>

                <div class="table-responsive">

                    <table
                        class="table table-striped
                               table-hover align-middle mb-0"
                    >

                        <thead class="table-light">

                            <tr>

                                <th
                                    scope="col"
                                    class="ps-4 py-3"
                                >
                                    Cédula (FK)
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
                                    Docente
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
                                    Grupo (FK)
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
                                    Código GRUPLAC
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
                                // CLAVE FORANEA 1
                                //
                                // participa_grupo.docente_cedula
                                //           ->
                                // docente.cedula
                                // -------------------------

                                $docenteCedula = filter_var(
                                    $relacion['docente_cedula'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    [
                                        'options' => [
                                            'min_range' => 1
                                        ]
                                    ]
                                );


                                // -------------------------
                                // CLAVE FORANEA 2
                                //
                                // participa_grupo.grupo_id
                                //           ->
                                // grupo_investigacion.id
                                // -------------------------

                                $grupoId = filter_var(
                                    $relacion['grupo_id'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    [
                                        'options' => [
                                            'min_range' => 1
                                        ]
                                    ]
                                );


                                // -------------------------
                                // Nombre del docente.
                                // -------------------------

                                $nombreDocente = 'No disponible';

                                if ($docenteCedula !== false) {

                                    $nombreDocente =
                                        $nombresDocentes[
                                            (string) $docenteCedula
                                        ] ?? 'Docente no disponible';
                                }


                                // -------------------------
                                // Codigo del grupo.
                                // -------------------------

                                $codigoGrupo = 'No disponible';

                                if ($grupoId !== false) {

                                    $codigoGrupo =
                                        $codigosGrupos[
                                            (string) $grupoId
                                        ] ?? 'Grupo no disponible';
                                }


                                // -------------------------
                                // Validar ambas claves.
                                // -------------------------

                                $clavesValidas =
                                    $docenteCedula !== false
                                    && $grupoId !== false;

                                ?>


                                <tr>


                                    <!-- Cedula -->

                                    <td class="ps-4 py-3 fw-semibold">

                                        <?= $docenteCedula !== false
                                            ? (int) $docenteCedula
                                            : 'No disponible' ?>

                                    </td>


                                    <!-- Nombre del docente -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombreDocente
                                        ) ?>

                                    </td>


                                    <!-- Identificador del grupo -->

                                    <td class="py-3">

                                        <?= $grupoId !== false
                                            ? (int) $grupoId
                                            : 'No disponible' ?>

                                    </td>


                                    <!-- Codigo GRUPLAC -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $codigoGrupo
                                        ) ?>

                                    </td>


                                    <!-- Acciones -->

                                    <td class="py-3 text-end pe-4">

                                        <?php if ($clavesValidas): ?>

                                            <form
                                                method="POST"
                                                action="/participa_grupo/retirar"
                                                class="m-0"
                                                onsubmit="return confirm('¿Desea retirar esta asignación? El docente y el grupo permanecerán registrados.');"
                                            >

                                                <!-- Token CSRF -->

                                                <input
                                                    type="hidden"
                                                    name="csrf"
                                                    value="<?= plantilla_escapar(
                                                        $tokenFormulario
                                                    ) ?>"
                                                >


                                                <!-- Primera clave foranea -->

                                                <input
                                                    type="hidden"
                                                    name="docente_cedula"
                                                    value="<?= (int) $docenteCedula ?>"
                                                >


                                                <!-- Segunda clave foranea -->

                                                <input
                                                    type="hidden"
                                                    name="grupo_id"
                                                    value="<?= (int) $grupoId ?>"
                                                >


                                                <!-- Retirar relacion -->

                                                <button
                                                    type="submit"
                                                    class="btn btn-outline-danger btn-sm"
                                                >

                                                    Retirar relación

                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <span class="text-body-secondary small">

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
             17. PIE DE LA TABLA
             ========================================= -->

        <?php if ($listaRelaciones !== []): ?>

            <div class="card-footer bg-white p-3">

                <div
                    class="d-flex flex-wrap
                           align-items-center
                           justify-content-between gap-2"
                >

                    <span class="text-body-secondary small">

                        Mostrando
                        <?= $totalRelaciones ?>
                        relación(es).

                    </span>

                    <a
                        href="/participa_grupo"
                        class="btn btn-outline-secondary btn-sm"
                    >

                        Actualizar listado

                    </a>

                </div>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- =====================================================
     18. NOTA SOBRE EL RETIRO DE RELACIONES
     ===================================================== -->

<section class="mt-4">

    <div class="alert alert-warning" role="note">

        <strong>Importante:</strong>

        al retirar una asignación solamente se elimina
        la pareja de la tabla puente

        <code>participa_grupo</code>.

        El docente y el grupo de investigación
        permanecen registrados en sus respectivas tablas.

        Esta tabla no tiene columna

        <code>activo</code>.

    </div>

</section>