<?php

declare(strict_types=1);

/*
 * PROYECTO DE INVESTIGACION - V3
 *
 * ARCHIVO: ServicioUsuariosV3.php
 *
 * QUE ES:
 * Implementacion de las operaciones de negocio
 * para consultar usuarios del sistema.
 *
 * POR QUE:
 * Mantiene separadas las reglas de negocio
 * y las consultas de MariaDB.
 *
 * QUE NO HACE:
 * No ejecuta SQL.
 * No devuelve respuestas HTTP.
 * No comprueba directamente el JWT.
 * No devuelve contrasenas.
 */

require_once __DIR__ . '/IServicioUsuariosV3.php';

require_once __DIR__
    . '/../repositorios/IRepositorioUsuariosV3.php';

final class ServicioUsuariosV3
    implements IServicioUsuariosV3
{
    private IRepositorioUsuariosV3 $repositorio;

    public function __construct(
        IRepositorioUsuariosV3 $repositorio
    ) {
        $this->repositorio = $repositorio;
    }

    /*
     * LISTAR USUARIOS
     *
     * Obtiene los usuarios desde el repositorio.
     * Incluye sus roles y estados actuales.
     *
     * El controlador y la guardia de seguridad
     * deben comprobar que quien solicita el
     * listado tenga rol ADMIN.
     */
    public function listar(): array
    {
        return $this->repositorio->listar();
    }

    /*
     * CONSULTAR USUARIO POR ID
     *
     * Regla de negocio:
     * El identificador debe ser mayor que cero.
     */
    public function obtenerPorId(int $id): ?array
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'El identificador del usuario debe ser positivo.'
            );
        }

        return $this->repositorio->obtenerPorId($id);
    }


    /*
     * REGISTRAR USUARIO - VERSION 3
     *
     * QUE HACE:
     * - Comprueba los campos obligatorios.
     * - Valida nombre de usuario y correo.
     * - Valida la longitud de la contrasena.
     * - Calcula el hash seguro.
     * - Solicita al repositorio crear la cuenta.
     *
     * QUE NO HACE:
     * - No ejecuta SQL.
     * - No maneja HTTP.
     * - No decide permisos JWT.
     * - No almacena contrasenas en texto plano.
     */
    public function crear(array $datos): int
    {
        // Validar que todos los campos sean texto.
        $campos = [
            'username',
            'email',
            'nombre_completo',
            'password'
        ];

        foreach ($campos as $campo) {
            if (
                !array_key_exists($campo, $datos) ||
                !is_string($datos[$campo])
            ) {
                throw new InvalidArgumentException(
                    'El campo ' . $campo . ' es obligatorio.'
                );
            }
        }

        $username = trim($datos['username']);
        $email = trim($datos['email']);
        $nombreCompleto = trim(
            $datos['nombre_completo']
        );

        // No eliminar espacios de la contrasena.
        $password = $datos['password'];

        /*
         * USERNAME
         * Letras ASCII, numeros, punto,
         * guion y guion bajo.
         */
        if (
            preg_match(
                '/\A[a-zA-Z0-9._-]{3,50}\z/',
                $username
            ) !== 1
        ) {
            throw new InvalidArgumentException(
                'El usuario debe tener entre 3 y 50 caracteres validos.'
            );
        }

        /*
         * EMAIL
         */
        if (
            strlen($email) > 150 ||
            filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            ) === false
        ) {
            throw new InvalidArgumentException(
                'Debe proporcionar un correo electronico valido.'
            );
        }

        /*
         * NOMBRE COMPLETO
         */
        if (
            strlen($nombreCompleto) < 2 ||
            strlen($nombreCompleto) > 120
        ) {
            throw new InvalidArgumentException(
                'El nombre completo debe tener entre 2 y 120 bytes.'
            );
        }

        /*
         * CONTRASENA
         *
         * Limite de 72 bytes para ser compatible
         * con bcrypt cuando PASSWORD_DEFAULT
         * utilice ese algoritmo.
         */
        if (
            strlen($password) < 12 ||
            strlen($password) > 72 ||
            trim($password) === ''
        ) {
            throw new InvalidArgumentException(
                'La contrasena debe tener entre 12 y 72 bytes.'
            );
        }

        /*
         * HASH DE CONTRASENA
         *
         * PASSWORD_DEFAULT selecciona un algoritmo
         * soportado por PHP.
         *
         * El hash incluye los parametros necesarios
         * para verificarlo posteriormente con
         * password_verify().
         */
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        /*
         * El repositorio guardara el usuario y
         * asignara el rol CONSULTA en una transaccion.
         */
        return $this->repositorio->crearConRolConsulta(
            $username,
            $email,
            $nombreCompleto,
            $passwordHash
        );
    }
}
