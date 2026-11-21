<?php

declare(strict_types=1);

/*
 * PROYECTO DE INVESTIGACION - V3
 *
 * ARCHIVO: ControladorUsuariosV3.php
 *
 * QUE ES:
 * Controlador HTTP para consultar usuarios.
 *
 * POR QUE:
 * Recibe peticiones y llama al servicio.
 *
 * QUE NO HACE:
 * No ejecuta SQL.
 * No comprueba contrasenas.
 * No sustituye la guardia JWT.
 */

require_once __DIR__ . '/../servicios/IServicioUsuariosV3.php';

final class ControladorUsuariosV3
{
    private IServicioUsuariosV3 $servicio;

    public function __construct(
        IServicioUsuariosV3 $servicio
    ) {
        $this->servicio = $servicio;
    }

    private function responder(
        int $estado,
        array $datos
    ): never {
        http_response_code($estado);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');

        echo json_encode(
            $datos,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    /*
     * GET /api/usuario
     * GET /api/usuario/{id}
     */
    /*
     * REGISTRO DE USUARIOS V3
     *
     * Solo se ejecuta despues de que
     * GuardiaAccesoV3 autoriza a ADMIN.
     *
     * El servicio valida y calcula el hash.
     * El repositorio inserta usuario y rol.
     */
    private function registrarUsuarioV3(): never
    {
        $tipo = trim(
            (string) ($_SERVER['CONTENT_TYPE'] ?? '')
        );

        if (
            preg_match(
                '~\Aapplication/json(?:\s*;|\z)~i',
                $tipo
            ) !== 1
        ) {
            $this->responder(415, [
                'estado' => 415,
                'mensaje' =>
                    'Se requiere Content-Type: application/json.'
            ]);
        }

        // Limitar el cuerpo a 16 KiB.
        $cuerpo = file_get_contents(
            'php://input',
            false,
            null,
            0,
            16385
        );

        if ($cuerpo === false) {
            $this->responder(400, [
                'estado' => 400,
                'mensaje' =>
                    'No fue posible leer la solicitud.'
            ]);
        }

        if (strlen($cuerpo) > 16384) {
            $this->responder(413, [
                'estado' => 413,
                'mensaje' =>
                    'La solicitud excede el tamano permitido.'
            ]);
        }

        try {
            $json = json_decode(
                $cuerpo,
                false,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $error) {
            $this->responder(422, [
                'estado' => 422,
                'mensaje' => 'El contenido JSON es invalido.'
            ]);
        }

        if (!is_object($json)) {
            $this->responder(422, [
                'estado' => 422,
                'mensaje' => 'Se esperaba un objeto JSON.'
            ]);
        }

        $datos = get_object_vars($json);

        // No aceptar asignaciones de roles enviadas
        // por el cliente: el rol sera CONSULTA.
        $permitidos = [
            'username',
            'email',
            'nombre_completo',
            'password'
        ];

        foreach (array_keys($datos) as $campo) {
            if (!in_array($campo, $permitidos, true)) {
                $this->responder(422, [
                    'estado' => 422,
                    'mensaje' =>
                        'Campo no permitido: ' . $campo
                ]);
            }
        }

        try {
            $id = $this->servicio->crear($datos);

            $this->responder(201, [
                'estado' => 201,
                'mensaje' =>
                    'Usuario registrado correctamente.',
                'datos' => [
                    'id' => $id,
                    'rol' => 'consulta'
                ]
            ]);

        } catch (InvalidArgumentException $error) {
            $this->responder(422, [
                'estado' => 422,
                'mensaje' => 'Datos invalidos.',
                'errores' => [
                    $error->getMessage()
                ]
            ]);

        } catch (DomainException $error) {
            $this->responder(409, [
                'estado' => 409,
                'mensaje' =>
                    'El usuario o correo ya esta registrado.'
            ]);

        } catch (PDOException $error) {
            // Duplicado detectado por un indice UNIQUE.
            if (
                (int) ($error->errorInfo[1] ?? 0) === 1062
            ) {
                $this->responder(409, [
                    'estado' => 409,
                    'mensaje' =>
                        'El usuario o correo ya esta registrado.'
                ]);
            }

            error_log(
                'Error de base de datos al registrar usuario V3.'
            );

            $this->responder(500, [
                'estado' => 500,
                'mensaje' => 'Error interno del servidor.'
            ]);

        } catch (Throwable $error) {
            error_log(
                'Error interno al registrar usuario V3.'
            );

            $this->responder(500, [
                'estado' => 500,
                'mensaje' => 'Error interno del servidor.'
            ]);
        }
    }

    public function procesarPeticion(
        string $metodo,
        ?string $id = null
    ): void {
        if (strtoupper($metodo) === 'POST') {
            if ($id !== null && $id !== '') {
                header('Allow: GET');

                $this->responder(405, [
                    'estado' => 405,
                    'mensaje' => 'Metodo no permitido.'
                ]);
            }

            $this->registrarUsuarioV3();
        }
        if (strtoupper($metodo) !== 'GET') {
            header('Allow: GET');

            $this->responder(405, [
                'estado' => 405,
                'mensaje' => 'Metodo no permitido.'
            ]);
        }

        try {
            if ($id === null || $id === '') {

                $usuarios = $this->servicio->listar();

                $this->responder(200, [
                    'tabla' => 'usuario',
                    'total' => count($usuarios),
                    'datos' => $usuarios
                ]);
            }

            $identificador = filter_var(
                $id,
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 1
                    ]
                ]
            );

            if ($identificador === false) {
                $this->responder(422, [
                    'estado' => 422,
                    'mensaje' => 'Identificador invalido.',
                    'errores' => [
                        'El ID debe ser un entero positivo.'
                    ]
                ]);
            }

            $usuario = $this->servicio->obtenerPorId(
                $identificador
            );

            if ($usuario === null) {
                $this->responder(404, [
                    'estado' => 404,
                    'mensaje' => 'Usuario no encontrado.'
                ]);
            }

            $this->responder(200, [
                'tabla' => 'usuario',
                'datos' => $usuario
            ]);

        } catch (Throwable $error) {

            error_log(
                'Error interno en ControladorUsuariosV3.'
            );

            $this->responder(500, [
                'estado' => 500,
                'mensaje' => 'Error interno del servidor.'
            ]);
        }
    }
}