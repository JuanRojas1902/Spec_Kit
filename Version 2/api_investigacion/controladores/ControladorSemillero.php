<?php

declare(strict_types=1);

/**
 * ARCHIVO: ControladorSemillero.php
 *
 * QUE ES:
 * Controlador encargado de procesar las peticiones
 * de consulta de semilleros de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite recibir las solicitudes, validar sus
 * parametros y generar las respuestas de la API.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No crea, modifica ni retira semilleros.
 * No administra directamente las reglas de negocio.
 */


require_once __DIR__ . '/../servicios/IServicioSemillero.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';


class ControladorSemillero
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // Dependencia de la interfaz del servicio.
    // =====================================================

    private IServicioSemillero $servicio;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe el servicio mediante inyeccion
    // de dependencias.
    // =====================================================

    public function __construct(
        IServicioSemillero $servicio
    ) {
        $this->servicio = $servicio;
    }


    // =====================================================
    // 3. PROCESAR PETICION
    //
    // El recurso semillero es de solo lectura.
    //
    // GET sin identificador: listar.
    // GET con identificador: consultar.
    //
    // Los demas metodos no estan permitidos.
    // =====================================================

    public function procesarPeticion(
        string $metodo,
        ?string $idStr = null
    ): void {

        try {

            // Listar todos los semilleros activos.

            if ($metodo === 'GET' && $idStr === null) {

                $this->listar();

            }


            // Consultar un semillero mediante su ID.

            elseif ($metodo === 'GET' && $idStr !== null) {

                $this->consultar(
                    $this->validarId($idStr)
                );

            }


            // Rechazar cualquier operacion de escritura.

            else {

                header('Allow: GET, OPTIONS');

                $this->responder(405, [
                    'estado' => 405,
                    'mensaje' => 'Método no permitido.',
                    'detalle' =>
                        'Los semilleros se administran desde los grupos de investigación.'
                ]);

            }


        // =================================================
        // 4. MANEJO DE EXCEPCIONES
        //
        // Traduce las excepciones del servicio
        // a los codigos correspondientes.
        // =================================================

        } catch (NoEncontradoExcepcion $error) {

            $this->responder(404, [
                'estado' => 404,
                'mensaje' => 'Semillero no encontrado.',
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
                'mensaje' => 'No fue posible completar la consulta.',
                'detalle' => 'Ocurrió un problema inesperado en el servicio.'
            ]);

        }
    }


    // =====================================================
    // 5. LISTAR SEMILLEROS
    //
    // Recupera los semilleros activos mediante el servicio.
    //
    // El limite predeterminado es de 1000 registros.
    //
    // Si no existen registros, devuelve 204 sin cuerpo.
    // =====================================================

    private function listar(): void
    {
        $limite = $this->validarLimite(
            $_GET['limite'] ?? '1000'
        );


        // Solicitar los semilleros al servicio.

        $semilleros = $this->servicio->obtenerTodos(
            $limite
        );


        // Si no existen registros activos,
        // se devuelve una respuesta sin contenido.

        if (empty($semilleros)) {

            http_response_code(204);

            return;

        }


        // Convertir los objetos en arreglos asociativos.

        $datos = [];

        foreach ($semilleros as $semillero) {

            $datos[] = $semillero->toArray();

        }


        // Generar el formato de listado del Spec Kit.

        $this->responder(200, [
            'tabla' => 'semillero',
            'limite' => $limite,
            'total' => count($datos),
            'datos' => $datos
        ]);
    }


    // =====================================================
    // 6. CONSULTAR UN SEMILLERO
    //
    // Recupera un semillero mediante su identificador.
    // Devuelve exclusivamente los datos del registro.
    // =====================================================

    private function consultar(int $id): void
    {
        $semillero = $this->servicio->obtenerPorId($id);

        $this->responder(
            200,
            $semillero->toArray()
        );
    }


    // =====================================================
    // 7. VALIDAR IDENTIFICADOR
    //
    // Comprueba que el identificador sea un entero
    // positivo dentro del rango permitido.
    // =====================================================

    private function validarId(string $idStr): int
    {
        $id = filter_var(
            $idStr,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                    'max_range' => 2147483647
                ]
            ]
        );

        if ($id === false) {

            throw new InvalidArgumentException(
                'El identificador debe ser un número entero positivo válido.'
            );

        }

        return $id;
    }


    // =====================================================
    // 8. VALIDAR LIMITE
    //
    // Comprueba que el limite de registros sea
    // un numero entero mayor que cero.
    // =====================================================

    private function validarLimite(mixed $valor): int
    {
        if (!is_string($valor) && !is_int($valor)) {

            throw new InvalidArgumentException(
                'El límite debe ser un número entero mayor que cero.'
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
                'El límite debe ser un número entero mayor que cero.'
            );

        }

        return $limite;
    }


    // =====================================================
    // 9. GENERAR RESPUESTA JSON
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