<?php
declare(strict_types=1);
require_once __DIR__ . '/../servicios/ensamblador.php';
require_once __DIR__ . '/../excepciones/ValidacionExcepcion.php';
require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

class ControladorObjetivoDesarrolloSostenible 
{
    private IServicioObjetivoDesarrolloSostenible $servicio;
    public function __construct() { $this->servicio = Ensamblador::crearServicioObjetivoDesarrolloSostenible(); }

    public function procesarPeticion(string $metodo, ?string $idStr): void 
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = $idStr !== null ? (int)$idStr : null;
        $datos = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            switch ($metodo) {
                case 'GET':
                    if ($id !== null) {
                        $this->responder(200, $this->servicio->obtenerPorId($id)->toArray());
                    } else {
                        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 1000;
                        if ($limite <= 0) { $this->responder(400, ["estado" => 400, "mensaje" => "Límite inválido."]); return; }
                        $resultados = $this->servicio->listar($limite);
                        if (count($resultados) === 0) { http_response_code(204); return; }
                        $this->responder(200, ["tabla" => "objetivo_desarrollo_sostenible", "total" => count($resultados), "datos" => array_map(fn($r) => $r->toArray(), $resultados)]);
                    }
                    break;
                case 'POST':
                    $this->servicio->crear($datos); $this->responder(200, ["estado" => 200, "mensaje" => "Exito"]); break;
                case 'PUT':
                    if ($id === null) throw new InvalidArgumentException("ID requerido en URL.");
                    $this->servicio->reemplazar($id, $datos); $this->responder(200, ["estado" => 200, "mensaje" => "Exito"]); break;
                case 'PATCH':
                    if ($id === null) throw new InvalidArgumentException("ID requerido en URL.");
                    $this->servicio->cambiarParcial($id, $datos); $this->responder(200, ["estado" => 200, "mensaje" => "Exito"]); break;
                case 'DELETE':
                    if ($id === null) throw new InvalidArgumentException("ID requerido en URL.");
                    $this->servicio->retirar($id); $this->responder(200, ["estado" => 200, "mensaje" => "Exito"]); break;
                default: $this->responder(405, ["estado" => 405, "mensaje" => "Método no permitido."]); break;
            }
        } catch (ValidacionExcepcion $e) { $this->responder(422, ["estado" => 422, "mensaje" => "Datos inválidos.", "errores" => $e->getErrores()]);
        } catch (NoEncontradoExcepcion $e) { $this->responder(404, ["estado" => 404, "mensaje" => "No encontrado."]);
        } catch (InvalidArgumentException $e) { $this->responder(400, ["estado" => 400, "mensaje" => $e->getMessage()]);
        } catch (Exception $e) { $this->responder(500, ["estado" => 500, "mensaje" => "Error interno.", "detalle" => $e->getMessage()]); }
    }
    private function responder(int $codigoHTTP, array $cuerpo): void { http_response_code($codigoHTTP); echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE); }
}