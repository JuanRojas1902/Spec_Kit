<?php
declare(strict_types=1);
require_once __DIR__ . '/../modelos/TerminoClave.php';

interface IServicioTerminoClave {
    public function listar(int $limite): array;
    public function obtenerPorId(string $termino): TerminoClave;
    public function crear(array $datos): void;
    public function reemplazar(string $termino, array $datos): void;
    public function cambiarParcial(string $termino, array $datos): void;
    public function retirar(string $termino): void;
}