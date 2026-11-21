<?php

declare(strict_types=1);

/**
 * ARCHIVO: login.php
 * CAPA: Frontend / Vista
 *
 * QUE ES:
 * Pantalla de inicio de sesion del Sistema
 * de Gestion de Investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite que una persona ingrese su usuario
 * y contrasena para acceder al sistema.
 *
 * Utiliza Bootstrap y el diseño principal
 * definido en plantilla.php.
 *
 * QUE NO HACE:
 * No consulta directamente MariaDB.
 * No ejecuta consultas SQL.
 * No valida contrasenas.
 * No crea usuarios.
 * No almacena credenciales.
 *
 * La autenticacion se procesa en index.php.
 */


// =========================================================
// 1. RECUPERAR LOS VALORES DEL FORMULARIO
//
// Se conserva solamente el nombre de usuario.
// Nunca se vuelve a mostrar la contrasena.
// =========================================================

$usuarioIngresado = '';

if (
    isset($valores) &&
    is_array($valores) &&
    isset($valores['usuario']) &&
    is_string($valores['usuario'])
) {

    $usuarioIngresado = $valores['usuario'];
}


// =========================================================
// 2. RECUPERAR EL TOKEN DE SEGURIDAD
//
// El token fue generado por index.php.
// Se utiliza para proteger el envio del formulario.
// =========================================================

$tokenLogin = '';

if (
    isset($csrf) &&
    is_string($csrf)
) {

    $tokenLogin = $csrf;
}

?>


<!-- =====================================================
     3. CONTENEDOR PRINCIPAL DEL LOGIN
     ===================================================== -->

<section class="container py-4 py-md-5">

    <div class="row justify-content-center">

        <div class="col-12 col-md-9 col-lg-7 col-xl-5">


            <!-- =========================================
                 4. TARJETA DEL FORMULARIO
                 ========================================= -->

            <div class="card border-0 shadow">

                <div class="card-body p-4 p-md-5">


                    <!-- =================================
                         5. ENCABEZADO DEL LOGIN
                         ================================= -->

                    <div class="text-center mb-4">

                        <div class="mb-3">

                            <span
                                class="d-inline-flex align-items-center
                                       justify-content-center rounded-circle
                                       bg-primary-subtle text-primary
                                       fw-bold"
                                style="width: 64px; height: 64px;"
                                aria-hidden="true"
                            >
                                SGI
                            </span>

                        </div>


                        <h1 class="h3 fw-bold mb-2">

                            Bienvenido

                        </h1>


                        <p class="text-body-secondary mb-0">

                            Sistema de Gestión de Investigación

                        </p>


                        <p class="text-body-secondary small mt-2">

                            Ingrese sus credenciales para acceder
                            al sistema.

                        </p>

                    </div>


                    <!-- =================================
                         6. FORMULARIO DE INICIO DE SESION
                         ================================= -->

                    <form
                        method="POST"
                        action="/login"
                        autocomplete="on"
                    >


                        <!-- Token de proteccion CSRF -->

                        <input
                            type="hidden"
                            name="csrf"
                            value="<?= plantilla_escapar($tokenLogin) ?>"
                        >


                        <!-- =============================
                             CAMPO USUARIO
                             ============================= -->

                        <div class="mb-3">

                            <label
                                for="usuario"
                                class="form-label fw-semibold"
                            >

                                Usuario

                            </label>


                            <input
                                type="text"
                                class="form-control form-control-lg"
                                id="usuario"
                                name="usuario"
                                placeholder="Ingrese su usuario"
                                value="<?= plantilla_escapar($usuarioIngresado) ?>"
                                maxlength="80"
                                autocomplete="username"
                                required
                                autofocus
                            >


                            <div class="form-text">

                                Escriba su nombre de usuario.

                            </div>

                        </div>


                        <!-- =============================
                             CAMPO CONTRASENA
                             ============================= -->

                        <div class="mb-4">

                            <label
                                for="contrasena"
                                class="form-label fw-semibold"
                            >

                                Contraseña

                            </label>


                            <input
                                type="password"
                                class="form-control form-control-lg"
                                id="contrasena"
                                name="contrasena"
                                placeholder="Ingrese su contraseña"
                                autocomplete="current-password"
                                required
                            >


                            <div class="form-text">

                                Su contraseña no se mostrará
                                mientras la escribe.

                            </div>

                        </div>


                        <!-- =============================
                             BOTON DE INGRESO
                             ============================= -->

                        <div class="d-grid">

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg fw-semibold"
                            >

                                Iniciar sesión

                            </button>

                        </div>

                    </form>


                    <!-- =================================
                         7. INFORMACION DE ACCESO
                         ================================= -->

                    <div class="text-center mt-4">

                        <p class="text-body-secondary small mb-0">

                            Acceso al módulo de investigación.

                        </p>

                        <p class="text-body-secondary small mb-0">

                            Universidad Monte Verde

                        </p>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 8. INFORMACION DEL PROYECTO
                 ========================================= -->

            <div class="text-center mt-4">

                <p class="text-body-secondary small">

                    Proyecto de aula — Módulo Investigación

                </p>

                <p class="text-body-secondary small mb-0">

                    Versión 2 · PHP · Bootstrap

                </p>

            </div>

        </div>

    </div>

</section>