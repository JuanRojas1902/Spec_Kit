<?php

declare(strict_types=1);

/**
 * ARCHIVO: ControladorDocente.php
 *
 * QUE ES:
 * Controlador encargado de recibir y procesar
 * las peticiones relacionadas con los docentes.
 *
 * POR QUE ESTA AQUI:
 * Permite separar la comunicacion HTTP de las reglas
 * de negocio y del acceso a la base de datos.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba la existencia de llaves foraneas.
 * No crea repositorios ni servicios concretos.
 */


require_once __DIR__ . '/../servicios/IServicioDocente.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

require_once __DIR__ . '/../excepciones/ConflictoDeIntegridadExcepcion.php';


class ControladorDocente
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // El controlador depende de la interfaz del servicio.
    // =====================================================

    private IServicioDocente $servicio;


    // =====================================================
    // 2. CONSTRUCTOR
    // Recibe el servicio mediante inyeccion de dependencias.
    // =====================================================

    public function __construct(IServicioDocente $servicio)
    {
        $this->servicio = $servicio;
    }


    // =====================================================
    // 3. PROCESAR PETICION
    //
    // Identifica la operacion solicitada segun el metodo
    // recibido y la presencia de una cedula en la ruta.
    // =====================================================

    public function procesarPeticion(
        string $metodo,
        ?string $idStr = null
    ): void {

        try {

            $cedula = null;

            // Validar la cedula recibida en la URL.

            if ($idStr !== null) {

                $cedula = filter_var(
                    $idStr,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );

                if ($cedula === false) {

                    throw new InvalidArgumentException(
                        'La cédula debe ser un número entero mayor que cero.'
                    );

                }
            }


            // GET: listar todos los docentes.

            if ($metodo === 'GET' && $cedula === null) {

                $this->listar();

            }


            // GET: consultar un docente especifico.

            elseif ($metodo === 'GET' && $cedula !== null) {

                $this->consultar($cedula);

            }


            // POST: registrar un docente.

            elseif ($metodo === 'POST' && $cedula === null) {

                $this->crear();

            }


            // PUT: reemplazar una ficha completa.

            elseif ($metodo === 'PUT' && $cedula !== null) {

                $this->actualizar($cedula, false);

            }


            // PATCH: actualizar algunos campos.

            elseif ($metodo === 'PATCH' && $cedula !== null) {

                $this->actualizar($cedula, true);

            }


            // DELETE: retirar un docente.

            elseif ($metodo === 'DELETE' && $cedula !== null) {

                $this->retirar($cedula);

            }


            // Cualquier otra combinacion no esta permitida.

            else {

                $this->responder(405, [
                    'estado' => 405,
                    'mensaje' => 'Método no permitido.',
                    'detalle' => 'La operación solicitada no está disponible.'
                ]);

            }


        // =================================================
        // 4. MANEJO DE EXCEPCIONES
        //
        // Cada excepcion se convierte en la respuesta
        // correspondiente segun el contrato de la API.
        // =================================================

        } catch (NoEncontradoExcepcion $error) {

            $this->responder(404, [
                'estado' => 404,
                'mensaje' => 'Docente no encontrado.',
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

            // El detalle tecnico se registra en el servidor,
            // pero no se muestra a la persona que usa el sistema.

            error_log($error->getMessage());

            $this->responder(500, [
                'estado' => 500,
                'mensaje' => 'No fue posible completar la operación.',
                'detalle' => 'Ocurrió un problema inesperado en el servicio.'
            ]);

        }
    }


    // =====================================================
    // 5. LISTAR DOCENTES
    //
    // Permite recibir un limite mediante la URL.
    // Si no existen datos, responde 204 sin contenido.
    // =====================================================

    private function listar(): void
    {
        $limite = filter_var(
            $_GET['limite'] ?? 1000,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($limite === false) {

            throw new InvalidArgumentException(
                'El límite debe ser un número entero mayor que cero.'
            );

        }

        $docentes = $this->servicio->obtenerTodos($limite);

        if (empty($docentes)) {

            http_response_code(204);

            return;

        }

        $datos = [];

        foreach ($docentes as $docente) {

            $datos[] = $docente->toArray();

        }

        $this->responder(200, [
            'tabla' => 'docente',
            'limite' => $limite,
            'total' => count($datos),
            'datos' => $datos
        ]);
    }


    // =====================================================
    // 6. CONSULTAR UN DOCENTE
    // Busca una ficha mediante su cedula.
    // =====================================================

    private function consultar(int $cedula): void
    {
        $docente = $this->servicio->obtenerPorId($cedula);

        $this->responder(
            200,
            $docente->toArray()
        );
    }


    // =====================================================
    // 7. CREAR DOCENTE
    //
    // Valida los datos obligatorios antes de solicitar
    // al servicio que registre el docente.
    // =====================================================

    private function crear(): void
    {
        $cuerpo = $this->leerCuerpo();

        if ($cuerpo === null) {
            return;
        }

        $errores = $this->validarCampos(
            $cuerpo,
            true,
            false
        );

        if (!empty($errores)) {

            $this->responderErrores($errores);

            return;

        }

        $filas = $this->servicio->crear(
            $cuerpo['cedula'],
            trim($cuerpo['nombres']),
            trim($cuerpo['apellidos'])
        );

        $this->responder(200, [
            'estado' => 200,
            'mensaje' => 'Docente creado exitosamente.',
            'filasAfectadas' => $filas
        ]);
    }


    // =====================================================
    // 8. ACTUALIZAR DOCENTE
    //
    // PUT: exige todos los campos modificables.
    // PATCH: solo valida y modifica los campos recibidos.
    //
    // La cedula y el estado activo no son modificables
    // mediante estas operaciones.
    // =====================================================

    private function actualizar(
        int $cedula,
        bool $parcial
    ): void {

        $cuerpo = $this->leerCuerpo();

        if ($cuerpo === null) {
            return;
        }

        $errores = $this->validarCampos(
            $cuerpo,
            false,
            $parcial
        );

        if (!empty($errores)) {

            $this->responderErrores($errores);

            return;

        }

        if ($parcial) {

            $campos = [];

            if (array_key_exists('nombres', $cuerpo)) {

                $campos['nombres'] = trim($cuerpo['nombres']);

            }

            if (array_key_exists('apellidos', $cuerpo)) {

                $campos['apellidos'] = trim($cuerpo['apellidos']);

            }

            // El servicio rechaza una actualizacion vacia.

            $filas = $this->servicio->actualizarParcial(
                $cedula,
                $campos
            );

        } else {

            $filas = $this->servicio->reemplazar(
                $cedula,
                trim($cuerpo['nombres']),
                trim($cuerpo['apellidos'])
            );

        }

        $this->responder(200, [
            'estado' => 200,
            'mensaje' => 'Docente actualizado exitosamente.',
            'filasAfectadas' => $filas
        ]);
    }


    // =====================================================
    // 9. RETIRAR DOCENTE
    //
    // Solicita el borrado logico al servicio.
    // No elimina fisicamente el registro.
    // =====================================================

    private function retirar(int $cedula): void
    {
        $filas = $this->servicio->retirar($cedula);

        $this->responder(200, [
            'estado' => 200,
            'mensaje' => 'Docente retirado exitosamente.',
            'filasAfectadas' => $filas
        ]);
    }


    // =====================================================
    // 10. LEER EL CUERPO DE LA PETICION
    //
    // Convierte el contenido JSON recibido en un arreglo.
    // Si el contenido no es valido, responde 422.
    // =====================================================

    private function leerCuerpo(): ?array
    {
        $contenido = file_get_contents('php://input');

        if ($contenido === false) {

            throw new RuntimeException(
                'No fue posible leer la petición.'
            );

        }

        if (trim($contenido) === '') {

            return [];

        }

        try {

            $datos = json_decode(
                $contenido,
                false,
                512,
                JSON_THROW_ON_ERROR
            );

        } catch (JsonException $error) {

            $this->responderErrores([
                'El contenido enviado no tiene un formato válido.'
            ]);

            return null;

        }

        if (!$datos instanceof stdClass) {

            $this->responderErrores([
                'Los datos deben enviarse como un objeto.'
            ]);

            return null;

        }

        return get_object_vars($datos);
    }


    // =====================================================
    // 11. VALIDAR LOS CAMPOS
    //
    // Comprueba los tipos, campos obligatorios y longitudes.
    // No consulta la base de datos.
    // =====================================================

    private function validarCampos(
        array $cuerpo,
        bool $crear,
        bool $parcial
    ): array {

        $errores = [];

        // La cedula es obligatoria al crear un docente.

        if ($crear) {

            if (
                !isset($cuerpo['cedula']) ||
                !is_int($cuerpo['cedula']) ||
                $cuerpo['cedula'] <= 0 ||
                $cuerpo['cedula'] > 2147483647
            ) {

                $errores[] =
                    'La cédula debe ser un número entero positivo válido.';

            }
        }


        // Definimos solamente los campos modificables.

        $campos = [
            'nombres' => 'Los nombres',
            'apellidos' => 'Los apellidos'
        ];


        foreach ($campos as $campo => $etiqueta) {

            // En PATCH no es obligatorio enviar todos
            // los campos, solamente los que se modifican.

            if (!array_key_exists($campo, $cuerpo)) {

                if (!$parcial) {

                    $errores[] = $etiqueta . ' son obligatorios.';

                }

                continue;

            }


            $valor = $cuerpo[$campo];


            // El campo debe contener un texto no vacio.

            if (!is_string($valor) || trim($valor) === '') {

                $errores[] =
                    $etiqueta . ' deben contener un texto válido.';

                continue;

            }


            // Comprobamos la longitud en caracteres,
            // incluyendo letras con tildes y otros acentos.

            if (preg_match('/\A.{1,100}\z/us', trim($valor)) !== 1) {

                $errores[] =
                    $etiqueta . ' no pueden superar los 100 caracteres.';

            }

        }

        return $errores;
    }


    // =====================================================
    // 12. RESPONDER ERRORES DE VALIDACION
    //
    // Construye el formato 422 establecido en los contratos.
    // =====================================================

    private function responderErrores(array $errores): void
    {
        $this->responder(422, [
            'estado' => 422,
            'mensaje' => 'Datos inválidos.',
            'errores' => $errores
        ]);
    }


    // =====================================================
    // 13. GENERAR RESPUESTA JSON
    //
    // Establece el codigo de respuesta y convierte
    // el arreglo de datos al formato JSON.
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