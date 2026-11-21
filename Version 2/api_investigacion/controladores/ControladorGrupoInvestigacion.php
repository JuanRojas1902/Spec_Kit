<?php

declare(strict_types=1);

/**
 * ARCHIVO: ControladorGrupoInvestigacion.php
 *
 * QUE ES:
 * Controlador encargado de procesar las peticiones
 * del maestro-detalle de grupos y semilleros.
 *
 * POR QUE ESTA AQUI:
 * Permite validar los datos recibidos, solicitar
 * las operaciones al servicio y generar respuestas.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No ejecuta procedimientos almacenados directamente.
 * No administra transacciones.
 * No comprueba previamente las llaves foraneas.
 */


require_once __DIR__ . '/../servicios/IServicioGrupoInvestigacion.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

require_once __DIR__ . '/../excepciones/ConflictoDeIntegridadExcepcion.php';


class ControladorGrupoInvestigacion
{
    // =====================================================
    // 1. ATRIBUTO
    // Dependencia de la interfaz del servicio.
    // =====================================================

    private IServicioGrupoInvestigacion $servicio;


    // =====================================================
    // 2. CONSTRUCTOR
    // Recibe el servicio mediante inyeccion de dependencias.
    // =====================================================

    public function __construct(
        IServicioGrupoInvestigacion $servicio
    ) {
        $this->servicio = $servicio;
    }


    // =====================================================
    // 3. PROCESAR PETICION
    //
    // Identifica la operacion solicitada.
    // El maestro-detalle no admite PATCH.
    // =====================================================

