<?php
declare(strict_types=1);
require_once __DIR__ . '/../modelos/Universidad.php';

interface IRepositorioUniversidad {
    public function listar(int $limite): array;
    public function obtenerPorId(int $id): ?Universidad;
    public function crear(Universidad $entidad): void;
    public function actualizar(Universidad $entidad): void;
    public function retirar(int $id): bool;
}