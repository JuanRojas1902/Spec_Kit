<?php

declare(strict_types=1);

/**
 * ARCHIVO: index.php
 * CAPA: Frontend / Enrutador
 *
 * QUE ES:
 * Punto de entrada de las pantallas del sistema
 * de investigacion en su Version 2.
 *
 * POR QUE ESTA AQUI:
 * Permite administrar las rutas, las sesiones,
 * los formularios y las llamadas al cliente de la API.
 *
 * Cada uno de los 16 recursos tiene su propio
 * bloque de rutas.
 *
 * QUE NO HACE:
 * No establece conexiones con MariaDB.
 * No ejecuta consultas SQL.
 * No implementa las reglas de negocio de la API.
 * No almacena contrasenas en el codigo.
 */


require_once __DIR__ . '/cliente_api.php';
require_once __DIR__ . '/seguridad_v3.php';


// =========================================================
// 1. IDENTIFICAR LA RUTA SOLICITADA
// =========================================================

$uri = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

$partes = explode('/', trim((string) $uri, '/'));

$partes = array_map('rawurldecode', $partes);

$ruta = $partes[0] ?? '';
$segundo = $partes[1] ?? '';
$tercero = $partes[2] ?? '';

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';


// =========================================================
// 2. ARCHIVOS PUBLICOS
//
// Permite entregar los archivos locales de Bootstrap,
// marca.css y las imagenes autorizadas.
// =========================================================

if ($ruta === 'publico') {

    $directorio = realpath(__DIR__ . '/publico');

    $archivo = realpath(
        __DIR__ . '/publico/' .
        implode('/', array_slice($partes, 1))
    );

    $tiposPermitidos = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'svg' => 'image/svg+xml'
    ];

    if (
        $directorio !== false &&
        $archivo !== false &&
        is_file($archivo) &&
        str_starts_with(
            $archivo,
            $directorio . DIRECTORY_SEPARATOR
        )
    ) {

        $extension = strtolower(
            pathinfo($archivo, PATHINFO_EXTENSION)
        );

        if (isset($tiposPermitidos[$extension])) {

            header(
                'Content-Type: ' .
                $tiposPermitidos[$extension]
            );

            header('X-Content-Type-Options: nosniff');

            readfile($archivo);

            exit;
        }
    }

    http_response_code(404);

    exit;
}


// =========================================================
// 3. CONFIGURAR LAS SESIONES
// =========================================================

ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax'
]);

session_start();

header('Cache-Control: no-store');


// Generar un token para proteger los formularios.

if (!isset($_SESSION['csrf'])) {

    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['csrf'];


// =========================================================
// 4. FUNCIONES AUXILIARES
//
// No reciben nombres de recursos para construir rutas.
// Solamente realizan operaciones comunes de la pantalla.
// =========================================================

function front_redirigir(string $ruta): never
{
    header('Location: ' . $ruta, true, 303);

    exit;
}


function front_texto(mixed $valor): string
{
    return is_string($valor) ? trim($valor) : '';
}


function front_entero(mixed $valor): int
{
    if (!is_string($valor) && !is_int($valor)) {
        return 0;
    }

    $numero = filter_var(
        $valor,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1,
                'max_range' => 2147483647
            ]
        ]
    );

    return $numero === false ? 0 : $numero;
}


function front_errores(array $resultado): array
{
    $errores = $resultado['errores'] ?? [];

    if (is_array($errores) && $errores !== []) {
        return $errores;
    }

    $mensaje = $resultado['mensaje']
        ?? 'No fue posible completar la operación.';

    $detalle = $resultado['detalle'] ?? '';

    if ($detalle !== '') {
        $mensaje .= ' ' . $detalle;
    }

    return [$mensaje];
}


function front_filas(
    array $resultado,
    array &$errores
): array {

    if (!$resultado['ok']) {

        $errores = array_merge(
            $errores,
            front_errores($resultado)
        );

        return [];
    }

    $datos = $resultado['datos'] ?? [];

    if (!is_array($datos) || !array_is_list($datos)) {

        $errores[] =
            'No fue posible interpretar la información recibida.';

        return [];
    }

    return $datos;
}


function front_ficha(
    array $resultado,
    array &$errores
): array {

    if (!$resultado['ok']) {

        $errores = array_merge(
            $errores,
            front_errores($resultado)
        );

        return [];
    }

    $datos = $resultado['datos'] ?? [];

    return is_array($datos) ? $datos : [];
}


function front_guardar_resultado(
    array $resultado,
    string $destino,
    string $mensaje,
    array &$errores
): void {

    if ($resultado['ok']) {

        $_SESSION['mensaje_exito'] = $mensaje;

        front_redirigir($destino);
    }

    $errores = array_merge(
        $errores,
        front_errores($resultado)
    );
}


function front_retirar_resultado(
    array $resultado,
    string $destino
): never {

    if ($resultado['ok']) {

        $_SESSION['mensaje_exito'] =
            'Se retiró correctamente.';

    } else {

        $_SESSION['errores'] =
            front_errores($resultado);
    }

    front_redirigir($destino);
}


// Seleccionar solamente los valores modificados
// para una actualizacion parcial.

