<?php

declare(strict_types=1);

/*
=====================================================
PROYECTO DE INVESTIGACION - VERSION 3

ARCHIVO:
controladores/ControladorAutenticacion.php

QUE ES:
Controlador HTTP para el inicio de sesion.

POR QUE:
Recibe las credenciales del frontend y solicita
al servicio que autentique al usuario.

QUE NO HACE:
- No ejecuta SQL.
- No compara contrasenas.
- No genera JWT directamente.
- No administra sesiones PHP.
=====================================================
*/

require_once __DIR__ . '/../servicios/IServicioAutenticacion.php';

final class ControladorAutenticacion
{
    private IServicioAutenticacion $servicio;

    public function __construct(
        IServicioAutenticacion $servicio
    ) {
        $this->servicio = $servicio;
    }

    private function responder(
        int $estado,
        array $datos
    ): void {
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

    public function procesarPeticion(
        string $metodo
    ): void {

        // El login solo acepta POST.
        if ($metodo !== 'POST') {
            header('Allow: POST');

            $this->responder(405, [
                'estado' => 405,
                'mensaje' => 'Método no permitido.'
            ]);
        }

        // Leer el JSON enviado por el frontend.
        $contenido = file_get_contents('php://input');

        if (
            $contenido === false ||
            strlen($contenido) > 8192
        ) {
            $this->responder(413, [
                'estado' => 413,
                'mensaje' => 'El contenido enviado es demasiado grande.'
            ]);
        }

        $datos = json_decode($contenido, true);

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($datos) ||
            array_is_list($datos)
        ) {
            $this->responder(400, [
                'estado' => 400,
                'mensaje' => 'El cuerpo debe ser un objeto JSON válido.'
            ]);
        }

        $username = $datos['username'] ?? null;
        $password = $datos['password'] ?? null;

        if (
            !is_string($username) ||
            !is_string($password) ||
            trim($username) === '' ||
            strlen($username) > 100 ||
            $password === ''
        ) {
            $this->responder(422, [
                'estado' => 422,
                'mensaje' => 'Datos inválidos.',
                'errores' => [
                    'Ingrese un usuario y una contraseña válidos.'
                ]
            ]);
        }

        try {
            // La capa de servicio comprueba la contraseña.
            $resultado = $this->servicio->iniciarSesion(
                trim($username),
                $password
            );

            if ($resultado === null) {
                $this->responder(401, [
                    'estado' => 401,
                    'mensaje' => 'Usuario o contraseña incorrectos.'
                ]);
            }

            // Respuesta compatible con el frontend V3.
            $this->responder(200, [
                'estado' => 200,
                'mensaje' => 'Inicio de sesión correcto.',
                'datos' => $resultado
            ]);

        } catch (Throwable $error) {

            // Registrar el fallo en el servidor,
            // sin exponer contraseñas ni secretos al cliente.
            error_log(
                'Error interno en autenticacion: ' .
                $error->getMessage()
            );

            $this->responder(500, [
                'estado' => 500,
                'mensaje' => 'No fue posible iniciar sesión.'
            ]);
        }
    }
}