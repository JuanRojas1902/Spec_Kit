<?php
declare(strict_types=1);

require_once __DIR__ . '/../vistas/grupo_investigacion/lista.php';
require_once __DIR__ . '/../vistas/grupo_investigacion/formulario.php';

function idValidoGrupo(mixed $valor): ?int
{
    if (!is_scalar($valor)) {
        return null;
    }
    $id = filter_var((string) $valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function datosFormularioGrupo(array $formulario): array
{
    $opcionales = ['url_gruplac', 'categoria', 'convocatoria', 'ambito', 'fecha_fundacion'];
    $datos = [];
    foreach ($opcionales as $campo) {
        $val = trim((string) ($formulario[$campo] ?? ''));
        $datos[$campo] = $val === '' ? null : $val;
    }

    $uni = trim((string) ($formulario['universidsad'] ?? ''));
    $datos['universidsad'] = $uni === '' ? null : (int) $uni;

    $interno = (string) ($formulario['interno'] ?? '1');
    $datos['interno'] = in_array($interno, ['0', '1'], true) ? (int) $interno : 1;
    $datos['nombre'] = trim((string) ($formulario['nombre'] ?? ''));
    return $datos;
}

function obtenerUniversidadesParaSelect(): array
{
    $resp = listarUniversidades();
    if (!$resp['correcta']) {
        return [];
    }
    return $resp['datos']['datos'] ?? [];
}

// 1. LISTAR
if ($ruta === '/grupos-investigacion' && $metodo === 'GET') {
    $respuesta = llamarApi('GET', 'grupo_investigacion');
    $grupos = $respuesta['datos']['datos'] ?? [];
    $tituloPagina = 'Grupos de Investigación';
    $contenidoVista = __DIR__ . '/../vistas/grupo_investigacion/lista.php';
    include __DIR__ . '/../vistas/compartidas/layout.php';
    exit;
}

// 2. FORMULARIO NUEVO
if ($ruta === '/grupos-investigacion/nuevo' && $metodo === 'GET') {
    $universidades = obtenerUniversidadesParaSelect();
    $tituloPagina = 'Nuevo Grupo de Investigación';
    $contenidoVista = __DIR__ . '/../vistas/grupo_investigacion/formulario.php';
    include __DIR__ . '/../vistas/compartidas/layout.php';
    exit;
}

// 3. FORMULARIO EDITAR
if ($ruta === '/grupos-investigacion/editar' && $metodo === 'GET') {
    $id = idValidoGrupo($_GET['id'] ?? null);
    if ($id === null) {
        http_response_code(400);
        exit('ID de grupo no válido.');
    }
    $respuesta = llamarApi('GET', 'grupo_investigacion', (string) $id);
    if (!$respuesta['correcta']) {
        http_response_code(404);
        exit('Grupo no encontrado.');
    }
    $valores = $respuesta['datos'];
    $universidades = obtenerUniversidadesParaSelect();
    $modo = 'editar';
    $tituloPagina = 'Editar Grupo de Investigación';
    $contenidoVista = __DIR__ . '/../vistas/grupo_investigacion/formulario.php';
    include __DIR__ . '/../vistas/compartidas/layout.php';
    exit;
}

// 4. CREAR (POST)
if ($ruta === '/grupos-investigacion/crear' && $metodo === 'POST') {
    $id = idValidoGrupo($_POST['id'] ?? null);
    if ($id === null) {
        http_response_code(422);
        exit('El ID debe ser un número entero positivo.');
    }
    $datos = datosFormularioGrupo($_POST);
    $datos['id'] = $id;

    $respuesta = llamarApi('POST', 'grupo_investigacion', '', $datos);
    if ($respuesta['codigo'] === 201 || $respuesta['codigo'] === 200) {
        $_SESSION['mensaje_grupo_investigacion'] = 'Grupo registrado correctamente.';
        header('Location: /grupos-investigacion', true, 303);
        exit;
    }
    http_response_code(422);
    exit('Error al registrar el grupo en la API.');
}

// 5. ACTUALIZAR (POST / PUT)
if ($ruta === '/grupos-investigacion/actualizar' && $metodo === 'POST') {
    $id = idValidoGrupo($_POST['id'] ?? null);
    if ($id === null) {
        http_response_code(400);
        exit('ID no válido para actualizar.');
    }
    $datos = datosFormularioGrupo($_POST);
    $respuesta = llamarApi('PUT', 'grupo_investigacion', (string) $id, $datos);

    if ($respuesta['codigo'] === 200) {
        $_SESSION['mensaje_grupo_investigacion'] = 'Grupo actualizado correctamente.';
        header('Location: /grupos-investigacion', true, 303);
        exit;
    }
    http_response_code(422);
    exit('Error al actualizar el grupo.');
}