<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: grupos_formulario.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Formulario para registrar y editar grupos de
 * investigacion junto con sus semilleros.
 *
 * POR QUE ESTA AQUI:
 * Permite capturar el encabezado del grupo y todos
 * los renglones de su detalle en una sola pantalla.
 *
 * CLAVE FORANEA DEL GRUPO:
 *
 * grupo_investigacion.universidad_id
 *              |
 *              v
 *         universidad.id
 *
 * El usuario elige la universidad por su nombre.
 * El formulario envia su identificador a la API.
 *
 * CLAVE FORANEA DEL SEMILLERO:
 *
 * semillero.grupo_id
 *          |
 *          v
 * grupo_investigacion.id
 *
 * Esa clave no se escribe en este formulario.
 * El procedimiento almacenado asigna el grupo_id.
 *
 * REGLAS IMPORTANTES:
 * - El ID del grupo es AUTO_INCREMENT.
 * - Al crear se muestran cinco renglones de detalle.
 * - Al editar se muestran TODOS los semilleros actuales
 *   mas tres renglones vacios.
 * - Los renglones vacios se descartan en index.php.
 * - POST crea el grupo y su detalle.
 * - PUT reemplaza el grupo y TODO su detalle.
 * - No existe PATCH para grupos de investigacion.
 *
 * QUE NO HACE:
 * - No ejecuta consultas SQL.
 * - No se conecta directamente con MariaDB.
 * - No llama procedimientos almacenados.
 * - No realiza peticiones HTTP directamente.
 * - No crea semilleros fuera del grupo.
 *
 * RECIBE DESDE index.php:
 * $modo, $valores, $universidades, $puedeGuardar, $csrf.
 * =========================================================
 */


// =========================================================
// 1. IDENTIFICAR EL MODO DEL FORMULARIO
// =========================================================

$esEdicion = ($modo ?? 'crear') === 'editar';

$tituloFormulario = $esEdicion
    ? 'Editar grupo de investigación'
    : 'Registrar grupo de investigación';

$descripcionFormulario = $esEdicion
    ? 'Actualice el grupo y revise todos sus semilleros.'
    : 'Registre el grupo junto con sus semilleros.';


// =========================================================
// 2. PREPARAR LOS DATOS DEL GRUPO
//
// Los datos provienen de index.php y pueden representar:
// - Una ficha consultada desde la API.
// - Lo escrito antes de un error de validacion.
// =========================================================

$datosGrupo = [];

if (isset($valores) && is_array($valores)) {

    $datosGrupo = $valores;
}

$idGrupo = filter_var(
    $datosGrupo['id'] ?? null,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1
        ]
    ]
);

$gruplac = $datosGrupo['gruplac'] ?? '';

$categoria = $datosGrupo['categoria'] ?? '';

$convocatoria = $datosGrupo['convocatoria'] ?? '';

$ambito = $datosGrupo['ambito'] ?? '';

$universidadSeleccionada = (string) (
    $datosGrupo['universidad_id'] ?? ''
);


// =========================================================
// 3. DEFINIR LA RUTA DEL FORMULARIO
//
// POST /grupos/nuevo           -> POST en la API.
// POST /grupos/{id}/editar     -> PUT en la API.
// =========================================================

$direccionFormulario = '/grupos/nuevo';

if ($esEdicion && $idGrupo !== false) {

    $direccionFormulario =
        '/grupos/' . $idGrupo . '/editar';
}


// =========================================================
// 4. PREPARAR LAS UNIVERSIDADES PARA EL DESPLEGABLE
//
// CLAVE FORANEA:
// grupo_investigacion.universidad_id
//        ->
// universidad.id
//
// El listado ya fue obtenido por index.php mediante
// listar_universidades().
// =========================================================

$listaUniversidades = [];

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

        $idUniversidad = filter_var(
            $universidad['id'],
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if ($idUniversidad === false) {
            continue;
        }

        $listaUniversidades[] = [
            'id' => $idUniversidad,
            'nombre' => (string) $universidad['nombre']
        ];
    }
}


// =========================================================
// 5. PREPARAR EL DETALLE DE SEMILLEROS
//
// CASO A: POST con error.
// Se recuperan los campos detalle_nombre[].
//
// CASO B: GET de edicion.
// La API devuelve detalle: [{id, nombre}, ...].
//
// CASO C: Formulario de creacion.
// Se muestran cinco renglones vacios.
// =========================================================

