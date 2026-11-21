<?php

declare(strict_types=1);

/**
 * =========================================================
 * ARCHIVO: docentes_grupo_formulario.php
 * CAPA: Frontend / Vista
 * VERSION: 2
 * =========================================================
 *
 * QUE ES:
 * Formulario para asignar un docente a un grupo
 * de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite crear una relacion en la tabla puente
 * participa_grupo utilizando dos desplegables.
 *
 * CLAVE FORANEA 1:
 *
 * participa_grupo.docente_cedula
 *              |
 *              v
 *         docente.cedula
 *
 * CLAVE FORANEA 2:
 *
 * participa_grupo.grupo_id
 *              |
 *              v
 *       grupo_investigacion.id
 *
 * CLAVE PRIMARIA COMPUESTA:
 *
 * PRIMARY KEY (docente_cedula, grupo_id)
 *
 * REGLAS:
 * - No se puede repetir la misma pareja.
 * - Solo se admiten identificadores positivos.
 * - No se crean docentes desde este formulario.
 * - No se crean grupos desde este formulario.
 * - No existe edicion PUT ni PATCH de la pareja.
 *
 * QUE NO HACE:
 * - No ejecuta SQL.
 * - No se conecta directamente con MariaDB.
 * - No realiza peticiones HTTP.
 * - No modifica tablas por si mismo.
 *
 * RECIBE DESDE index.php:
 *
 * $opciones1: catalogo de docentes.
 * $opciones2: catalogo de grupos.
 * $valores: datos anteriores del formulario.
 * $csrf: token de seguridad.
 * $puedeGuardar: permiso para habilitar el boton.
 *
 * El enrutador procesa:
 *
 * POST /participa_grupo/nueva
 *
 * Y el cliente de API realiza:
 *
 * POST /api/participa_grupo
 * =========================================================
 */


// =========================================================
// 1. FUNCION LOCAL PARA EXTRAER REGISTROS
//
// El cliente HTTP puede entregar:
// - Una lista directa.
// - Una respuesta normalizada con "datos".
// - Una respuesta que contiene "respuesta.datos".
//
// No realizamos nuevas consultas en la vista.
// =========================================================