    public function procesarPeticion(
        string $metodo,
        ?string $idStr = null
    ): void {

        try {

            // GET sin identificador: listar grupos.

            if ($metodo === 'GET' && $idStr === null) {

                $this->listar();

            }


            // GET con identificador: consultar grupo.

            elseif ($metodo === 'GET' && $idStr !== null) {

                $this->consultar(
                    $this->validarId($idStr)
                );

            }


            // POST: crear grupo y semilleros.

            elseif ($metodo === 'POST' && $idStr === null) {

                $this->crear();

            }


            // PUT: reemplazar encabezado y detalle.

            elseif ($metodo === 'PUT' && $idStr !== null) {

                $this->reemplazar(
                    $this->validarId($idStr)
                );

            }


            // DELETE: retirar grupo.

            elseif ($metodo === 'DELETE' && $idStr !== null) {

                $this->retirar(
                    $this->validarId($idStr)
                );

            }


            // No existe una operacion PATCH para el grupo.

            else {

                header(
                    $idStr === null
                        ? 'Allow: GET, POST'
                        : 'Allow: GET, PUT, DELETE'
                );

                $this->responder(405, [
                    'estado' => 405,
                    'mensaje' => 'Método no permitido.',
                    'detalle' => 'La operación solicitada no está disponible.'
                ]);

            }


        // =================================================
        // 4. MANEJO DE EXCEPCIONES
        //
        // Traduce las excepciones a los codigos
        // establecidos en el contrato de la API.
        // =================================================

        } catch (NoEncontradoExcepcion $error) {

            $this->responder(404, [
                'estado' => 404,
                'mensaje' => 'Grupo no encontrado.',
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

            // El detalle tecnico se registra en el servidor.
            // No se exponen detalles internos al usuario.

            error_log($error->getMessage());

            $this->responder(500, [
                'estado' => 500,
                'mensaje' => 'No fue posible completar la operación.',
                'detalle' => 'Ocurrió un problema inesperado en el servicio.'
            ]);

        }
    }


    // =====================================================
    // 5. LISTAR GRUPOS DE INVESTIGACION
    //
    // Devuelve solamente los datos del encabezado.
    // No admite un parametro de limite.
    // =====================================================

    private function listar(): void
    {
        if (array_key_exists('limite', $_GET)) {

            throw new InvalidArgumentException(
                'El listado de grupos no admite límite.'
            );

        }

        $grupos = $this->servicio->obtenerTodos();

        if (empty($grupos)) {

            http_response_code(204);

            return;

        }

        $datos = [];

        foreach ($grupos as $grupo) {

            // false indica que no se incluye el detalle.

            $datos[] = $grupo->toArray(false);

        }

        $this->responder(200, [
            'tabla' => 'grupo_investigacion',
            'limite' => null,
            'total' => count($datos),
            'datos' => $datos
        ]);
    }


    // =====================================================
    // 6. CONSULTAR UN GRUPO
    //
    // Devuelve el encabezado y todos sus semilleros.
    // =====================================================

    private function consultar(int $id): void
    {
        $grupo = $this->servicio->obtenerPorId($id);

        $this->responder(
            200,
            $grupo->toArray()
        );
    }


    // =====================================================
    // 7. CREAR GRUPO DE INVESTIGACION
    //
    // Valida el encabezado y el detalle antes de
    // solicitar al servicio que almacene los datos.
    // =====================================================

    private function crear(): void
    {
        $cuerpo = $this->leerCuerpo();

        if ($cuerpo === null) {
            return;
        }

        $errores = $this->validarCampos($cuerpo);

        if (!empty($errores)) {

            $this->responderErrores($errores);

            return;

        }

        $grupo = $this->servicio->crear(
            trim($cuerpo->gruplac),
            trim($cuerpo->categoria),
            trim($cuerpo->convocatoria),
            trim($cuerpo->ambito),
            $cuerpo->universidad_id,
            $this->prepararDetalle($cuerpo->detalle)
        );

        $this->responder(200, [
            'estado' => 200,
            'mensaje' => 'Grupo creado exitosamente.',
            'grupo' => $grupo->toArray()
        ]);
    }


    // =====================================================
    // 8. REEMPLAZAR GRUPO DE INVESTIGACION
    //
    // PUT exige todos los campos del encabezado
    // y la lista completa de semilleros.
    //
    // El detalle anterior sera reemplazado.
    // =====================================================

    private function reemplazar(int $id): void
    {
        $cuerpo = $this->leerCuerpo();

        if ($cuerpo === null) {
            return;
        }

        $errores = $this->validarCampos($cuerpo);

        if (!empty($errores)) {

            $this->responderErrores($errores);

            return;

        }

        $grupo = $this->servicio->reemplazar(
            $id,
            trim($cuerpo->gruplac),
            trim($cuerpo->categoria),
            trim($cuerpo->convocatoria),
            trim($cuerpo->ambito),
            $cuerpo->universidad_id,
            $this->prepararDetalle($cuerpo->detalle)
        );

        $this->responder(200, [
            'estado' => 200,
            'mensaje' => 'Grupo reemplazado.',
            'grupo' => $grupo->toArray()
        ]);
    }


    // =====================================================
    // 9. RETIRAR GRUPO DE INVESTIGACION
    //
    // Solicita el borrado logico del grupo.
    //
    // El procedimiento almacenado verifica
    // si existen semilleros activos.
    // =====================================================

    private function retirar(int $id): void
    {
        $filas = $this->servicio->retirar($id);

        $this->responder(200, [
            'estado' => 200,
            'mensaje' => 'Grupo retirado.',
            'filasAfectadas' => $filas
        ]);
    }


    // =====================================================
    // 10. VALIDAR IDENTIFICADOR
    //
    // Comprueba que el identificador de la ruta sea
    // un numero entero positivo valido.
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
    // 11. LEER CUERPO DE LA PETICION
    //
    // Convierte el JSON recibido en un objeto.
    //
    // Conservamos los objetos y arreglos separados
    // para validar correctamente la lista de semilleros.
    // =====================================================

    private function leerCuerpo(): ?stdClass
    {
        $contenido = file_get_contents('php://input');

        if ($contenido === false) {

            throw new RuntimeException(
                'No fue posible leer la petición.'
            );

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

        return $datos;
    }


    // =====================================================
    // 12. VALIDACION DEL MAESTRO Y DEL DETALLE
    //
    // NIVEL 1: campos del grupo.
    // NIVEL 2: cada renglon de semilleros.
    //
    // Se acumulan los errores encontrados para
    // devolverlos juntos al usuario.
    // =====================================================

    private function validarCampos(stdClass $cuerpo): array
    {
        $errores = [];


        // -------------------------------------------------
        // NIVEL 1: VALIDAR EL ENCABEZADO DEL GRUPO
        // -------------------------------------------------

        $campos = [
            'gruplac' => [
                'etiqueta' => 'El GrupLAC',
                'maximo' => 255
            ],
            'categoria' => [
                'etiqueta' => 'La categoría',
                'maximo' => 50
            ],
            'convocatoria' => [
                'etiqueta' => 'La convocatoria',
                'maximo' => 255
            ],
            'ambito' => [
                'etiqueta' => 'El ámbito',
                'maximo' => 255
            ]
        ];


        foreach ($campos as $campo => $reglas) {

            $valor = $cuerpo->{$campo} ?? null;

            $etiqueta = $reglas['etiqueta'];

            $maximo = $reglas['maximo'];


            // Comprobar que el campo sea obligatorio.

            if (!is_string($valor) || trim($valor) === '') {

                $errores[] =
                    $etiqueta . ' debe contener un texto válido.';

                continue;

            }


            // Comprobar la longitud maxima permitida.

            if (!$this->longitudValida($valor, $maximo)) {

                $errores[] =
                    $etiqueta . ' no puede superar los '
                    . $maximo . ' caracteres.';

            }

        }


        // Validamos el formato de la llave foranea.
        // MariaDB comprobara si la universidad existe.

        $universidadId = $cuerpo->universidad_id ?? null;

        if (
            !is_int($universidadId) ||
            $universidadId <= 0 ||
            $universidadId > 2147483647
        ) {

            $errores[] =
                'Debe seleccionar una universidad válida.';

        }


        // -------------------------------------------------
        // NIVEL 2: VALIDAR EL DETALLE DE SEMILLEROS
        // -------------------------------------------------

        if (
            !property_exists($cuerpo, 'detalle') ||
            !is_array($cuerpo->detalle)
        ) {

            $errores[] =
                'Debe enviar una lista de semilleros.';

            return $errores;

        }


        // Una lista vacia es valida.
        // Si hay semilleros, validamos cada renglon.

        foreach ($cuerpo->detalle as $indice => $semillero) {

            // Los indices empiezan en cero.
            // Los renglones se muestran desde uno.

            $numeroRenglon = $indice + 1;


            if (!$semillero instanceof stdClass) {

                $errores[] =
                    'El renglón ' . $numeroRenglon
                    . ': debe contener los datos de un semillero.';

                continue;

            }


            $nombre = $semillero->nombre ?? null;


            // Validar que el nombre sea obligatorio.

            if (!is_string($nombre) || trim($nombre) === '') {

                $errores[] =
                    'El renglón ' . $numeroRenglon
                    . ': el nombre es obligatorio.';

                continue;

            }


            // El nombre puede tener hasta 100 caracteres.

            if (!$this->longitudValida($nombre, 100)) {

                $errores[] =
                    'El renglón ' . $numeroRenglon
                    . ': el nombre no puede superar los 100 caracteres.';

            }

        }

        return $errores;
    }


    // =====================================================
    // 13. VALIDAR LONGITUD DEL TEXTO
    //
    // Cuenta caracteres incluyendo tildes y otros
    // caracteres Unicode.
    // =====================================================

    private function longitudValida(
        string $valor,
        int $maximo
    ): bool {

        $patron = '/\A.{1,' . $maximo . '}\z/us';

        return preg_match(
            $patron,
            trim($valor)
        ) === 1;
    }


    // =====================================================
    // 14. PREPARAR DETALLE
    //
    // Convierte los semilleros validados en arreglos.
    //
    // Solo conserva el nombre, porque MariaDB genera
    // el identificador y asigna el grupo correspondiente.
    // =====================================================

    private function prepararDetalle(array $semilleros): array
    {
        $detalle = [];

        foreach ($semilleros as $semillero) {

            $detalle[] = [
                'nombre' => trim($semillero->nombre)
            ];

        }

        return $detalle;
    }


    // =====================================================
    // 15. RESPONDER ERRORES DE VALIDACION
    //
    // Genera el formato 422 establecido en los contratos.
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
    // 16. GENERAR RESPUESTA JSON
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