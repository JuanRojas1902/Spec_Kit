<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: grupos_lista.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Pantalla para consultar los grupos de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Muestra los grupos activos y sus datos principales.
 * Permite acceder a la consulta maestro-detalle,
 * registrar, editar y retirar grupos.
 *
 * CLAVE FORANEA:
 *
 * grupo_investigacion.universidad_id
 *           |
 *           v
 *      universidad.id
 *
 * Esta vista muestra el nombre de la universidad
 * asociada a cada grupo.
 *
 * QUIEN OBTIENE LOS DATOS:
 * front_php/index.php consulta:
 *
 * - listar_grupos_investigacion()
 * - listar_universidades()
 *
 * Las respuestas llegan mediante:
 *
 * - $registros
 * - $universidades
 *
 * QUE NO HACE:
 * - No se conecta directamente a MariaDB.
 * - No ejecuta consultas SQL.
 * - No realiza peticiones HTTP.
 * - No modifica datos directamente.
 * - No administra los semilleros del grupo.
 *
 * IMPORTANTE:
 * El retiro del grupo es logico.
 *
 * Un grupo que tenga semilleros activos
 * no puede retirarse: la API devuelve HTTP 409.
 * =========================================================
 */


// =========================================================
// 1. PREPARAR LOS GRUPOS RECIBIDOS
// =========================================================

$listaGrupos = [];

if (
    isset($registros) &&
    is_array($registros)
) {

    foreach ($registros as $registro) {

        if (is_array($registro)) {

            $listaGrupos[] = $registro;
        }
    }
}


// =========================================================
// 2. PREPARAR LOS NOMBRES DE LAS UNIVERSIDADES
//
// CLAVE FORANEA:
//
// grupo_investigacion.universidad_id
//           ->
// universidad.id
//
// Utilizamos un arreglo para encontrar el nombre
// de la universidad a partir de su identificador.
// =========================================================

$nombresUniversidades = [];

if (
    isset($universidades) &&
    is_array($universidades)
) {

    foreach ($universidades as $universidad) {

        if (
            !is_array($universidad) ||
            !isset(
                $universidad['id'],
                $universidad['nombre']
            )
        ) {

            continue;
        }

        $idUniversidad = (string) $universidad['id'];

        $nombreUniversidad =
            (string) $universidad['nombre'];

        $nombresUniversidades[$idUniversidad] =
            $nombreUniversidad;
    }
}


// =========================================================
// 3. CONTAR LOS GRUPOS
// =========================================================

$totalGrupos = count($listaGrupos);


// =========================================================
// 4. RECUPERAR TOKEN CSRF
// =========================================================

$tokenFormulario = '';

if (
    isset($csrf) &&
    is_string($csrf)
) {

    $tokenFormulario = $csrf;
}


// =========================================================
// 5. IDENTIFICAR SI EXISTEN ERRORES
// =========================================================

$hayErrores = isset($erroresValidacion)
    && is_array($erroresValidacion)
    && $erroresValidacion !== [];

?>


<!-- =====================================================
     6. ENCABEZADO
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap
               justify-content-between
               align-items-center gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">
                Administración de grupos de investigación
            </h2>

            <p class="text-body-secondary mb-0">

                Consulte los grupos registrados y sus
                universidades asociadas.

            </p>

        </div>


        <!-- Registrar grupo -->

        <a
            href="/grupos/nuevo"
            class="btn btn-primary"
        >
            + Registrar grupo
        </a>

    </div>

</section>


<!-- =====================================================
     7. INFORMACION SOBRE LA CLAVE FORANEA
     ===================================================== -->

<section class="mb-4">

    <div
        class="alert alert-info"
        role="note"
    >

        <strong>Relación con universidades:</strong>

        cada grupo pertenece a una universidad.

        Al registrar o editar un grupo, deberá
        seleccionar una universidad existente.

    </div>

</section>


<!-- =====================================================
     8. RESUMEN
     ===================================================== -->

