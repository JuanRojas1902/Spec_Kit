<?php
declare(strict_types=1);
require_once __DIR__ . '/../modelos/LineaInvestigacion.php';

interface IServicioLineaInvestigacion {
    public function listar(int $limite): array;
    public function obtenerPorId(int $id): LineaInvestigacion;
    public function crear(array $datos): void;
    public function reemplazar(int $id, array $datos): void;
    public function cambiarParcial(int $id, array $datos): void;
    public function retirar(int $id): void;
}