function front_cambios(
    array $datos,
    array $resultadoOriginal,
    array &$errores
): ?array {

    if (!$resultadoOriginal['ok']) {

        $errores = array_merge(
            $errores,
            front_errores($resultadoOriginal)
        );

        return null;
    }

    $original = $resultadoOriginal['datos'] ?? [];

    $cambios = [];

    foreach ($datos as $campo => $valor) {

        if (
            $valor !== '' &&
            $valor !== null &&
            (string) $valor !==
                (string) ($original[$campo] ?? '')
        ) {

            $cambios[$campo] = $valor;
        }
    }

    if ($cambios === []) {

        $errores[] = 'No hay cambios para guardar.';

        return null;
    }

    return $cambios;
}


// Preparar los renglones de semilleros
// enviados desde el formulario de grupos.

function front_detalle_semilleros(
    mixed $nombres,
    array &$errores
): array {

    $detalle = [];

    if (!is_array($nombres)) {

        $errores[] = 'Los semilleros no son válidos.';

        return [];
    }

    foreach ($nombres as $nombre) {

        if (!is_string($nombre)) {

            $errores[] = 'Nombre de semillero inválido.';

            continue;
        }

        $nombre = trim($nombre);

        if ($nombre !== '') {

            $detalle[] = [
                'nombre' => $nombre
            ];
        }
    }

    return $detalle;
}


// =========================================================
// 5. VARIABLES COMPARTIDAS CON LAS VISTAS
// =========================================================

$vistaContenido = null;
$titulo = 'Sistema de Investigación';

$erroresValidacion = $_SESSION['errores'] ?? [];
$mensajeAlerta = $_SESSION['mensaje_exito'] ?? '';

unset(
    $_SESSION['errores'],
    $_SESSION['mensaje_exito']
);

$registros = [];
$valores = [];
$opciones1 = [];
$opciones2 = [];
$universidades = [];
$grupos = [];

$modo = 'crear';
$puedeGuardar = true;

$autenticado = frontObtenerJwtV3() !== null;


// =========================================================
// 6. COMPROBAR EL TOKEN DE LOS FORMULARIOS
// =========================================================

if ($metodo === 'POST') {

    $token = $_POST['csrf'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($csrf, $token)
    ) {

        http_response_code(403);

        exit(
            'La solicitud no es válida. ' .
            'Regrese a la pantalla anterior y vuelva a intentarlo.'
        );
    }
}


// =========================================================
// 7. INICIAR SESION
//
// Las credenciales se obtienen mediante variables
// de entorno. La contrasena se verifica con su hash.
// =========================================================

/*
 * LOGIN_FRONT_V3
 * Autenticacion contra la API y almacenamiento JWT.
 */
