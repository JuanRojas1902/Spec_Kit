<?php
declare(strict_types=1);

require_once __DIR__ . '/IRepositorioAreaAplicacion.php';
require_once __DIR__ . '/../modelos/AreaAplicacion.php';

class RepositorioAreaAplicacionMariaDB implements IRepositorioAreaAplicacion 
{
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function listar(int $limite): array 
    {
        $sql = "SELECT id, nombre FROM area_aplicacion WHERE activo = 1 LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        foreach ($filas as $fila) {
            $resultado[] = new AreaAplicacion((int)$fila['id'], $fila['nombre']);
        }
        return $resultado;
    }

    public function obtenerPorId(int $id): ?AreaAplicacion 
    {
        $sql = "SELECT id, nombre FROM area_aplicacion WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) return null;

        return new AreaAplicacion((int)$fila['id'], $fila['nombre']);
    }

    public function crear(AreaAplicacion $entidad): void 
    {
        $sql = "INSERT INTO area_aplicacion (id, nombre) VALUES (:id, :nombre)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $entidad->getId(),
            ':nombre' => $entidad->getNombre()
        ]);
    }

    public function actualizar(AreaAplicacion $entidad): void 
    {
        $sql = "UPDATE area_aplicacion SET nombre = :nombre WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $entidad->getId(),
            ':nombre' => $entidad->getNombre()
        ]);
    }

    public function retirar(int $id): bool 
    {
        $sql = "UPDATE area_aplicacion SET activo = 0 WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}