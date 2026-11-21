<?php

declare(strict_types=1);

/*
=====================================================
PROYECTO DE INVESTIGACION - VERSION 3

ARCHIVO: controladores/GuardiaAccesoV3.php

QUE ES:
Proteccion HTTP mediante JWT Bearer y roles.

POR QUE:
La API debe rechazar peticiones sin autenticacion
y operaciones que el usuario no tiene autorizadas.

QUE NO HACE:
- No ejecuta SQL.
- No comprueba contrasenas.
- No crea usuarios.
- No administra sesiones del navegador.
=====================================================
*/

require_once __DIR__ . '/../servicios/JwtHs256.php';
require_once __DIR__ . '/../repositorios/IRepositorioAutenticacion.php';

final class GuardiaAccesoV3
{
    private IRepositorioAutenticacion $repositorio;

    public function __construct(
        IRepositorioAutenticacion $repositorio
    ) {
        $this->repositorio = $repositorio;
    }

    private function responder(
        int $estado,
        string $mensaje
    ): never {
        http_response_code($estado);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');

        echo json_encode([
            'estado' => $estado,
            'mensaje' => $mensaje
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    private function obtenerBearer(): ?string
    {
        $cabecera = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? null;

        if (!is_string($cabecera) || $cabecera === '') {
            if (function_exists('getallheaders')) {
                foreach (getallheaders() as $nombre => $valor) {
                    if (
                        strcasecmp((string) $nombre, 'Authorization') === 0
                        && is_string($valor)
                    ) {
                        $cabecera = $valor;
                        break;
                    }
                }
            }
        }

        if (!is_string($cabecera)) {
            return null;
        }

        if (
            preg_match(
                '/\ABearer[ \t]+([A-Za-z0-9._-]+)\z/i',
                trim($cabecera),
                $coincidencias
            ) !== 1
        ) {
            return null;
        }

        return $coincidencias[1];
    }

    public function autorizar(string $metodo): void
    {
        $token = $this->obtenerBearer();

        if ($token === null) {
            $this->responder(
                401,
                'Debe iniciar sesion para acceder.'
            );
        }

        // Verificar firma y vencimiento del JWT.
        $datos = JwtHs256::verificar($token);

        if ($datos === null) {
            $this->responder(
                401,
                'Token invalido o vencido.'
            );
        }

        $usuarioId = filter_var(
            $datos['sub'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($usuarioId === false) {
            $this->responder(
                401,
                'Identificador de usuario invalido.'
            );
        }

        try {
            // Consultar MariaDB en cada peticion.
            // El repositorio solo devuelve roles activos
            // de cuentas que permanecen activas.
            $roles = $this->repositorio
                ->obtenerRolesActivos($usuarioId);

        } catch (Throwable $error) {
            error_log('Error al consultar permisos V3.');

            $this->responder(
                503,
                'No fue posible verificar los permisos.'
            );
        }

        // Cuenta desactivada o sin roles activos.
        if ($roles === []) {
            $this->responder(
                401,
                'La cuenta no tiene acceso activo.'
            );
        }

        $esAdmin = in_array('admin', $roles, true);
        $esConsulta = in_array('consulta', $roles, true);

        if (!$esAdmin && !$esConsulta) {
            $this->responder(
                403,
                'No tiene un rol autorizado.'
            );
        }

                /*
         * ADMIN_SOLO_V3
         *
         * QUE ES:
         * Proteccion especial de usuarios y roles.
         *
         * POR QUE:
         * CONSULTA puede leer recursos academicos,
         * pero no informacion administrativa.
         *
         * QUE NO HACE:
         * No crea usuarios.
         * No asigna roles.
         * No modifica MariaDB.
         */

        $rutaAdministrativaV3 = (string) parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        $partesAdministrativasV3 = explode(
            '/',
            trim($rutaAdministrativaV3, '/')
        );

        $recursoAdministrativoV3 =
            $partesAdministrativasV3[1] ?? '';

        $esRutaAdministrativaV3 =
            ($partesAdministrativasV3[0] ?? '') === 'api'
            &&
            (
                $recursoAdministrativoV3 === 'admin'
                ||
                in_array(
                    $recursoAdministrativoV3,
                    [
                        'usuario',
                        'usuarios',
                        'rol',
                        'roles',
                        'rol_usuario',
                        'usuario_rol'
                    ],
                    true
                )
            );

        if ($esRutaAdministrativaV3 && !$esAdmin) {
            $this->responder(
                403,
                'Solo ADMIN puede administrar usuarios y roles.'
            );
        }

        // Los usuarios de consulta solo pueden leer.
        $esLectura = in_array(
            strtoupper($metodo),
            ['GET', 'HEAD'],
            true
        );

        if (!$esLectura && !$esAdmin) {
            $this->responder(
                403,
                'Solo ADMIN puede modificar registros.'
            );
        }

        // Si llega aqui, la peticion esta autorizada.
    }
}