<?php

declare(strict_types=1);

/**
 * ARCHIVO: plantilla.php
 * CAPA: Frontend / Vista compartida
 *
 * QUE ES:
 * Plantilla HTML principal del Sistema de Investigación.
 *
 * POR QUE ESTA AQUI:
 * Permite mantener un diseño visual uniforme
 * en todas las pantallas de la Version 2.
 *
 * Contiene:
 * - Encabezado principal.
 * - Menu de navegacion de los 16 recursos.
 * - Informacion de la sesion.
 * - Boton para cerrar sesion.
 * - Mensajes de exito y error.
 * - Contenido de la vista seleccionada.
 * - Pie de pagina.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No realiza peticiones directas a la API.
 * No implementa reglas de negocio.
 * No verifica contrasenas.
 *
 * REQUISITOS:
 * - Bootstrap local.
 * - marca.css local.
 * - PHP 8.3 sin frameworks.
 * - Los datos visibles se escapan con htmlspecialchars.
 */


// =========================================================
// 1. FUNCION PARA MOSTRAR TEXTO DE FORMA SEGURA
//
// Evita que el contenido de las variables se interprete
// como codigo HTML.
// =========================================================

function plantilla_escapar(mixed $valor): string
{
    if (!is_scalar($valor) && $valor !== null) {
        return '';
    }

    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}


// =========================================================
// 2. COMPROBAR LA SESION ACTUAL
//
// El enrutador index.php es responsable de administrar
// y validar la sesion.
// =========================================================

$usuarioActual = $_SESSION['usuario'] ?? null;

$sesionActiva = is_string($usuarioActual)
    && $usuarioActual !== '';


// =========================================================
// 3. PREPARAR VARIABLES DE LA PLANTILLA
// =========================================================

$tituloPagina = isset($titulo) && is_string($titulo)
    ? $titulo
    : 'Sistema de Investigación';

$rutaActual = isset($ruta) && is_string($ruta)
    ? $ruta
    : '';

$mensajeExito = isset($mensajeAlerta)
    && is_string($mensajeAlerta)
        ? $mensajeAlerta
        : '';

$listaErrores = isset($erroresValidacion)
    && is_array($erroresValidacion)
        ? $erroresValidacion
        : [];

$tokenFormulario = isset($csrf) && is_string($csrf)
    ? $csrf
    : '';

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <!-- =================================================
         4. CONFIGURACION DEL DOCUMENTO
         ================================================= -->

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content="Sistema de Gestión de Investigación - Versión 2"
    >

    <title>
        <?= plantilla_escapar($tituloPagina) ?>
        | Sistema de Investigación
    </title>


    <!-- =================================================
         5. BOOTSTRAP LOCAL

         No utilizamos CDN.
         ================================================= -->

    <link
        rel="stylesheet"
        href="/publico/bootstrap.min.css"
    >


    <!-- =================================================
         6. IDENTIDAD VISUAL DEL PROYECTO
         ================================================= -->

    <link
        rel="stylesheet"
        href="/publico/marca.css"
    >

</head>

