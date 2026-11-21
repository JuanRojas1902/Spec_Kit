<?php
declare(strict_types=1);

require_once __DIR__ . '/../vistas/semillero/lista.php';
require_once __DIR__ . '/../vistas/semillero/formulario.php';

function idValidoSemillero(mixed $valor): ?int
{
    if (!is_scalar($valor)) {
        return null;
    }
    $id = filter_var((string) $valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function datosFormularioSemillero(array $formulario): array
{
    $opcionales = ['mision', 'vision', 'fecha_creacion', 'url_colciencias'];
    $datos = [];
    foreach ($opcionales as $campo) {
        $val = trim((string) ($formulario[$campo] ?? ''));
        $datos[$campo] = $val === '' ? null : $val;
    }

    $grupo = trim((string) ($formulario['grupo_investigacion'] ?? ''));
    $datos['grupo_investigacion'] = $grupo === '' ? null : (int) $grupo;
    $datos['nombre'] = trim((string) ($formulario['nombre'] ?? ''));
    return $datos;
}

function obtenerGruposParaSelectSemillero(): array
{
    $resp = llamarApi('GET', 'grupo_investigacion');
    if (!$resp['codigo'] >= 200 || $resp['codigo'] >= 300) {
        return [];
    }
    return $resp['datos']['datos'] ?? [];
}

// 1. LISTAR
if ($ruta === '/semilleros' && $metodo === 'GET') {
    $respuesta = llamarApi('GET', 'semillero');
    $semilleros = $respuesta['datos']['datos'] ?? [];
    $tituloPagina = 'Semilleros de Investigación';
    $contenidoVista = __DIR__ . '/../vistas/semillero/lista.php';
    include __DIR__ . '/../vistas/compartidas/layout.php';
    exit;
}

// 2. NUEVO
if ($ruta === '/semilleros/nuevo' && $metodo === 'GET') {
    $grupos = obtenerGruposParaSelectSemillero();
    $tituloPagina = 'Nuevo Semillero de Investigación';
    $contenidoVista = __DIR__ . '/../vistas/semillero/formulario.php';
    include __DIR__ . '/../vistas/compartidas/layout.php';
    exit;
}

// 3. EDITAR
if ($ruta === '/semilleros/editar' && $metodo === 'GET') {
    $id = idValidoSemillero($_GET['id'] ?? null);
    if ($id === null) {
        http_response_code(400);
        exit('ID de semillero no válido.');
    }
    $respuesta = llamarApi('GET', 'semillero', (string) $id);
    if (!$respuesta['codigo'] >= 200 || $respuesta['codigo'] >= 300) {
        http_response_code(404);
        exit('Semillero no encontrado.');
    }
    $valores = $respuesta['datos'];
    $grupos = obtenerGruposParaSelectSemillero();
    $modo = 'editar';
    $tituloPagina = 'Editar Semillero de Investigación';
    $contenidoVista = __DIR__ . '/../vistas/semillero/formulario.php';
    include __DIR__ . '/../vistas/compartidas/layout.php';
    exit;
}

// 4. CREAR (POST)
if ($ruta === '/semilleros/crear' && $metodo === 'POST') {
    $id = idValidoSemillero($_POST['id'] ?? null);
    if ($id === null) {
        http_response_code(422);
        exit('El ID debe ser un número entero positivo.');
    }
    $datos = datosFormularioSemillero($_POST);
    $datos['id'] = $id;

    $respuesta = llamarApi('POST', 'semillero', '', $datos);
    if ($respuesta['codigo'] === 201 || $respuesta['codigo'] === 200) {
        $_SESSION['mensaje_semillero'] = 'Semillero registrado correctamente.';
        header('Location: /semilleros', true, 303);
        exit;
    }
    http_response_code(422);
    exit('Error al registrar el semillero en la API.');
}

// 5. ACTUALIZAR (POST / PUT)
if ($ruta === '/semilleros/actualizar' && $metodo === 'POST') {
    $id = idValidoSemillero($_POST['id'] ?? null);
    if ($id === null) {
        http_response_code(400);
        exit('ID no válido para actualizar.');
    }
    $datos = datosFormularioSemillero($_POST);
    $respuesta = llamarApi('PUT', 'semillero', (string) $id, $datos);

    if ($respuesta['codigo'] === 200) {
        $_SESSION['mensaje_semillero'] = 'Semillero actualizado correctamente.';
        header('Location: /semilleros', true, 303);
        exit;
    }
    http_response_code(422);
    exit('Error al actualizar el semillero.');
}