<?php
declare(strict_types=1);

class GrupoControlador {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // GET /api/grupo
    public function listar(): void {
        $stmt = $this->db->query("SELECT codigo, nombre, sigla, universidad_id FROM grupo_investigacion");
        $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode(['data' => $grupos]);
    }

    // GET /api/grupo/{codigo}
    public function obtenerPorCodigo(int $codigo): void {
        $stmt = $this->db->prepare("SELECT codigo, nombre, sigla, universidad_id FROM grupo_investigacion WHERE codigo = ?");
        $stmt->execute([$codigo]);
        $grupo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$grupo) {
            http_response_code(404);
            echo json_encode(['error' => 'El grupo de investigación no existe (HTTP 404).']);
            return;
        }

        http_response_code(200);
        echo json_encode(['data' => $grupo]);
    }

    // POST /api/grupo
    public function crear(array $datos): void {
        if (empty($datos['codigo']) || empty($datos['nombre'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Los campos codigo y nombre son obligatorios.']);
            return;
        }

        // Verificar código duplicado (HTTP 409)
        $stmtCheck = $this->db->prepare("SELECT codigo FROM grupo_investigacion WHERE codigo = ?");
        $stmtCheck->execute([$datos['codigo']]);
        if ($stmtCheck->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'El código de grupo ya se encuentra registrado.']);
            return;
        }

        $stmt = $this->db->prepare("INSERT INTO grupo_investigacion (codigo, nombre, sigla, universidad_id) VALUES (?, ?, ?, ?)");
        $universidadId = !empty($datos['universidad_id']) ? (int)$datos['universidad_id'] : 1;
        $sigla = $datos['sigla'] ?? '';

        $stmt->execute([$datos['codigo'], $datos['nombre'], $sigla, $universidadId]);

        http_response_code(201);
        echo json_encode(['mensaje' => 'Grupo creado exitosamente', 'codigo' => $datos['codigo']]);
    }

    // PUT /api/grupo/{codigo}
    public function actualizar(int $codigo, array $datos): void {
        $stmtCheck = $this->db->prepare("SELECT codigo FROM grupo_investigacion WHERE codigo = ?");
        $stmtCheck->execute([$codigo]);
        if (!$stmtCheck->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Grupo no encontrado.']);
            return;
        }

        $stmt = $this->db->prepare("UPDATE grupo_investigacion SET nombre = ?, sigla = ? WHERE codigo = ?");
        $stmt->execute([$datos['nombre'], $datos['sigla'] ?? '', $codigo]);

        http_response_code(200);
        echo json_encode(['mensaje' => 'Grupo actualizado correctamente.']);
    }

    // DELETE /api/grupo/{codigo}
    public function eliminar(int $codigo): void {
        $stmt = $this->db->prepare("DELETE FROM grupo_investigacion WHERE codigo = ?");
        $stmt->execute([$codigo]);

        http_response_code(200);
        echo json_encode(['mensaje' => 'Grupo eliminado correctamente.']);
    }
}