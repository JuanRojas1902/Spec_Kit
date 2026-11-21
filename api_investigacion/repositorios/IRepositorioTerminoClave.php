<?php
declare(strict_types=1);
require_once __DIR__ . '/../modelos/TerminoClave.php';

interface IRepositorioTerminoClave {
    public function listar(int $limite): array;
    public function obtenerPorId(string $termino): ?TerminoClave;
    public function crear(TerminoClave $entidad): void;
    public function actualizar(TerminoClave $entidad): void;
    public function retirar(string $termino): bool;
}