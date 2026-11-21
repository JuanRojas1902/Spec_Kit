<?php

declare(strict_types=1);

/**
 * ARCHIVO: ConflictoDeIntegridadExcepcion.php
 *
 * QUE ES:
 * Una excepcion personalizada que representa un conflicto
 * de integridad en los datos del sistema.
 *
 * POR QUE ESTA AQUI:
 * Permite identificar los rechazos de la base de datos
 * y diferenciarlos de otros errores del sistema.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con la base de datos.
 * No genera respuestas HTTP.
 * No contiene reglas de negocio.
 */

class ConflictoDeIntegridadExcepcion extends RuntimeException
{
    // Hereda el constructor y los metodos de RuntimeException.
    // No necesita propiedades ni metodos adicionales.
}