<?php
declare(strict_types=1);

require_once __DIR__ . '/../excepciones/ConflictoExcepcion.php';

class RepositorioGrupoInvestigacionMariaDB
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function listar(): array
    {
        $stmt = $this->conexion->prepare('CALL sp_listar_grupo_investigacion()');
        $stmt->execute();
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Obligatorio en la v2
        return $filas;
    }

    public function consultar(int $id): ?array
    {
        $stmt = $this->conexion->prepare('CALL sp_consultar_grupo_investigacion(?)');
        $stmt->execute([$id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $fila ?: null;
    }

    public function crear(array $datos): array
    {
        try {
            $stmt = $this->conexion->prepare('CALL sp_crear_grupo_investigacion(?, ?, ?, ?, ?)');
            $stmt->execute([
                $datos['gruplac'],
                $datos['categoria'],
                $datos['convocatoria'],
                $datos['ambito'],
                $datos['universidad_id']
            ]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $fila ?: [];
        } catch (PDOException $e) {
            $this->manejarErrorIntegridad($e);
        }
    }

    public function actualizar(int $id, array $datos): void
    {
        try {
            $stmt = $this->conexion->prepare('CALL sp_actualizar_grupo_investigacion(?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $id,
                $datos['gruplac'],
                $datos['categoria'],
                $datos['convocatoria'],
                $datos['ambito'],
                $datos['universidad_id']
            ]);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $this->manejarErrorIntegridad($e);
        }
    }

    public function eliminar(int $id): void
    {
        try {
            $stmt = $this->conexion->prepare('CALL sp_eliminar_grupo_investigacion(?)');
            $stmt->execute([$id]);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $this->manejarErrorIntegridad($e);
        }
    }

    private function manejarErrorIntegridad(PDOException $e): void
    {
        $sqlstate = $e->errorInfo[0] ?? '';
        $codigo   = $e->errorInfo[1] ?? 0;

        // Regla propia del procedimiento (SIGNAL 45000)
        if ($sqlstate === '45000') {
            throw new ConflictoExcepcion($e->errorInfo[2] ?? 'Conflicto de regla de negocio.');
        }
        // Padre inexistente (ej. universidad_id no existe)
        if ($codigo === 1452) {
            throw new ConflictoExcepcion('La universidad vinculada no existe en la base de datos.');
        }
        // Registro duplicado
        if ($codigo === 1062) {
            throw new ConflictoExcepcion('Este registro ya existe en el sistema.');
        }
        // Borrado físico bloqueado (solo por seguridad, aunque usemos lógico)
        if ($codigo === 1451) {
            throw new ConflictoExcepcion('No se puede retirar el registro porque otros dependen de él.');
        }
        
        throw $e; // Si es otro error (como caída de BD), dejamos que estalle como 500
    }
}