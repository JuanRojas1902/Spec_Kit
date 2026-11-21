<?php
declare(strict_types=1);

/**
 * Qué hace: Transporta los errores de validación desde el Servicio hasta el Controlador.
 * Por qué está aquí: Permite lanzar múltiples errores a la vez (ej. "Falta el id", "Falta el área").
 */
class ValidacionExcepcion extends Exception 
{
    private array $errores;

    public function __construct(array $errores) 
    {
        parent::__construct("Datos inválidos.");
        $this->errores = $errores;
    }

    public function getErrores(): array 
    {
        return $this->errores;
    }
}