<?php

declare(strict_types=1);

/**
 * ARCHIVO: GrupoInvestigacion.php
 *
 * QUE ES:
 * Modelo que representa un grupo de investigacion
 * junto con sus semilleros asociados.
 *
 * POR QUE ESTA AQUI:
 * Permite representar la relacion maestro-detalle
 * mediante un objeto de PHP.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No ejecuta procedimientos almacenados.
 * No administra transacciones.
 * No genera respuestas ni pantallas.
 */

class GrupoInvestigacion
{
    // =====================================================
    // 1. ATRIBUTOS DEL MAESTRO
    //
    // Representan los datos principales del grupo.
    // El id puede ser null porque MariaDB lo genera
    // automaticamente al crear un nuevo registro.
    // =====================================================

    private ?int $id;

    private string $gruplac;

    private string $categoria;

    private string $convocatoria;

    private string $ambito;

    private int $universidadId;


    // =====================================================
    // 2. ATRIBUTO DEL DETALLE
    //
    // Contiene los semilleros asociados al grupo.
    // Un grupo puede tener cero o varios semilleros.
    // =====================================================

    private array $detalle;


    // =====================================================
    // 3. CONSTRUCTOR
    //
    // Inicializa los datos del grupo y de sus semilleros.
    // =====================================================

    public function __construct(
        ?int $id,
        string $gruplac,
        string $categoria,
        string $convocatoria,
        string $ambito,
        int $universidadId,
        array $detalle = []
    ) {
        $this->id = $id;
        $this->gruplac = $gruplac;
        $this->categoria = $categoria;
        $this->convocatoria = $convocatoria;
        $this->ambito = $ambito;
        $this->universidadId = $universidadId;
        $this->detalle = $detalle;
    }


    // =====================================================
    // 4. GETTERS
    //
    // Permiten consultar los atributos privados del modelo.
    // =====================================================

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getGruplac(): string
    {
        return $this->gruplac;
    }


    public function getCategoria(): string
    {
        return $this->categoria;
    }


    public function getConvocatoria(): string
    {
        return $this->convocatoria;
    }


    public function getAmbito(): string
    {
        return $this->ambito;
    }


    public function getUniversidadId(): int
    {
        return $this->universidadId;
    }


    public function getDetalle(): array
    {
        return $this->detalle;
    }


    // =====================================================
    // 5. CONVERSION A ARREGLO
    //
    // Convierte el modelo en un arreglo asociativo.
    //
    // incluirDetalle = true:
    // Devuelve el grupo junto con sus semilleros.
    //
    // incluirDetalle = false:
    // Devuelve solamente el encabezado del grupo.
    // =====================================================

    public function toArray(
        bool $incluirDetalle = true
    ): array {

        $datos = [
            'gruplac' => $this->gruplac,
            'categoria' => $this->categoria,
            'convocatoria' => $this->convocatoria,
            'ambito' => $this->ambito,
            'universidad_id' => $this->universidadId
        ];


        // El id se incluye solamente cuando existe.

        if ($this->id !== null) {

            $datos = ['id' => $this->id] + $datos;

        }


        // Agregamos los semilleros cuando se solicita
        // la informacion completa del grupo.

        if ($incluirDetalle) {

            $datos['detalle'] = $this->detalle;

        }


        return $datos;
    }
}