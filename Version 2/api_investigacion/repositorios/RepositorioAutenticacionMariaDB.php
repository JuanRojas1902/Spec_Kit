<?php

declare(strict_types=1);

/*
=======================================================
PROYECTO DE INVESTIGACION - VERSION 3

ARCHIVO:
repositorios/RepositorioAutenticacionMariaDB.php

QUE ES:
Repositorio que consulta las cuentas y sus roles.

POR QUE:
La autenticacion necesita consultar MariaDB mediante
PDO y sentencias preparadas.

QUE NO HACE:
- No recibe peticiones HTTP.
- No verifica contrasenas.
- No genera tokens JWT.
- No administra sesiones PHP.
- No asigna ni retira roles.
=======================================================
*/

require_once __DIR__ . '/IRepositorioAutenticacion.php';

final class RepositorioAutenticacionMariaDB
    implements IRepositorioAutenticacion
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /*
     * Consulta un usuario activo por su username.
     *
     * La contraseña almacenada se devuelve como hash
     * para que el servicio use password_verify().
     *
     * Nunca se compara la contraseña en SQL.
     */
    public function buscarUsuarioActivo(
        string $username
    ): ?array {

        $sql = '
            SELECT
                id,
                username,
                email,
                nombre_completo,
                password_hash
            FROM usuario
            WHERE username = ?
              AND activo = 1
            LIMIT 1
        ';

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            $username
        ]);

        $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($usuario === false) {
            return null;
        }

        return $usuario;
    }

    /*
     * Consulta los roles activos de un usuario.
     *
     * CLAVES FORANEAS:
     *
     * rol_usuario.usuario_id -> usuario.id
     * rol_usuario.rol_id     -> rol.id
     *
     * Solo se devuelven roles activos de cuentas
     * que tambien permanecen activas.
     */
    public function obtenerRolesActivos(
        int $usuarioId
    ): array {

        $sql = '
            SELECT DISTINCT
                r.nombre
            FROM rol_usuario ru
            INNER JOIN rol r
                ON r.id = ru.rol_id
            INNER JOIN usuario u
                ON u.id = ru.usuario_id
            WHERE ru.usuario_id = ?
              AND u.activo = 1
              AND r.activo = 1
            ORDER BY r.nombre
        ';

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            $usuarioId
        ]);

        $roles = $consulta->fetchAll(PDO::FETCH_COLUMN);

        return array_values(
            array_map('strval', $roles)
        );
    }
}