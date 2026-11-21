<?php
declare(strict_types=1);

/**
 * Qué hace: Se comunica con la API mediante peticiones HTTP (curl).
 * Por qué está aquí: Es el puente entre el Front y el Backend. 
 * Regla innegociable: Trabaja EXCLUSIVAMENTE con arreglos. No importa ninguna clase del Backend.
 */

/**
 * ============================================================================
 * FUNCIÓN 1: OBTENER LISTA (Usada en vistas/lista.php)
 * ============================================================================
 */
function obtener_areas_conocimiento(): array 
{
    // Dentro de Docker, el host no es "localhost", sino el nombre del contenedor de la API.
    $url = 'http://api-investigacion/api/area_conocimiento';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Timeout corto para que la pantalla no se quede cargando si la API se cae
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); 
    
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // CRITERIO P7: Manejo de caída de la API. Si falla, devolvemos un arreglo con el error.
    if ($respuesta === false || $codigoHttp >= 500) {
        return ['error_api' => 'La conexión con el servidor no está disponible.'];
    }

    // RF1: Si no hay filas activas, la API responde 204 sin cuerpo.
    if ($codigoHttp === 204) {
        return [];
    }

    $datosDecodificados = json_decode($respuesta, true);
    return $datosDecodificados['datos'] ?? [];
}

/**
 * ============================================================================
 * FUNCIÓN 2: CREAR (Usada en vistas/formulario.php)
 * ============================================================================
 */
function crear_area_conocimiento(array $datos): array 
{
    $url = 'http://api-investigacion/api/area_conocimiento';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_POST, true);
    
    // Convertimos el arreglo de PHP a JSON puro para la API
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $respuesta = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($respuesta === false || $codigoHttp >= 500) {
        return ['error_api' => 'La conexión con el servidor falló o la llave ya existe.'];
    }

    $datosDecodificados = json_decode($respuesta, true) ?? [];

    // Si el código es 422, la API nos devolvió la lista de errores de validación
    if ($codigoHttp === 422) {
        return ['errores' => $datosDecodificados['errores'] ?? ['Error de validación desconocido.']];
    }

    return $datosDecodificados;
}