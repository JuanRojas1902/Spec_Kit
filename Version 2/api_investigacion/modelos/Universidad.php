<?php
declare(strict_types=1);

class Universidad 
{
    private int $id;
    private string $nombre;
    private string $tipo;
    private string $ciudad;

    public function __construct(int $id, string $nombre, string $tipo, string $ciudad) 
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->ciudad = $ciudad;
    }

    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getTipo(): string { return $this->tipo; }
    public function getCiudad(): string { return $this->ciudad; }

    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'ciudad' => $this->ciudad
        ];
    }
}