$nombresSemilleros = [];


// ---------------------------------------------------------
// 5.1. CONSERVAR LOS DATOS ENVIADOS POR EL FORMULARIO
// ---------------------------------------------------------

if (
    isset($datosGrupo['detalle_nombre']) &&
    is_array($datosGrupo['detalle_nombre'])
) {

    foreach ($datosGrupo['detalle_nombre'] as $nombre) {

        if (is_string($nombre)) {

            $nombresSemilleros[] = $nombre;
        }
    }
}


// ---------------------------------------------------------
// 5.2. CARGAR LOS SEMILLEROS EXISTENTES
// ---------------------------------------------------------

elseif (
    $esEdicion &&
    isset($datosGrupo['detalle']) &&
    is_array($datosGrupo['detalle'])
) {

    foreach ($datosGrupo['detalle'] as $semillero) {

        if (
            is_array($semillero) &&
            isset($semillero['nombre']) &&
            is_string($semillero['nombre'])
        ) {

            $nombresSemilleros[] =
                $semillero['nombre'];
        }
    }
}


// ---------------------------------------------------------
// 5.3. COMPLETAR LOS RENGLONES
// ---------------------------------------------------------

if (!$esEdicion) {

    // Crear: minimo cinco renglones.
    //
    // Si hubo un error de validacion, se conservan
    // todos los valores que la persona habia escrito.

    while (count($nombresSemilleros) < 5) {

        $nombresSemilleros[] = '';
    }

} else {

    // Editar: conservar TODOS los nombres recuperados
    // y agregar tres renglones vacios.
    //
    // Nunca se limita el arreglo a cinco semilleros:
    // hacerlo podria retirar registros sin querer.

    $nombresSemilleros[] = '';
    $nombresSemilleros[] = '';
    $nombresSemilleros[] = '';
}


// =========================================================
// 6. TOKEN DE SEGURIDAD CSRF
// =========================================================

$tokenFormulario = isset($csrf) && is_string($csrf)
    ? $csrf
    : '';


// =========================================================
// 7. HABILITAR O DESHABILITAR EL GUARDADO
//
// No permitimos guardar si:
// - El grupo no se pudo cargar durante una edicion.
// - No existen universidades disponibles.
// =========================================================

$formularioHabilitado =
    ($puedeGuardar ?? true) === true
    && $listaUniversidades !== [];

if ($esEdicion && $idGrupo === false) {

    $formularioHabilitado = false;
}


// =========================================================
// 8. IDENTIFICAR LA UNIVERSIDAD ACTUAL
// =========================================================

$universidadEncontrada = false;

foreach ($listaUniversidades as $universidad) {

    if (
        (string) $universidad['id']
        === $universidadSeleccionada
    ) {

        $universidadEncontrada = true;
        break;
    }
}

?>


<!-- =====================================================
     9. ENCABEZADO DEL FORMULARIO
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

                <?= plantilla_escapar(
                    $descripcionFormulario
                ) ?>

            </p>

        </div>

        <a
            href="/grupos"
            class="btn btn-outline-secondary"
        >
            Volver al listado
        </a>

    </div>

</section>


<!-- =====================================================
     10. INFORMACION SOBRE LAS RELACIONES
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Universidad obligatoria:</strong>

        cada grupo de investigación debe estar
        asociado a una universidad existente.

        <hr>

        <strong>Semilleros:</strong>

        los semilleros se guardan como parte del grupo.

        No es necesario escribir el identificador
        del grupo en cada semillero.

    </div>

</section>


<!-- =====================================================
     11. VERIFICAR SI EXISTEN UNIVERSIDADES
     ===================================================== -->

