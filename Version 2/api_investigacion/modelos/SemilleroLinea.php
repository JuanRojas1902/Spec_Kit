<?php

declare(strict_types=1);

/**
 * ARCHIVO: SemilleroLinea.php
 *
 * QUE ES:
 * Modelo que representa la relacion entre un semillero
 * y una linea de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite representar mediante un objeto de PHP
 * la relacion de muchos a muchos entre los semilleros
 * y las lineas de investigacion.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba la existencia de llaves foraneas.
 * No administra directamente las relaciones.
 * No procesa peticiones HTTP.
 */

class SemilleroLinea
{
    // =====================================================
    // 1. ATRIBUTOS
    //
    // Los dos atributos conforman la llave primaria
    // compuesta de la tabla semillero_linea.
    // =====================================================

    private int $semilleroId;

    private int $lineaId;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Inicializa las dos claves que identifican
    // la relacion entre el semillero y la linea.
    // =====================================================

    public function __construct(
        int $semilleroId,
        int $lineaId
    ) {
        $this->semilleroId = $semilleroId;

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

    public function getSemilleroId(): int
    {
        return $this->semilleroId;
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
            'semillero_id' => $this->semilleroId,
            'linea_id' => $this->lineaId
        ];
    }
}