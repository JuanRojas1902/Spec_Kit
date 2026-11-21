<?php
declare(strict_types=1);

class LineaInvestigacion 
{
    private ?int $id; // Es nulo cuando se crea por primera vez (POST) porque MariaDB lo genera[cite: 10]
    private string $nombre;
    private string $descripcion;

    public function __construct(?int $id, string $nombre, string $descripcion) 
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }

    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): string { return $this->descripcion; }

    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion
        ];
    }
}