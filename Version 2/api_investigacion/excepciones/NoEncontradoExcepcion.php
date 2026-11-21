<?php
declare(strict_types=1);

/**
 * Qué hace: Se lanza cuando un registro no existe o ya fue retirado.
 * Por qué está aquí: Separa la lógica de negocio del código HTTP 404.
 */
class NoEncontradoExcepcion extends Exception 
{
    public function __construct(string $mensaje = "Registro no encontrado.") 
    {
        parent::__construct($mensaje);
    }
}