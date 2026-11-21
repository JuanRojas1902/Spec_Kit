<?php
declare(strict_types=1);

require_once __DIR__ . '/IRepositorioLineaInvestigacion.php';
require_once __DIR__ . '/../modelos/LineaInvestigacion.php';

class RepositorioLineaInvestigacionMariaDB implements IRepositorioLineaInvestigacion 
{
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function listar(int $limite): array 
    {
        $sql = "SELECT id, nombre, descripcion FROM linea_investigacion WHERE activo = 1 LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        foreach ($filas as $fila) {
            $resultado[] = new LineaInvestigacion((int)$fila['id'], $fila['nombre'], $fila['descripcion']);
        }
        return $resultado;
    }

    public function obtenerPorId(int $id): ?LineaInvestigacion 
    {
        $sql = "SELECT id, nombre, descripcion FROM linea_investigacion WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) return null;

        return new LineaInvestigacion((int)$fila['id'], $fila['nombre'], $fila['descripcion']);
    }

    public function crear(LineaInvestigacion $entidad): void 
    {
        // EXPLICACIÓN PARA CLASE: A diferencia de las otras tablas, aquí NO insertamos el 'id'.
        // La base de datos está configurada como AUTO_INCREMENT y ella misma asigna el número[cite: 9, 10].
        $sql = "INSERT INTO linea_investigacion (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $entidad->getNombre(),
            ':descripcion' => $entidad->getDescripcion()
        ]);
    }

    public function actualizar(LineaInvestigacion $entidad): void 
    {
        $sql = "UPDATE linea_investigacion SET nombre = :nombre, descripcion = :descripcion WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $entidad->getId(),
            ':nombre' => $entidad->getNombre(),
            ':descripcion' => $entidad->getDescripcion()
        ]);
    }

    public function retirar(int $id): bool 
    {
        $sql = "UPDATE linea_investigacion SET activo = 0 WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}