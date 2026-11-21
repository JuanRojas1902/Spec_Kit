<?php
declare(strict_types=1);

class TerminoClave 
{
    private string $termino;
    private ?string $terminoIngles; // Puede ser nulo según el contrato[cite: 9]

    public function __construct(string $termino, ?string $terminoIngles = null) 
    {
        $this->termino = $termino;
        $this->terminoIngles = $terminoIngles;
    }

    public function getTermino(): string { return $this->termino; }
    public function getTerminoIngles(): ?string { return $this->terminoIngles; }

    public function toArray(): array 
    {
        return [
            'termino' => $this->termino,
            'termino_ingles' => $this->terminoIngles
        ];
    }
}