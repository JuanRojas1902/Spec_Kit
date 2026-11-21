<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: docentes_semillero_lista.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Listado de relaciones entre docentes y semilleros.
 *
 * POR QUE ESTA AQUI:
 * Permite consultar las asignaciones registradas
 * en la tabla puente participa_semillero.
 *
 * CLAVES FORANEAS:
 *
 * participa_semillero.docente_cedula -> docente.cedula
 * participa_semillero.semillero_id   -> semillero.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 * (docente_cedula, semillero_id)
 *
 * OPERACIONES PERMITIDAS:
 * GET    - Consultar relaciones.
 * POST   - Crear una relacion.
 * DELETE - Retirar una relacion.
 *
 * PUT y PATCH no estan permitidos.
 *
 * QUE NO HACE:
 * - No ejecuta SQL.
 * - No consulta MariaDB directamente.
 * - No realiza peticiones HTTP.
 * - No elimina docentes ni semilleros.
 *
 * DATOS QUE ENTREGA index.php:
 * $registros  - Relaciones.
 * $opciones1  - Docentes.
 * $opciones2  - Semilleros.
 * $csrf       - Token de seguridad.
 * =========================================================
 */


// =========================================================
// 1. NORMALIZAR LAS RESPUESTAS DE LA API
//
// Acepta listados directos o respuestas que contienen
// las filas dentro de "datos" o "respuesta".
// =========================================================

