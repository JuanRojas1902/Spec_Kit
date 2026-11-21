<?php
declare(strict_types=1);

require_once __DIR__ . '/../servicios/ensamblador.php';
require_once __DIR__ . '/../excepciones/ValidacionExcepcion.php';
require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

/**
 * Fase 5: Controlador de Área de Conocimiento
 * Qué hace: Lee el JSON, decide qué método del servicio llamar y devuelve respuestas HTTP.
 * Por qué está aquí: Separa las peticiones web de la lógica de negocio y base de datos[cite: 18].
 */
class ControladorAreaConocimiento 
{
    private IServicioAreaConocimiento $servicio;

    public function __construct() 
    {
        $this->servicio = Ensamblador::crearServicioAreaConocimiento();
    }

    public function procesarPeticion(string $metodo, ?string $idStr): void 
    {
        // Forzamos que toda respuesta de este controlador sea JSON
        header('Content-Type: application/json; charset=utf-8');

        $id = $idStr !== null ? (int)$idStr : null;
        
        // Leemos el cuerpo crudo de la petición
        $cuerpo = file_get_contents('php://input');
        $datos = json_decode($cuerpo, true) ?? [];

        try {
            switch ($metodo) {
                case 'GET':
                    if ($id !== null) {
                        $this->verUno($id);
                    } else {
                        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 1000;
                        if ($limite <= 0) {
                            $this->responder(400, ["estado" => 400, "mensaje" => "Límite inválido."]);
                            return;
                        }
                        $this->listar($limite);
                    }
                    break;
                case 'POST':
                    $this->servicio->crear($datos);
                    $this->responder(200, ["estado" => 200, "mensaje" => "Operación realizada exitosamente.", "filasAfectadas" => 1]);
                    break;
                case 'PUT':
                    if ($id === null) throw new InvalidArgumentException("ID requerido en URL.");
                    $this->servicio->reemplazar($id, $datos);
                    $this->responder(200, ["estado" => 200, "mensaje" => "Operación realizada exitosamente.", "filasAfectadas" => 1]);
                    break;
                case 'PATCH':
                    if ($id === null) throw new InvalidArgumentException("ID requerido en URL.");
                    $this->servicio->cambiarParcial($id, $datos);
                    $this->responder(200, ["estado" => 200, "mensaje" => "Operación realizada exitosamente.", "filasAfectadas" => 1]);
                    break;
                case 'DELETE':
                    if ($id === null) throw new InvalidArgumentException("ID requerido en URL.");
                    $this->servicio->retirar($id);
                    $this->responder(200, ["estado" => 200, "mensaje" => "Operación realizada exitosamente.", "filasAfectadas" => 1]);
                    break;
                default:
                    $this->responder(405, ["estado" => 405, "mensaje" => "Método no permitido."]);
                    break;
            }
        } catch (ValidacionExcepcion $e) {
            // Error 422: Datos inválidos[cite: 12]
            $this->responder(422, ["estado" => 422, "mensaje" => "Datos inválidos.", "errores" => $e->getErrores()]);
        } catch (NoEncontradoExcepcion $e) {
            // Error 404: No existe[cite: 12]
            $this->responder(404, ["estado" => 404, "mensaje" => "Registro no encontrado.", "detalle" => "No existe el registro solicitado."]);
        } catch (InvalidArgumentException $e) {
            // Error 400: Parámetro inválido o PATCH vacío[cite: 12]
            $this->responder(400, ["estado" => 400, "mensaje" => $e->getMessage()]);
        } catch (Exception $e) {
            // Error 500: Falla interna de BD o servidor[cite: 12]
            $this->responder(500, ["estado" => 500, "mensaje" => "Error interno del servidor.", "detalle" => $e->getMessage()]);
        }
    }

    private function listar(int $limite): void 
    {
        $areas = $this->servicio->listar($limite);
        
        if (count($areas) === 0) {
            http_response_code(204); // 204 Sin contenido (la lista está vacía)[cite: 12]
            return;
        }

        $datos = array_map(fn($area) => $area->toArray(), $areas);
        
        $this->responder(200, [
            "tabla" => "area_conocimiento",
            "limite" => $limite,
            "total" => count($datos),
            "datos" => $datos
        ]);
    }

    private function verUno(int $id): void 
    {
        $area = $this->servicio->obtenerPorId($id);
        $this->responder(200, $area->toArray());
    }

    private function responder(int $codigoHTTP, array $cuerpo): void 
    {
        http_response_code($codigoHTTP);
        echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
    }
}