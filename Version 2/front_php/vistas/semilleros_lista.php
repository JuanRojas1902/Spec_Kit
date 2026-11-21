<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: semilleros_lista.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Pantalla de consulta de los semilleros activos.
 *
 * POR QUE ESTA AQUI:
 * Permite visualizar los semilleros y conocer
 * a que grupo de investigacion pertenece cada uno.
 *
 * CLAVE PRIMARIA:
 * semillero.id
 *
 * CLAVE FORANEA:
 * semillero.grupo_id
 *           |
 *           v
 * grupo_investigacion.id
 *
 * DATOS:
 * - $registros contiene los semilleros.
 * - $grupos, cuando esta disponible, contiene
 *   los grupos para mostrar su codigo GRUPLAC.
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No se conecta directamente con MariaDB.
 * - No realiza peticiones HTTP.
 * - No registra semilleros.
 * - No edita semilleros.
 * - No retira semilleros.
 *
 * REGLA DE LA V2:
 * Los semilleros se administran exclusivamente
 * desde el grupo de investigacion maestro.
 *
 * GET /api/semillero          -> permitido.
 * GET /api/semillero/{id}     -> permitido.
 *
 * POST, PUT, PATCH y DELETE   -> HTTP 405.
 * =========================================================
 */


// =========================================================
// 1. PREPARAR LOS SEMILLEROS
//
// index.php obtiene los datos mediante
// listar_semilleros().
//
// Esta vista solamente presenta la informacion.
// =========================================================

$listaSemilleros = [];

$datosSemilleros = $registros ?? [];


// Aceptar tanto un arreglo directo como una respuesta
// que contenga el listado dentro de "datos".

if (
    is_array($datosSemilleros) &&
    isset($datosSemilleros['datos']) &&
    is_array($datosSemilleros['datos'])
) {

    $datosSemilleros = $datosSemilleros['datos'];
}


if (is_array($datosSemilleros)) {

    foreach ($datosSemilleros as $semillero) {

        if (is_array($semillero)) {

            $listaSemilleros[] = $semillero;
        }
    }
}


// =========================================================
// 2. PREPARAR EL CATALOGO DE GRUPOS
//
// CLAVE FORANEA:
//
// semillero.grupo_id
//          ->
// grupo_investigacion.id
//
// El nombre mostrado sera el codigo GRUPLAC.
// =========================================================

$listaGrupos = [];

$datosGrupos = $grupos ?? [];


// El cliente HTTP puede devolver un arreglo con "datos".

if (
    is_array($datosGrupos) &&
    isset($datosGrupos['datos']) &&
    is_array($datosGrupos['datos'])
) {

    $datosGrupos = $datosGrupos['datos'];
}


if (is_array($datosGrupos)) {

    foreach ($datosGrupos as $grupo) {

        if (is_array($grupo)) {

            $listaGrupos[] = $grupo;
        }
    }
}


// =========================================================
// 3. CREAR EL DICCIONARIO DE NOMBRES
//
// Ejemplo:
//
// $nombresGrupos['1'] = 'COL0001';
//
// Esto permite encontrar el GRUPLAC a partir
// del grupo_id de cada semillero.
// =========================================================

$nombresGrupos = [];

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

    $codigoGrupo = $grupo['gruplac'] ?? '';

    if (
        !is_string($codigoGrupo) ||
        trim($codigoGrupo) === ''
    ) {

        continue;
    }

    $nombresGrupos[(string) $idGrupo] =
        $codigoGrupo;
}


// =========================================================
// 4. CONTAR LOS REGISTROS
// =========================================================

$totalSemilleros = count($listaSemilleros);


// =========================================================
// 5. IDENTIFICAR ERRORES DE CONSULTA
// =========================================================

$hayErrores = isset($erroresValidacion)
    && is_array($erroresValidacion)
    && $erroresValidacion !== [];

?>


<!-- =====================================================
     6. ENCABEZADO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap align-items-center
               justify-content-between gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">

                Consulta de semilleros

            </h2>

            <p class="text-body-secondary mb-0">

                Consulte los semilleros registrados
                y sus grupos de investigación asociados.

            </p>

        </div>


        <!-- Regresar a grupos -->

        <a
            href="/grupos"
            class="btn btn-outline-primary"
        >

            Ir a grupos de investigación

        </a>

    </div>

</section>


<!-- =====================================================
     7. INFORMACION SOBRE LA CLAVE FORANEA
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Relación con grupos:</strong>

        cada semillero pertenece a un grupo
        de investigación mediante la clave foránea

        <code>grupo_id</code>.

        Para administrar un semillero, debe utilizar
        el formulario de su grupo maestro.

    </div>

</section>


<!-- =====================================================
     8. RESUMEN DE REGISTROS
     ===================================================== -->

<section class="mb-4">

    <div class="row g-3">

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <p class="text-body-secondary mb-2">

                        Semilleros del listado

                    </p>

                    <h3 class="display-6 fw-bold text-primary mb-1">

                        <?= $totalSemilleros ?>

                    </h3>

                    <p class="small text-body-secondary mb-0">

                        Registros recibidos desde la API

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     9. LISTADO DE SEMILLEROS
     ===================================================== -->