if ($ruta === 'login' && $segundo === '') {

    if ($autenticado) {
        front_redirigir('/');
    }

    $titulo = 'Iniciar sesion';
    $vistaContenido = __DIR__ . '/vistas/login_v3.php';

    if ($metodo === 'POST') {

        $usuario = front_texto($_POST['usuario'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        $valores = ['usuario' => $usuario];

        $bloqueadoHasta = (int) (
            $_SESSION['bloqueado_hasta'] ?? 0
        );

        if ($bloqueadoHasta > time()) {

            $erroresValidacion[] =
                'Demasiados intentos. Intente mas tarde.';

        } else {

            $resultado = frontIniciarSesionV3(
                $usuario,
                is_string($contrasena) ? $contrasena : ''
            );

            if (($resultado['ok'] ?? false) === true) {
                front_redirigir('/');
            }

            $_SESSION['intentos_login'] =
                (int) ($_SESSION['intentos_login'] ?? 0) + 1;

            if ($_SESSION['intentos_login'] >= 5) {
                $_SESSION['bloqueado_hasta'] = time() + 60;
                $_SESSION['intentos_login'] = 0;
            }

            $erroresValidacion[] = (string) (
                $resultado['mensaje'] ??
                'No fue posible iniciar sesion.'
            );
        }
    }
}
// =========================================================
// 8. CERRAR SESION
// =========================================================

elseif (
    $ruta === 'logout' &&
    $segundo === '' &&
    $metodo === 'POST'
) {

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {

        $parametros = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }

    session_destroy();

    front_redirigir('/login');
}


// =========================================================
// 9. PROTEGER LAS PANTALLAS DEL SISTEMA
// =========================================================

elseif (!$autenticado) {

    front_redirigir('/login');
}


/*
 * CONTROL_LECTURA_FRONT_V3
 *
 * QUE ES:
 * Bloquea operaciones de escritura y formularios
 * administrativos a usuarios sin rol ADMIN.
 *
 * POR QUE:
 * El rol CONSULTA solamente puede visualizar datos.
 *
 * QUE NO HACE:
 * No sustituye los controles JWT del backend.
 * No realiza SQL.
 * No modifica la base de datos.
 */
elseif (
    $autenticado &&
    !frontEsAdminV3() &&
    (
        !in_array(
            strtoupper($metodo),
            ['GET', 'HEAD'],
            true
        )
        ||
        in_array(
            strtolower($segundo),
            [
                'nuevo', 'nueva', 'crear',
                'editar', 'retirar', 'eliminar',
                'asignar', 'desactivar', 'activar'
            ],
            true
        )
        ||
        in_array(
            strtolower($tercero),
            [
                'nuevo', 'nueva', 'crear',
                'editar', 'retirar', 'eliminar',
                'asignar', 'desactivar', 'activar'
            ],
            true
        )
    )
) {
    http_response_code(403);

    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');

    exit(
        'Acceso denegado: esta cuenta solamente tiene permisos de lectura.'
    );
}



// =========================================================
// 10. PAGINA DE INICIO
// =========================================================

elseif (
    $ruta === '' &&
    $metodo === 'GET'
) {

    $vistaContenido =
        __DIR__ . '/vistas/inicio.php';
}


// =========================================================
// 11. AREA DE CONOCIMIENTO
// =========================================================

elseif ($ruta === 'area_conocimiento') {

    $titulo = 'Áreas de conocimiento';

    $id = front_entero($segundo);

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_areas_conocimiento(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/area_conocimiento_lista.php';

    } elseif (
        ($segundo === 'nuevo' && $tercero === '') ||
        ($id > 0 && $tercero === 'editar')
    ) {

        $modo = $segundo === 'nuevo'
            ? 'crear'
            : 'editar';

        $vistaContenido =
            __DIR__ . '/vistas/area_conocimiento_formulario.php';

        if ($metodo === 'POST') {

            $datos = [
                'gran_area' => front_texto(
                    $_POST['gran_area'] ?? ''
                ),
                'area' => front_texto(
                    $_POST['area'] ?? ''
                ),
                'disciplina' => front_texto(
                    $_POST['disciplina'] ?? ''
                )
            ];

            $valores = [
                'id' => $modo === 'crear'
                    ? front_entero($_POST['id'] ?? '')
                    : $id
            ] + $datos;

            $resultado = null;

            if ($modo === 'crear') {

                $resultado =
                    crear_area_conocimiento($valores);

            } elseif (
                ($_POST['accion'] ?? '') === 'parcial'
            ) {

                $cambios = front_cambios(
                    $datos,
                    obtener_area_conocimiento($id),
                    $erroresValidacion
                );

                if ($cambios !== null) {

                    $resultado =
                        actualizar_area_conocimiento(
                            $id,
                            $cambios
                        );
                }

            } else {

                $resultado =
                    reemplazar_area_conocimiento(
                        $id,
                        $datos
                    );
            }

            if ($resultado !== null) {

                front_guardar_resultado(
                    $resultado,
                    '/area_conocimiento',
                    'Se guardó el área de conocimiento.',
                    $erroresValidacion
                );
            }

        } elseif ($modo === 'editar') {

            $valores = front_ficha(
                obtener_area_conocimiento($id),
                $erroresValidacion
            );

            $puedeGuardar = $valores !== [];
        }

    } elseif (
        $id > 0 &&
        $tercero === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_area_conocimiento($id),
            '/area_conocimiento'
        );
    }
}


// =========================================================
// 12. OBJETIVO DE DESARROLLO SOSTENIBLE
// =========================================================

elseif ($ruta === 'objetivo_desarrollo_sostenible') {

    $titulo = 'Objetivos de desarrollo sostenible';

    $id = front_entero($segundo);

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_objetivos_desarrollo_sostenible(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/objetivo_desarrollo_sostenible_lista.php';

    } elseif (
        ($segundo === 'nuevo' && $tercero === '') ||
        ($id > 0 && $tercero === 'editar')
    ) {

        $modo = $segundo === 'nuevo'
            ? 'crear'
            : 'editar';

        $vistaContenido =
            __DIR__ . '/vistas/objetivo_desarrollo_sostenible_formulario.php';

        if ($metodo === 'POST') {

            $datos = [
                'nombre' => front_texto(
                    $_POST['nombre'] ?? ''
                ),
                'categoria' => front_texto(
                    $_POST['categoria'] ?? ''
                )
            ];

            $valores = [
                'id' => $modo === 'crear'
                    ? front_entero($_POST['id'] ?? '')
                    : $id
            ] + $datos;

            $resultado = null;

            if ($modo === 'crear') {

                $resultado =
                    crear_objetivo_desarrollo_sostenible(
                        $valores
                    );

            } elseif (
                ($_POST['accion'] ?? '') === 'parcial'
            ) {

                $cambios = front_cambios(
                    $datos,
                    obtener_objetivo_desarrollo_sostenible($id),
                    $erroresValidacion
                );

                if ($cambios !== null) {

                    $resultado =
                        actualizar_objetivo_desarrollo_sostenible(
                            $id,
                            $cambios
                        );
                }

            } else {

                $resultado =
                    reemplazar_objetivo_desarrollo_sostenible(
                        $id,
                        $datos
                    );
            }

            if ($resultado !== null) {

                front_guardar_resultado(
                    $resultado,
                    '/objetivo_desarrollo_sostenible',
                    'Se guardó el objetivo.',
                    $erroresValidacion
                );
            }

        } elseif ($modo === 'editar') {

            $valores = front_ficha(
                obtener_objetivo_desarrollo_sostenible($id),
                $erroresValidacion
            );

            $puedeGuardar = $valores !== [];
        }

    } elseif (
        $id > 0 &&
        $tercero === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_objetivo_desarrollo_sostenible($id),
            '/objetivo_desarrollo_sostenible'
        );
    }
}


