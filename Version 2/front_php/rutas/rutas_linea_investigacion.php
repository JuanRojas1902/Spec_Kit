<?php
declare(strict_types=1);

require_once __DIR__ . '/../vistas/linea_investigacion/lista.php';
require_once __DIR__ . '/../vistas/linea_investigacion/formulario.php';

function idValidoLinea(mixed $valor): ?int
{
    if (!is_scalar($valor)) {
        return null;
    }
    $id = filter_var((string) $valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function datosFormularioLinea(array $formulario): array
{
    $objetivo = trim((string) ($formulario['objetivo'] ?? ''));
    return [
        'nombre' => trim((string) ($formulario['nombre'] ?? '')),
        'objetivo' => $objetivo === '' ? null : $objetivo,
    ];
}

// 1. LISTAR
if ($ruta === '/lineas-investigacion' && $metodo === 'GET') {
    $respuesta = llamarApi('GET', 'linea_investigacion');
    $lineas = $respuesta['datos']['datos'] ?? [];
    $tituloPagina = 'Líneas de Investigación';
    $contenidoVista = __DIR__ . '/../vistas/linea_investigacion/lista.php';
    include __DIR__ . '/../vistas/compartidas/layout.php';
    exit;
}

// 2. NUEVO
if ($ruta === '/lineas-investigacion/nuevo' && $metodo === 'GET') {
    $tituloPagina = 'Nueva Línea de Investigación';
    $contenidoVista = __DIR__ . '/../vistas/linea_investigacion/formulario.php';
    include __DIR__ . '/../vistas/compartidas/layout.php';
    exit;
}

// 3. EDITAR
if ($ruta === '/lineas-investigacion/editar' && $metodo === 'GET') {
    $id = idValidoLinea($_GET['id'] ?? null);
    if ($id === null) {
        http_response_code(400);
        exit('ID de línea no válido.');
    }
    $respuesta = llamarApi('GET', 'linea_investigacion', (string) $id);
    if (!$respuesta['codigo'] >= 200 || $respuesta['codigo'] >= 300) {
        http_response_code(404);
        exit('Línea de investigación no encontrada.');
    }
    $valores = $respuesta['datos'];
    $modo = 'editar';
    $tituloPagina = 'Editar Línea de Investigación';
    $contenidoVista = __DIR__ . '/../vistas/linea_investigacion/formulario.php';
    include __DIR__ . '/../vistas/compartidas/layout.php';
    exit;
}

// 4. CREAR (POST)
if ($ruta === '/lineas-investigacion/crear' && $metodo === 'POST') {
    $id = idValidoLinea($_POST['id'] ?? null);
    if ($id === null) {
        http_response_code(422);
        exit('El ID debe ser un número entero positivo.');
    }
    $datos = datosFormularioLinea($_POST);
    $datos['id'] = $id;

    $respuesta = llamarApi('POST', 'linea_investigacion', '', $datos);
    if ($respuesta['codigo'] === 201 || $respuesta['codigo'] === 200) {
        $_SESSION['mensaje_linea'] = 'Línea de investigación registrada correctamente.';
        header('Location: /lineas-investigacion', true, 303);
        exit;
    }
    http_response_code(422);
    exit('Error al registrar la línea de investigación en la API.');
}

// 5. ACTUALIZAR (POST / PUT)
if ($ruta === '/lineas-investigacion/actualizar' && $metodo === 'POST') {
    $id = idValidoLinea($_POST['id'] ?? null);
    if ($id === null) {
        http_response_code(400);
        exit('ID no válido para actualizar.');
    }
    $datos = datosFormularioLinea($_POST);
    $respuesta = llamarApi('PUT', 'linea_investigacion', (string) $id, $datos);

    if ($respuesta['codigo'] === 200) {
        $_SESSION['mensaje_linea'] = 'Línea de investigación actualizada correctamente.';
        header('Location: /lineas-investigacion', true, 303);
        exit;
    }
    http_response_code(422);
    exit('Error al actualizar la línea de investigación.');
}