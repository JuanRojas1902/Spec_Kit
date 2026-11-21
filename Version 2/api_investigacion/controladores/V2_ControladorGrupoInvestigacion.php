<?php
declare(strict_types=1);

require_once __DIR__ . '/../servicios/ServicioGrupoInvestigacion.php';
require_once __DIR__ . '/../excepciones/ConflictoExcepcion.php';

class ControladorGrupoInvestigacion
{
    private ServicioGrupoInvestigacion $servicio;

    public function __construct(ServicioGrupoInvestigacion $servicio)
    {
        $this->servicio = $servicio;
    }

    public function manejarPeticion(string $metodo, ?string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            switch ($metodo) {
                case 'GET':
                    if ($id) {
                        $grupo = $this->servicio->consultar((int) $id);
                        if ($grupo) {
                            echo json_encode($grupo);
                        } else {
                            http_response_code(404);
                            echo json_encode(['estado' => 404, 'mensaje' => 'Grupo no encontrado.']);
                        }
                    } else {
                        $grupos = $this->servicio->listar();
                        if (empty($grupos)) {
                            // Criterio de la V2: 204 No Content para tabla vacía
                            http_response_code(204); 
                        } else {
                            // Envolvemos la respuesta en el "sobre" que pide la metodología
                            echo json_encode([
                                'tabla'  => 'grupo_investigacion',
                                'total'  => count($grupos),
                                'datos'  => $grupos
                            ]);
                        }
                    }
                    break;

                case 'POST':
                    $datos = json_decode(file_get_contents('php://input'), true);
                    $nuevoGrupo = $this->servicio->crear($datos);
                    http_response_code(201);
                    echo json_encode($nuevoGrupo);
                    break;

                case 'PUT':
                case 'PATCH':
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['estado' => 400, 'mensaje' => 'Falta el ID del grupo.']);
                        break;
                    }
                    $datos = json_decode(file_get_contents('php://input'), true);
                    $this->servicio->actualizar((int) $id, $datos);
                    echo json_encode(['estado' => 200, 'mensaje' => 'Grupo actualizado correctamente.']);
                    break;

                case 'DELETE':
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['estado' => 400, 'mensaje' => 'Falta el ID del grupo.']);
                        break;
                    }
                    $this->servicio->eliminar((int) $id);
                    echo json_encode(['estado' => 200, 'mensaje' => 'Grupo eliminado (borrado lógico).']);
                    break;

                default:
                    http_response_code(405);
                    echo json_encode(['estado' => 405, 'mensaje' => 'Método no permitido.']);
                    break;
            }

        } catch (ConflictoExcepcion $e) {
            // ¡ESTA ES LA MAGIA DEL 409!
            http_response_code(409);
            echo json_encode([
                'estado' => 409,
                'mensaje' => $e->getMessage()
            ]);

        } catch (Exception $e) {
            $codigo = $e->getCode() !== 0 ? $e->getCode() : 500;
            http_response_code($codigo);
            echo json_encode([
                'estado' => $codigo,
                'mensaje' => $codigo === 500 ? 'Error interno del servidor.' : $e->getMessage()
            ]);
        }
    }
}