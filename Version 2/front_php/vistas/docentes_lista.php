<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: docentes_lista.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Pantalla que presenta el listado de docentes
 * activos del modulo de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite consultar los docentes y acceder
 * a las opciones de registrar, editar y retirar.
 *
 * QUE NO HACE:
 * - No se conecta directamente a MariaDB.
 * - No ejecuta consultas SQL.
 * - No realiza peticiones HTTP.
 * - No modifica registros por si misma.
 * - No implementa reglas de negocio.
 *
 * FUNCIONAMIENTO:
 * 1. index.php llama a listar_docentes().
 * 2. cliente_api.php consulta GET /api/docente.
 * 3. index.php prepara la variable $registros.
 * 4. Esta vista muestra los resultados.
 *
 * SEGURIDAD:
 * - Los datos se escapan con htmlspecialchars.
 * - Retirar utiliza POST y token CSRF.
 * - No se utilizan enlaces GET para modificar datos.
 * =========================================================
 */


// =========================================================
// 1. PREPARAR LA LISTA DE DOCENTES
//
// Los datos fueron obtenidos previamente por index.php.
// =========================================================

$docentes = [];

if (
    isset($registros) &&
    is_array($registros)
) {

    foreach ($registros as $registro) {

        if (is_array($registro)) {

            $docentes[] = $registro;
        }
    }
}


// =========================================================
// 2. CONTAR LOS REGISTROS RECIBIDOS
// =========================================================

$totalDocentes = count($docentes);


// =========================================================
// 3. PREPARAR EL TOKEN DE SEGURIDAD
//
// El token es generado por index.php.
// =========================================================

$tokenFormulario = '';

if (
    isset($csrf) &&
    is_string($csrf)
) {

    $tokenFormulario = $csrf;
}


// =========================================================
// 4. DETERMINAR SI LA CONSULTA PRESENTO ERRORES
// =========================================================

$hayErrores = isset($erroresValidacion)
    && is_array($erroresValidacion)
    && $erroresValidacion !== [];

?>


<!-- =====================================================
     5. ENCABEZADO DEL MODULO DE DOCENTES
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap justify-content-between
               align-items-center gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">

                Administración de docentes

            </h2>

            <p class="text-body-secondary mb-0">

                Consulte y administre los docentes
                registrados en el sistema de investigación.

            </p>

        </div>


        <!-- Boton para registrar un nuevo docente -->

        <a
            href="/docentes/nuevo"
            class="btn btn-primary"
        >

            + Registrar docente

        </a>

    </div>

</section>


<!-- =====================================================
     6. TARJETA DE RESUMEN
     ===================================================== -->

<section class="mb-4">

    <div class="row g-3">

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <p class="text-body-secondary mb-2">

                        Docentes registrados en el listado

                    </p>

                    <h3 class="display-6 fw-bold text-primary mb-1">

                        <?= $totalDocentes ?>

                    </h3>

                    <p class="text-body-secondary small mb-0">

                        Registros recibidos desde la API

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     7. TABLA PRINCIPAL
     ===================================================== -->

