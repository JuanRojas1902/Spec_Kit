<?php

declare(strict_types=1);

/*
=====================================================
PROYECTO DE INVESTIGACION - VERSION 3
ARCHIVO: front_php/seguridad_v3.php

QUE ES:
Funciones para autenticar al usuario contra la API
y guardar el JWT dentro de la sesion PHP.

POR QUE:
El frontend nunca debe conectarse directamente
a MariaDB para comprobar las credenciales.

QUE NO HACE:
- No ejecuta SQL.
- No firma ni verifica criptograficamente JWT.
- No sustituye los permisos del backend.
- No muestra el token al navegador.
=====================================================
*/

function frontIniciarSesionV3(
    string $username,
    string $password
): array {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new LogicException(
            'La sesion PHP debe estar iniciada.'
        );
    }

    $username = trim($username);

    if (
        $username === '' ||
        strlen($username) > 100 ||
        $password === ''
    ) {
        return [
            'ok' => false,
            'mensaje' => 'Ingrese usuario y contraseña.'
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'ok' => false,
            'mensaje' => 'El cliente HTTP no esta disponible.'
        ];
    }

    $urlApi = rtrim(
        (string) (getenv('URL_API') ?: 'http://api-facturas:8026'),
        '/'
    );

    $json = json_encode(
        [
            'username' => $username,
            'password' => $password
        ],
        JSON_THROW_ON_ERROR
    );

    $curl = curl_init($urlApi . '/api/login');

    if ($curl === false) {
        return [
            'ok' => false,
            'mensaje' => 'No fue posible conectar con la API.'
        ];
    }

    try {
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_FOLLOWLOCATION => false
        ]);

        $respuesta = curl_exec($curl);
        $estado = (int) curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );
    } finally {
        curl_close($curl);
    }

    if ($respuesta === false) {
        return [
            'ok' => false,
            'mensaje' => 'La API no esta disponible.'
        ];
    }

    if ($estado !== 200) {
        return [
            'ok' => false,
            'mensaje' => $estado === 401
                ? 'Usuario o contraseña incorrectos.'
                : 'No fue posible iniciar sesion.'
        ];
    }

    $datos = json_decode($respuesta, true);

    if (!is_array($datos)) {
        return [
            'ok' => false,
            'mensaje' => 'Respuesta invalida de la API.'
        ];
    }

    $contenido = $datos['datos'] ?? [];
    $token = $contenido['token'] ?? null;
    $usuario = $contenido['usuario'] ?? null;
    $duracion = $contenido['expira_en'] ?? null;

    if (
        !is_string($token) ||
        strlen($token) > 4096 ||
        preg_match(
            '/\A[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\z/',
            $token
        ) !== 1 ||
        !is_array($usuario) ||
        !is_array($usuario['roles'] ?? null) ||
        $usuario['roles'] === [] ||
        !array_is_list($usuario['roles'])
    ) {
        return [
            'ok' => false,
            'mensaje' => 'Contrato de login invalido.'
        ];
    }

    $roles = $usuario['roles'];

    foreach ($roles as $rol) {
        if (!is_string($rol)) {
            return [
                'ok' => false,
                'mensaje' => 'Roles invalidos.'
            ];
        }
    }

    if (
        !in_array('admin', $roles, true) &&
        !in_array('consulta', $roles, true)
    ) {
        return [
            'ok' => false,
            'mensaje' => 'La cuenta no tiene acceso autorizado.'
        ];
    }

    $segundos = filter_var(
        $duracion,
        FILTER_VALIDATE_INT
    );

    if ($segundos === false || $segundos <= 0) {
        return [
            'ok' => false,
            'mensaje' => 'Duracion del token invalida.'
        ];
    }

    // Impedir que un inicio de sesion reutilice
    // el identificador de sesion anterior.
    session_regenerate_id(true);

    $nombre = (string) (
        $usuario['username'] ?? $username
    );

    // Compatible con las pantallas actuales de V2.
    $_SESSION['usuario'] = $nombre;

    // Informacion especifica de la V3.
    $_SESSION['usuario_v3'] = [
        'id' => (int) ($usuario['id'] ?? 0),
        'username' => $nombre,
        'roles' => $roles
    ];

    $_SESSION['jwt'] = $token;

    $_SESSION['jwt_expira_en'] = time() + min(
        $segundos,
        3600
    );

    // Renovar CSRF al iniciar sesion.
    $_SESSION['csrf'] = bin2hex(random_bytes(32));

    unset(
        $_SESSION['intentos_login'],
        $_SESSION['bloqueado_hasta']
    );

    return ['ok' => true];
}

/*
 * Recupera el JWT solamente si la sesion
 * todavia no ha vencido.
 */
function frontObtenerJwtV3(): ?string
{
    $token = $_SESSION['jwt'] ?? null;
    $vence = $_SESSION['jwt_expira_en'] ?? 0;

    if (
        !is_string($token) ||
        $token === '' ||
        time() >= (int) $vence
    ) {
        unset(
            $_SESSION['jwt'],
            $_SESSION['jwt_expira_en'],
            $_SESSION['usuario'],
            $_SESSION['usuario_v3']
        );

        return null;
    }

    return $token;
}

/*
 * Sirve para mostrar u ocultar botones.
 * El backend debe comprobar los permisos reales.
 */
function frontEsAdminV3(): bool
{
    if (frontObtenerJwtV3() === null) {
        return false;
    }

    $roles = $_SESSION['usuario_v3']['roles'] ?? [];

    return is_array($roles)
        && in_array('admin', $roles, true);
}

/*
 * Cierre de sesion del frontend.
 * Destruye la sesion y elimina la cookie.
 */
function frontCerrarSesionV3(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $parametros['path'],
            'domain' => $parametros['domain'],
            'secure' => $parametros['secure'],
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    session_destroy();
}