$extraerFilas = static function (mixed $origen): array {

    for ($nivel = 0; $nivel < 4; $nivel++) {

        if (!is_array($origen)) {
            return [];
        }

        if (array_is_list($origen)) {
            break;
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

    $filas = [];

    foreach ($origen as $fila) {

        if (is_array($fila)) {
            $filas[] = $fila;
        }
    }

    return $filas;
};


// =========================================================
// 2. RECUPERAR LAS RELACIONES DOCENTE-SEMILLERO
// =========================================================

$listaRelaciones = $extraerFilas(
    $registros ?? []
);


// =========================================================
// 3. RECUPERAR EL CATALOGO DE DOCENTES
//
// FK: participa_semillero.docente_cedula
//              -> docente.cedula
// =========================================================

$listaDocentes = $extraerFilas(
    $opciones1 ?? []
);


// =========================================================
// 4. RECUPERAR EL CATALOGO DE SEMILLEROS
//
// FK: participa_semillero.semillero_id
//              -> semillero.id
// =========================================================

$listaSemilleros = $extraerFilas(
    $opciones2 ?? []
);


// =========================================================
// 5. CREAR DICCIONARIO DE DOCENTES
//
// Ejemplo:
// 1017245 => "Ana Torres Gomez"
// =========================================================

$nombresDocentes = [];

foreach ($listaDocentes as $docente) {

    $cedula = filter_var(
        $docente['cedula'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
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
// 6. CREAR DICCIONARIO DE SEMILLEROS
//
// Ejemplo:
// 1 => "Semillero de Vision Artificial"
// =========================================================

$nombresSemilleros = [];

foreach ($listaSemilleros as $semillero) {

    $idSemillero = filter_var(
        $semillero['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($idSemillero === false) {
        continue;
    }

    $nombre = trim(
        (string) ($semillero['nombre'] ?? '')
    );

    if ($nombre !== '') {

        $nombresSemilleros[(string) $idSemillero] =
            $nombre;
    }
}


// =========================================================
// 7. PREPARAR DATOS PARA LA PANTALLA
// =========================================================

$totalRelaciones = count($listaRelaciones);

$tokenFormulario = isset($csrf) && is_string($csrf)
    ? $csrf
    : '';


// Detectar errores de consulta cuando index.php entrega
// el resultado completo de cliente_api.php.

$errorConsulta = (
    isset($registros) &&
    is_array($registros) &&
    array_key_exists('ok', $registros) &&
    $registros['ok'] === false
);


// Detectar cuando el enrutador no entrego registros.

$datosNoRecibidos = !isset($registros);


// Los errores de validacion se muestran en plantilla.php.

$hayErrores = (
    isset($erroresValidacion) &&
    is_array($erroresValidacion) &&
    $erroresValidacion !== []
);

?>


<!-- =====================================================
     8. ENCABEZADO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3">

        <div>

            <h2 class="h4 fw-bold mb-2">
                Docentes por semillero
            </h2>

            <p class="text-body-secondary mb-0">
                Consulte las relaciones entre docentes
                y semilleros de investigación.
            </p>

        </div>

        <a
            href="/participa_semillero/nueva"
            class="btn btn-primary"
        >
            + Nueva asignación
        </a>

    </div>

</section>


<!-- =====================================================
     9. INFORMACION SOBRE CLAVES FORANEAS
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Claves foráneas de participa_semillero:</strong>

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
            Una misma pareja docente–semillero
            no puede registrarse dos veces.
        </p>

    </div>

</section>


<!-- =====================================================
     10. RESUMEN
     ===================================================== -->

<section class="mb-4">

    <div class="row g-3">

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <p class="text-body-secondary mb-2">
                        Asignaciones del listado
                    </p>

                    <h3 class="display-6 fw-bold
                               text-primary mb-1">
                        <?= $totalRelaciones ?>
                    </h3>

                    <p class="small text-body-secondary mb-0">
                        Relaciones recibidas desde la API.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     11. TABLA DE RELACIONES
     ===================================================== -->

<section>

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-3
                    d-flex flex-wrap justify-content-between
                    align-items-center gap-2">

            <h3 class="h5 fw-semibold mb-0">
                Listado de participaciones
            </h3>

            <span class="badge text-bg-primary">
                <?= $totalRelaciones ?> registros
            </span>

        </div>

        <div class="card-body p-0">


            <!-- =========================================
                 12. ERROR AL OBTENER DATOS
                 ========================================= -->

            <?php if (
                $listaRelaciones === [] &&
                ($errorConsulta || $datosNoRecibidos || $hayErrores)
            ): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">
                        No se pudo cargar el listado
                    </h4>

                    <p class="text-body-secondary">
                        Revise la conexión con la API
                        y los mensajes de la página.
                    </p>

                    <a
                        href="/participa_semillero"
                        class="btn btn-outline-primary"
                    >
                        Intentar nuevamente
                    </a>

                </div>


            <!-- =========================================
                 13. LISTADO VACIO
                 ========================================= -->

            <?php elseif ($listaRelaciones === []): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">
                        No hay asignaciones registradas
                    </h4>

                    <p class="text-body-secondary">
                        Todavía no existen relaciones
                        entre docentes y semilleros.
                    </p>

                    <a
                        href="/participa_semillero/nueva"
                        class="btn btn-primary"
                    >
                        Crear primera asignación
                    </a>

                </div>


            <!-- =========================================
                 14. TABLA PRINCIPAL
                 ========================================= -->

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-striped
                                  table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th scope="col" class="ps-4 py-3">
                                    Cédula (FK)
                                </th>

                                <th scope="col" class="py-3">
                                    Docente
                                </th>

                                <th scope="col" class="py-3">
                                    Semillero (FK)
                                </th>

                                <th scope="col" class="py-3">
                                    Nombre del semillero
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
                                // CLAVE FORANEA 1
                                //
                                // participa_semillero.docente_cedula
                                //        -> docente.cedula
                                // -------------------------

                                $cedula = filter_var(
                                    $relacion['docente_cedula'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    ['options' => ['min_range' => 1]]
                                );


                                // -------------------------
                                // CLAVE FORANEA 2
                                //
                                // participa_semillero.semillero_id
                                //        -> semillero.id
                                // -------------------------

                                $semilleroId = filter_var(
                                    $relacion['semillero_id'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    ['options' => ['min_range' => 1]]
                                );


                                // Obtener nombre del docente.

                                $nombreDocente = 'No disponible';

                                if ($cedula !== false) {

                                    $nombreDocente =
                                        $nombresDocentes[
                                            (string) $cedula
                                        ] ?? 'Docente no disponible';
                                }


                                // Obtener nombre del semillero.

                                $nombreSemillero = 'No disponible';

                                if ($semilleroId !== false) {

                                    $nombreSemillero =
                                        $nombresSemilleros[
                                            (string) $semilleroId
                                        ] ?? 'Semillero no disponible';
                                }


                                // Verificar las dos claves.

                                $clavesValidas =
                                    $cedula !== false &&
                                    $semilleroId !== false;

                                ?>

                                <tr>

                                    <!-- Cedula -->

                                    <td class="ps-4 py-3 fw-semibold">

                                        <?= $cedula !== false
                                            ? (int) $cedula
                                            : 'No disponible' ?>

                                    </td>


                                    <!-- Nombre del docente -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombreDocente
                                        ) ?>

                                    </td>


                                    <!-- ID del semillero -->

                                    <td class="py-3">

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


                                    <!-- Retirar la relacion -->

                                    <td class="py-3 text-end pe-4">

                                        <?php if ($clavesValidas): ?>

                                            <form
                                                method="POST"
                                                action="/participa_semillero/retirar"
                                                class="m-0"
                                                onsubmit="return confirm('¿Retirar esta relación? El docente y el semillero no serán eliminados.');"
                                            >

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
                                                    name="docente_cedula"
                                                    value="<?= (int) $cedula ?>"
                                                >

                                                <!-- Segunda FK -->

                                                <input
                                                    type="hidden"
                                                    name="semillero_id"
                                                    value="<?= (int) $semilleroId ?>"
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
             15. PIE DEL LISTADO
             ========================================= -->

        <?php if ($listaRelaciones !== []): ?>

            <div class="card-footer bg-white p-3
                        d-flex flex-wrap align-items-center
                        justify-content-between gap-2">

                <span class="small text-body-secondary">

                    Mostrando <?= $totalRelaciones ?>
                    relación(es).

                </span>

                <a
                    href="/participa_semillero"
                    class="btn btn-outline-secondary btn-sm"
                >
                    Actualizar listado
                </a>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- =====================================================
     16. INFORMACION SOBRE EL RETIRO
     ===================================================== -->

<section class="mt-4">

    <div class="alert alert-warning" role="note">

        <strong>Importante:</strong>

        retirar una asignación elimina solamente
        la pareja de la tabla
        <code>participa_semillero</code>.

        El docente y el semillero permanecen
        registrados en sus respectivas tablas.

        No existe edición mediante PUT o PATCH.

    </div>

</section>