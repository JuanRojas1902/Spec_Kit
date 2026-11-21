<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: grupos_linea_lista.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Pantalla para consultar las relaciones entre
 * grupos y lineas de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite visualizar las asignaciones existentes
 * en la tabla puente grupo_linea.
 *
 * CLAVE FORANEA 1:
 * grupo_linea.grupo_id
 *             -> grupo_investigacion.id
 *
 * CLAVE FORANEA 2:
 * grupo_linea.linea_id
 *             -> linea_investigacion.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 * PRIMARY KEY (grupo_id, linea_id)
 *
 * OPERACIONES:
 * GET: consultar las relaciones.
 * POST: crear una nueva relacion.
 * DELETE: retirar una relacion existente.
 *
 * PUT Y PATCH:
 * No estan permitidos.
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No se conecta directamente a MariaDB.
 * - No realiza peticiones HTTP.
 * - No elimina grupos ni lineas de investigacion.
 *
 * DATOS RECIBIDOS DESDE index.php:
 *
 * $registros: relaciones de grupo_linea.
 * $opciones1: grupos de investigacion.
 * $opciones2: lineas de investigacion.
 * $csrf: token de seguridad.
 *
 * El enrutador procesa las operaciones y utiliza
 * cliente_api.php para comunicarse con la API.
 * =========================================================
 */


// =========================================================
// 1. FUNCION LOCAL PARA EXTRAER LAS FILAS
//
// Soporta una lista directa o un sobre de respuesta
// con las claves "respuesta" y "datos".
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
// 2. RECUPERAR LAS RELACIONES GRUPO-LINEA
// =========================================================

$listaRelaciones = $extraerFilas(
    $registros ?? []
);


// =========================================================
// 3. RECUPERAR LOS GRUPOS
//
// CLAVE FORANEA:
// grupo_linea.grupo_id
//        -> grupo_investigacion.id
// =========================================================

$listaGrupos = $extraerFilas(
    $opciones1 ?? ($grupos ?? [])
);


// =========================================================
// 4. RECUPERAR LAS LINEAS DE INVESTIGACION
//
// CLAVE FORANEA:
// grupo_linea.linea_id
//        -> linea_investigacion.id
// =========================================================

$listaLineas = $extraerFilas(
    $opciones2 ?? ($lineas ?? [])
);


// =========================================================
// 5. CREAR DICCIONARIO DE GRUPOS
//
// Ejemplo:
// 1 => COL0001
// =========================================================

$codigosGrupos = [];

foreach ($listaGrupos as $grupo) {

    $id = filter_var(
        $grupo['id'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => ['min_range' => 1]
        ]
    );

    if ($id === false) {
        continue;
    }

    $gruplac = trim(
        (string) ($grupo['gruplac'] ?? '')
    );

    if ($gruplac !== '') {

        $codigosGrupos[(string) $id] =
            $gruplac;
    }
}


// =========================================================
// 6. CREAR DICCIONARIO DE LINEAS
//
// Ejemplo:
// 1 => Desarrollo de Software
// =========================================================

$nombresLineas = [];

