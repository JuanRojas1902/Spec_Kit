<?php
declare(strict_types=1);

/**
 * Fase 1: Modelo AreaConocimiento
 * Qué hace: Representa en memoria una fila de la tabla `area_conocimiento`.
 * Por qué está aquí: Separa los datos puros de la lógica de bases de datos o HTTP.
 */
class AreaConocimiento 
{
    // C1: id ahora es INT estrictamente, como dicta el nuevo spec.
    private int $id;
    private string $granArea;
    private string $area;
    private string $disciplina;

    // Nota: La columna `activo` no se mapea en esta clase. 
    // El contrato exige que nunca aparezca en los JSON que entran o salen de la API.

    public function __construct(int $id, string$granArea, string $area, string$disciplina) 
    {
        $this->id =$id;
        $this->granArea =$granArea;
        $this->area =$area;
        $this->disciplina =$disciplina;
    }

    // Getters para que el repositorio pueda leer los datos al guardar
    public function getId(): int { return $this->id; }
    public function getGranArea(): string { return $this->granArea; }
    public function getArea(): string { return $this->area; }
    public function getDisciplina(): string { return $this->disciplina; }

    /**
     * Convierte el objeto a un arreglo asociativo para que json_encode lo entienda.
     * C2: Los nombres de las llaves coinciden exactamente con las columnas de la BD.
     */
    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'gran_area' => $this->granArea,
            'area' => $this->area,
            'disciplina' => $this->disciplina
        ];
    }
}