<body class="bg-body-tertiary d-flex flex-column min-vh-100">


    <!-- =================================================
         7. ENCABEZADO PRINCIPAL
         ================================================= -->

    <header>

        <nav
            class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm"
            aria-label="Navegación principal"
        >

            <div class="container-fluid px-lg-4">


                <!-- Nombre del sistema -->

                <a
                    class="navbar-brand fw-bold"
                    href="/"
                >
                    Sistema de Investigación
                    <span class="badge text-bg-light ms-1">
                        V2
                    </span>
                </a>


                <?php if ($sesionActiva): ?>

                    <!-- Informacion de la sesion -->

                    <div class="d-flex align-items-center gap-3">

                        <span class="text-white d-none d-md-inline">

                            Usuario:

                            <strong>
                                <?= plantilla_escapar($usuarioActual) ?>
                            </strong>

                        </span>


                        <!-- Cerrar sesion mediante POST -->

                        <form
                            action="/logout"
                            method="POST"
                            class="m-0"
                        >

                            <input
                                type="hidden"
                                name="csrf"
                                value="<?= plantilla_escapar($tokenFormulario) ?>"
                            >

                            <button
                                type="submit"
                                class="btn btn-outline-light btn-sm"
                            >
                                Cerrar sesión
                            </button>

                        </form>

                    </div>

                <?php endif; ?>

            </div>

        </nav>

    </header>


    <!-- =================================================
         8. CONTENEDOR PRINCIPAL
         ================================================= -->

    <div class="container-fluid px-3 px-lg-4 py-4 flex-grow-1">

        <div class="row g-4">


            <?php if ($sesionActiva): ?>


                <!-- =====================================
                     9. MENU LATERAL DE NAVEGACION
                     ===================================== -->

                <aside class="col-12 col-lg-3 col-xl-2">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body p-3">


                            <!-- Inicio -->

                            <nav aria-label="Secciones del sistema">

                                <a
                                    href="/"
                                    class="btn w-100 text-start mb-3
                                        <?= $rutaActual === ''
                                            ? 'btn-primary'
                                            : 'btn-outline-primary' ?>"
                                    <?= $rutaActual === ''
                                        ? 'aria-current="page"'
                                        : '' ?>
                                >
                                    Inicio
                                </a>


                                <!-- =====================
                                     CATALOGOS DE LA V1
                                     ===================== -->

                                <h2 class="h6 text-uppercase fw-bold mt-3 mb-2">
                                    Catálogos
                                </h2>

                                <div class="list-group list-group-flush mb-3">


                                    <a
                                        href="/area_conocimiento"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'area_conocimiento'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'area_conocimiento'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Áreas de conocimiento
                                    </a>


                                    <a
                                        href="/objetivo_desarrollo_sostenible"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'objetivo_desarrollo_sostenible'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'objetivo_desarrollo_sostenible'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Objetivos de desarrollo sostenible
                                    </a>


                                    <a
                                        href="/area_aplicacion"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'area_aplicacion'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'area_aplicacion'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Áreas de aplicación
                                    </a>


                                    <a
                                        href="/termino_clave"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'termino_clave'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'termino_clave'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Términos clave
                                    </a>


                                    <a
                                        href="/universidad"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'universidad'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'universidad'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Universidades
                                    </a>


                                    <a
                                        href="/linea_investigacion"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'linea_investigacion'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'linea_investigacion'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Líneas de investigación
                                    </a>

                                </div>


                                <!-- =====================
                                     RECURSOS PRINCIPALES
                                     DE LA V2
                                     ===================== -->

                                <h2 class="h6 text-uppercase fw-bold mt-4 mb-2">
                                    Investigación
                                </h2>

                                <div class="list-group list-group-flush mb-3">


                                    <a
                                        href="/docentes"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'docentes'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'docentes'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Docentes
                                    </a>


                                    <a
                                        href="/grupos"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'grupos'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'grupos'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Grupos de investigación
                                    </a>


                                    <a
                                        href="/semilleros"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'semilleros'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'semilleros'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Semilleros
                                    </a>

                                </div>


                                <!-- =====================
                                     SIETE TABLAS PUENTE
                                     ===================== -->

                                <h2 class="h6 text-uppercase fw-bold mt-4 mb-2">
                                    Relaciones
                                </h2>

                                <div class="list-group list-group-flush">


                                    <a
                                        href="/participa_grupo"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'participa_grupo'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'participa_grupo'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Docentes por grupo
                                    </a>


                                    <a
                                        href="/participa_semillero"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'participa_semillero'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'participa_semillero'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Docentes por semillero
                                    </a>


                                    <a
                                        href="/grupo_linea"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'grupo_linea'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'grupo_linea'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Líneas por grupo
                                    </a>


                                    <a
                                        href="/semillero_linea"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'semillero_linea'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'semillero_linea'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Líneas por semillero
                                    </a>


                                    <a
                                        href="/ac_linea"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'ac_linea'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'ac_linea'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Áreas de conocimiento por línea
                                    </a>


                                    <a
                                        href="/ods_linea"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'ods_linea'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'ods_linea'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        ODS por línea
                                    </a>


                                    <a
                                        href="/aa_linea"
                                        class="list-group-item list-group-item-action
                                            <?= $rutaActual === 'aa_linea'
                                                ? 'active'
                                                : '' ?>"
                                        <?= $rutaActual === 'aa_linea'
                                            ? 'aria-current="page"'
                                            : '' ?>
                                    >
                                        Áreas de aplicación por línea
                                    </a>

                                </div>

                            </nav>

                        </div>

                    </div>

                </aside>

            <?php endif; ?>


            <!-- =========================================
                 10. CONTENIDO DE LA PANTALLA
                 ========================================= -->

            <main
                id="contenido-principal"
                class="<?= $sesionActiva
                    ? 'col-12 col-lg-9 col-xl-10'
                    : 'col-12' ?>"
            >

                <div class="container-fluid p-0">


                    <!-- =============================
                         TITULO DE LA PANTALLA
                         ============================= -->

                    <?php if ($sesionActiva): ?>

                        <div
                            class="d-flex flex-wrap align-items-center
                                   justify-content-between gap-3 mb-4"
                        >

                            <div>

                                <p class="text-body-secondary small mb-1">
                                    Módulo de investigación
                                </p>

                                <h1 class="h3 fw-bold mb-0">
                                    <?= plantilla_escapar($tituloPagina) ?>
                                </h1>

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- =============================
                         MENSAJE DE EXITO
                         ============================= -->

                    <?php if ($mensajeExito !== ''): ?>

                        <div
                            class="alert alert-success alert-dismissible fade show"
                            role="status"
                        >

                            <?= plantilla_escapar($mensajeExito) ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Cerrar aviso"
                            ></button>

                        </div>

                    <?php endif; ?>


                    <!-- =============================
                         MENSAJES DE ERROR
                         ============================= -->

                    <?php if ($listaErrores !== []): ?>

                        <div
                            class="alert alert-danger"
                            role="alert"
                        >

                            <strong>
                                No fue posible completar la operación.
                            </strong>

                            <ul class="mb-0 mt-2">

                                <?php foreach ($listaErrores as $error): ?>

                                    <?php if (is_scalar($error)): ?>

                                        <li>
                                            <?= plantilla_escapar($error) ?>
                                        </li>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                            </ul>

                        </div>

                    <?php endif; ?>


                    <!-- =============================
                         VISTA SELECCIONADA
                         ============================= -->

                    <?php

                    if (
                        isset($vistaContenido) &&
                        is_string($vistaContenido) &&
                        is_file($vistaContenido)
                    ) {

                        // Cargar la vista elegida por index.php.
                        //
                        // La vista puede utilizar las variables
                        // preparadas anteriormente por el enrutador.

                        require $vistaContenido;

                    } else {

                        // Durante la construccion de la Fase 7,
                        // algunas vistas todavia no estan listas.
                        //
                        // Evitamos que falte un archivo y se genere
                        // un error fatal en toda la pagina.

                        ?>

                        <div class="card border-0 shadow-sm">

                            <div class="card-body p-4">

                                <h2 class="h5">
                                    Sección en preparación
                                </h2>

                                <p class="text-body-secondary mb-3">
                                    Esta pantalla todavía está en construcción.
                                </p>

                                <a
                                    href="/"
                                    class="btn btn-primary"
                                >
                                    Volver al inicio
                                </a>

                            </div>

                        </div>

                        <?php
                    }

                    ?>

                </div>

            </main>

        </div>

    </div>


    <!-- =================================================
         11. PIE DE PAGINA
         ================================================= -->

    <footer class="bg-body border-top mt-auto">

        <div class="container-fluid px-3 px-lg-4 py-3">

            <div
                class="d-flex flex-wrap justify-content-between
                       align-items-center gap-2"
            >

                <span class="text-body-secondary small">
                    Sistema de Gestión de Investigación
                </span>

                <span class="text-body-secondary small">
                    Versión 2 · PHP 8.3 · Bootstrap · MariaDB
                </span>

            </div>

        </div>

    </footer>


    <!-- =================================================
         12. JAVASCRIPT DE BOOTSTRAP LOCAL
         ================================================= -->

    <script
        src="/publico/bootstrap.bundle.min.js"
    ></script>

</body>

</html>