<?php

declare(strict_types=1);

/*
 * SEGURIDAD V3
 *
 * QUE ES:
 * Obtiene la cabecera Bearer desde la sesion PHP.
 *
 * POR QUE:
 * La API necesita el JWT para autorizar peticiones.
 *
 * QUE NO HACE:
 * No conecta con MariaDB.
 * No genera ni firma tokens.
 * No almacena tokens en el navegador.
 */
function frontCabecerasBearerV3(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return [];
    }

    $token = $_SESSION['jwt'] ?? null;
    $expiracion = $_SESSION['jwt_expira_en'] ?? 0;

    if (
        !is_string($token) ||
        strlen($token) > 4096 ||
        time() >= (int) $expiracion ||
        preg_match(
            '/\A[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\z/',
            $token
        ) !== 1
    ) {
        return [];
    }

    return ['Authorization: Bearer ' . $token];
}


/**
 * ARCHIVO: cliente_api.php
 * CAPA: Frontend / Cliente HTTP
 *
 * QUE ES:
 * Cliente encargado de comunicar el frontend
 * con los 16 recursos de la API de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Centraliza las peticiones realizadas mediante cURL
 * y permite interpretar las respuestas de la API.
 *
 * Cada recurso tiene funciones independientes,
 * respetando el Articulo 10 de la constitucion.
 *
 * QUE NO HACE:
 * No establece conexiones con MariaDB.
 * No ejecuta consultas SQL.
 * No contiene formularios HTML.
 * No implementa reglas de negocio.
 * No almacena contrasenas.
 */


// =========================================================
// 1. OBTENER LA DIRECCION DE LA API
// =========================================================

function obtener_url_api(): string
{
    $url = getenv('URL_API');

    if ($url === false || $url === '') {
        $url = 'http://api-facturas:8026';
    }

    return rtrim($url, '/');
}


// =========================================================
// 2. GENERAR UN RESULTADO DE ERROR
// =========================================================

function resultado_error_api(
    int $estado,
    string $mensaje,
    array $errores = [],
    string $detalle = ''
): array {

    return [
        'ok' => false,
        'estado' => $estado,
        'respuesta' => [],
        'datos' => [],
        'mensaje' => $mensaje,
        'errores' => $errores,
        'detalle' => $detalle
    ];
}


// =========================================================
// 3. REALIZAR UNA PETICION A LA API
//
// Esta funcion solamente realiza la comunicacion.
// Las rutas de los recursos se declaran individualmente.
// =========================================================

