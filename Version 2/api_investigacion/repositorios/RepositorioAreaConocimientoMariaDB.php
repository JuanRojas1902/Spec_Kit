<?php
declare(strict_types=1);

require_once __DIR__ . '/IRepositorioAreaConocimiento.php';
require_once __DIR__ . '/../modelos/AreaConocimiento.php';

/**
 * Fase 3: Repositorio Concreto (MariaDB)
 * Qué hace: Ejecuta las sentencias SQL usando PDO.
 * Por qué está aquí: Es la única capa de toda la aplicación que sabe que usamos MariaDB. 
 * Si mañana cambiamos a PostgreSQL, solo se cambia este archivo.
 */
class RepositorioAreaConocimientoMariaDB implements IRepositorioAreaConocimiento 
{
    private PDO $pdo;

    public function __construct(PDO $pdo) 
    {
        $this->pdo = $pdo;
    }

    public function listar(int $limite): array 
    {
        // REGLA FASE 3: Filtrar siempre por activo = 1[cite: 17].
        $sql = "SELECT id, gran_area, area, disciplina FROM area_conocimiento WHERE activo = 1 LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        // Usamos bindValue explícito con PARAM_INT para evitar errores de sintaxis en el LIMIT de MariaDB
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        
        // Convertimos los arreglos crudos de la base de datos en objetos puros de nuestro Modelo
        foreach ($filas as $fila) {
            $resultado[] = new AreaConocimiento(
                (int)$fila['id'],
                $fila['gran_area'],
                $fila['area'],
                $fila['disciplina']
            );
        }
        return $resultado;
    }

    public function obtenerPorId(int $id): ?AreaConocimiento 
    {
        // REGLA FASE 3: Consultar garantizando que no esté retirado (activo = 1)[cite: 17].
        $sql = "SELECT id, gran_area, area, disciplina FROM area_conocimiento WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            return null;
        }

        return new AreaConocimiento(
            (int)$fila['id'],
            $fila['gran_area'],
            $fila['area'],
            $fila['disciplina']
        );
    }

    public function crear(AreaConocimiento $area): void 
    {
        // Nota: No enviamos 'activo' porque la BD le pone 1 por defecto[cite: 14].
        $sql = "INSERT INTO area_conocimiento (id, gran_area, area, disciplina) VALUES (:id, :gran_area, :area, :disciplina)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $area->getId(),
            ':gran_area' => $area->getGranArea(),
            ':area' => $area->getArea(),
            ':disciplina' => $area->getDisciplina()
        ]);
    }

    public function actualizar(AreaConocimiento $area): void 
    {
        $sql = "UPDATE area_conocimiento SET gran_area = :gran_area, area = :area, disciplina = :disciplina WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $area->getId(),
            ':gran_area' => $area->getGranArea(),
            ':area' => $area->getArea(),
            ':disciplina' => $area->getDisciplina()
        ]);
    }

    public function retirar(int $id): bool 
    {
        // REGLA FASE 3: Borrado Lógico con UPDATE, NUNCA DELETE FROM[cite: 17].
        $sql = "UPDATE area_conocimiento SET activo = 0 WHERE id = :id AND activo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        // rowCount() nos dirá si la fila existía y se modificó, o si ya estaba en 0 (o no existía)
        return $stmt->rowCount() > 0;
    }
}