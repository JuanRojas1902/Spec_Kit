<?php

declare(strict_types=1);

/*
 * PROYECTO DE INVESTIGACION - V3
 *
 * QUE ES:
 * Servicio responsable de autenticar usuarios.
 *
 * POR QUE:
 * Comprueba las credenciales y genera un JWT
 * cuando el usuario tiene roles activos.
 *
 * QUE NO HACE:
 * No ejecuta SQL directamente.
 * No recibe ni responde peticiones HTTP.
 * No administra sesiones del navegador.
 */

require_once __DIR__ . '/IServicioAutenticacion.php';
require_once __DIR__ . '/JwtHs256.php';
require_once __DIR__ . '/../repositorios/IRepositorioAutenticacion.php';

final class ServicioAutenticacion
    implements IServicioAutenticacion
{
    private IRepositorioAutenticacion $repositorio;

    public function __construct(
        IRepositorioAutenticacion $repositorio
    ) {
        $this->repositorio = $repositorio;
    }

    public function iniciarSesion(
        string $username,
        string $password
    ): ?array {

        $username = trim($username);

        // Validacion basica de las credenciales.
        if (
            $username === '' ||
            strlen($username) > 100 ||
            $password === ''
        ) {
            return null;
        }

        // Buscar exclusivamente cuentas activas.
        $usuario = $this->repositorio
            ->buscarUsuarioActivo($username);

        // No revelar si el usuario existe o no.
        if ($usuario === null) {
            return null;
        }

        // Comparar la contrasena con su hash.
        $hash = (string) (
            $usuario['password_hash'] ?? ''
        );

        if (!password_verify($password, $hash)) {
            return null;
        }

        $usuarioId = (int) ($usuario['id'] ?? 0);

        if ($usuarioId <= 0) {
            return null;
        }

        // Consultar permisos vigentes en MariaDB.
        $roles = $this->repositorio
            ->obtenerRolesActivos($usuarioId);

        // Una cuenta sin roles activos no inicia sesion.
        if ($roles === []) {
            return null;
        }

        // El token contiene el ID, pero no los roles.
        $token = JwtHs256::emitir($usuarioId);

        // Contrato que consumira el frontend PHP.
        return [
            'token' => $token,
            'expira_en' => 3600,
            'usuario' => [
                'id' => $usuarioId,
                'username' => (string) $usuario['username'],
                'email' => (string) $usuario['email'],
                'nombre_completo' => $usuario['nombre_completo'] ?? null,
                'roles' => $roles
            ]
        ];
    }
}