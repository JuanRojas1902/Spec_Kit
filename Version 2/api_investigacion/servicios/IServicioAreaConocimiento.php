<?php
declare(strict_types=1);

require_once __DIR__ . '/../modelos/AreaConocimiento.php';

/**
 * Fase 2: Interfaz del Servicio
 * Qué hace: Define el contrato de las reglas de negocio (validar y procesar).
 * Por qué está aquí: El controlador llamará a estos métodos sin saber si por debajo hay MariaDB o memoria RAM.
 */
interface IServicioAreaConocimiento 
{
    public function listar(int $limite): array;
    
    public function obtenerPorId(int $id): AreaConocimiento; // Lanza excepción si no existe
    
    // Recibe el arreglo asociativo crudo del controlador (json decodificado)
    public function crear(array $datos): void;
    
    public function reemplazar(int $id, array $datos): void; // PUT
    
    public function cambiarParcial(int $id, array $datos): void; // PATCH
    
    public function retirar(int $id): void;
}