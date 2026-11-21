<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: inicio.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Pagina principal del Sistema de Gestion de Investigacion.
 *
 * POR QUE ESTA AQUI:
 * Presenta los modulos del proyecto y proporciona accesos
 * a los 16 recursos disponibles en el enrutador.
 *
 * QUE NO HACE:
 * - No establece conexiones con MariaDB.
 * - No ejecuta consultas SQL.
 * - No modifica registros.
 * - No realiza peticiones HTTP a la API.
 * - No procesa credenciales.
 *
 * ARQUITECTURA:
 * Esta vista es cargada por front_php/index.php
 * dentro de front_php/vistas/plantilla.php.
 *
 * TECNOLOGIAS:
 * PHP 8.3, HTML5 y Bootstrap local.
 * =========================================================
 */


// =========================================================
// 1. RECURSOS DE LOS CATALOGOS DE LA VERSION 1
//
// Conservamos los seis catalogos originales.
// =========================================================

$catalogos = [

    [
        'nombre' => 'Áreas de conocimiento',
        'descripcion' => 'Clasificación de áreas y disciplinas.',
        'ruta' => '/area_conocimiento'
    ],

    [
        'nombre' => 'Objetivos de desarrollo sostenible',
        'descripcion' => 'Consulta de los objetivos ODS.',
        'ruta' => '/objetivo_desarrollo_sostenible'
    ],

    [
        'nombre' => 'Áreas de aplicación',
        'descripcion' => 'Áreas donde se aplica la investigación.',
        'ruta' => '/area_aplicacion'
    ],

    [
        'nombre' => 'Términos clave',
        'descripcion' => 'Palabras y términos de investigación.',
        'ruta' => '/termino_clave'
    ],

    [
        'nombre' => 'Universidades',
        'descripcion' => 'Instituciones universitarias registradas.',
        'ruta' => '/universidad'
    ],

    [
        'nombre' => 'Líneas de investigación',
        'descripcion' => 'Líneas que orientan los proyectos.',
        'ruta' => '/linea_investigacion'
    ]

];


// =========================================================
// 2. RECURSOS PRINCIPALES DE LA VERSION 2
// =========================================================

$recursosInvestigacion = [

    [
        'nombre' => 'Docentes',
        'descripcion' => 'Registro y administración de docentes.',
        'ruta' => '/docentes',
        'accion' => 'Gestionar docentes'
    ],

    [
        'nombre' => 'Grupos de investigación',
        'descripcion' => 'Administración de grupos y sus semilleros.',
        'ruta' => '/grupos',
        'accion' => 'Gestionar grupos'
    ],

    [
        'nombre' => 'Semilleros',
        'descripcion' => 'Consulta de los semilleros registrados.',
        'ruta' => '/semilleros',
        'accion' => 'Consultar semilleros'
    ]

];


// =========================================================
// 3. RELACIONES DE LA VERSION 2
//
// Son las siete tablas puente.
// =========================================================

$relaciones = [

    [
        'nombre' => 'Docentes por grupo',
        'descripcion' => 'Asociar docentes con grupos.',
        'ruta' => '/participa_grupo'
    ],

    [
        'nombre' => 'Docentes por semillero',
        'descripcion' => 'Asociar docentes con semilleros.',
        'ruta' => '/participa_semillero'
    ],

    [
        'nombre' => 'Líneas por grupo',
        'descripcion' => 'Relacionar grupos con líneas.',
        'ruta' => '/grupo_linea'
    ],

    [
        'nombre' => 'Líneas por semillero',
        'descripcion' => 'Relacionar semilleros con líneas.',
        'ruta' => '/semillero_linea'
    ],

    [
        'nombre' => 'Áreas de conocimiento por línea',
        'descripcion' => 'Relacionar disciplinas y líneas.',
        'ruta' => '/ac_linea'
    ],

    [
        'nombre' => 'ODS por línea',
        'descripcion' => 'Relacionar objetivos ODS y líneas.',
        'ruta' => '/ods_linea'
    ],

    [
        'nombre' => 'Áreas de aplicación por línea',
        'descripcion' => 'Relacionar áreas de aplicación y líneas.',
        'ruta' => '/aa_linea'
    ]

];

?>


<!-- =====================================================
     4. BANNER PRINCIPAL
     ===================================================== -->

<section
    class="rounded-3 shadow-sm text-white p-4 p-md-5 mb-4"
    style="background-color: #0d6efd;"
>

    <div class="row align-items-center g-4">

        <div class="col-12 col-lg-8">

            <p class="text-white-50 fw-semibold mb-2">
                Proyecto de aula · Versión 2
            </p>

            <h2 class="display-6 fw-bold mb-3">

                Sistema de Gestión de Investigación

            </h2>

            <p class="fs-5 mb-4">

                Plataforma para administrar docentes,
                grupos de investigación, semilleros,
                catálogos y relaciones académicas.

            </p>

            <div class="d-flex flex-wrap gap-2">

                <a
                    href="/docentes"
                    class="btn btn-light fw-semibold"
                >
                    Gestionar docentes
                </a>

                <a
                    href="/grupos"
                    class="btn btn-warning fw-semibold"
                >
                    Grupos de investigación
                </a>

            </div>

        </div>

        <div class="col-12 col-lg-4 text-center">

            <div
                class="bg-white bg-opacity-10 rounded-3 p-4"
            >

                <div class="display-4 fw-bold mb-2">
                    16
                </div>

                <p class="mb-0">
                    Recursos del sistema
                </p>

                <p class="text-white-50 small mt-2 mb-0">
                    6 catálogos V1 + 10 recursos V2
                </p>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     5. RESUMEN DE LOS MODULOS
     ===================================================== -->

