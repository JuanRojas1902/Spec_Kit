<?php

declare(strict_types=1);

/**
 * ARCHIVO: ControladorAaLinea.php
 *
 * QUE ES:
 * Controlador encargado de procesar las peticiones
 * relacionadas con areas de aplicacion y lineas
 * de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite recibir las solicitudes, validar los datos
 * y generar las respuestas correspondientes de la API.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba directamente las llaves foraneas.
 * No modifica relaciones existentes.
 * No implementa directamente las reglas de negocio.
 */


require_once __DIR__ . '/../servicios/IServicioAaLinea.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

require_once __DIR__ . '/../excepciones/ConflictoDeIntegridadExcepcion.php';


class ControladorAaLinea
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // Dependencia de la interfaz del servicio.
    // =====================================================

    private IServicioAaLinea $servicio;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe el servicio mediante inyeccion
    // de dependencias.
    // =====================================================

    public function __construct(
        IServicioAaLinea $servicio
    ) {
        $this->servicio = $servicio;
    }


    // =====================================================
    // 3. PROCESAR PETICION
    //
    // GET: listar relaciones.
    // POST: asignar una relacion.
    // DELETE: retirar una relacion.
    //
    // PUT y PATCH no estan permitidos.
    // =====================================================

    public function procesarPeticion(
        string $metodo,
        ?string $areaAplicacionIdStr = null,
        ?string $lineaIdStr = null
    ): void {

        try {

            // Listar relaciones.

            if (
                $metodo === 'GET' &&
                $areaAplicacionIdStr === null &&
                $lineaIdStr === null
            ) {

                $this->listar();

            }


            // Asignar una relacion.

            elseif (
                $metodo === 'POST' &&
                $areaAplicacionIdStr === null &&
                $lineaIdStr === null
            ) {

                $this->asignar();

            }


            // Retirar una relacion.

            elseif ($metodo === 'DELETE') {

                if (
                    $areaAplicacionIdStr === null ||
                    $lineaIdStr === null
                ) {

                    throw new InvalidArgumentException(
                        'Debe proporcionar las dos claves de la relación.'
                    );

                }


                $areaAplicacionId = $this->validarClaveRuta(
                    $areaAplicacionIdStr,
                    'area_aplicacion_id'
                );


                $lineaId = $this->validarClaveRuta(
                    $lineaIdStr,
                    'linea_id'
                );


                $this->retirar(
                    $areaAplicacionId,
                    $lineaId
                );

            }


            // Rechazar los metodos no permitidos.

            else {

                header('Allow: GET, POST, DELETE, OPTIONS');

                $this->responder(405, [
                    'estado' => 405,
                    'mensaje' => 'Método no permitido.',
                    'detalle' =>
                        'Las relaciones solo se pueden listar, asignar y retirar.'
                ]);

            }


        // =================================================
        // 4. MANEJO DE EXCEPCIONES
        //
        // Traduce las excepciones del servicio
        // a respuestas HTTP.
        // =================================================

        } catch (NoEncontradoExcepcion $error) {

            $this->responder(404, [
                'estado' => 404,
                'mensaje' => 'Relación no encontrada.',
                'detalle' => $error->getMessage()
            ]);

        } catch (ConflictoDeIntegridadExcepcion $error) {

            $this->responder(409, [
                'estado' => 409,
                'mensaje' => 'Conflicto de integridad.',
                'detalle' => $error->getMessage()
            ]);

        } catch (InvalidArgumentException $error) {

            $this->responder(400, [
                'estado' => 400,
                'mensaje' => 'Solicitud inválida.',
                'detalle' => $error->getMessage()
            ]);

        } catch (Throwable $error) {

            // Registrar el error tecnico en el servidor.

            error_log($error->getMessage());


            // No exponer informacion interna al usuario.

            $this->responder(500, [
                'estado' => 500,
                'mensaje' => 'No fue posible completar la operación.',
                'detalle' => 'Ocurrió un problema inesperado en el servicio.'
            ]);

        }
    }


    // =====================================================
    // 5. LISTAR RELACIONES
    //
    // Devuelve las relaciones registradas entre areas
    // de aplicacion y lineas de investigacion.
    //
    // Si no existen relaciones, responde 204
    // sin contenido.
    // =====================================================

    private function listar(): void
    {
        $limite = $this->validarLimite(
            $_GET['limite'] ?? '1000'
        );


        // Solicitar las relaciones al servicio.

        $relaciones = $this->servicio->obtenerTodos(
            $limite
        );


        // Si no hay relaciones, devolver 204.

        if (empty($relaciones)) {

            http_response_code(204);

            return;

        }


        // Convertir los objetos en arreglos asociativos.

        $datos = [];

        foreach ($relaciones as $relacion) {

            $datos[] = $relacion->toArray();

        }


        // Generar la respuesta de listado.

        $this->responder(200, [
            'tabla' => 'aa_linea',
            'limite' => $limite,
            'total' => count($datos),
            'datos' => $datos
        ]);
    }


    // =====================================================
    // 6. ASIGNAR RELACION
    //
    // Recibe un cuerpo JSON con area_aplicacion_id
    // y linea_id.
    //
    // Valida que las dos claves sean enteros positivos.
    //
    // Si los datos son invalidos, responde 422.
    // =====================================================

    private function asignar(): void
    {
        $datos = $this->leerCuerpo();


        // Comprobar que el cuerpo sea un objeto JSON.

        if ($datos === null) {

            $this->responder(422, [
                'estado' => 422,
                'mensaje' => 'Datos inválidos.',
                'errores' => [
                    'Debe proporcionar un objeto JSON válido.'
                ]
            ]);

            return;

        }


        // Validar las dos claves.

        $errores = [];


        if (
            !isset($datos->area_aplicacion_id) ||
            !$this->esClaveValida($datos->area_aplicacion_id)
        ) {

            $errores[] =
                'area_aplicacion_id debe ser un entero positivo válido.';

        }


        if (
            !isset($datos->linea_id) ||
            !$this->esClaveValida($datos->linea_id)
        ) {

            $errores[] =
                'linea_id debe ser un entero positivo válido.';

        }


        // Si existen errores, devolver 422.

        if (!empty($errores)) {

            $this->responder(422, [
                'estado' => 422,
                'mensaje' => 'Datos inválidos.',
                'errores' => $errores
            ]);

            return;

        }


        // Solicitar al servicio la asignacion.

        $filasAfectadas = $this->servicio->asignar(
            $datos->area_aplicacion_id,
            $datos->linea_id
        );


        // Generar la respuesta exitosa.

        $this->responder(200, [
            'estado' => 200,
            'mensaje' => 'Pareja asignada.',
            'filasAfectadas' => $filasAfectadas
        ]);
    }


    // =====================================================
    // 7. RETIRAR RELACION
    //
    // Recibe las dos claves de la llave primaria
    // compuesta y solicita el retiro al servicio.
    // =====================================================

    private function retirar(
        int $areaAplicacionId,
        int $lineaId
    ): void {

        $filasAfectadas = $this->servicio->retirar(
            $areaAplicacionId,
            $lineaId
        );


        $this->responder(200, [
            'estado' => 200,
            'mensaje' => 'Pareja retirada.',
            'filasAfectadas' => $filasAfectadas
        ]);
    }


    // =====================================================
    // 8. LEER CUERPO JSON
    //
    // Recupera los datos enviados en la peticion.
    // Solamente acepta objetos JSON.
    // =====================================================

    private function leerCuerpo(): ?stdClass
    {
        $contenido = file_get_contents('php://input');

        if ($contenido === false) {

            return null;

        }

        try {

            $datos = json_decode(
                $contenido,
                false,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!$datos instanceof stdClass) {

                return null;

            }

            return $datos;

        } catch (JsonException $error) {

            return null;

        }
    }


    // =====================================================
    // 9. VALIDAR CLAVES DEL CUERPO
    //
    // Comprueba que el valor sea un entero positivo
    // dentro del rango permitido por INT.
    // =====================================================

    private function esClaveValida(mixed $valor): bool
    {
        return is_int($valor)
            && $valor > 0
            && $valor <= 2147483647;
    }


    // =====================================================
    // 10. VALIDAR CLAVES DE LA RUTA
    //
    // Convierte las claves recibidas en la URL
    // en numeros enteros positivos.
    // =====================================================

    private function validarClaveRuta(
        string $valor,
        string $campo
    ): int {

        $clave = filter_var(
            $valor,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                    'max_range' => 2147483647
                ]
            ]
        );

        if ($clave === false) {

            throw new InvalidArgumentException(
                $campo . ' debe ser un entero positivo válido.'
            );

        }

        return $clave;
    }


    // =====================================================
    // 11. VALIDAR LIMITE
    //
    // Comprueba que el limite sea un entero positivo.
    // =====================================================

    private function validarLimite(mixed $valor): int
    {
        if (!is_int($valor) && !is_string($valor)) {

            throw new InvalidArgumentException(
                'El límite debe ser un entero mayor que cero.'
            );

        }

        $limite = filter_var(
            $valor,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1
                ]
            ]
        );

        if ($limite === false) {

            throw new InvalidArgumentException(
                'El límite debe ser un entero mayor que cero.'
            );

        }

        return $limite;
    }


    // =====================================================
    // 12. GENERAR RESPUESTA JSON
    //
    // Establece el codigo de respuesta y convierte
    // los datos en formato JSON.
    // =====================================================

    private function responder(
        int $estado,
        array $datos
    ): void {

        http_response_code($estado);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $datos,
            JSON_UNESCAPED_UNICODE |
            JSON_INVALID_UTF8_SUBSTITUTE
        );
    }
}