<?php if ($listaUniversidades === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay universidades disponibles para
        seleccionar o no fue posible cargarlas.

        Para guardar un grupo primero debe existir
        al menos una universidad activa.

        <a
            href="/universidad"
            class="alert-link"
        >
            Consultar universidades
        </a>

    </div>

<?php endif; ?>


<!-- =====================================================
     12. FORMULARIO MAESTRO-DETALLE
     ===================================================== -->

<section class="mb-4">

    <form
        method="POST"
        action="<?= plantilla_escapar(
            $direccionFormulario
        ) ?>"
    >

        <!-- Token CSRF -->

        <input
            type="hidden"
            name="csrf"
            value="<?= plantilla_escapar(
                $tokenFormulario
            ) ?>"
        >


        <!-- =============================================
             13. ENCABEZADO DEL GRUPO (MAESTRO)
             ============================================= -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white p-3">

                <h3 class="h5 fw-bold mb-0">

                    Datos del grupo de investigación

                </h3>

            </div>


            <div class="card-body p-4">

                <div class="row g-4">


                    <!-- =================================
                         14. ID DEL GRUPO
                         PK AUTO_INCREMENT
                         ================================= -->

                    <?php if ($esEdicion): ?>

                        <div class="col-12 col-md-4">

                            <label
                                for="grupo_id"
                                class="form-label fw-semibold"
                            >
                                ID del grupo
                            </label>

                            <input
                                type="number"
                                id="grupo_id"
                                class="form-control"
                                value="<?= plantilla_escapar(
                                    $idGrupo === false
                                        ? ''
                                        : $idGrupo
                                ) ?>"
                                readonly
                            >

                            <div class="form-text">

                                Identificador generado
                                automáticamente por MariaDB.

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- =================================
                         15. CODIGO GRUPLAC
                         VARCHAR(255)
                         ================================= -->

                    <div class="col-12 col-md-8">

                        <label
                            for="gruplac"
                            class="form-label fw-semibold"
                        >

                            Código GRUPLAC

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="gruplac"
                            name="gruplac"
                            class="form-control"
                            maxlength="255"
                            value="<?= plantilla_escapar(
                                $gruplac
                            ) ?>"
                            placeholder="Ejemplo: COL0002"
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                            required
                        >

                    </div>


                    <!-- =================================
                         16. CATEGORIA
                         VARCHAR(50)
                         ================================= -->

                    <div class="col-12 col-md-4">

                        <label
                            for="categoria"
                            class="form-label fw-semibold"
                        >

                            Categoría

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="categoria"
                            name="categoria"
                            class="form-control"
                            maxlength="50"
                            value="<?= plantilla_escapar(
                                $categoria
                            ) ?>"
                            placeholder="Ejemplo: A"
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                            required
                        >

                    </div>


                    <!-- =================================
                         17. CONVOCATORIA
                         VARCHAR(255)
                         ================================= -->

                    <div class="col-12 col-md-6">

                        <label
                            for="convocatoria"
                            class="form-label fw-semibold"
                        >

                            Convocatoria

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="convocatoria"
                            name="convocatoria"
                            class="form-control"
                            maxlength="255"
                            value="<?= plantilla_escapar(
                                $convocatoria
                            ) ?>"
                            placeholder="Ejemplo: Convocatoria 2026"
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                            required
                        >

                    </div>


                    <!-- =================================
                         18. AMBITO
                         VARCHAR(255)
                         ================================= -->

                    <div class="col-12 col-md-6">

                        <label
                            for="ambito"
                            class="form-label fw-semibold"
                        >

                            Ámbito

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="ambito"
                            name="ambito"
                            class="form-control"
                            maxlength="255"
                            value="<?= plantilla_escapar(
                                $ambito
                            ) ?>"
                            placeholder="Ejemplo: Nacional"
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                            required
                        >

                    </div>


                    <!-- =================================
                         19. UNIVERSIDAD
                         CLAVE FORANEA OBLIGATORIA
                         =================================

                         grupo_investigacion.universidad_id
                                      |
                                      v
                                 universidad.id

                         El valor enviado sera el ID.
                         El texto mostrado sera el nombre.
                         ================================= -->

                    <div class="col-12">

                        <label
                            for="universidad_id"
                            class="form-label fw-semibold"
                        >

                            Universidad

                            <span class="text-danger">*</span>

                        </label>

                        <select
                            id="universidad_id"
                            name="universidad_id"
                            class="form-select"
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                            required
                        >

                            <option value="">

                                -- Seleccione una universidad --

                            </option>


                            <?php if (
                                $universidadSeleccionada !== ''
                                && !$universidadEncontrada
                            ): ?>

                                <option
                                    value="<?= plantilla_escapar(
                                        $universidadSeleccionada
                                    ) ?>"
                                    selected
                                    disabled
                                >

                                    Universidad ID
                                    <?= plantilla_escapar(
                                        $universidadSeleccionada
                                    ) ?>
                                    (no disponible)

                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $listaUniversidades as $universidad
                            ): ?>

                                <?php

                                $seleccionada =
                                    (string) $universidad['id']
                                    === $universidadSeleccionada;

                                ?>

                                <option
                                    value="<?= (int) $universidad['id'] ?>"
                                    <?= $seleccionada
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $universidad['nombre']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">

                            Clave foránea:
                            <code>universidad_id</code>.

                            La lista se carga mediante
                            <code>listar_universidades()</code>.

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =============================================
             20. DETALLE DE SEMILLEROS
             ============================================= -->

        <div class="card border-0 shadow-sm mb-4">

            <div
                class="card-header bg-white p-3
                       d-flex flex-wrap justify-content-between
                       align-items-center gap-2"
            >

                <h3 class="h5 fw-bold mb-0">

                    Semilleros del grupo

                </h3>

                <span class="badge text-bg-primary">

                    Maestro-detalle

                </span>

            </div>


            <div class="card-body p-4">


                <p class="text-body-secondary">

                    Escriba el nombre de cada semillero
                    que pertenecerá al grupo.

                    Puede dejar renglones vacíos:
                    el sistema no los enviará a la API.

                </p>


                <!-- Información sobre grupo_id -->

                <div class="alert alert-light border">

                    <strong>Clave foránea:</strong>

                    <code>semillero.grupo_id</code>

                    hace referencia a

                    <code>grupo_investigacion.id</code>.

                    El procedimiento almacenado asigna
                    esta relación automáticamente.

                </div>


                <!-- =====================================
                     21. RENGLONES DEL DETALLE
                     ===================================== -->

                <div class="row g-3">

                    <?php foreach (
                        $nombresSemilleros as $indice => $nombre
                    ): ?>

                        <div class="col-12">

                            <label
                                for="detalle_<?= (int) $indice ?>"
                                class="form-label fw-semibold"
                            >

                                Semillero
                                <?= (int) $indice + 1 ?>

                            </label>

                            <input
                                type="text"
                                id="detalle_<?= (int) $indice ?>"
                                name="detalle_nombre[]"
                                class="form-control"
                                maxlength="100"
                                value="<?= plantilla_escapar(
                                    $nombre
                                ) ?>"
                                placeholder="Nombre del semillero"
                                <?= $formularioHabilitado
                                    ? ''
                                    : 'disabled' ?>
                            >

                            <div class="form-text">

                                Máximo 100 caracteres.

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <!-- =====================================
                     22. INFORMACION DEL DETALLE
                     ===================================== -->

                <div class="mt-4">

                    <p class="small text-body-secondary mb-0">

                        Los nombres registrados se enviarán
                        en el arreglo JSON

                        <code>detalle</code>

                        junto con los datos del grupo.

                    </p>

                </div>

            </div>

        </div>


        <!-- =============================================
             23. ADVERTENCIA AL EDITAR
             ============================================= -->

        <?php if ($esEdicion): ?>

            <div
                class="alert alert-warning"
                role="note"
            >

                <strong>Importante al editar:</strong>

                al guardar, la API reemplazará todo
                el detalle del grupo.

                Los semilleros que deje de incluir
                se retirarán lógicamente.

                Los semilleros guardados nuevamente
                recibirán identificadores nuevos.

                Revise todos los nombres antes
                de continuar.

            </div>

        <?php endif; ?>


        <!-- =============================================
             24. BOTONES DEL FORMULARIO
             ============================================= -->

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <div
                    class="d-flex flex-wrap
                           justify-content-between
                           align-items-center gap-3"
                >

                    <p class="text-body-secondary small mb-0">

                        Los campos marcados con
                        <span class="text-danger">*</span>
                        son obligatorios.

                    </p>


                    <div class="d-flex flex-wrap gap-2">


                        <!-- Cancelar -->

                        <a
                            href="/grupos"
                            class="btn btn-outline-secondary"
                        >

                            Cancelar

                        </a>


                        <!-- Guardar grupo y detalle -->

                        <button
                            type="submit"
                            class="btn btn-primary"
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                        >

                            <?= $esEdicion
                                ? 'Guardar grupo y semilleros'
                                : 'Registrar grupo y semilleros' ?>

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

</section>