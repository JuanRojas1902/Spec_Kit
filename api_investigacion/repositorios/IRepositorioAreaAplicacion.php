<?php
declare(strict_types=1);
require_once __DIR__ . '/../modelos/AreaAplicacion.php';

interface IRepositorioAreaAplicacion {
    public function listar(int $limite): array;
    public function obtenerPorId(int $id): ?AreaAplicacion;
    public function crear(AreaAplicacion $entidad): void;
    public function actualizar(AreaAplicacion $entidad): void;
    public function retirar(int $id): bool;
}