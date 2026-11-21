<?php
declare(strict_types=1);

require_once __DIR__ . '/IRepositorioObjetivoDesarrolloSostenible.php';
require_once __DIR__ . '/../modelos/ObjetivoDesarrolloSostenible.php';

class RepositorioObjetivoDesarrolloSostenibleMariaDB implements IRepositorioObjetivoDesarrolloSostenible 
{
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function listar(int $limite): array 
    {
        $sql = "SELECT id, nombre, categoria FROM objetivo_desarrollo_sostenible WHERE activo = 1 LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        foreach ($filas as $fila) {
            $resultado[] = new ObjetivoDesarrolloSostenible((int)$fila['id'], $fila['nombre'], $fila['categoria']);
        }
        return $resultado;
    }

    public function obtenerPorId(int $id): ?ObjetivoDesarrolloSostenible 
    {
        $sql = "SELECT id, nombre, categoria FROM objetivo_desarrollo_sostenible WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) return null;

        return new ObjetivoDesarrolloSostenible((int)$fila['id'], $fila['nombre'], $fila['categoria']);
    }

    public function crear(ObjetivoDesarrolloSostenible $entidad): void 
    {
        $sql = "INSERT INTO objetivo_desarrollo_sostenible (id, nombre, categoria) VALUES (:id, :nombre, :categoria)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $entidad->getId(),
            ':nombre' => $entidad->getNombre(),
            ':categoria' => $entidad->getCategoria()
        ]);
    }

    public function actualizar(ObjetivoDesarrolloSostenible $entidad): void 
    {
        $sql = "UPDATE objetivo_desarrollo_sostenible SET nombre = :nombre, categoria = :categoria WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $entidad->getId(),
            ':nombre' => $entidad->getNombre(),
            ':categoria' => $entidad->getCategoria()
        ]);
    }

    public function retirar(int $id): bool 
    {
        $sql = "UPDATE objetivo_desarrollo_sostenible SET activo = 0 WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}