// =========================================================
// 13. AREA DE APLICACION
// =========================================================

elseif ($ruta === 'area_aplicacion') {

    $titulo = 'Áreas de aplicación';

    $id = front_entero($segundo);

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_areas_aplicacion(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/area_aplicacion_lista.php';

    } elseif (
        ($segundo === 'nuevo' && $tercero === '') ||
        ($id > 0 && $tercero === 'editar')
    ) {

        $modo = $segundo === 'nuevo'
            ? 'crear'
            : 'editar';

        $vistaContenido =
            __DIR__ . '/vistas/area_aplicacion_formulario.php';

        if ($metodo === 'POST') {

            $datos = [
                'nombre' => front_texto(
                    $_POST['nombre'] ?? ''
                )
            ];

            $valores = [
                'id' => $modo === 'crear'
                    ? front_entero($_POST['id'] ?? '')
                    : $id
            ] + $datos;

            $resultado = null;

            if ($modo === 'crear') {

                $resultado =
                    crear_area_aplicacion($valores);

            } elseif (
                ($_POST['accion'] ?? '') === 'parcial'
            ) {

                $cambios = front_cambios(
                    $datos,
                    obtener_area_aplicacion($id),
                    $erroresValidacion
                );

                if ($cambios !== null) {

                    $resultado = actualizar_area_aplicacion(
                        $id,
                        $cambios
                    );
                }

            } else {

                $resultado = reemplazar_area_aplicacion(
                    $id,
                    $datos
                );
            }

            if ($resultado !== null) {

                front_guardar_resultado(
                    $resultado,
                    '/area_aplicacion',
                    'Se guardó el área de aplicación.',
                    $erroresValidacion
                );
            }

        } elseif ($modo === 'editar') {

            $valores = front_ficha(
                obtener_area_aplicacion($id),
                $erroresValidacion
            );

            $puedeGuardar = $valores !== [];
        }

    } elseif (
        $id > 0 &&
        $tercero === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_area_aplicacion($id),
            '/area_aplicacion'
        );
    }
}


// =========================================================
// 14. TERMINO CLAVE
//
// Su llave primaria es una cadena de texto.
// =========================================================

elseif ($ruta === 'termino_clave') {

    $titulo = 'Términos clave';

    $termino = $segundo;

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_terminos_clave(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/termino_clave_lista.php';

    } elseif (
        ($segundo === 'nuevo' && $tercero === '') ||
        ($termino !== '' && $tercero === 'editar')
    ) {

        $modo = $tercero === 'editar'
            ? 'editar'
            : 'crear';

        $vistaContenido =
            __DIR__ . '/vistas/termino_clave_formulario.php';

        if ($metodo === 'POST') {

            $ingles = front_texto(
                $_POST['termino_ingles'] ?? ''
            );

            $datos = [
                'termino_ingles' =>
                    $ingles === '' ? null : $ingles
            ];

            $valores = [
                'termino' => $modo === 'crear'
                    ? front_texto($_POST['termino'] ?? '')
                    : $termino
            ] + $datos;

            $resultado = null;

            if ($modo === 'crear') {

                $resultado = crear_termino_clave($valores);

            } elseif (
                ($_POST['accion'] ?? '') === 'parcial'
            ) {

                $cambios = front_cambios(
                    $datos,
                    obtener_termino_clave($termino),
                    $erroresValidacion
                );

                if ($cambios !== null) {

                    $resultado = actualizar_termino_clave(
                        $termino,
                        $cambios
                    );
                }

            } else {

                $resultado = reemplazar_termino_clave(
                    $termino,
                    $datos
                );
            }

            if ($resultado !== null) {

                front_guardar_resultado(
                    $resultado,
                    '/termino_clave',
                    'Se guardó el término clave.',
                    $erroresValidacion
                );
            }

        } elseif ($modo === 'editar') {

            $valores = front_ficha(
                obtener_termino_clave($termino),
                $erroresValidacion
            );

            $puedeGuardar = $valores !== [];
        }

    } elseif (
        $termino !== '' &&
        $tercero === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_termino_clave($termino),
            '/termino_clave'
        );
    }
}


// =========================================================
// 15. UNIVERSIDAD
// =========================================================

