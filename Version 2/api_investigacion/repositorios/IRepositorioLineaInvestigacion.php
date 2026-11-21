<?php
declare(strict_types=1);
require_once __DIR__ . '/../modelos/LineaInvestigacion.php';

interface IRepositorioLineaInvestigacion {
    public function listar(int $limite): array;
    public function obtenerPorId(int $id): ?LineaInvestigacion;
    public function crear(LineaInvestigacion $entidad): void;
    public function actualizar(LineaInvestigacion $entidad): void;
    public function retirar(int $id): bool;
}