foreach ($listaLineas as $linea) {

    $id = filter_var(
        $linea['id'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => ['min_range' => 1]
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


// =========================================================
// 7. CONTAR LAS RELACIONES
// =========================================================

$totalRelaciones = count($listaRelaciones);


// =========================================================
// 8. TOKEN CSRF
// =========================================================

$tokenFormulario = isset($csrf)
    && is_string($csrf)
        ? $csrf
        : '';


// =========================================================
// 9. VERIFICAR ERRORES DE CONSULTA
// =========================================================

$errorConsulta = (
    isset($registros) &&
    is_array($registros) &&
    array_key_exists('ok', $registros) &&
    $registros['ok'] === false
);

$datosNoRecibidos = !isset($registros);

$hayErrores = isset($erroresValidacion)
    && is_array($erroresValidacion)
    && $erroresValidacion !== [];


// =========================================================
// 10. HABILITAR ACCIONES DE ESCRITURA
// =========================================================

$accionesHabilitadas =
    ($puedeGuardar ?? true) === true
    && $tokenFormulario !== '';

?>


<!-- =====================================================
     11. ENCABEZADO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap justify-content-between
               align-items-center gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">

                Grupos por línea de investigación

            </h2>

            <p class="text-body-secondary mb-0">

                Consulte las líneas de investigación
                asociadas a cada grupo.

            </p>

        </div>

        <?php if ($accionesHabilitadas): ?>

            <a
                href="/grupo_linea/nueva"
                class="btn btn-primary"
            >

                + Nueva asignación

            </a>

        <?php endif; ?>

    </div>

</section>


<!-- =====================================================
     12. INFORMACION DE CLAVES FORANEAS
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Claves foráneas:</strong>

        <ul class="mb-0 mt-2">

            <li>

                <code>grupo_id</code>

                hace referencia a

                <code>grupo_investigacion.id</code>.

            </li>

            <li>

                <code>linea_id</code>

                hace referencia a

                <code>linea_investigacion.id</code>.

            </li>

        </ul>

        <p class="mb-0 mt-2">

            Cada pareja grupo–línea debe ser única.

        </p>

    </div>

</section>


<!-- =====================================================
     13. TARJETA DE RESUMEN
     ===================================================== -->

<section class="mb-4">

    <div class="row g-3">

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <p class="text-body-secondary mb-2">

                        Relaciones registradas

                    </p>

                    <h3
                        class="display-6 fw-bold
                               text-primary mb-1"
                    >

                        <?= $totalRelaciones ?>

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
     14. LISTADO PRINCIPAL
     ===================================================== -->

<section>

    <div class="card border-0 shadow-sm">

        <div
            class="card-header bg-white p-3
                   d-flex flex-wrap justify-content-between
                   align-items-center gap-2"
        >

            <h3 class="h5 fw-semibold mb-0">

                Listado de grupos y líneas

            </h3>

            <span class="badge text-bg-primary">

                <?= $totalRelaciones ?> registros

            </span>

        </div>


        <div class="card-body p-0">


            <!-- =========================================
                 15. ERROR DE CONSULTA
                 ========================================= -->

            <?php if (
                $listaRelaciones === [] &&
                (
                    $errorConsulta ||
                    $datosNoRecibidos ||
                    $hayErrores
                )
            ): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No se pudo cargar el listado

                    </h4>

                    <p class="text-body-secondary">

                        Revise la conexión con la API
                        y los mensajes de error.

                    </p>

                    <a
                        href="/grupo_linea"
                        class="btn btn-outline-primary"
                    >

                        Intentar nuevamente

                    </a>

                </div>


            <!-- =========================================
                 16. LISTADO VACIO
                 ========================================= -->

            <?php elseif ($listaRelaciones === []): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No hay relaciones registradas

                    </h4>

                    <p class="text-body-secondary">

                        No se encontraron asociaciones
                        entre grupos y líneas.

                    </p>

                    <?php if ($accionesHabilitadas): ?>

                        <a
                            href="/grupo_linea/nueva"
                            class="btn btn-primary"
                        >

                            Crear primera asignación

                        </a>

                    <?php endif; ?>

                </div>


            <!-- =========================================
                 17. TABLA DE RELACIONES
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
                                    Grupo ID (FK)
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
                                    Código GRUPLAC
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
                                    Línea ID (FK)
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
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
                                //
                                // grupo_linea.grupo_id
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
                                // CLAVE FORANEA 2:
                                //
                                // grupo_linea.linea_id
                                //           ->
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
                                // Consultar los nombres
                                // dentro de los catalogos
                                // preparados.
                                // -------------------------

                                $codigoGrupo = 'No disponible';

                                if ($grupoId !== false) {

                                    $codigoGrupo =
                                        $codigosGrupos[
                                            (string) $grupoId
                                        ] ?? 'Grupo no disponible';
                                }


                                $nombreLinea = 'No disponible';

                                if ($lineaId !== false) {

                                    $nombreLinea =
                                        $nombresLineas[
                                            (string) $lineaId
                                        ] ?? 'Línea no disponible';
                                }


                                // Comprobar la pareja.

                                $clavesValidas =
                                    $grupoId !== false
                                    && $lineaId !== false;

                                ?>


                                <tr>


                                    <!-- Identificador del grupo -->

                                    <td class="ps-4 py-3 fw-semibold">

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


                                    <!-- Identificador de linea -->

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


                                    <!-- Acciones -->

                                    <td class="py-3 text-end pe-4">

                                        <?php if (
                                            $clavesValidas &&
                                            $accionesHabilitadas
                                        ): ?>

                                            <form
                                                method="POST"
                                                action="/grupo_linea/retirar"
                                                class="m-0"
                                                onsubmit="return confirm('¿Retirar esta relación? El grupo y la línea permanecerán registrados.');"
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
                                                    name="grupo_id"
                                                    value="<?= (int) $grupoId ?>"
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

                                                Solo consulta

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
             18. PIE DEL LISTADO
             ========================================= -->

        <?php if ($listaRelaciones !== []): ?>

            <div
                class="card-footer bg-white p-3
                       d-flex flex-wrap justify-content-between
                       align-items-center gap-2"
            >

                <span class="small text-body-secondary">

                    Mostrando <?= $totalRelaciones ?>
                    relación(es).

                </span>

                <a
                    href="/grupo_linea"
                    class="btn btn-outline-secondary btn-sm"
                >

                    Actualizar listado

                </a>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- =====================================================
     19. NOTA SOBRE INTEGRIDAD REFERENCIAL
     ===================================================== -->

<section class="mt-4">

    <div class="alert alert-warning" role="note">

        <strong>Importante:</strong>

        retirar una relación elimina únicamente
        la pareja de la tabla puente

        <code>grupo_linea</code>.

        El grupo y la línea de investigación
        permanecen registrados.

        Esta tabla no tiene columna
        <code>activo</code>
        y no permite editar parejas con
        PUT ni PATCH.

    </div>

</section>