<section class="mb-4">

    <div class="row g-3">

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <p class="text-body-secondary mb-2">
                        Grupos de investigación
                    </p>

                    <h3 class="display-6 fw-bold text-primary mb-1">

                        <?= $totalGrupos ?>

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
     9. LISTADO DE GRUPOS
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
                Listado de grupos
            </h3>

            <span class="badge text-bg-primary">

                <?= $totalGrupos ?> registros

            </span>

        </div>


        <div class="card-body p-0">


            <!-- =========================================
                 10. LISTADO VACIO
                 ========================================= -->

            <?php if (
                $listaGrupos === [] &&
                !$hayErrores
            ): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">
                        Todavía no hay datos
                    </h4>

                    <p class="text-body-secondary">

                        No existen grupos de investigación
                        activos para mostrar.

                    </p>

                    <a
                        href="/grupos/nuevo"
                        class="btn btn-primary"
                    >
                        Registrar primer grupo
                    </a>

                </div>


            <!-- =========================================
                 11. ERROR AL OBTENER LOS DATOS
                 ========================================= -->

            <?php elseif (
                $listaGrupos === [] &&
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
                        href="/grupos"
                        class="btn btn-outline-primary"
                    >
                        Intentar nuevamente
                    </a>

                </div>


            <!-- =========================================
                 12. TABLA DE GRUPOS
                 ========================================= -->

            <?php else: ?>

                <div class="table-responsive">

                    <table
                        class="table table-striped
                               table-hover
                               align-middle mb-0"
                    >

                        <thead class="table-light">

                            <tr>

                                <th scope="col" class="ps-4 py-3">
                                    ID
                                </th>

                                <th scope="col" class="py-3">
                                    Grupo (GRUPLAC)
                                </th>

                                <th scope="col" class="py-3">
                                    Categoría
                                </th>

                                <th scope="col" class="py-3">
                                    Convocatoria
                                </th>

                                <th scope="col" class="py-3">
                                    Ámbito
                                </th>

                                <th scope="col" class="py-3">
                                    Universidad
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
                                $listaGrupos as $grupo
                            ): ?>

                                <?php

                                // -------------------------
                                // Identificador del grupo.
                                // -------------------------

                                $idGrupo = filter_var(
                                    $grupo['id'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    [
                                        'options' => [
                                            'min_range' => 1
                                        ]
                                    ]
                                );


                                // -------------------------
                                // Campos de la tabla.
                                // -------------------------

                                $gruplac = $grupo['gruplac']
                                    ?? '';

                                $categoria = $grupo['categoria']
                                    ?? '';

                                $convocatoria =
                                    $grupo['convocatoria']
                                    ?? '';

                                $ambito = $grupo['ambito']
                                    ?? '';


                                // -------------------------
                                // CLAVE FORANEA
                                //
                                // grupo_investigacion
                                //      .universidad_id
                                //
                                //          ->
                                //
                                // universidad.id
                                // -------------------------

                                $universidadId =
                                    (string) (
                                        $grupo['universidad_id']
                                        ?? ''
                                    );

                                $nombreUniversidad =
                                    $nombresUniversidades[
                                        $universidadId
                                    ] ?? (
                                        'Universidad ID ' .
                                        $universidadId
                                    );

                                ?>


                                <tr>


                                    <!-- ID -->

                                    <td class="ps-4 py-3 fw-semibold">

                                        <?= plantilla_escapar(
                                            $grupo['id'] ?? ''
                                        ) ?>

                                    </td>


                                    <!-- Codigo GRUPLAC -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $gruplac
                                        ) ?>

                                    </td>


                                    <!-- Categoria -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $categoria
                                        ) ?>

                                    </td>


                                    <!-- Convocatoria -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $convocatoria
                                        ) ?>

                                    </td>


                                    <!-- Ambito -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $ambito
                                        ) ?>

                                    </td>


                                    <!-- Universidad relacionada -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombreUniversidad
                                        ) ?>

                                    </td>


                                    <!-- Acciones -->

                                    <td class="py-3 text-end pe-4">

                                        <?php if (
                                            $idGrupo !== false
                                        ): ?>

                                            <div
                                                class="d-flex flex-wrap
                                                       justify-content-end
                                                       gap-2"
                                            >


                                                <!-- Ver detalle -->

                                                <a
                                                    href="/grupos/<?= (int) $idGrupo ?>"
                                                    class="btn btn-outline-secondary btn-sm"
                                                >
                                                    Ver detalle
                                                </a>


                                                <!-- Editar grupo -->

                                                <a
                                                    href="/grupos/<?= (int) $idGrupo ?>/editar"
                                                    class="btn btn-outline-primary btn-sm"
                                                >
                                                    Editar
                                                </a>


                                                <!-- Retirar grupo -->

                                                <form
                                                    method="POST"
                                                    action="/grupos/<?= (int) $idGrupo ?>/retirar"
                                                    class="m-0"
                                                    onsubmit="return confirm('¿Desea retirar este grupo? Si tiene semilleros activos, el sistema no permitirá retirarlo.');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="csrf"
                                                        value="<?= plantilla_escapar(
                                                            $tokenFormulario
                                                        ) ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-outline-danger btn-sm"
                                                    >
                                                        Retirar
                                                    </button>

                                                </form>

                                            </div>

                                        <?php else: ?>

                                            <span class="text-body-secondary small">
                                                Sin acciones disponibles
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

        <?php if ($listaGrupos !== []): ?>

            <div class="card-footer bg-white p-3">

                <div
                    class="d-flex flex-wrap
                           justify-content-between
                           align-items-center gap-2"
                >

                    <span class="small text-body-secondary">

                        Mostrando <?= $totalGrupos ?>
                        grupo(s) de investigación.

                    </span>

                    <a
                        href="/grupos"
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

        <strong>Grupos y semilleros:</strong>

        los semilleros pertenecen a un grupo de investigación.

        Utilice <strong>Ver detalle</strong> para consultar
        los semilleros asociados a cada grupo.

        Un grupo con semilleros activos no puede retirarse.

    </div>

</section>