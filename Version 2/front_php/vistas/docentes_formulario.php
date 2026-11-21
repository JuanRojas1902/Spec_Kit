<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: docentes_formulario.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Formulario para registrar y editar docentes.
 *
 * POR QUE ESTA AQUI:
 * Permite ingresar los datos de un docente
 * mediante una pantalla HTML con Bootstrap.
 *
 * CLAVES:
 * - cedula: clave primaria (PK).
 * - Esta tabla NO tiene claves foraneas (FK).
 *
 * CAMPOS:
 * - cedula: entero positivo.
 * - nombres: texto obligatorio, maximo 100 caracteres.
 * - apellidos: texto obligatorio, maximo 100 caracteres.
 *
 * IMPORTANTE:
 * El campo activo no se muestra porque el retiro
 * logico se procesa mediante DELETE en la API.
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No se conecta directamente con MariaDB.
 * - No realiza peticiones HTTP.
 * - No modifica registros por si mismo.
 * - No implementa reglas de negocio.
 *
 * FUNCIONAMIENTO:
 * index.php prepara $modo, $valores y $csrf.
 * Esta vista construye el formulario.
 *
 * El formulario envia POST al frontend.
 * index.php utiliza cliente_api.php para realizar
 * POST, PUT o PATCH segun la operacion seleccionada.
 * =========================================================
 */


// =========================================================
// 1. IDENTIFICAR EL MODO DEL FORMULARIO
//
// Crear: registrar un docente.
// Editar: modificar un docente existente.
// =========================================================

$modoFormulario = isset($modo) && $modo === 'editar'
    ? 'editar'
    : 'crear';

$esEdicion = $modoFormulario === 'editar';


// =========================================================
// 2. RECUPERAR LOS VALORES
//
// Los valores pueden venir de:
// - Una consulta GET de la API.
// - Un formulario POST que presento errores.
//
// Esto permite conservar la informacion escrita.
// =========================================================

$datosFormulario = [];

if (isset($valores) && is_array($valores)) {

    $datosFormulario = $valores;
}


$cedulaFormulario = $datosFormulario['cedula'] ?? '';

$nombresFormulario = $datosFormulario['nombres'] ?? '';

$apellidosFormulario = $datosFormulario['apellidos'] ?? '';


// =========================================================
// 3. RECUPERAR EL TOKEN CSRF
// =========================================================

$tokenFormulario = '';

if (isset($csrf) && is_string($csrf)) {

    $tokenFormulario = $csrf;
}


// =========================================================
// 4. VERIFICAR SI SE PUEDE GUARDAR
//
// Al editar, el enrutador puede deshabilitar
// el guardado si no encontro el registro.
// =========================================================

$formularioHabilitado = !isset($puedeGuardar)
    || $puedeGuardar === true;


// =========================================================
// 5. DEFINIR TITULO Y DIRECCION DEL FORMULARIO
// =========================================================

if ($esEdicion) {

    $tituloFormulario = 'Editar docente';

    $descripcionFormulario =
        'Modifique la información del docente seleccionado.';

    $direccionFormulario =
        '/docentes/' . (int) $cedulaFormulario . '/editar';

} else {

    $tituloFormulario = 'Registrar docente';

    $descripcionFormulario =
        'Ingrese los datos para registrar un nuevo docente.';

    $direccionFormulario = '/docentes';
    $direccionFormulario .= '/nuevo';
}

?>


<!-- =====================================================
     6. ENCABEZADO DEL FORMULARIO
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap align-items-center
               justify-content-between gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">

                <?= plantilla_escapar($tituloFormulario) ?>

            </h2>

            <p class="text-body-secondary mb-0">

                <?= plantilla_escapar($descripcionFormulario) ?>

            </p>

        </div>


        <!-- Regresar al listado -->

        <a
            href="/docentes"
            class="btn btn-outline-secondary"
        >

            Volver al listado

        </a>

    </div>

</section>


<!-- =====================================================
     7. INFORMACION DEL FORMULARIO
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Información:</strong>

        Los campos marcados con

        <span class="text-danger fw-bold">*</span>

        son obligatorios.

        <?php if ($esEdicion): ?>

            La cédula es la clave primaria y
            no puede modificarse.

        <?php endif; ?>

    </div>

</section>


<!-- =====================================================
     8. TARJETA PRINCIPAL
     ===================================================== -->