elseif ($ruta === 'universidad') {

    $titulo = 'Universidades';

    $id = front_entero($segundo);

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_universidades(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/universidad_lista.php';

    } elseif (
        ($segundo === 'nuevo' && $tercero === '') ||
        ($id > 0 && $tercero === 'editar')
    ) {

        $modo = $segundo === 'nuevo'
            ? 'crear'
            : 'editar';

        $vistaContenido =
            __DIR__ . '/vistas/universidad_formulario.php';

        if ($metodo === 'POST') {

            $datos = [
                'nombre' => front_texto(
                    $_POST['nombre'] ?? ''
                ),
                'tipo' => front_texto(
                    $_POST['tipo'] ?? ''
                ),
                'ciudad' => front_texto(
                    $_POST['ciudad'] ?? ''
                )
            ];

            $valores = [
                'id' => $modo === 'crear'
                    ? front_entero($_POST['id'] ?? '')
                    : $id
            ] + $datos;

            $resultado = null;

            if ($modo === 'crear') {

                $resultado =
                    crear_universidad($valores);

            } elseif (
                ($_POST['accion'] ?? '') === 'parcial'
            ) {

                $cambios = front_cambios(
                    $datos,
                    obtener_universidad($id),
                    $erroresValidacion
                );

                if ($cambios !== null) {

                    $resultado = actualizar_universidad(
                        $id,
                        $cambios
                    );
                }

            } else {

                $resultado = reemplazar_universidad(
                    $id,
                    $datos
                );
            }

            if ($resultado !== null) {

                front_guardar_resultado(
                    $resultado,
                    '/universidad',
                    'Se guardó la universidad.',
                    $erroresValidacion
                );
            }

        } elseif ($modo === 'editar') {

            $valores = front_ficha(
                obtener_universidad($id),
                $erroresValidacion
            );

            $puedeGuardar = $valores !== [];
        }

    } elseif (
        $id > 0 &&
        $tercero === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_universidad($id),
            '/universidad'
        );
    }
}


// =========================================================
// 16. LINEA DE INVESTIGACION
//
// La llave primaria se genera en la base de datos.
// =========================================================

elseif ($ruta === 'linea_investigacion') {

    $titulo = 'Líneas de investigación';

    $id = front_entero($segundo);

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_lineas_investigacion(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/linea_investigacion_lista.php';

    } elseif (
        ($segundo === 'nuevo' && $tercero === '') ||
        ($id > 0 && $tercero === 'editar')
    ) {

        $modo = $segundo === 'nuevo'
            ? 'crear'
            : 'editar';

        $vistaContenido =
            __DIR__ . '/vistas/linea_investigacion_formulario.php';

        if ($metodo === 'POST') {

            $datos = [
                'nombre' => front_texto(
                    $_POST['nombre'] ?? ''
                ),
                'descripcion' => front_texto(
                    $_POST['descripcion'] ?? ''
                )
            ];

            $valores = $datos;

            if ($modo === 'editar') {

                $valores['id'] = $id;
            }

            $resultado = null;

            if ($modo === 'crear') {

                $resultado =
                    crear_linea_investigacion($datos);

            } elseif (
                ($_POST['accion'] ?? '') === 'parcial'
            ) {

                $cambios = front_cambios(
                    $datos,
                    obtener_linea_investigacion($id),
                    $erroresValidacion
                );

                if ($cambios !== null) {

                    $resultado =
                        actualizar_linea_investigacion(
                            $id,
                            $cambios
                        );
                }

            } else {

                $resultado =
                    reemplazar_linea_investigacion(
                        $id,
                        $datos
                    );
            }

            if ($resultado !== null) {

                front_guardar_resultado(
                    $resultado,
                    '/linea_investigacion',
                    'Se guardó la línea de investigación.',
                    $erroresValidacion
                );
            }

        } elseif ($modo === 'editar') {

            $valores = front_ficha(
                obtener_linea_investigacion($id),
                $erroresValidacion
            );

            $puedeGuardar = $valores !== [];
        }

    } elseif (
        $id > 0 &&
        $tercero === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_linea_investigacion($id),
            '/linea_investigacion'
        );
    }
}


// =========================================================
// 17. DOCENTES
// =========================================================

elseif ($ruta === 'docentes') {

    $titulo = 'Docentes';

    $cedula = front_entero($segundo);

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_docentes(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/docentes_lista.php';

    } elseif (
        ($segundo === 'nuevo' && $tercero === '') ||
        ($cedula > 0 && $tercero === 'editar')
    ) {

        $modo = $segundo === 'nuevo'
            ? 'crear'
            : 'editar';

        $vistaContenido =
            __DIR__ . '/vistas/docentes_formulario.php';

        if ($metodo === 'POST') {

            $datos = [
                'nombres' => front_texto(
                    $_POST['nombres'] ?? ''
                ),
                'apellidos' => front_texto(
                    $_POST['apellidos'] ?? ''
                )
            ];

            $valores = [
                'cedula' => $modo === 'crear'
                    ? front_entero($_POST['cedula'] ?? '')
                    : $cedula
            ] + $datos;

            $resultado = null;

            if ($modo === 'crear') {

                $resultado = crear_docente($valores);

            } elseif (
                ($_POST['accion'] ?? '') === 'parcial'
            ) {

                $cambios = front_cambios(
                    $datos,
                    obtener_docente($cedula),
                    $erroresValidacion
                );

                if ($cambios !== null) {

                    $resultado = actualizar_docente(
                        $cedula,
                        $cambios
                    );
                }

            } else {

                $resultado = reemplazar_docente(
                    $cedula,
                    $datos
                );
            }

            if ($resultado !== null) {

                front_guardar_resultado(
                    $resultado,
                    '/docentes',
                    'Se guardó el docente.',
                    $erroresValidacion
                );
            }

        } elseif ($modo === 'editar') {

            $valores = front_ficha(
                obtener_docente($cedula),
                $erroresValidacion
            );

            $puedeGuardar = $valores !== [];
        }

    } elseif (
        $cedula > 0 &&
        $tercero === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_docente($cedula),
            '/docentes'
        );
    }
}


