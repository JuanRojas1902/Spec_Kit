<?php

declare(strict_types=1);

/*
 * PROYECTO DE INVESTIGACION - V3
 *
 * ARCHIVO: RepositorioUsuariosMariaDBV3.php
 *
 * QUE ES:
 * Implementacion del repositorio de usuarios
 * utilizando PHP PDO y MariaDB.
 *
 * POR QUE:
 * Centraliza las consultas SQL.
 *
 * QUE NO HACE:
 * No devuelve contrasenas ni password_hash.
 * No comprueba JWT.
 * No devuelve respuestas HTTP.
 * No modifica registros.
 */

require_once __DIR__ . '/IRepositorioUsuariosV3.php';

final class RepositorioUsuariosMariaDBV3
    implements IRepositorioUsuariosV3
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /*
     * LISTAR USUARIOS
     *
     * Devuelve usuarios activos e inactivos.
     * Incluye sus roles activos.
     */
    public function listar(): array
    {
        $consulta = $this->pdo->prepare(
            'SELECT
                id,
                username,
                email,
                nombre_completo,
                activo
             FROM usuario
             ORDER BY id DESC'
        );

        $consulta->execute();

        $usuarios = $consulta->fetchAll(
            PDO::FETCH_ASSOC
        );

        if ($usuarios === []) {
            return [];
        }

        /*
         * Obtener todos los roles activos
         * en una sola consulta adicional.
         */
        $consultaRoles = $this->pdo->prepare(
            'SELECT
                ru.usuario_id,
                r.nombre
             FROM rol_usuario ru
             INNER JOIN rol r
                ON r.id = ru.rol_id
             WHERE r.activo = 1
             ORDER BY r.nombre'
        );

        $consultaRoles->execute();

        $rolesPorUsuario = [];

        foreach (
            $consultaRoles->fetchAll(PDO::FETCH_ASSOC)
            as $fila
        ) {
            $usuarioId = (int) $fila['usuario_id'];

            $rolesPorUsuario[$usuarioId][] =
                (string) $fila['nombre'];
        }

        foreach ($usuarios as &$usuario) {

            $id = (int) $usuario['id'];

            $usuario['id'] = $id;
            $usuario['activo'] =
                (int) $usuario['activo'];

            $usuario['roles'] =
                $rolesPorUsuario[$id] ?? [];
        }

        unset($usuario);

        return $usuarios;
    }

    /*
     * OBTENER UN USUARIO POR SU ID
     *
     * Utiliza consultas preparadas.
     */
    public function obtenerPorId(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $consulta = $this->pdo->prepare(
            'SELECT
                id,
                username,
                email,
                nombre_completo,
                activo
             FROM usuario
             WHERE id = ?
             LIMIT 1'
        );

        $consulta->execute([$id]);

        $usuario = $consulta->fetch(
            PDO::FETCH_ASSOC
        );

        if ($usuario === false) {
            return null;
        }

        $consultaRoles = $this->pdo->prepare(
            'SELECT r.nombre
             FROM rol_usuario ru
             INNER JOIN rol r
                ON r.id = ru.rol_id
             WHERE ru.usuario_id = ?
               AND r.activo = 1
             ORDER BY r.nombre'
        );

        $consultaRoles->execute([$id]);

        $roles = $consultaRoles->fetchAll(
            PDO::FETCH_COLUMN
        );

        $usuario['id'] = (int) $usuario['id'];
        $usuario['activo'] =
            (int) $usuario['activo'];

        $usuario['roles'] = array_map(
            'strval',
            $roles
        );

        return $usuario;
    }

    /*
     * CREAR USUARIO CON ROL CONSULTA
     *
     * QUE HACE:
     * - Busca el rol CONSULTA activo.
     * - Comprueba duplicados existentes.
     * - Inserta el usuario.
     * - Asigna el rol CONSULTA.
     *
     * POR QUE:
     * Una transaccion evita que se guarde
     * un usuario sin la asignacion de su rol.
     *
     * IMPORTANTE:
     * El servicio debe validar los campos y
     * calcular password_hash antes de llamar
     * a este metodo.
     */
    public function crearConRolConsulta(
        string $username,
        string $email,
        string $nombreCompleto,
        string $passwordHash
    ): int {
        $this->pdo->beginTransaction();

        try {
            // Comprobar que el rol CONSULTA exista.
            $consultaRol = $this->pdo->prepare(
                'SELECT id
                 FROM rol
                 WHERE nombre = ?
                   AND activo = 1
                 LIMIT 1'
            );

            $consultaRol->execute(['consulta']);

            $rolId = $consultaRol->fetchColumn();

            if ($rolId === false) {
                throw new RuntimeException(
                    'No existe un rol CONSULTA activo.'
                );
            }

            // Comprobar nombres de usuario y correos repetidos.
            $consultaDuplicados = $this->pdo->prepare(
                'SELECT id
                 FROM usuario
                 WHERE username = ?
                    OR email = ?
                 LIMIT 1'
            );

            $consultaDuplicados->execute([
                $username,
                $email
            ]);

            if ($consultaDuplicados->fetchColumn() !== false) {
                throw new DomainException(
                    'El usuario o correo ya esta registrado.'
                );
            }

            // Registrar la cuenta.
            // La contrasena ya debe llegar cifrada.
            $insertarUsuario = $this->pdo->prepare(
                'INSERT INTO usuario
                    (
                        username,
                        email,
                        nombre_completo,
                        password_hash,
                        activo
                    )
                 VALUES (?, ?, ?, ?, 1)'
            );

            $insertarUsuario->execute([
                $username,
                $email,
                $nombreCompleto,
                $passwordHash
            ]);

            $usuarioId = (int) $this->pdo->lastInsertId();

            if ($usuarioId <= 0) {
                throw new RuntimeException(
                    'No fue posible obtener el ID del usuario.'
                );
            }

            // Asociar automaticamente el rol CONSULTA.
            $asignarRol = $this->pdo->prepare(
                'INSERT INTO rol_usuario
                    (usuario_id, rol_id)
                 VALUES (?, ?)'
            );

            $asignarRol->execute([
                $usuarioId,
                (int) $rolId
            ]);

            // Confirmar ambas operaciones juntas.
            $this->pdo->commit();

            return $usuarioId;

        } catch (Throwable $error) {

            // Si una operacion falla, deshacer todo.
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $error;
        }
    }
}
