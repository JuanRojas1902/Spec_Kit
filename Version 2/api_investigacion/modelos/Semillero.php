<?php

declare(strict_types=1);

/**
 * ARCHIVO: Semillero.php
 *
 * QUE ES:
 * Modelo que representa un semillero de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite representar los datos de un semillero
 * mediante un objeto de PHP, aplicando los principios
 * de la programacion orientada a objetos.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No crea, modifica ni retira semilleros.
 * No procesa peticiones HTTP.
 * No genera respuestas ni pantallas.
 */

class Semillero
{
    // =====================================================
    // 1. ATRIBUTOS
    //
    // Representan los datos de un semillero.
    // Son privados para aplicar el encapsulamiento.
    // =====================================================

    private int $id;

    private string $nombre;

    private int $grupoId;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Inicializa los atributos del objeto a partir
    // de los datos recuperados por el repositorio.
    // =====================================================

    public function __construct(
        int $id,
        string $nombre,
        int $grupoId
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->grupoId = $grupoId;
    }


    // =====================================================
    // 3. GETTERS
    //
    // Permiten consultar los atributos privados.
    //
    // No utilizamos setters porque este recurso
    // solamente permite operaciones de lectura.
    // =====================================================

    public function getId(): int
    {
        return $this->id;
    }


    public function getNombre(): string
    {
        return $this->nombre;
    }


    public function getGrupoId(): int
    {
        return $this->grupoId;
    }


    // =====================================================
    // 4. CONVERSION A ARREGLO
    //
    // Convierte el objeto en un arreglo asociativo,
    // respetando los nombres establecidos en el
    // contrato de la API.
    // =====================================================

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'grupo_id' => $this->grupoId
        ];
    }
}