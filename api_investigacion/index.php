<?php
declare(strict_types=1);

// 1. Requerir todos los controladores
require_once __DIR__ . '/controladores/ControladorAreaConocimiento.php';
require_once __DIR__ . '/controladores/ControladorUniversidad.php';
require_once __DIR__ . '/controladores/ControladorObjetivoDesarrolloSostenible.php';
require_once __DIR__ . '/controladores/ControladorAreaAplicacion.php';
require_once __DIR__ . '/controladores/ControladorTerminoClave.php';
require_once __DIR__ . '/controladores/ControladorLineaInvestigacion.php';

// 2. Configuración global: Siempre respondemos en JSON y permitimos CORS.
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
// Respuesta rápida para el pre-flight de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 3. Capturamos qué quiere hacer el usuario y a qué ruta
$metodo = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 4. Ruta de Diagnóstico
if ($uri === '/') {
    if ($metodo === 'GET') {
        http_response_code(200);
        echo json_encode([
            "mensaje" => "API de Investigación funcionando",
            "version" => "v1"
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(405);
        echo json_encode(["estado" => 405, "mensaje" => "Método no permitido."]);
    }
    exit();
}

// 5. Desarmamos la URL
$partes = explode('/', trim($uri, '/'));

if ($partes[0] !== 'api') {
    http_response_code(404);
    echo json_encode(["estado" => 404, "mensaje" => "Ruta no encontrada."]);
    exit();
}

$recurso = $partes[1] ?? null;
$idStr = isset($partes[2]) ? urldecode($partes[2]) : null;

// Ejemplo de cómo conectarlo en tu Front Controller (index.php) V2
if ($recurso === 'grupo_investigacion') {
    $repositorio = new RepositorioGrupoInvestigacionMariaDB($pdo);
    $servicio = new ServicioGrupoInvestigacion($repositorio);
    $controlador = new ControladorGrupoInvestigacion($servicio);
    $controlador->manejarPeticion($metodo, $id);
    exit;
}

// 6. El Switch Maestro
switch ($recurso) {
    
    case 'area_conocimiento':
        $controlador = new ControladorAreaConocimiento();
        $controlador->procesarPeticion($metodo, $idStr);
        break;

    case 'universidad':
        $controlador = new ControladorUniversidad();
        $controlador->procesarPeticion($metodo, $idStr);
        break;

    case 'objetivo_desarrollo_sostenible':
        $controlador = new ControladorObjetivoDesarrolloSostenible();
        $controlador->procesarPeticion($metodo, $idStr);
        break;

    case 'area_aplicacion':
        $controlador = new ControladorAreaAplicacion();
        $controlador->procesarPeticion($metodo, $idStr);
        break;

    case 'termino_clave':
        $controlador = new ControladorTerminoClave();
        $controlador->procesarPeticion($metodo, $idStr);
        break;

    case 'linea_investigacion':
        $controlador = new ControladorLineaInvestigacion();
        $controlador->procesarPeticion($metodo, $idStr);
        break;

    default:
        http_response_code(404);
        echo json_encode(["estado" => 404, "mensaje" => "Ruta no encontrada."]);
        break;
}