function cliente_api_peticion(
    string $metodo,
    string $ruta,
    ?array $cuerpo = null
): array {

    if (!function_exists('curl_init')) {

        return resultado_error_api(
            0,
            'El servicio no está disponible.'
        );
    }

    $url = obtener_url_api() . $ruta;

    $conexion = curl_init($url);

    if ($conexion === false) {

        return resultado_error_api(
            0,
            'No fue posible establecer la conexión.'
        );
    }

    $opciones = [
        CURLOPT_CUSTOMREQUEST => $metodo,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => [
            ...frontCabecerasBearerV3(),
            'Content-Type: application/json',
            'Accept: application/json'
        ]
    ];


    // Convertir el cuerpo de la peticion en JSON.

    if ($cuerpo !== null) {

        $json = json_encode(
            (object) $cuerpo,
            JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {

            curl_close($conexion);

            return resultado_error_api(
                0,
                'No fue posible preparar los datos.'
            );
        }

        $opciones[CURLOPT_POSTFIELDS] = $json;
    }


    // Configurar y ejecutar la peticion.

    if (!curl_setopt_array($conexion, $opciones)) {

        curl_close($conexion);

        return resultado_error_api(
            0,
            'El servicio no está disponible.'
        );
    }

    $respuesta = curl_exec($conexion);

    $estado = (int) curl_getinfo(
        $conexion,
        CURLINFO_HTTP_CODE
    );

    curl_close($conexion);


    // Error de comunicacion.

    if ($respuesta === false || $estado === 0) {

        return resultado_error_api(
            0,
            'El servicio no está disponible. Intente nuevamente.'
        );
    }


    // Listado vacio.
    //
    // Se debe interpretar antes de intentar leer JSON.

    if ($estado === 204) {

        return [
            'ok' => true,
            'estado' => 204,
            'respuesta' => [
                'total' => 0,
                'datos' => []
            ],
            'datos' => [],
            'mensaje' => 'Todavía no hay datos.',
            'errores' => [],
            'detalle' => ''
        ];
    }


    // Interpretar la respuesta JSON.

    $json = json_decode($respuesta, true);


    // Respuesta exitosa.

    if ($estado >= 200 && $estado < 300) {

        if (!is_array($json)) {

            return resultado_error_api(
                $estado,
                'No fue posible interpretar la información recibida.'
            );
        }

        return [
            'ok' => true,
            'estado' => $estado,
            'respuesta' => $json,
            'datos' => $json['datos'] ?? $json,
            'mensaje' => $json['mensaje'] ?? '',
            'errores' => [],
            'detalle' => ''
        ];
    }


    // Error de validacion de los datos.

    if ($estado === 422) {

        $errores = [];

        if (is_array($json['errores'] ?? null)) {

            foreach ($json['errores'] as $error) {

                if (is_string($error)) {
                    $errores[] = $error;
                }
            }
        }

        return resultado_error_api(
            422,
            'Revise los datos ingresados.',
            $errores
        );
    }


    // Errores conocidos.
    //
    // No se muestran mensajes tecnicos del servidor.

    $mensaje = match ($estado) {

        400 => 'La solicitud contiene datos inválidos.',

        401 => 'No ha iniciado sesión o su sesión ha vencido.',

        403 => 'No tiene permiso para realizar esta operación.',

        404 => 'El registro solicitado no existe.',

        405 => 'Esta operación no está permitida.',

        409 => 'No fue posible realizar la operación. Revise los duplicados y las relaciones existentes.',

        default => 'No fue posible completar la operación.'
    };

    $detalle = '';

    if ($estado === 409) {

        $detalleOriginal = $json['detalle'] ?? '';

        $detalle = match ($detalleOriginal) {

            'Ya existe un registro o una relación con esa clave.'
                => 'El registro o la relación ya existe.',

            'Alguno de los códigos a los que apunta la ficha no existe.'
                => 'Alguno de los registros seleccionados no existe.',

            'No se puede retirar la ficha porque hay otras fichas que dependen de ella.'
                => 'Existen otros registros que dependen de este.',

            'Primero retire los semilleros de este grupo.'
                => 'Primero retire los semilleros de este grupo.',

            default => ''
        };
    }

    return resultado_error_api(
        $estado,
        $mensaje,
        [],
        $detalle
    );
}


// =========================================================
// 4. CATALOGO AREA DE CONOCIMIENTO
// =========================================================

function listar_areas_conocimiento(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET',
        '/api/area_conocimiento?limite=' . $limite
    );
}

function obtener_area_conocimiento(int $id): array
{
    return cliente_api_peticion(
        'GET',
        '/api/area_conocimiento/' . $id
    );
}

function crear_area_conocimiento(array $datos): array
{
    return cliente_api_peticion(
        'POST', '/api/area_conocimiento', $datos
    );
}

function reemplazar_area_conocimiento(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PUT', '/api/area_conocimiento/' . $id, $datos
    );
}

function actualizar_area_conocimiento(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PATCH', '/api/area_conocimiento/' . $id, $datos
    );
}

function retirar_area_conocimiento(int $id): array
{
    return cliente_api_peticion(
        'DELETE', '/api/area_conocimiento/' . $id
    );
}


// =========================================================
// 5. CATALOGO OBJETIVO DE DESARROLLO SOSTENIBLE
// =========================================================

