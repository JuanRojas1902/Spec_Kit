<?php
declare(strict_types=1);

require_once __DIR__ . '/IRepositorioTerminoClave.php';
require_once __DIR__ . '/../modelos/TerminoClave.php';

class RepositorioTerminoClaveMariaDB implements IRepositorioTerminoClave 
{
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function listar(int $limite): array 
    {
        $sql = "SELECT termino, termino_ingles FROM termino_clave WHERE activo = 1 LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        foreach ($filas as $fila) {
            $resultado[] = new TerminoClave($fila['termino'], $fila['termino_ingles']);
        }
        return $resultado;
    }

    public function obtenerPorId(string $termino): ?TerminoClave 
    {
        // EXPLICACIÓN PARA CLASE: Esta tabla no tiene columna 'id'. 
        // Su llave primaria es el propio texto ('termino')[cite: 9].
        $sql = "SELECT termino, termino_ingles FROM termino_clave WHERE termino = :termino AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':termino' => $termino]);
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) return null;

        return new TerminoClave($fila['termino'], $fila['termino_ingles']);
    }

    public function crear(TerminoClave $entidad): void 
    {
        $sql = "INSERT INTO termino_clave (termino, termino_ingles) VALUES (:termino, :termino_ingles)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':termino' => $entidad->getTermino(),
            ':termino_ingles' => $entidad->getTerminoIngles()
        ]);
    }

    public function actualizar(TerminoClave $entidad): void 
    {
        $sql = "UPDATE termino_clave SET termino_ingles = :termino_ingles WHERE termino = :termino AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':termino' => $entidad->getTermino(),
            ':termino_ingles' => $entidad->getTerminoIngles()
        ]);
    }

    public function retirar(string $termino): bool 
    {
        $sql = "UPDATE termino_clave SET activo = 0 WHERE termino = :termino AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':termino' => $termino]);
        return $stmt->rowCount() > 0;
    }
}