// =========================================================
// 18. GRUPOS DE INVESTIGACION
//
// Maestro-detalle.
// Tiene una pantalla adicional para consultar
// el grupo junto con sus semilleros.
// No utiliza actualizacion parcial.
// =========================================================

elseif ($ruta === 'grupos') {

    $titulo = 'Grupos de investigación';

    $id = front_entero($segundo);

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_grupos_investigacion(),
            $erroresValidacion
        );

        $universidades = front_filas(
            listar_universidades(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/grupos_lista.php';

    } elseif (
        ($segundo === 'nuevo' && $tercero === '') ||
        ($id > 0 && $tercero === 'editar')
    ) {

        $modo = $segundo === 'nuevo'
            ? 'crear'
            : 'editar';

        $vistaContenido =
            __DIR__ . '/vistas/grupos_formulario.php';

        if ($metodo === 'POST') {

            $detalle = front_detalle_semilleros(
                $_POST['detalle_nombre'] ?? [],
                $erroresValidacion
            );

            $datos = [
                'gruplac' => front_texto(
                    $_POST['gruplac'] ?? ''
                ),
                'categoria' => front_texto(
                    $_POST['categoria'] ?? ''
                ),
                'convocatoria' => front_texto(
                    $_POST['convocatoria'] ?? ''
                ),
                'ambito' => front_texto(
                    $_POST['ambito'] ?? ''
                ),
                'universidad_id' => front_entero(
                    $_POST['universidad_id'] ?? ''
                ),
                'detalle' => $detalle
            ];

            $valores = $datos;

            if ($modo === 'editar') {

                $valores['id'] = $id;
            }

            // Conservar los renglones originales
            // si falla la operacion.

            $valores['detalle_nombre'] =
                $_POST['detalle_nombre'] ?? [];

            if ($erroresValidacion === []) {

                if ($modo === 'crear') {

                    $resultado =
                        crear_grupo_investigacion($datos);

                } else {

                    $resultado =
                        reemplazar_grupo_investigacion(
                            $id,
                            $datos
                        );
                }

                if ($resultado['ok']) {

                    $grupoGuardado =
                        $resultado['respuesta']['grupo']['id']
                        ?? $id;

                    $_SESSION['mensaje_exito'] =
                        'Se guardó el grupo de investigación.';

                    if (front_entero($grupoGuardado) > 0) {

                        front_redirigir(
                            '/grupos/' . $grupoGuardado
                        );
                    }

                    front_redirigir('/grupos');
                }

                $erroresValidacion = array_merge(
                    $erroresValidacion,
                    front_errores($resultado)
                );
            }

        } elseif ($modo === 'editar') {

            $valores = front_ficha(
                obtener_grupo_investigacion($id),
                $erroresValidacion
            );

            $puedeGuardar = $valores !== [];
        }

        // Obtener las universidades para el desplegable.

        $universidades = front_filas(
            listar_universidades(),
            $erroresValidacion
        );

        $puedeGuardar =
            $puedeGuardar && $universidades !== [];

    } elseif (
        $id > 0 &&
        $tercero === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_grupo_investigacion($id),
            '/grupos'
        );

    } elseif (
        $id > 0 &&
        $tercero === '' &&
        $metodo === 'GET'
    ) {

        $valores = front_ficha(
            obtener_grupo_investigacion($id),
            $erroresValidacion
        );

        $universidades = front_filas(
            listar_universidades(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/grupos_detalle.php';
    }
}


// =========================================================
// 19. SEMILLEROS
//
// Solamente permite consultar el listado.
// =========================================================

elseif (
    $ruta === 'semilleros' &&
    $segundo === '' &&
    $metodo === 'GET'
) {

    $titulo = 'Semilleros';

    $registros = front_filas(
        listar_semilleros(),
        $erroresValidacion
    );

    if ($registros !== []) {

        $grupos = front_filas(
            listar_grupos_investigacion(),
            $erroresValidacion
        );
    }

    $vistaContenido =
        __DIR__ . '/vistas/semilleros_lista.php';
}


// =========================================================
// 20. DOCENTES POR GRUPO
// =========================================================

elseif ($ruta === 'participa_grupo') {

    $titulo = 'Docentes por grupo';

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_participa_grupo(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/docentes_grupo_lista.php';

    } elseif ($segundo === 'nueva') {

        $vistaContenido =
            __DIR__ . '/vistas/docentes_grupo_formulario.php';

        if ($metodo === 'POST') {

            $cedula = front_entero(
                $_POST['docente_cedula'] ?? ''
            );

            $grupoId = front_entero(
                $_POST['grupo_id'] ?? ''
            );

            $valores = [
                'docente_cedula' => $cedula,
                'grupo_id' => $grupoId
            ];

            front_guardar_resultado(
                asignar_participa_grupo($cedula, $grupoId),
                '/participa_grupo',
                'Se asignó el docente al grupo.',
                $erroresValidacion
            );
        }

    } elseif (
        $segundo === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_participa_grupo(
                front_entero($_POST['docente_cedula'] ?? ''),
                front_entero($_POST['grupo_id'] ?? '')
            ),
            '/participa_grupo'
        );
    }

    if ($registros !== [] || $segundo === 'nueva') {

        $opciones1 = front_filas(
            listar_docentes(),
            $erroresValidacion
        );

        $opciones2 = front_filas(
            listar_grupos_investigacion(),
            $erroresValidacion
        );
    }
}


