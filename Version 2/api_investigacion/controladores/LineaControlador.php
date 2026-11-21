<?php
declare(strict_types=1);

/**
 * Controlador del módulo Línea de Investigación.
 * Gestiona el ciclo de vida CRUD y captura excepciones PDO.
 */
class LineaControlador {
    private PDO $db;

    // Constructor con inyección de la instancia PDO
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // GET /api/linea - Obtener listado
    public function listar(): void {
        try {
            $stmt = $this->db->query("SELECT codigo, nombre FROM linea_investigacion");
            $lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(['data' => $lineas]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Fallo SQL al consultar líneas: ' . $e->getMessage()]);
        }
    }

    // POST /api/linea - Registrar nueva línea
    public function crear(array $datos): void {
        if (empty($datos['codigo']) || empty($datos['nombre'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Los campos código y nombre son obligatorios.']);
            return;
        }

        try {
            // Verificar existencia previa del código
            $stmtCheck = $this->db->prepare("SELECT codigo FROM linea_investigacion WHERE codigo = ?");
            $stmtCheck->execute([$datos['codigo']]);
            if ($stmtCheck->fetch()) {
                http_response_code(409);
                echo json_encode(['error' => 'El código de la línea de investigación ya existe.']);
                return;
            }

            // Inserción parametrizada segura
            $stmt = $this->db->prepare("INSERT INTO linea_investigacion (codigo, nombre) VALUES (?, ?)");
            $stmt->execute([$datos['codigo'], trim($datos['nombre'])]);

            http_response_code(201);
            echo json_encode(['mensaje' => 'Línea de investigación creada con éxito', 'codigo' => $datos['codigo']]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Fallo SQL al insertar línea: ' . $e->getMessage()]);
        }
    }

    // GET /api/linea/{codigo} - Obtener por ID
    public function obtenerPorCodigo(int $codigo): void {
        try {
            $stmt = $this->db->prepare("SELECT codigo, nombre FROM linea_investigacion WHERE codigo = ?");
            $stmt->execute([$codigo]);
            $linea = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$linea) {
                http_response_code(404);
                echo json_encode(['error' => 'La línea de investigación solicitada no existe.']);
                return;
            }

            http_response_code(200);
            echo json_encode(['data' => $linea]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Fallo SQL al buscar línea: ' . $e->getMessage()]);
        }
    }

    // PUT /api/linea/{codigo} - Actualizar registro
    public function actualizar(int $codigo, array $datos): void {
        try {
            $stmtCheck = $this->db->prepare("SELECT codigo FROM linea_investigacion WHERE codigo = ?");
            $stmtCheck->execute([$codigo]);
            if (!$stmtCheck->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'La línea de investigación no existe.']);
                return;
            }

            $stmt = $this->db->prepare("UPDATE linea_investigacion SET nombre = ? WHERE codigo = ?");
            $stmt->execute([trim($datos['nombre']), $codigo]);

            http_response_code(200);
            echo json_encode(['mensaje' => 'Línea de investigación actualizada correctamente.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Fallo SQL al actualizar línea: ' . $e->getMessage()]);
        }
    }

    // DELETE /api/linea/{codigo} - Eliminar registro
    public function eliminar(int $codigo): void {
        try {
            $stmt = $this->db->prepare("DELETE FROM linea_investigacion WHERE codigo = ?");
            $stmt->execute([$codigo]);

            http_response_code(200);
            echo json_encode(['mensaje' => 'Línea de investigación eliminada correctamente.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Fallo SQL al eliminar línea: ' . $e->getMessage()]);
        }
    }
}