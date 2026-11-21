<?php
declare(strict_types=1);

require_once __DIR__ . '/../modelos/AreaConocimiento.php';

/**
 * Fase 2: Interfaz del Repositorio
 * Qué hace: Define los métodos de acceso a datos, pero sin obligar a usar SQL.
 * Por qué está aquí: Permite inyectar un repositorio falso en la prueba de capas sin encender MariaDB.
 */
interface IRepositorioAreaConocimiento 
{
    /**
     * @return AreaConocimiento[]
     */
    public function listar(int $limite): array;
    
    public function obtenerPorId(int $id): ?AreaConocimiento;
    
    // C3: El borrado es lógico, por eso retirar devuelve bool (si afectó filas o no) y no borra el objeto.
    public function crear(AreaConocimiento $area): void;
    
    public function actualizar(AreaConocimiento $area): void;
    
    public function retirar(int $id): bool;
}