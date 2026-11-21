<?php

declare(strict_types=1);

/**
 * ARCHIVO: AcLinea.php
 *
 * QUE ES:
 * Modelo que representa la relacion entre un area
 * de conocimiento y una linea de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite representar mediante un objeto de PHP
 * la relacion de muchos a muchos entre las areas
 * de conocimiento y las lineas de investigacion.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba la existencia de llaves foraneas.
 * No administra directamente las relaciones.
 * No procesa peticiones HTTP.
 */

class AcLinea
{
    // =====================================================
    // 1. ATRIBUTOS
    //
    // Los dos atributos conforman la llave primaria
    // compuesta de la tabla ac_linea.
    // =====================================================

    private int $areaConocimientoId;

    private int $lineaId;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Inicializa las dos claves que identifican
    // la relacion entre el area de conocimiento
    // y la linea de investigacion.
    // =====================================================

    public function __construct(
        int $areaConocimientoId,
        int $lineaId
    ) {
        $this->areaConocimientoId = $areaConocimientoId;

        $this->lineaId = $lineaId;
    }


    // =====================================================
    // 3. GETTERS
    //
    // Permiten consultar los atributos privados.
    //
    // No se implementan setters porque las relaciones
    // de las tablas puente no se modifican.
    // =====================================================

    public function getAreaConocimientoId(): int
    {
        return $this->areaConocimientoId;
    }


    public function getLineaId(): int
    {
        return $this->lineaId;
    }


    // =====================================================
    // 4. CONVERSION A ARREGLO
    //
    // Convierte el objeto en un arreglo asociativo
    // respetando los nombres definidos en el Spec Kit.
    // =====================================================

    public function toArray(): array
    {
        return [
            'area_conocimiento_id' => $this->areaConocimientoId,
            'linea_id' => $this->lineaId
        ];
    }
}