<section class="mb-5">

    <div class="row g-3">

        <!-- Catalogos -->

        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <span class="badge text-bg-primary mb-3">
                        Versión 1
                    </span>

                    <h3 class="h5 fw-bold">
                        Catálogos
                    </h3>

                    <p class="text-body-secondary mb-3">

                        Seis recursos para organizar
                        la información de investigación.

                    </p>

                    <p class="fw-semibold text-primary mb-0">
                        6 recursos
                    </p>

                </div>

            </div>

        </div>


        <!-- Investigacion -->

        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <span class="badge text-bg-success mb-3">
                        Versión 2
                    </span>

                    <h3 class="h5 fw-bold">
                        Investigación
                    </h3>

                    <p class="text-body-secondary mb-3">

                        Gestión de docentes, grupos
                        de investigación y semilleros.

                    </p>

                    <p class="fw-semibold text-success mb-0">
                        3 recursos
                    </p>

                </div>

            </div>

        </div>


        <!-- Relaciones -->

        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <span class="badge text-bg-secondary mb-3">
                        Versión 2
                    </span>

                    <h3 class="h5 fw-bold">
                        Relaciones
                    </h3>

                    <p class="text-body-secondary mb-3">

                        Asignación de docentes, grupos,
                        semilleros y líneas de investigación.

                    </p>

                    <p class="fw-semibold mb-0">
                        7 tablas puente
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     6. SECCION DE INVESTIGACION V2
     ===================================================== -->

<section class="mb-5">

    <div class="mb-3">

        <h2 class="h4 fw-bold">
            Módulo de investigación
        </h2>

        <p class="text-body-secondary">
            Accede a los recursos principales de la Versión 2.
        </p>

    </div>

    <div class="row g-3">

        <?php foreach ($recursosInvestigacion as $recurso): ?>

            <div class="col-12 col-md-6 col-xl-4">

                <div class="card border-0 shadow-sm h-100">

                    <div
                        class="card-body p-4 d-flex flex-column"
                    >

                        <h3 class="h5 fw-bold mb-3">

                            <?= plantilla_escapar(
                                $recurso['nombre']
                            ) ?>

                        </h3>

                        <p class="text-body-secondary">

                            <?= plantilla_escapar(
                                $recurso['descripcion']
                            ) ?>

                        </p>

                        <div class="mt-auto pt-3">

                            <a
                                href="<?= plantilla_escapar(
                                    $recurso['ruta']
                                ) ?>"
                                class="btn btn-outline-primary w-100"
                            >

                                <?= plantilla_escapar(
                                    $recurso['accion']
                                ) ?>

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</section>


<!-- =====================================================
     7. SECCION DE CATALOGOS V1
     ===================================================== -->

<section class="mb-5">

    <div class="mb-3">

        <h2 class="h4 fw-bold">
            Catálogos del sistema
        </h2>

        <p class="text-body-secondary">
            Información de referencia conservada desde la V1.
        </p>

    </div>

    <div class="row g-3">

        <?php foreach ($catalogos as $catalogo): ?>

            <div class="col-12 col-md-6 col-xl-4">

                <div class="card border-0 shadow-sm h-100">

                    <div
                        class="card-body p-4 d-flex flex-column"
                    >

                        <h3 class="h6 fw-bold">

                            <?= plantilla_escapar(
                                $catalogo['nombre']
                            ) ?>

                        </h3>

                        <p class="text-body-secondary small">

                            <?= plantilla_escapar(
                                $catalogo['descripcion']
                            ) ?>

                        </p>

                        <div class="mt-auto pt-2">

                            <a
                                href="<?= plantilla_escapar(
                                    $catalogo['ruta']
                                ) ?>"
                                class="btn btn-outline-primary btn-sm"
                            >
                                Consultar catálogo
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</section>


<!-- =====================================================
     8. SECCION DE RELACIONES V2
     ===================================================== -->

<section class="mb-5">

    <div class="mb-3">

        <h2 class="h4 fw-bold">
            Relaciones de investigación
        </h2>

        <p class="text-body-secondary">
            Administra las asociaciones entre los recursos.
        </p>

    </div>

    <div class="row g-3">

        <?php foreach ($relaciones as $relacion): ?>

            <div class="col-12 col-md-6 col-xl-4">

                <div class="card border-0 shadow-sm h-100">

                    <div
                        class="card-body p-4 d-flex flex-column"
                    >

                        <h3 class="h6 fw-bold">

                            <?= plantilla_escapar(
                                $relacion['nombre']
                            ) ?>

                        </h3>

                        <p class="text-body-secondary small">

                            <?= plantilla_escapar(
                                $relacion['descripcion']
                            ) ?>

                        </p>

                        <div class="mt-auto pt-2">

                            <a
                                href="<?= plantilla_escapar(
                                    $relacion['ruta']
                                ) ?>"
                                class="btn btn-outline-secondary btn-sm"
                            >
                                Ver relaciones
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</section>


<!-- =====================================================
     9. INFORMACION TECNICA
     ===================================================== -->

<section class="mb-4">

    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <h2 class="h5 fw-bold mb-3">
                Arquitectura del proyecto
            </h2>

            <div class="row g-3">

                <div class="col-12 col-md-6">

                    <h3 class="h6 fw-semibold">
                        Frontend
                    </h3>

                    <p class="text-body-secondary small mb-0">

                        Desarrollado con PHP y Bootstrap.
                        Consume la API mediante HTTP
                        y presenta formularios y listados.

                    </p>

                </div>

                <div class="col-12 col-md-6">

                    <h3 class="h6 fw-semibold">
                        API y persistencia
                    </h3>

                    <p class="text-body-secondary small mb-0">

                        API desarrollada en PHP orientado
                        a objetos, organizada en capas
                        y conectada a MariaDB.

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>