<section>

    <div class="card border-0 shadow-sm">

        <!-- Encabezado de la tarjeta -->

        <div
            class="card-header bg-white p-3
                   d-flex flex-wrap justify-content-between
                   align-items-center gap-2"
        >

            <h3 class="h5 fw-semibold mb-0">

                Listado de semilleros

            </h3>

            <span class="badge text-bg-primary">

                <?= $totalSemilleros ?> registros

            </span>

        </div>


        <div class="card-body p-0">


            <!-- =========================================
                 10. LISTADO VACIO
                 ========================================= -->

            <?php if (
                $listaSemilleros === [] &&
                !$hayErrores
            ): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No hay semilleros registrados

                    </h4>

                    <p class="text-body-secondary">

                        No se encontraron semilleros
                        activos para mostrar.

                    </p>

                    <a
                        href="/grupos/nuevo"
                        class="btn btn-primary"
                    >

                        Registrar grupo con semilleros

                    </a>

                </div>


            <!-- =========================================
                 11. ERROR DE CONSULTA
                 ========================================= -->

            <?php elseif (
                $listaSemilleros === [] &&
                $hayErrores
            ): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No se pudo cargar el listado

                    </h4>

                    <p class="text-body-secondary">

                        Revise el mensaje de error
                        que aparece en la parte superior.

                    </p>

                    <a
                        href="/semilleros"
                        class="btn btn-outline-primary"
                    >

                        Intentar nuevamente

                    </a>

                </div>


            <!-- =========================================
                 12. TABLA PRINCIPAL
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
                                    ID
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
                                    Nombre del semillero
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
                                    Grupo (FK)
                                </th>

                                <th
                                    scope="col"
                                    class="py-3 text-end pe-4"
                                >
                                    Consulta
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach (
                                $listaSemilleros as $semillero
                            ): ?>

                                <?php

                                // -------------------------
                                // ID del semillero (PK).
                                // -------------------------

                                $idSemillero = $semillero['id']
                                    ?? '';

                                // -------------------------
                                // Nombre del semillero.
                                // -------------------------

                                $nombreSemillero =
                                    $semillero['nombre'] ?? '';

                                // -------------------------
                                // CLAVE FORANEA:
                                //
                                // semillero.grupo_id
                                //          ->
                                // grupo_investigacion.id
                                // -------------------------

                                $grupoId = filter_var(
                                    $semillero['grupo_id'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    [
                                        'options' => [
                                            'min_range' => 1
                                        ]
                                    ]
                                );

                                // Buscar el codigo GRUPLAC.

                                $codigoGrupo = '';

                                if ($grupoId !== false) {

                                    $codigoGrupo =
                                        $nombresGrupos[
                                            (string) $grupoId
                                        ] ?? (
                                            'Grupo ID ' . $grupoId
                                        );
                                }

                                ?>

                                <tr>


                                    <!-- ID -->

                                    <td class="ps-4 py-3 fw-semibold">

                                        <?= plantilla_escapar(
                                            $idSemillero
                                        ) ?>

                                    </td>


                                    <!-- Nombre -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombreSemillero
                                        ) ?>

                                    </td>


                                    <!-- Grupo asociado -->

                                    <td class="py-3">

                                        <?php if (
                                            $grupoId !== false
                                        ): ?>

                                            <div>

                                                <span class="fw-semibold">

                                                    <?= plantilla_escapar(
                                                        $codigoGrupo
                                                    ) ?>

                                                </span>

                                                <div
                                                    class="text-body-secondary small"
                                                >

                                                    grupo_id:
                                                    <?= (int) $grupoId ?>

                                                </div>

                                            </div>

                                        <?php else: ?>

                                            <span class="text-body-secondary">

                                                Grupo no disponible

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Consultar grupo -->

                                    <td class="py-3 text-end pe-4">

                                        <?php if (
                                            $grupoId !== false &&
                                            isset(
                                                $nombresGrupos[
                                                    (string) $grupoId
                                                ]
                                            )
                                        ): ?>

                                            <a
                                                href="/grupos/<?= (int) $grupoId ?>"
                                                class="btn btn-outline-primary btn-sm"
                                            >

                                                Ver grupo

                                            </a>

                                        <?php else: ?>

                                            <span class="text-body-secondary small">

                                                Sin enlace disponible

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
             13. PIE DE LA TABLA
             ========================================= -->

        <?php if ($listaSemilleros !== []): ?>

            <div class="card-footer bg-white p-3">

                <div
                    class="d-flex flex-wrap justify-content-between
                           align-items-center gap-2"
                >

                    <span class="text-body-secondary small">

                        Mostrando <?= $totalSemilleros ?>
                        semillero(s).

                    </span>

                    <a
                        href="/semilleros"
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
     14. NOTA SOBRE MAESTRO-DETALLE
     ===================================================== -->

<section class="mt-4">

    <div class="alert alert-warning" role="note">

        <strong>Administración de semilleros:</strong>

        los semilleros no se registran, editan ni
        retiran individualmente en esta versión.

        Estas operaciones se realizan mediante el
        grupo maestro, utilizando el formulario
        de grupos de investigación.

    </div>

</section>