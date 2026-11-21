<?php

declare(strict_types=1);

/**
 * ARCHIVO: Docente.php
 *
 * QUE ES:
 * Modelo que representa a un docente investigador.
 *
 * POR QUE ESTA AQUI:
 * Permite representar los datos de un docente mediante
 * un objeto de PHP y aplicar el encapsulamiento.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No procesa peticiones HTTP.
 * No genera respuestas ni pantallas.
 */

class Docente
{
    // =====================================================
    // 1. ATRIBUTOS
    // Representan los datos de un docente.
    // Son privados para proteger el acceso a los datos.
    // =====================================================

    private int $cedula;

    private string $nombres;

    private string $apellidos;


    // =====================================================
    // 2. CONSTRUCTOR
    // Inicializa los atributos al crear un nuevo objeto.
    // =====================================================

    public function __construct(
        int $cedula,
        string $nombres,
        string $apellidos
    ) {
        $this->cedula = $cedula;
        $this->nombres = $nombres;
        $this->apellidos = $apellidos;
    }


    // =====================================================
    // 3. GETTERS
    // Permiten consultar los atributos privados.
    // =====================================================

    public function getCedula(): int
    {
        return $this->cedula;
    }


    public function getNombres(): string
    {
        return $this->nombres;
    }


    public function getApellidos(): string
    {
        return $this->apellidos;
    }


    // =====================================================
    // 4. SETTERS
    // Permiten modificar los atributos autorizados.
    //
    // La cedula no tiene setter porque es la llave
    // primaria y no debe modificarse.
    // =====================================================

    public function setNombres(string $nombres): void
    {
        $this->nombres = $nombres;
    }


    public function setApellidos(string $apellidos): void
    {
        $this->apellidos = $apellidos;
    }


    // =====================================================
    // 5. CONVERSION A ARREGLO
    // Convierte el objeto en un arreglo asociativo.
    // Facilita su posterior conversion a JSON.
    // =====================================================

    public function toArray(): array
    {
        return [
            'cedula' => $this->cedula,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos
        ];
    }
}