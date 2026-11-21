<?php

declare(strict_types=1);


/*
 * RUTA_USUARIOS_V3_ADMIN
 *
 * GET /api/usuario
 * GET /api/usuario/{id}
 *
 * Solo usuarios autenticados con rol ADMIN.
 */

$rutaUsuariosV3 = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

if (
    is_string($rutaUsuariosV3) &&
    preg_match(
        '~\A/api/usuario(?:/([^/]+))?/?\z~',
        $rutaUsuariosV3,
        $partesUsuarioV3
    ) === 1
) {
    $metodoUsuarioV3 = strtoupper(
        (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')
    );

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Cache-Control: no-store');

    if ($metodoUsuarioV3 === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    try {
        require_once __DIR__ . '/servicios/ensamblador.php';

        // IMPORTANTE:
        // Revisar JWT y rol ADMIN antes del controlador.
        $guardiaUsuariosV3 = crearGuardiaAccesoV3();

        $guardiaUsuariosV3->autorizar(
            $metodoUsuarioV3
        );

        $controladorUsuariosV3 =
            crearControladorUsuariosV3();

        $controladorUsuariosV3->procesarPeticion(
            $metodoUsuarioV3,
            $partesUsuarioV3[1] ?? null
        );

        exit;

    } catch (Throwable $error) {
        error_log(
            'Error interno en ruta de usuarios V3: ' .
            $error->getMessage()
        );

        http_response_code(503);

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        echo json_encode([
            'estado' => 503,
            'mensaje' => 'No fue posible atender la solicitud.'
        ]);

        exit;
    }
}



/*
=====================================================
GUARDIA_API_V3_ACTIVA

QUE ES:
Proteccion de las rutas /api/* mediante JWT Bearer.

POR QUE:
Ningun controlador de recursos debe ejecutarse
antes de verificar identidad y permisos.

QUE NO HACE:
- No valida contrasenas.
- No realiza consultas SQL.
- No modifica registros.
- No bloquea el endpoint publico /api/login.
=====================================================
*/

$rutaAccesoV3 = rtrim(
    (string) parse_url(
        $_SERVER['REQUEST_URI'] ?? '/',
        PHP_URL_PATH
    ),
    '/'
);

$metodoAccesoV3 = strtoupper(
    (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')
);

if (
    str_starts_with($rutaAccesoV3, '/api/') &&
    $rutaAccesoV3 !== '/api/login'
) {
    // Mantener las cabeceras de CORS utilizadas en V2.
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Responder a solicitudes preliminares del navegador.
    if ($metodoAccesoV3 === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    try {
        require_once __DIR__ . '/servicios/ensamblador.php';

        $guardiaV3 = crearGuardiaAccesoV3();

        // Revisa JWT, cuenta activa y roles actuales.
        $guardiaV3->autorizar($metodoAccesoV3);

    } catch (Throwable $error) {
        error_log(
            'Error interno de seguridad V3: ' .
            $error->getMessage()
        );

        http_response_code(503);

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        header('Cache-Control: no-store');

        echo json_encode([
            'estado' => 503,
            'mensaje' => 'No fue posible verificar el acceso.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}


/*
 * LOGIN_V3_ROUTE
 *
 * QUE ES:
 * Ruta HTTP de inicio de sesion.
 *
 * POR QUE:
 * Conecta el controlador V3 sin modificar los
 * recursos existentes de las versiones 1 y 2.
 *
 * QUE NO HACE:
 * No ejecuta SQL ni contiene reglas de negocio.
 */

$rutaLoginV3 = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

if (rtrim((string) $rutaLoginV3, '/') === '/api/login') {
    try {
        require_once __DIR__ . '/servicios/ensamblador.php';

        $controladorLoginV3 = crearControladorAutenticacionV3();

        $controladorLoginV3->procesarPeticion(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );

    } catch (Throwable $error) {
        error_log(
            'Error interno en login V3: ' .
            $error->getMessage()
        );

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        http_response_code(500);

        echo json_encode([
            'estado' => 500,
            'mensaje' => 'No fue posible procesar el inicio de sesion.'
        ]);

        exit;
    }
}


/**
 * ARCHIVO: index.php
 * CAPA: Enrutador principal de la API
 *
 * QUE ES:
 * Punto de entrada de la API de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite identificar el recurso solicitado y dirigir
 * la peticion hacia su controlador correspondiente.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No implementa reglas de negocio.
 * No crea directamente repositorios ni servicios.
 * No administra formularios ni pantallas.
 */

// =========================================================
// 1. CONFIGURACION DE CABECERAS
// =========================================================

header('Content-Type: application/json; charset=UTF-8');

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

header('Access-Control-Allow-Headers: Content-Type, Authorization');

// =========================================================
// 2. IDENTIFICAR EL METODO DE LA PETICION
// =========================================================

$metodo = $_SERVER['REQUEST_METHOD'];

// Permitir las peticiones previas realizadas por el navegador.

if ($metodo === 'OPTIONS') {

    http_response_code(200);

    exit;

}

// =========================================================
// 3. IDENTIFICAR LA RUTA SOLICITADA
// =========================================================

$uri = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);

if (!is_string($uri)) {

    http_response_code(400);

    echo json_encode([
        'estado' => 400,
        'mensaje' => 'Solicitud inválida.',
        'detalle' => 'La ruta solicitada no es válida.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}

// =========================================================
// 4. RECURSOS OFICIALES DE LA VERSION 2
//
// Se conservan los seis catalogos de la V1.
// Se incorporan los diez recursos nuevos de la V2.
// =========================================================

$recursos = [

    // Catalogos de la V1.

    '/api/area_conocimiento',
    '/api/objetivo_desarrollo_sostenible',
    '/api/area_aplicacion',
    '/api/termino_clave',
    '/api/universidad',
    '/api/linea_investigacion',

    // Entidades de la V2.

    '/api/docente',
    '/api/grupo_investigacion',
    '/api/semillero',

    // Tablas puente.

    '/api/participa_grupo',
    '/api/participa_semillero',
    '/api/grupo_linea',
    '/api/semillero_linea',
    '/api/ac_linea',
    '/api/ods_linea',
    '/api/aa_linea'
];

// =========================================================
// 5. DIAGNOSTICO PRINCIPAL
//
// GET / permite comprobar que la API esta funcionando
// y conocer los recursos definidos para esta version.
// =========================================================

if ($uri === '/') {

    if ($metodo === 'GET') {

        http_response_code(200);

        echo json_encode([
            'mensaje' => 'API de Investigación funcionando',
            'version' => 'v2',
            'recursos' => $recursos,
            'contratos' =>
                'docs/spec_kit/versiones/v2_investigacion/6_contratos.md'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    } else {

        header('Allow: GET, OPTIONS');

        http_response_code(405);

        echo json_encode([
            'estado' => 405,
            'mensaje' => 'Método no permitido.',
            'detalle' => 'La ruta principal solo permite consultas.'
        ], JSON_UNESCAPED_UNICODE);

    }

    exit;

}

// =========================================================
// 6. SEPARAR LOS COMPONENTES DE LA RUTA
//
// Ejemplo:
//
// /api/docente/1017245
//
// partes[0] = api
// partes[1] = docente
// partes[2] = 1017245
//
// Para las tablas puente existe una segunda clave.
// =========================================================

$partes = explode('/', trim($uri, '/'));

// Validar la estructura general de las rutas.

if (
    ($partes[0] ?? '') !== 'api' ||
    !isset($partes[1]) ||
    count($partes) > 4 ||
    in_array('', $partes, true)
) {

    http_response_code(404);

    echo json_encode([
        'estado' => 404,
        'mensaje' => 'Recurso no encontrado.',
        'detalle' => 'La ruta solicitada no existe.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}

// Obtener el nombre del recurso.

$recurso = $partes[1];

// Obtener la primera clave de la ruta.

$parametro = isset($partes[2])
    ? rawurldecode($partes[2])
    : null;

// Obtener la segunda clave de la ruta.

$parametro2 = isset($partes[3])
    ? rawurldecode($partes[3])
    : null;

// Comprobar que el recurso pertenezca a la API.

if (!in_array('/api/' . $recurso, $recursos, true)) {

    http_response_code(404);

    echo json_encode([
        'estado' => 404,
        'mensaje' => 'Recurso no encontrado.',
        'detalle' => 'El recurso solicitado no existe.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}

// =========================================================
// 7. VALIDAR LA SEGUNDA CLAVE
//
// Solo las siete tablas puente utilizan dos claves
// primarias dentro de la ruta.
// =========================================================

$tablasPuente = [
    'participa_grupo',
    'participa_semillero',
    'grupo_linea',
    'semillero_linea',
    'ac_linea',
    'ods_linea',
    'aa_linea'
];

if (
    $parametro2 !== null &&
    !in_array($recurso, $tablasPuente, true)
) {

    http_response_code(404);

    echo json_encode([
        'estado' => 404,
        'mensaje' => 'Ruta inválida.',
        'detalle' => 'El recurso solicitado no utiliza dos claves.'
    ], JSON_UNESCAPED_UNICODE);

    exit;

}

// =========================================================
// 8. CARGAR EL ENSAMBLADOR Y ENRUTAR LA PETICION
//
// Cada recurso tiene su propio case y su propia
// funcion de ensamblaje.
//
// No se construyen nombres de clases dinamicamente.
// =========================================================

try {

    require_once __DIR__ . '/servicios/ensamblador.php';

    switch ($recurso) {

        // =================================================
        // CATALOGOS DE LA VERSION 1
        // =================================================

        // 1. AREA DE CONOCIMIENTO

        case 'area_conocimiento':

            $controlador = crearControladorAreaConocimiento();

            $controlador->procesarPeticion(
                $metodo,
                $parametro
            );

            break;

        // 2. OBJETIVO DE DESARROLLO SOSTENIBLE

        case 'objetivo_desarrollo_sostenible':

            $controlador =
                crearControladorObjetivoDesarrolloSostenible();

            $controlador->procesarPeticion(
                $metodo,
                $parametro
            );

            break;

        // 3. AREA DE APLICACION

        case 'area_aplicacion':

            $controlador = crearControladorAreaAplicacion();

            $controlador->procesarPeticion(
                $metodo,
                $parametro
            );

            break;

        // 4. TERMINO CLAVE

        case 'termino_clave':

            $controlador = crearControladorTerminoClave();

            $controlador->procesarPeticion(
                $metodo,
                $parametro
            );

            break;

        // 5. UNIVERSIDAD

        case 'universidad':

            $controlador = crearControladorUniversidad();

            $controlador->procesarPeticion(
                $metodo,
                $parametro
            );

            break;

        // 6. LINEA DE INVESTIGACION

        case 'linea_investigacion':

            $controlador = crearControladorLineaInvestigacion();

            $controlador->procesarPeticion(
                $metodo,
                $parametro
            );

            break;

        // =================================================
        // ENTIDADES DE LA VERSION 2
        // =================================================

        // 7. DOCENTE

        case 'docente':

            $controlador = crearControladorDocente();

            $controlador->procesarPeticion(
                $metodo,
                $parametro
            );

            break;

        // 8. GRUPO DE INVESTIGACION
        // Maestro-detalle mediante procedimientos almacenados.

        case 'grupo_investigacion':

            $controlador = crearControladorGrupoInvestigacion();

            $controlador->procesarPeticion(
                $metodo,
                $parametro
            );

            break;

        // 9. SEMILLERO
        // Recurso de solo lectura.

        case 'semillero':

            $controlador = crearControladorSemillero();

            $controlador->procesarPeticion(
                $metodo,
                $parametro
            );

            break;

        // =================================================
        // TABLAS PUENTE DE LA VERSION 2
        //
        // Estas reciben dos claves en la ruta.
        // =================================================

        // 10. PARTICIPA GRUPO

        case 'participa_grupo':

            $controlador = crearControladorParticipaGrupo();

            $controlador->procesarPeticion(
                $metodo,
                $parametro,
                $parametro2
            );

            break;

        // 11. PARTICIPA SEMILLERO

        case 'participa_semillero':

            $controlador = crearControladorParticipaSemillero();

            $controlador->procesarPeticion(
                $metodo,
                $parametro,
                $parametro2
            );

            break;

        // 12. GRUPO LINEA

        case 'grupo_linea':

            $controlador = crearControladorGrupoLinea();

            $controlador->procesarPeticion(
                $metodo,
                $parametro,
                $parametro2
            );

            break;

        // 13. SEMILLERO LINEA

        case 'semillero_linea':

            $controlador = crearControladorSemilleroLinea();

            $controlador->procesarPeticion(
                $metodo,
                $parametro,
                $parametro2
            );

            break;

        // 14. AREA DE CONOCIMIENTO - LINEA

        case 'ac_linea':

            $controlador = crearControladorAcLinea();

            $controlador->procesarPeticion(
                $metodo,
                $parametro,
                $parametro2
            );

            break;

        // 15. OBJETIVO DE DESARROLLO SOSTENIBLE - LINEA

        case 'ods_linea':

            $controlador = crearControladorOdsLinea();

            $controlador->procesarPeticion(
                $metodo,
                $parametro,
                $parametro2
            );

            break;

        // 16. AREA DE APLICACION - LINEA

        case 'aa_linea':

            $controlador = crearControladorAaLinea();

            $controlador->procesarPeticion(
                $metodo,
                $parametro,
                $parametro2
            );

            break;

        // =================================================
        // RECURSO NO ENCONTRADO
        // =================================================

        default:

            http_response_code(404);

            echo json_encode([
                'estado' => 404,
                'mensaje' => 'Recurso no encontrado.',
                'detalle' => 'La ruta solicitada no existe.'
            ], JSON_UNESCAPED_UNICODE);

            break;

    }

// =========================================================
// 9. MANEJO DE ERRORES INESPERADOS
//
// Evita mostrar informacion tecnica al usuario.
// Registra el error en el servidor.
// =========================================================

} catch (Throwable $error) {

    error_log($error->getMessage());

    http_response_code(500);

    echo json_encode([
        'estado' => 500,
        'mensaje' => 'No fue posible completar la operación.',
        'detalle' => 'Ocurrió un problema inesperado en el servicio.'
    ], JSON_UNESCAPED_UNICODE);

}