$extraerFilas = static function (mixed $origen): array {

    if (!is_array($origen)) {
        return [];
    }

    if (
        isset($origen['datos']) &&
        is_array($origen['datos'])
    ) {

        $origen = $origen['datos'];

    } elseif (
        isset($origen['respuesta']['datos']) &&
        is_array($origen['respuesta']['datos'])
    ) {

        $origen = $origen['respuesta']['datos'];
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
// 2. RECUPERAR EL CATALOGO DE DOCENTES
//
// Fuente: listar_docentes()
//
// CLAVE FORANEA:
// participa_grupo.docente_cedula -> docente.cedula
// =========================================================

$docentesDisponibles = [];

$datosDocentes = $extraerFilas(
    $opciones1 ?? []
);

foreach ($datosDocentes as $docente) {

    $cedula = filter_var(
        $docente['cedula'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
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

    if ($nombreCompleto === '') {

        $nombreCompleto = 'Docente';
    }

    $docentesDisponibles[] = [
        'cedula' => $cedula,
        'nombre' => $nombreCompleto
    ];
}


// =========================================================
// 3. RECUPERAR EL CATALOGO DE GRUPOS
//
// Fuente: listar_grupos_investigacion()
//
// CLAVE FORANEA:
// participa_grupo.grupo_id -> grupo_investigacion.id
// =========================================================

$gruposDisponibles = [];

$datosGrupos = $extraerFilas(
    $opciones2 ?? []
);

foreach ($datosGrupos as $grupo) {

    $idGrupo = filter_var(
        $grupo['id'] ?? null,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ]
        ]
    );

    if ($idGrupo === false) {
        continue;
    }

    $codigoGrupo = trim(
        (string) ($grupo['gruplac'] ?? '')
    );

    if ($codigoGrupo === '') {

        $codigoGrupo = 'Grupo';
    }

    $gruposDisponibles[] = [
        'id' => $idGrupo,
        'gruplac' => $codigoGrupo
    ];
}


// =========================================================
// 4. RECUPERAR VALORES ANTERIORES
//
// Si la API responde 409 o 422, conservamos
// los identificadores seleccionados.
//
// Nunca inventamos registros para los desplegables.
// =========================================================

$datosFormulario = [];

if (
    isset($valores) &&
    is_array($valores)
) {

    $datosFormulario = $valores;
}


// Recuperar valores de POST cuando corresponda.

if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
) {

    if (
        !isset($datosFormulario['docente_cedula']) &&
        isset($_POST['docente_cedula'])
    ) {

        $datosFormulario['docente_cedula'] =
            $_POST['docente_cedula'];
    }

    if (
        !isset($datosFormulario['grupo_id']) &&
        isset($_POST['grupo_id'])
    ) {

        $datosFormulario['grupo_id'] =
            $_POST['grupo_id'];
    }
}


$docenteSeleccionado = is_scalar(
    $datosFormulario['docente_cedula'] ?? null
)
    ? (string) $datosFormulario['docente_cedula']
    : '';

$grupoSeleccionado = is_scalar(
    $datosFormulario['grupo_id'] ?? null
)
    ? (string) $datosFormulario['grupo_id']
    : '';


// =========================================================
// 5. COMPROBAR SI LOS REGISTROS SELECCIONADOS EXISTEN
// =========================================================

$docenteEncontrado = false;

foreach ($docentesDisponibles as $docente) {

    if (
        (string) $docente['cedula']
        === $docenteSeleccionado
    ) {

        $docenteEncontrado = true;
        break;
    }
}


$grupoEncontrado = false;

foreach ($gruposDisponibles as $grupo) {

    if (
        (string) $grupo['id']
        === $grupoSeleccionado
    ) {

        $grupoEncontrado = true;
        break;
    }
}


// =========================================================
// 6. VERIFICAR QUE SE PUEDA CREAR LA ASIGNACION
//
// Debe existir por lo menos un docente
// y un grupo disponibles.
// =========================================================

$formularioHabilitado =
    ($puedeGuardar ?? true) === true
    && $docentesDisponibles !== []
    && $gruposDisponibles !== [];


// =========================================================
// 7. RECUPERAR TOKEN CSRF
// =========================================================

$tokenFormulario = isset($csrf)
    && is_string($csrf)
        ? $csrf
        : '';

?>


<!-- =====================================================
     8. ENCABEZADO DEL FORMULARIO
     ===================================================== -->

<section class="mb-4">

    <div
        class="d-flex flex-wrap
               justify-content-between
               align-items-center gap-3"
    >

        <div>

            <h2 class="h4 fw-bold mb-2">

                Nueva asignación de docente a grupo

            </h2>

            <p class="text-body-secondary mb-0">

                Seleccione un docente y un grupo
                para registrar su relación.

            </p>

        </div>

        <a
            href="/participa_grupo"
            class="btn btn-outline-secondary"
        >

            Volver al listado

        </a>

    </div>

</section>


<!-- =====================================================
     9. INFORMACION SOBRE LAS CLAVES FORANEAS
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-info" role="note">

        <strong>Claves foráneas:</strong>

        <ul class="mb-0 mt-2">

            <li>

                <code>docente_cedula</code>

                referencia a

                <code>docente.cedula</code>.

            </li>

            <li>

                <code>grupo_id</code>

                referencia a

                <code>grupo_investigacion.id</code>.

            </li>

        </ul>

        <p class="mt-2 mb-0">

            Seleccione registros existentes.
            La misma pareja no puede registrarse
            dos veces.

        </p>

    </div>

</section>


<!-- =====================================================
     10. VERIFICAR LOS CATALOGOS
     ===================================================== -->

<?php if ($docentesDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay docentes disponibles o no fue posible
        consultar el catálogo de docentes.

        <a
            href="/docentes"
            class="alert-link"
        >
            Consultar docentes
        </a>

    </div>

<?php endif; ?>


<?php if ($gruposDisponibles === []): ?>

    <div class="alert alert-warning" role="alert">

        No hay grupos disponibles o no fue posible
        consultar el catálogo de grupos.

        <a
            href="/grupos"
            class="alert-link"
        >
            Consultar grupos
        </a>

    </div>

<?php endif; ?>


<!-- =====================================================
     11. FORMULARIO PRINCIPAL
     ===================================================== -->

<section class="mb-4">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-3">

            <h3 class="h5 fw-bold mb-0">

                Información de la asignación

            </h3>

        </div>


        <div class="card-body p-4">

            <form
                method="POST"
                action="/participa_grupo/nueva"
            >

                <!-- =====================================
                     12. TOKEN CSRF
                     ===================================== -->

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= plantilla_escapar(
                        $tokenFormulario
                    ) ?>"
                >


                <div class="row g-4">


                    <!-- =================================
                         13. PRIMERA CLAVE FORANEA
                         docente_cedula
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="docente_cedula"
                            class="form-label fw-semibold"
                        >

                            Docente

                            <span class="text-danger">*</span>

                        </label>


                        <select
                            id="docente_cedula"
                            name="docente_cedula"
                            class="form-select"
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                            required
                        >

                            <option value="">

                                -- Seleccione un docente --

                            </option>


                            <?php if (
                                $docenteSeleccionado !== ''
                                && !$docenteEncontrado
                            ): ?>

                                <option
                                    value=""
                                    selected
                                    disabled
                                >

                                    Docente seleccionado no disponible

                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $docentesDisponibles as $docente
                            ): ?>

                                <?php

                                $seleccionado =
                                    (string) $docente['cedula']
                                    === $docenteSeleccionado;

                                ?>

                                <option
                                    value="<?= (int) $docente['cedula'] ?>"
                                    <?= $seleccionado
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $docente['nombre']
                                    ) ?>

                                    —

                                    <?= (int) $docente['cedula'] ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div class="form-text">

                            Clave foránea:

                            <code>docente_cedula</code>.

                            El formulario enviará
                            la cédula del docente.

                        </div>

                    </div>


                    <!-- =================================
                         14. SEGUNDA CLAVE FORANEA
                         grupo_id
                         ================================= -->

                    <div class="col-12 col-lg-6">

                        <label
                            for="grupo_id"
                            class="form-label fw-semibold"
                        >

                            Grupo de investigación

                            <span class="text-danger">*</span>

                        </label>


                        <select
                            id="grupo_id"
                            name="grupo_id"
                            class="form-select"
                            <?= $formularioHabilitado
                                ? ''
                                : 'disabled' ?>
                            required
                        >

                            <option value="">

                                -- Seleccione un grupo --

                            </option>


                            <?php if (
                                $grupoSeleccionado !== ''
                                && !$grupoEncontrado
                            ): ?>

                                <option
                                    value=""
                                    selected
                                    disabled
                                >

                                    Grupo seleccionado no disponible

                                </option>

                            <?php endif; ?>


                            <?php foreach (
                                $gruposDisponibles as $grupo
                            ): ?>

                                <?php

                                $seleccionado =
                                    (string) $grupo['id']
                                    === $grupoSeleccionado;

                                ?>

                                <option
                                    value="<?= (int) $grupo['id'] ?>"
                                    <?= $seleccionado
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= plantilla_escapar(
                                        $grupo['gruplac']
                                    ) ?>

                                    — ID:

                                    <?= (int) $grupo['id'] ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div class="form-text">

                            Clave foránea:

                            <code>grupo_id</code>.

                            El formulario enviará
                            el identificador del grupo.

                        </div>

                    </div>

                </div>


                <!-- =====================================
                     15. EXPLICACION DE LA RELACION
                     ===================================== -->

                <div class="alert alert-light border mt-4">

                    <h4 class="h6 fw-bold">

                        ¿Qué sucede al guardar?

                    </h4>

                    <p class="small mb-0">

                        Se creará una relación entre
                        el docente y el grupo seleccionados.

                        No se modificará la información
                        de ninguno de los dos registros.

                    </p>

                </div>


                <!-- =====================================
                     16. BOTONES DEL FORMULARIO
                     ===================================== -->

                <div
                    class="d-flex flex-wrap gap-2
                           border-top mt-4 pt-4"
                >

                    <button
                        type="submit"
                        class="btn btn-primary"
                        <?= $formularioHabilitado
                            ? ''
                            : 'disabled' ?>
                    >

                        Asignar docente al grupo

                    </button>


                    <a
                        href="/participa_grupo"
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
     17. NOTA SOBRE LA CLAVE PRIMARIA COMPUESTA
     ===================================================== -->

<section class="mb-4">

    <div class="alert alert-warning" role="note">

        <strong>Clave primaria compuesta:</strong>

        la pareja formada por

        <code>docente_cedula</code>

        y

        <code>grupo_id</code>

        debe ser única.

        Si esa asignación ya existe,
        la API responderá con el error
        <strong>HTTP 409</strong>.

        Para cambiar una pareja, primero se retira
        la relación anterior y después se crea
        una nueva.

    </div>

</section>