<section>

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-3">

            <h3 class="h5 fw-semibold mb-0">

                Información del docente

            </h3>

        </div>


        <div class="card-body p-4">


            <!-- =========================================
                 9. FORMULARIO HTML
                 ========================================= -->

            <form
                method="POST"
                action="<?= plantilla_escapar($direccionFormulario) ?>"
                autocomplete="on"
            >


                <!-- =============================
                     TOKEN DE SEGURIDAD
                     ============================= -->

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= plantilla_escapar($tokenFormulario) ?>"
                >


                <div class="row g-4">


                    <!-- =================================
                         10. CEDULA
                         CLAVE PRIMARIA (PK)
                         ================================= -->

                    <div class="col-12 col-md-6">

                        <label
                            for="cedula"
                            class="form-label fw-semibold"
                        >

                            Cédula

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <input
                            type="number"
                            class="form-control"
                            id="cedula"
                            name="cedula"
                            min="1"
                            max="2147483647"
                            step="1"
                            value="<?= plantilla_escapar($cedulaFormulario) ?>"
                            placeholder="Ejemplo: 1030456"
                            <?= $esEdicion ? 'readonly' : '' ?>
                            <?= $formularioHabilitado ? '' : 'disabled' ?>
                            required
                        >


                        <div class="form-text">

                            <?php if ($esEdicion): ?>

                                La cédula identifica al docente
                                y no se puede cambiar.

                            <?php else: ?>

                                Ingrese un número entero positivo
                                que no esté registrado.

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- =================================
                         11. NOMBRES
                         VARCHAR(100)
                         ================================= -->

                    <div class="col-12 col-md-6">

                        <label
                            for="nombres"
                            class="form-label fw-semibold"
                        >

                            Nombres

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            class="form-control"
                            id="nombres"
                            name="nombres"
                            maxlength="100"
                            value="<?= plantilla_escapar($nombresFormulario) ?>"
                            placeholder="Ingrese los nombres"
                            autocomplete="given-name"
                            <?= $formularioHabilitado ? '' : 'disabled' ?>
                            required
                        >


                        <div class="form-text">

                            Máximo 100 caracteres.

                        </div>

                    </div>


                    <!-- =================================
                         12. APELLIDOS
                         VARCHAR(100)
                         ================================= -->

                    <div class="col-12 col-md-6">

                        <label
                            for="apellidos"
                            class="form-label fw-semibold"
                        >

                            Apellidos

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            class="form-control"
                            id="apellidos"
                            name="apellidos"
                            maxlength="100"
                            value="<?= plantilla_escapar($apellidosFormulario) ?>"
                            placeholder="Ingrese los apellidos"
                            autocomplete="family-name"
                            <?= $formularioHabilitado ? '' : 'disabled' ?>
                            required
                        >


                        <div class="form-text">

                            Máximo 100 caracteres.

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     13. EXPLICACION DE LAS OPERACIONES
                     ===================================== -->

                <?php if ($esEdicion): ?>

                    <div class="alert alert-light border mt-4">

                        <h4 class="h6 fw-bold">

                            Opciones de guardado

                        </h4>

                        <p class="small mb-2">

                            <strong>Guardar la ficha completa:</strong>

                            reemplaza todos los campos
                            editables mediante PUT.

                        </p>

                        <p class="small mb-0">

                            <strong>Guardar solo lo que cambié:</strong>

                            actualiza únicamente los campos
                            que fueron modificados mediante PATCH.

                        </p>

                    </div>

                <?php endif; ?>


                <!-- =====================================
                     14. BOTONES DEL FORMULARIO
                     ===================================== -->

                <div
                    class="d-flex flex-wrap gap-2
                           border-top mt-4 pt-4"
                >


                    <?php if ($esEdicion): ?>


                        <!-- GUARDAR FICHA COMPLETA -->

                        <button
                            type="submit"
                            name="accion"
                            value="completo"
                            class="btn btn-primary"
                            <?= $formularioHabilitado ? '' : 'disabled' ?>
                        >

                            Guardar la ficha completa

                        </button>


                        <!-- GUARDAR CAMBIOS PARCIALES -->

                        <button
                            type="submit"
                            name="accion"
                            value="parcial"
                            class="btn btn-outline-primary"
                            <?= $formularioHabilitado ? '' : 'disabled' ?>
                        >

                            Guardar solo lo que cambié

                        </button>


                    <?php else: ?>


                        <!-- REGISTRAR DOCENTE -->

                        <button
                            type="submit"
                            class="btn btn-primary"
                            <?= $formularioHabilitado ? '' : 'disabled' ?>
                        >

                            Registrar docente

                        </button>


                    <?php endif; ?>


                    <!-- CANCELAR -->

                    <a
                        href="/docentes"
                        class="btn btn-outline-secondary"
                    >

                        Cancelar

                    </a>

                </div>

            </form>

        </div>

    </div>

</section>


<!-- =====================================================
     15. INFORMACION ADICIONAL
     ===================================================== -->

<section class="mt-4">

    <div class="card border-0 shadow-sm">

        <div class="card-body p-3">

            <p class="text-body-secondary small mb-0">

                <strong>Nota:</strong>

                Los docentes tienen una cédula como
                clave primaria.

                Esta tabla no contiene claves foráneas.
                Las relaciones con grupos y semilleros
                se administran desde sus respectivas
                pantallas de asignación.

            </p>

        </div>

    </div>

</section>