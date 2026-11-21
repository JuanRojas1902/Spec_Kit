<?php

declare(strict_types=1);

/**
 * ARCHIVO: AaLinea.php
 *
 * QUE ES:
 * Modelo que representa la relacion entre un area
 * de aplicacion y una linea de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite representar mediante un objeto de PHP
 * la relacion de muchos a muchos entre las areas
 * de aplicacion y las lineas de investigacion.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba la existencia de llaves foraneas.
 * No administra directamente las relaciones.
 * No procesa peticiones HTTP.
 */

class AaLinea
{
    // =====================================================
    // 1. ATRIBUTOS
    //
    // Los dos atributos conforman la llave primaria
    // compuesta de la tabla aa_linea.
    // =====================================================

    private int $areaAplicacionId;

    private int $lineaId;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Inicializa las dos claves que identifican
    // la relacion entre el area de aplicacion
    // y la linea de investigacion.
    // =====================================================

    public function __construct(
        int $areaAplicacionId,
        int $lineaId
    ) {
        $this->areaAplicacionId = $areaAplicacionId;

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

    public function getAreaAplicacionId(): int
    {
        return $this->areaAplicacionId;
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
            'area_aplicacion_id' => $this->areaAplicacionId,
            'linea_id' => $this->lineaId
        ];
    }
}