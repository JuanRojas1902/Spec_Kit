<?php
declare(strict_types=1);
require_once __DIR__ . '/../modelos/ObjetivoDesarrolloSostenible.php';

interface IRepositorioObjetivoDesarrolloSostenible {
    public function listar(int $limite): array;
    public function obtenerPorId(int $id): ?ObjetivoDesarrolloSostenible;
    public function crear(ObjetivoDesarrolloSostenible $entidad): void;
    public function actualizar(ObjetivoDesarrolloSostenible $entidad): void;
    public function retirar(int $id): bool;
}