<?php
declare(strict_types=1);
require_once __DIR__ . '/../modelos/AreaAplicacion.php';

interface IServicioAreaAplicacion {
    public function listar(int $limite): array;
    public function obtenerPorId(int $id): AreaAplicacion;
    public function crear(array $datos): void;
    public function reemplazar(int $id, array $datos): void;
    public function cambiarParcial(int $id, array $datos): void;
    public function retirar(int $id): void;
}