function listar_objetivos_desarrollo_sostenible(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET',
        '/api/objetivo_desarrollo_sostenible?limite=' . $limite
    );
}

function obtener_objetivo_desarrollo_sostenible(int $id): array
{
    return cliente_api_peticion(
        'GET', '/api/objetivo_desarrollo_sostenible/' . $id
    );
}

function crear_objetivo_desarrollo_sostenible(array $datos): array
{
    return cliente_api_peticion(
        'POST', '/api/objetivo_desarrollo_sostenible', $datos
    );
}

function reemplazar_objetivo_desarrollo_sostenible(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PUT', '/api/objetivo_desarrollo_sostenible/' . $id, $datos
    );
}

function actualizar_objetivo_desarrollo_sostenible(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PATCH', '/api/objetivo_desarrollo_sostenible/' . $id, $datos
    );
}

function retirar_objetivo_desarrollo_sostenible(int $id): array
{
    return cliente_api_peticion(
        'DELETE', '/api/objetivo_desarrollo_sostenible/' . $id
    );
}


// =========================================================
// 6. CATALOGO AREA DE APLICACION
// =========================================================

function listar_areas_aplicacion(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/area_aplicacion?limite=' . $limite
    );
}

function obtener_area_aplicacion(int $id): array
{
    return cliente_api_peticion(
        'GET', '/api/area_aplicacion/' . $id
    );
}

function crear_area_aplicacion(array $datos): array
{
    return cliente_api_peticion(
        'POST', '/api/area_aplicacion', $datos
    );
}

function reemplazar_area_aplicacion(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PUT', '/api/area_aplicacion/' . $id, $datos
    );
}

function actualizar_area_aplicacion(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PATCH', '/api/area_aplicacion/' . $id, $datos
    );
}

function retirar_area_aplicacion(int $id): array
{
    return cliente_api_peticion(
        'DELETE', '/api/area_aplicacion/' . $id
    );
}


// =========================================================
// 7. CATALOGO TERMINO CLAVE
//
// La llave primaria es un texto.
// rawurlencode permite enviar espacios y caracteres
// especiales dentro de la direccion.
// =========================================================

function listar_terminos_clave(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/termino_clave?limite=' . $limite
    );
}

function obtener_termino_clave(string $termino): array
{
    return cliente_api_peticion(
        'GET', '/api/termino_clave/' . rawurlencode($termino)
    );
}

function crear_termino_clave(array $datos): array
{
    return cliente_api_peticion(
        'POST', '/api/termino_clave', $datos
    );
}

function reemplazar_termino_clave(string $termino, array $datos): array
{
    return cliente_api_peticion(
        'PUT', '/api/termino_clave/' . rawurlencode($termino), $datos
    );
}

function actualizar_termino_clave(string $termino, array $datos): array
{
    return cliente_api_peticion(
        'PATCH', '/api/termino_clave/' . rawurlencode($termino), $datos
    );
}

function retirar_termino_clave(string $termino): array
{
    return cliente_api_peticion(
        'DELETE', '/api/termino_clave/' . rawurlencode($termino)
    );
}


// =========================================================
// 8. CATALOGO UNIVERSIDAD
// =========================================================

function listar_universidades(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/universidad?limite=' . $limite
    );
}

function obtener_universidad(int $id): array
{
    return cliente_api_peticion(
        'GET', '/api/universidad/' . $id
    );
}

function crear_universidad(array $datos): array
{
    return cliente_api_peticion(
        'POST', '/api/universidad', $datos
    );
}

function reemplazar_universidad(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PUT', '/api/universidad/' . $id, $datos
    );
}

function actualizar_universidad(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PATCH', '/api/universidad/' . $id, $datos
    );
}

function retirar_universidad(int $id): array
{
    return cliente_api_peticion(
        'DELETE', '/api/universidad/' . $id
    );
}


// =========================================================
// 9. CATALOGO LINEA DE INVESTIGACION
// =========================================================

