<?php
declare(strict_types=1);

require_once __DIR__ . '/IRepositorioUniversidad.php';
require_once __DIR__ . '/../modelos/Universidad.php';

class RepositorioUniversidadMariaDB implements IRepositorioUniversidad 
{
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function listar(int $limite): array 
    {
        // EXPLICACIÓN PARA CLASE: Siempre filtramos con "WHERE activo = 1" para ocultar 
        // los registros que tienen borrado lógico sin eliminarlos de la BD física.
        $sql = "SELECT id, nombre, tipo, ciudad FROM universidad WHERE activo = 1 LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        foreach ($filas as $fila) {
            $resultado[] = new Universidad((int)$fila['id'], $fila['nombre'], $fila['tipo'], $fila['ciudad']);
        }
        return $resultado;
    }

    public function obtenerPorId(int $id): ?Universidad 
    {
        // EXPLICACIÓN PARA CLASE: Usamos consultas preparadas (prepare) y pasamos el :id
        // como parámetro. Esto evita ataques de Inyección SQL[cite: 7].
        $sql = "SELECT id, nombre, tipo, ciudad FROM universidad WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) return null;

        return new Universidad((int)$fila['id'], $fila['nombre'], $fila['tipo'], $fila['ciudad']);
    }

    public function crear(Universidad $entidad): void 
    {
        // No enviamos 'activo' porque la base de datos le pone 1 automáticamente por defecto[cite: 9].
        $sql = "INSERT INTO universidad (id, nombre, tipo, ciudad) VALUES (:id, :nombre, :tipo, :ciudad)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $entidad->getId(),
            ':nombre' => $entidad->getNombre(),
            ':tipo' => $entidad->getTipo(),
            ':ciudad' => $entidad->getCiudad()
        ]);
    }

    public function actualizar(Universidad $entidad): void 
    {
        $sql = "UPDATE universidad SET nombre = :nombre, tipo = :tipo, ciudad = :ciudad WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $entidad->getId(),
            ':nombre' => $entidad->getNombre(),
            ':tipo' => $entidad->getTipo(),
            ':ciudad' => $entidad->getCiudad()
        ]);
    }

    public function retirar(int $id): bool 
    {
        // EXPLICACIÓN PARA CLASE: Aquí aplicamos el "Borrado Lógico". 
        // Cambiamos activo a 0 con UPDATE en lugar de usar DELETE FROM[cite: 9].
        $sql = "UPDATE universidad SET activo = 0 WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}