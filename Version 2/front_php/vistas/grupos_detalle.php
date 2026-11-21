<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: grupos_detalle.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Pantalla de consulta de un grupo de investigacion
 * junto con sus semilleros.
 *
 * POR QUE ESTA AQUI:
 * Permite visualizar el patron maestro-detalle
 * utilizado en la Version 2.
 *
 * CLAVES FORANEAS:
 *
 * 1. grupo_investigacion.universidad_id
 *                      ->
 *                     universidad.id
 *
 * 2. semillero.grupo_id
 *                      ->
 *                     grupo_investigacion.id
 *
 * QUE NO HACE:
 * - No ejecuta SQL.
 * - No modifica registros.
 * - No consulta MariaDB directamente.
 * - No realiza peticiones HTTP.
 * - No elimina semilleros.
 *
 * DATOS:
 * index.php obtiene el grupo desde cliente_api.php
 * y prepara las variables de la vista.
 *
 * La API devuelve el grupo y un arreglo "detalle"
 * que contiene los semilleros asociados.
 * =========================================================
 */


// =========================================================
// 1. RECUPERAR LA FICHA DEL GRUPO
//
// El enrutador principal prepara $valores.
// Se admiten tambien estructuras con "grupo"
// para facilitar la lectura de la respuesta.
// =========================================================

$fichaGrupo = [];

if (isset($valores) && is_array($valores)) {

    $fichaGrupo = $valores;
}

if (
    $fichaGrupo === [] &&
    isset($grupo) &&
    is_array($grupo)
) {

    $fichaGrupo = $grupo;
}


// =========================================================
// 2. COMPROBAR SI LA RESPUESTA VIENE ENVUELTA
// =========================================================

if (
    isset($fichaGrupo['grupo']) &&
    is_array($fichaGrupo['grupo'])
) {

    $fichaGrupo = $fichaGrupo['grupo'];
}

if (
    isset($fichaGrupo['datos']) &&
    is_array($fichaGrupo['datos']) &&
    isset($fichaGrupo['datos']['id'])
) {

    $fichaGrupo = $fichaGrupo['datos'];
}


// =========================================================
// 3. PREPARAR LOS CAMPOS DEL GRUPO
// =========================================================

$idGrupo = filter_var(
    $fichaGrupo['id'] ?? null,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1
        ]
    ]
);

$gruplac = $fichaGrupo['gruplac'] ?? '';

$categoria = $fichaGrupo['categoria'] ?? '';

$convocatoria = $fichaGrupo['convocatoria'] ?? '';

$ambito = $fichaGrupo['ambito'] ?? '';

$universidadId = $fichaGrupo['universidad_id'] ?? '';


// =========================================================
// 4. OBTENER EL NOMBRE DE LA UNIVERSIDAD
//
// CLAVE FORANEA:
// grupo_investigacion.universidad_id
//                  ->
// universidad.id
//
// Las universidades fueron consultadas previamente
// por index.php utilizando la API.
// =========================================================

$nombreUniversidad = '';

if (
    isset($universidades) &&
    is_array($universidades)
) {

    foreach ($universidades as $universidad) {

        if (!is_array($universidad)) {
            continue;
        }

        $idActual = $universidad['id'] ?? null;

        if (
            $idActual !== null &&
            (string) $idActual === (string) $universidadId
        ) {

            $nombreUniversidad = (string) (
                $universidad['nombre'] ?? ''
            );

            break;
        }
    }
}


// Si la universidad no aparece en el catalogo,
// mostramos su identificador sin inventar el nombre.

if ($nombreUniversidad === '') {

    $nombreUniversidad = $universidadId !== ''
        ? 'Universidad ID ' . $universidadId
        : 'No disponible';
}


// =========================================================
// 5. RECUPERAR LOS SEMILLEROS DEL GRUPO
//
// CLAVE FORANEA:
// semillero.grupo_id
//          ->
// grupo_investigacion.id
//
// Los semilleros se reciben en "detalle".
// =========================================================