<section>

    <div class="card border-0 shadow-sm">

        <!-- Encabezado de la tarjeta -->

        <div
            class="card-header bg-white border-bottom
                   d-flex flex-wrap justify-content-between
                   align-items-center gap-2 p-3"
        >

            <h3 class="h5 fw-semibold mb-0">

                Listado de docentes

            </h3>

            <span class="badge text-bg-primary">

                <?= $totalDocentes ?> registros

            </span>

        </div>


        <div class="card-body p-0">


            <!-- =========================================
                 8. MENSAJE DE LISTADO VACIO
                 ========================================= -->

            <?php if ($docentes === [] && !$hayErrores): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        Todavía no hay datos

                    </h4>

                    <p class="text-body-secondary">

                        No se encontraron docentes activos
                        para mostrar en este momento.

                    </p>

                    <a
                        href="/docentes/nuevo"
                        class="btn btn-primary mt-2"
                    >

                        Registrar primer docente

                    </a>

                </div>


            <!-- =========================================
                 9. MENSAJE DE ERROR DE CONSULTA
                 ========================================= -->

            <?php elseif ($docentes === [] && $hayErrores): ?>

                <div class="text-center p-5">

                    <h4 class="h5 fw-semibold">

                        No se pudo cargar el listado

                    </h4>

                    <p class="text-body-secondary">

                        Revise el mensaje de error
                        que aparece en la parte superior.

                    </p>

                    <a
                        href="/docentes"
                        class="btn btn-outline-primary mt-2"
                    >

                        Intentar nuevamente

                    </a>

                </div>


            <!-- =========================================
                 10. MOSTRAR LOS DOCENTES
                 ========================================= -->

            <?php else: ?>

                <div class="table-responsive">

                    <table
                        class="table table-striped
                               table-hover align-middle mb-0"
                    >

                        <!-- Encabezados de columnas -->

                        <thead class="table-light">

                            <tr>

                                <th
                                    scope="col"
                                    class="px-4 py-3"
                                >
                                    Cédula
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
                                    Nombres
                                </th>

                                <th
                                    scope="col"
                                    class="py-3"
                                >
                                    Apellidos
                                </th>

                                <th
                                    scope="col"
                                    class="py-3 text-end pe-4"
                                >
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <!-- Registros recibidos desde la API -->

                        <tbody>

                            <?php foreach ($docentes as $docente): ?>

                                <?php

                                // Recuperar los campos del docente.

                                $cedula = filter_var(
                                    $docente['cedula'] ?? null,
                                    FILTER_VALIDATE_INT,
                                    [
                                        'options' => [
                                            'min_range' => 1
                                        ]
                                    ]
                                );

                                $nombres = $docente['nombres'] ?? '';

                                $apellidos = $docente['apellidos'] ?? '';

                                ?>

                                <tr>


                                    <!-- Cedula -->

                                    <td class="px-4 py-3 fw-semibold">

                                        <?= plantilla_escapar(
                                            $docente['cedula'] ?? ''
                                        ) ?>

                                    </td>


                                    <!-- Nombres -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $nombres
                                        ) ?>

                                    </td>


                                    <!-- Apellidos -->

                                    <td class="py-3">

                                        <?= plantilla_escapar(
                                            $apellidos
                                        ) ?>

                                    </td>


                                    <!-- Acciones -->

                                    <td class="py-3 text-end pe-4">

                                        <?php if ($cedula !== false): ?>

                                            <div
                                                class="d-flex flex-wrap
                                                       justify-content-end
                                                       gap-2"
                                            >


                                                <!-- Editar docente -->

                                                <a
                                                    href="/docentes/<?= (int) $cedula ?>/editar"
                                                    class="btn btn-outline-primary btn-sm"
                                                    aria-label="Editar docente <?= plantilla_escapar($cedula) ?>"
                                                >

                                                    Editar

                                                </a>


                                                <!-- Retirar docente -->

                                                <form
                                                    method="POST"
                                                    action="/docentes/<?= (int) $cedula ?>/retirar"
                                                    class="m-0"
                                                    onsubmit="return confirm('¿Está seguro de que desea retirar este docente?');"
                                                >

                                                    <!-- Token CSRF -->

                                                    <input
                                                        type="hidden"
                                                        name="csrf"
                                                        value="<?= plantilla_escapar($tokenFormulario) ?>"
                                                    >


                                                    <!-- Boton retirar -->

                                                    <button
                                                        type="submit"
                                                        class="btn btn-outline-danger btn-sm"
                                                        aria-label="Retirar docente <?= plantilla_escapar($cedula) ?>"
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


        <!-- =================================================
             11. PIE DE LA TARJETA
             ================================================= -->

        <?php if ($docentes !== []): ?>

            <div class="card-footer bg-white p-3">

                <div
                    class="d-flex flex-wrap justify-content-between
                           align-items-center gap-2"
                >

                    <span class="text-body-secondary small">

                        Mostrando <?= $totalDocentes ?>
                        registro(s) del listado.

                    </span>

                    <a
                        href="/docentes"
                        class="btn btn-outline-secondary btn-sm"
                    >

                        Actualizar listado

                    </a>

                </div>

            </div>

        <?php endif; ?>

    </div>

</section>