// =========================================================
// 21. DOCENTES POR SEMILLERO
// =========================================================

elseif ($ruta === 'participa_semillero') {

    $titulo = 'Docentes por semillero';

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_participa_semillero(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/docentes_semillero_lista.php';

    } elseif ($segundo === 'nueva') {

        $vistaContenido =
            __DIR__ . '/vistas/docentes_semillero_formulario.php';

        if ($metodo === 'POST') {

            $cedula = front_entero(
                $_POST['docente_cedula'] ?? ''
            );

            $semilleroId = front_entero(
                $_POST['semillero_id'] ?? ''
            );

            $valores = [
                'docente_cedula' => $cedula,
                'semillero_id' => $semilleroId
            ];

            front_guardar_resultado(
                asignar_participa_semillero(
                    $cedula,
                    $semilleroId
                ),
                '/participa_semillero',
                'Se asignó el docente al semillero.',
                $erroresValidacion
            );
        }

    } elseif (
        $segundo === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_participa_semillero(
                front_entero($_POST['docente_cedula'] ?? ''),
                front_entero($_POST['semillero_id'] ?? '')
            ),
            '/participa_semillero'
        );
    }

    if ($registros !== [] || $segundo === 'nueva') {

        $opciones1 = front_filas(
            listar_docentes(),
            $erroresValidacion
        );

        $opciones2 = front_filas(
            listar_semilleros(),
            $erroresValidacion
        );
    }
}


// =========================================================
// 22. LINEAS POR GRUPO (TABLA PUENTE grupo_linea)
//
// FK 1: grupo_linea.grupo_id -> grupo_investigacion.id
// FK 2: grupo_linea.linea_id -> linea_investigacion.id
//
// Utiliza las vistas individuales de listado y formulario.
// =========================================================

elseif ($ruta === 'grupo_linea') {

    $titulo = 'Líneas por grupo';

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_grupo_linea(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/grupos_linea_lista.php';

    } elseif ($segundo === 'nueva') {

        $vistaContenido =
            __DIR__ . '/vistas/grupos_linea_formulario.php';

        if ($metodo === 'POST') {

            $grupoId = front_entero(
                $_POST['grupo_id'] ?? ''
            );

            $lineaId = front_entero(
                $_POST['linea_id'] ?? ''
            );

            $valores = [
                'grupo_id' => $grupoId,
                'linea_id' => $lineaId
            ];

            front_guardar_resultado(
                asignar_grupo_linea($grupoId, $lineaId),
                '/grupo_linea',
                'Se asignó la línea al grupo.',
                $erroresValidacion
            );
        }

    } elseif (
        $segundo === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_grupo_linea(
                front_entero($_POST['grupo_id'] ?? ''),
                front_entero($_POST['linea_id'] ?? '')
            ),
            '/grupo_linea'
        );
    }

    if ($registros !== [] || $segundo === 'nueva') {

        $opciones1 = front_filas(
            listar_grupos_investigacion(),
            $erroresValidacion
        );

        $opciones2 = front_filas(
            listar_lineas_investigacion(),
            $erroresValidacion
        );
    }
}


// =========================================================
// 23. LINEAS POR SEMILLERO
// =========================================================

elseif ($ruta === 'semillero_linea') {

    $titulo = 'Líneas por semillero';

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_semillero_linea(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/lineas_semillero_lista.php';

    } elseif ($segundo === 'nueva') {

        $vistaContenido =
            __DIR__ . '/vistas/lineas_semillero_formulario.php';

        if ($metodo === 'POST') {

            $semilleroId = front_entero(
                $_POST['semillero_id'] ?? ''
            );

            $lineaId = front_entero(
                $_POST['linea_id'] ?? ''
            );

            $valores = [
                'semillero_id' => $semilleroId,
                'linea_id' => $lineaId
            ];

            front_guardar_resultado(
                asignar_semillero_linea(
                    $semilleroId,
                    $lineaId
                ),
                '/semillero_linea',
                'Se asignó la línea al semillero.',
                $erroresValidacion
            );
        }

    } elseif (
        $segundo === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_semillero_linea(
                front_entero($_POST['semillero_id'] ?? ''),
                front_entero($_POST['linea_id'] ?? '')
            ),
            '/semillero_linea'
        );
    }

    if ($registros !== [] || $segundo === 'nueva') {

        $opciones1 = front_filas(
            listar_semilleros(),
            $erroresValidacion
        );

        $opciones2 = front_filas(
            listar_lineas_investigacion(),
            $erroresValidacion
        );
    }
}