$listaSemilleros = [];

$detalleRecibido = $fichaGrupo['detalle'] ?? [];

if (is_array($detalleRecibido)) {

    foreach ($detalleRecibido as $semillero) {

        if (is_array($semillero)) {

            $listaSemilleros[] = $semillero;
        }
    }
}


// =========================================================
// 6. CONTAR LOS SEMILLEROS
// =========================================================

$totalSemilleros = count($listaSemilleros);


// =========================================================
// 7. COMPROBAR SI LA FICHA ESTA DISPONIBLE
// =========================================================

$fichaDisponible = $idGrupo !== false;

?>


<!-- =====================================================
     8. ENCABEZADO DE LA PANTALLA
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap align-items-center
               justify-content-between gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">

                Detalle del grupo de investigación

            </h2>

            <p class="text-body-secondary mb-0">

                Consulte la información del grupo
                y sus semilleros asociados.

            </p>

        </div>

        <div class="d-flex flex-wrap gap-2">

            <a
                href="/grupos"
                class="btn btn-outline-secondary"
            >
                Volver al listado
            </a>

            <?php if ($fichaDisponible): ?>

                <a
                    href="/grupos/<?= (int) $idGrupo ?>/editar"
                    class="btn btn-primary"
                >
                    Editar grupo
                </a>

            <?php endif; ?>

        </div>

    </div>

</section>


<?php if (!$fichaDisponible): ?>

    <!-- =================================================
         9. GRUPO NO DISPONIBLE
         ================================================= -->

    <div class="alert alert-warning" role="alert">

        <strong>No se pudo mostrar el grupo.</strong>

        La ficha solicitada no existe, fue retirada
        o no se pudo recuperar desde la API.

    </div>