function listar_lineas_investigacion(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/linea_investigacion?limite=' . $limite
    );
}

function obtener_linea_investigacion(int $id): array
{
    return cliente_api_peticion(
        'GET', '/api/linea_investigacion/' . $id
    );
}

function crear_linea_investigacion(array $datos): array
{
    return cliente_api_peticion(
        'POST', '/api/linea_investigacion', $datos
    );
}

function reemplazar_linea_investigacion(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PUT', '/api/linea_investigacion/' . $id, $datos
    );
}

function actualizar_linea_investigacion(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PATCH', '/api/linea_investigacion/' . $id, $datos
    );
}

function retirar_linea_investigacion(int $id): array
{
    return cliente_api_peticion(
        'DELETE', '/api/linea_investigacion/' . $id
    );
}


// =========================================================
// 10. DOCENTE
// =========================================================

function listar_docentes(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/docente?limite=' . $limite
    );
}

function obtener_docente(int $cedula): array
{
    return cliente_api_peticion(
        'GET', '/api/docente/' . $cedula
    );
}

function crear_docente(array $datos): array
{
    return cliente_api_peticion(
        'POST', '/api/docente', $datos
    );
}

function reemplazar_docente(int $cedula, array $datos): array
{
    return cliente_api_peticion(
        'PUT', '/api/docente/' . $cedula, $datos
    );
}

function actualizar_docente(int $cedula, array $datos): array
{
    return cliente_api_peticion(
        'PATCH', '/api/docente/' . $cedula, $datos
    );
}

function retirar_docente(int $cedula): array
{
    return cliente_api_peticion(
        'DELETE', '/api/docente/' . $cedula
    );
}


// =========================================================
// 11. GRUPO DE INVESTIGACION
//
// Recurso maestro-detalle.
// No admite PATCH.
// El listado no recibe limite.
// =========================================================

function listar_grupos_investigacion(): array
{
    return cliente_api_peticion(
        'GET', '/api/grupo_investigacion'
    );
}

function obtener_grupo_investigacion(int $id): array
{
    return cliente_api_peticion(
        'GET', '/api/grupo_investigacion/' . $id
    );
}

function crear_grupo_investigacion(array $datos): array
{
    return cliente_api_peticion(
        'POST', '/api/grupo_investigacion', $datos
    );
}

function reemplazar_grupo_investigacion(int $id, array $datos): array
{
    return cliente_api_peticion(
        'PUT', '/api/grupo_investigacion/' . $id, $datos
    );
}

function retirar_grupo_investigacion(int $id): array
{
    return cliente_api_peticion(
        'DELETE', '/api/grupo_investigacion/' . $id
    );
}


// =========================================================
// 12. SEMILLERO
//
// Recurso de solo lectura.
// Se crea y retira mediante el grupo de investigacion.
// =========================================================

function listar_semilleros(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/semillero?limite=' . $limite
    );
}

function obtener_semillero(int $id): array
{
    return cliente_api_peticion(
        'GET', '/api/semillero/' . $id
    );
}


// =========================================================
// 13. TABLA PUENTE PARTICIPA_GRUPO
// =========================================================

function listar_participa_grupo(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/participa_grupo?limite=' . $limite
    );
}

function asignar_participa_grupo(int $cedula, int $grupoId): array
{
    return cliente_api_peticion(
        'POST',
        '/api/participa_grupo',
        [
            'docente_cedula' => $cedula,
            'grupo_id' => $grupoId
        ]
    );
}

function retirar_participa_grupo(int $cedula, int $grupoId): array
{
    return cliente_api_peticion(
        'DELETE',
        '/api/participa_grupo/' . $cedula . '/' . $grupoId
    );
}


// =========================================================
// 14. TABLA PUENTE PARTICIPA_SEMILLERO
// =========================================================

function listar_participa_semillero(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/participa_semillero?limite=' . $limite
    );
}