// =========================================================
// 24. AREAS DE CONOCIMIENTO POR LINEA
// =========================================================

elseif ($ruta === 'ac_linea') {

    $titulo = 'Áreas de conocimiento por línea';

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_ac_linea(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/areas_conocimiento_linea_lista.php';

    } elseif ($segundo === 'nueva') {

        $vistaContenido =
            __DIR__ . '/vistas/areas_conocimiento_linea_formulario.php';

        if ($metodo === 'POST') {

            $areaId = front_entero(
                $_POST['area_conocimiento_id'] ?? ''
            );

            $lineaId = front_entero(
                $_POST['linea_id'] ?? ''
            );

            $valores = [
                'area_conocimiento_id' => $areaId,
                'linea_id' => $lineaId
            ];

            front_guardar_resultado(
                asignar_ac_linea($areaId, $lineaId),
                '/ac_linea',
                'Se asignó el área de conocimiento.',
                $erroresValidacion
            );
        }

    } elseif (
        $segundo === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_ac_linea(
                front_entero($_POST['area_conocimiento_id'] ?? ''),
                front_entero($_POST['linea_id'] ?? '')
            ),
            '/ac_linea'
        );
    }

    if ($registros !== [] || $segundo === 'nueva') {

        $opciones1 = front_filas(
            listar_areas_conocimiento(),
            $erroresValidacion
        );

        $opciones2 = front_filas(
            listar_lineas_investigacion(),
            $erroresValidacion
        );
    }
}


// =========================================================
// 25. OBJETIVOS DE DESARROLLO SOSTENIBLE POR LINEA
// =========================================================

elseif ($ruta === 'ods_linea') {

    $titulo = 'ODS por línea';

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_ods_linea(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/ods_linea_lista.php';

    } elseif ($segundo === 'nueva') {

        $vistaContenido =
            __DIR__ . '/vistas/ods_linea_formulario.php';

        if ($metodo === 'POST') {

            $odsId = front_entero(
                $_POST['ods_id'] ?? ''
            );

            $lineaId = front_entero(
                $_POST['linea_id'] ?? ''
            );

            $valores = [
                'ods_id' => $odsId,
                'linea_id' => $lineaId
            ];

            front_guardar_resultado(
                asignar_ods_linea($odsId, $lineaId),
                '/ods_linea',
                'Se asignó el objetivo.',
                $erroresValidacion
            );
        }

    } elseif (
        $segundo === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_ods_linea(
                front_entero($_POST['ods_id'] ?? ''),
                front_entero($_POST['linea_id'] ?? '')
            ),
            '/ods_linea'
        );
    }

    if ($registros !== [] || $segundo === 'nueva') {

        $opciones1 = front_filas(
            listar_objetivos_desarrollo_sostenible(),
            $erroresValidacion
        );

        $opciones2 = front_filas(
            listar_lineas_investigacion(),
            $erroresValidacion
        );
    }
}


// =========================================================
// 26. AREAS DE APLICACION POR LINEA
// =========================================================

elseif ($ruta === 'aa_linea') {

    $titulo = 'Áreas de aplicación por línea';

    if ($segundo === '' && $metodo === 'GET') {

        $registros = front_filas(
            listar_aa_linea(),
            $erroresValidacion
        );

        $vistaContenido =
            __DIR__ . '/vistas/areas_aplicacion_linea_lista.php';

    } elseif ($segundo === 'nueva') {

        $vistaContenido =
            __DIR__ . '/vistas/areas_aplicacion_linea_formulario.php';

        if ($metodo === 'POST') {

            $areaId = front_entero(
                $_POST['area_aplicacion_id'] ?? ''
            );

            $lineaId = front_entero(
                $_POST['linea_id'] ?? ''
            );

            $valores = [
                'area_aplicacion_id' => $areaId,
                'linea_id' => $lineaId
            ];

            front_guardar_resultado(
                asignar_aa_linea($areaId, $lineaId),
                '/aa_linea',
                'Se asignó el área de aplicación.',
                $erroresValidacion
            );
        }

    } elseif (
        $segundo === 'retirar' &&
        $metodo === 'POST'
    ) {

        front_retirar_resultado(
            retirar_aa_linea(
                front_entero($_POST['area_aplicacion_id'] ?? ''),
                front_entero($_POST['linea_id'] ?? '')
            ),
            '/aa_linea'
        );
    }

    if ($registros !== [] || $segundo === 'nueva') {

        $opciones1 = front_filas(
            listar_areas_aplicacion(),
            $erroresValidacion
        );

        $opciones2 = front_filas(
            listar_lineas_investigacion(),
            $erroresValidacion
        );
    }
}


// =========================================================
// 27. PAGINA NO ENCONTRADA
// =========================================================

if ($vistaContenido === null) {

    http_response_code(404);

    $titulo = 'Página no encontrada';

    $vistaContenido =
        __DIR__ . '/vistas/no_encontrada.php';
}


// =========================================================
// 28. CARGAR LA PLANTILLA PRINCIPAL
//
// La plantilla muestra el encabezado, menu,
// contenido, alertas y pie de pagina.
// =========================================================
require __DIR__ . '/vistas/plantilla.php';