<?php else: ?>


    <!-- =================================================
         10. RESUMEN
         ================================================= -->

    <section class="mb-4">

        <div class="row g-3">

            <div class="col-12 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body p-4">

                        <p class="text-body-secondary mb-2">

                            Grupo de investigación

                        </p>

                        <h3 class="h4 fw-bold text-primary mb-1">

                            <?= plantilla_escapar($gruplac) ?>

                        </h3>

                        <p class="small text-body-secondary mb-0">

                            ID:
                            <?= (int) $idGrupo ?>

                        </p>

                    </div>

                </div>

            </div>


            <div class="col-12 col-md-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body p-4">

                        <p class="text-body-secondary mb-2">

                            Semilleros asociados

                        </p>

                        <h3 class="display-6 fw-bold text-primary mb-1">

                            <?= $totalSemilleros ?>

                        </h3>

                        <p class="small text-body-secondary mb-0">

                            Registros recibidos en el detalle.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- =================================================
         11. INFORMACION DEL GRUPO
         ================================================= -->

    <section class="mb-4">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white p-3">

                <h3 class="h5 fw-bold mb-0">

                    Información del grupo

                </h3>

            </div>


            <div class="card-body p-4">

                <div class="row g-4">


                    <!-- ID -->

                    <div class="col-12 col-md-4">

                        <p class="small text-body-secondary mb-1">
                            Identificador
                        </p>

                        <p class="fw-semibold mb-0">

                            <?= (int) $idGrupo ?>

                        </p>

                    </div>


                    <!-- GRUPLAC -->

                    <div class="col-12 col-md-8">

                        <p class="small text-body-secondary mb-1">
                            Código GRUPLAC
                        </p>

                        <p class="fw-semibold mb-0">

                            <?= plantilla_escapar($gruplac) ?>

                        </p>

                    </div>


                    <!-- CATEGORIA -->

                    <div class="col-12 col-md-4">

                        <p class="small text-body-secondary mb-1">
                            Categoría
                        </p>

                        <p class="fw-semibold mb-0">

                            <?= plantilla_escapar($categoria) ?>

                        </p>

                    </div>


                    <!-- CONVOCATORIA -->

                    <div class="col-12 col-md-8">

                        <p class="small text-body-secondary mb-1">
                            Convocatoria
                        </p>

                        <p class="fw-semibold mb-0">

                            <?= plantilla_escapar($convocatoria) ?>

                        </p>

                    </div>


                    <!-- AMBITO -->

                    <div class="col-12 col-md-6">

                        <p class="small text-body-secondary mb-1">
                            Ámbito
                        </p>

                        <p class="fw-semibold mb-0">

                            <?= plantilla_escapar($ambito) ?>

                        </p>

                    </div>


                    <!-- =================================
                         12. UNIVERSIDAD (FK)
                         ================================= -->

                    <div class="col-12 col-md-6">

                        <p class="small text-body-secondary mb-1">
                            Universidad asociada
                        </p>

                        <p class="fw-semibold mb-1">

                            <?= plantilla_escapar(
                                $nombreUniversidad
                            ) ?>

                        </p>

                        <p class="small text-body-secondary mb-0">

                            Clave foránea:

                            <code>universidad_id</code>

                            =

                            <?= plantilla_escapar(
                                $universidadId
                            ) ?>

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- =================================================
         13. LISTADO DE SEMILLEROS (DETALLE)
         ================================================= -->

    <section class="mb-4">

        <div class="card border-0 shadow-sm">

            <div
                class="card-header bg-white p-3
                       d-flex flex-wrap align-items-center
                       justify-content-between gap-2"
            >

                <h3 class="h5 fw-bold mb-0">

                    Semilleros del grupo

                </h3>

                <span class="badge text-bg-primary">

                    <?= $totalSemilleros ?> registros

                </span>

            </div>


            <div class="card-body p-0">


                <?php if ($listaSemilleros === []): ?>

                    <!-- No hay semilleros -->

                    <div class="text-center p-5">

                        <h4 class="h5 fw-semibold">

                            Sin semilleros asociados

                        </h4>

                        <p class="text-body-secondary mb-0">

                            Este grupo no tiene semilleros
                            activos en su detalle.

                        </p>

                    </div>

                <?php else: ?>

                    <!-- Tabla de semilleros -->

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
                                        Grupo asociado (FK)
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach (
                                    $listaSemilleros as $semillero
                                ): ?>

                                    <?php

                                    $idSemillero =
                                        $semillero['id'] ?? '';

                                    $nombreSemillero =
                                        $semillero['nombre'] ?? '';

                                    // El detalle pertenece al grupo
                                    // que estamos consultando.
                                    //
                                    // Si la API incluye grupo_id,
                                    // utilizamos ese valor.
                                    //
                                    // Si no lo incluye, mostramos
                                    // el ID del grupo padre.

                                    $grupoRelacionado =
                                        $semillero['grupo_id']
                                        ?? $idGrupo;

                                    ?>

                                    <tr>

                                        <td class="ps-4 py-3">

                                            <?= plantilla_escapar(
                                                $idSemillero
                                            ) ?>

                                        </td>

                                        <td class="py-3 fw-semibold">

                                            <?= plantilla_escapar(
                                                $nombreSemillero
                                            ) ?>

                                        </td>

                                        <td class="py-3">

                                            <?= plantilla_escapar(
                                                $grupoRelacionado
                                            ) ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </section>


    <!-- =================================================
         14. EXPLICACION DE LA RELACION MAESTRO-DETALLE
         ================================================= -->

    <section class="mb-4">

        <div class="alert alert-info" role="note">

            <strong>Relación maestro-detalle:</strong>

            el grupo de investigación es el registro
            maestro y sus semilleros son los registros
            de detalle.

            Cada semillero utiliza la clave foránea

            <code>grupo_id</code>

            para relacionarse con su grupo.

            <hr>

            Los cambios en el grupo y sus semilleros
            deben realizarse desde el formulario
            de edición del grupo.

        </div>

    </section>

<?php endif; ?>