function asignar_participa_semillero(int $cedula, int $semilleroId): array
{
    return cliente_api_peticion(
        'POST',
        '/api/participa_semillero',
        [
            'docente_cedula' => $cedula,
            'semillero_id' => $semilleroId
        ]
    );
}

function retirar_participa_semillero(int $cedula, int $semilleroId): array
{
    return cliente_api_peticion(
        'DELETE',
        '/api/participa_semillero/' . $cedula . '/' . $semilleroId
    );
}


// =========================================================
// 15. TABLA PUENTE GRUPO_LINEA
// =========================================================

function listar_grupo_linea(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/grupo_linea?limite=' . $limite
    );
}

function asignar_grupo_linea(int $grupoId, int $lineaId): array
{
    return cliente_api_peticion(
        'POST',
        '/api/grupo_linea',
        [
            'grupo_id' => $grupoId,
            'linea_id' => $lineaId
        ]
    );
}

function retirar_grupo_linea(int $grupoId, int $lineaId): array
{
    return cliente_api_peticion(
        'DELETE',
        '/api/grupo_linea/' . $grupoId . '/' . $lineaId
    );
}


// =========================================================
// 16. TABLA PUENTE SEMILLERO_LINEA
// =========================================================

function listar_semillero_linea(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/semillero_linea?limite=' . $limite
    );
}

function asignar_semillero_linea(int $semilleroId, int $lineaId): array
{
    return cliente_api_peticion(
        'POST',
        '/api/semillero_linea',
        [
            'semillero_id' => $semilleroId,
            'linea_id' => $lineaId
        ]
    );
}

function retirar_semillero_linea(int $semilleroId, int $lineaId): array
{
    return cliente_api_peticion(
        'DELETE',
        '/api/semillero_linea/' . $semilleroId . '/' . $lineaId
    );
}


// =========================================================
// 17. TABLA PUENTE AC_LINEA
// =========================================================

function listar_ac_linea(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/ac_linea?limite=' . $limite
    );
}

function asignar_ac_linea(int $areaConocimientoId, int $lineaId): array
{
    return cliente_api_peticion(
        'POST',
        '/api/ac_linea',
        [
            'area_conocimiento_id' => $areaConocimientoId,
            'linea_id' => $lineaId
        ]
    );
}

function retirar_ac_linea(int $areaConocimientoId, int $lineaId): array
{
    return cliente_api_peticion(
        'DELETE',
        '/api/ac_linea/' . $areaConocimientoId . '/' . $lineaId
    );
}


// =========================================================
// 18. TABLA PUENTE ODS_LINEA
// =========================================================

function listar_ods_linea(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/ods_linea?limite=' . $limite
    );
}

function asignar_ods_linea(int $odsId, int $lineaId): array
{
    return cliente_api_peticion(
        'POST',
        '/api/ods_linea',
        [
            'ods_id' => $odsId,
            'linea_id' => $lineaId
        ]
    );
}

function retirar_ods_linea(int $odsId, int $lineaId): array
{
    return cliente_api_peticion(
        'DELETE',
        '/api/ods_linea/' . $odsId . '/' . $lineaId
    );
}


// =========================================================
// 19. TABLA PUENTE AA_LINEA
// =========================================================

function listar_aa_linea(int $limite = 1000): array
{
    return cliente_api_peticion(
        'GET', '/api/aa_linea?limite=' . $limite
    );
}

function asignar_aa_linea(int $areaAplicacionId, int $lineaId): array
{
    return cliente_api_peticion(
        'POST',
        '/api/aa_linea',
        [
            'area_aplicacion_id' => $areaAplicacionId,
            'linea_id' => $lineaId
        ]
    );
}

function retirar_aa_linea(int $areaAplicacionId, int $lineaId): array
{
    return cliente_api_peticion(
        'DELETE',
        '/api/aa_linea/' . $areaAplicacionId . '/' . $lineaId
    );
}