<?php

declare(strict_types=1);

/**
 * ARCHIVO: GrupoLinea.php
 *
 * QUE ES:
 * Modelo que representa la relacion entre un grupo
 * de investigacion y una linea de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite representar mediante un objeto de PHP
 * la relacion de muchos a muchos entre los grupos
 * y las lineas de investigacion.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba la existencia de llaves foraneas.
 * No administra directamente las relaciones.
 * No procesa peticiones HTTP.
 */

class GrupoLinea
{
    // =====================================================
    // 1. ATRIBUTOS
    //
    // Los dos atributos conforman la llave primaria
    // compuesta de la tabla grupo_linea.
    // =====================================================

    private int $grupoId;

    private int $lineaId;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Inicializa las dos claves que identifican
    // la relacion entre el grupo y la linea.
    // =====================================================

    public function __construct(
        int $grupoId,
        int $lineaId
    ) {
        $this->grupoId = $grupoId;

        $this->lineaId = $lineaId;
    }


    // =====================================================
    // 3. GETTERS
    //
    // Permiten consultar los atributos privados.
    //
    // No se implementan setters porque las relaciones
    // de las tablas puente no se modifican.
    //
    // Para cambiar una relacion se debe retirar
    // la pareja anterior y asignar una nueva.
    // =====================================================

    public function getGrupoId(): int
    {
        return $this->grupoId;
    }


    public function getLineaId(): int
    {
        return $this->lineaId;
    }


    // =====================================================
    // 4. CONVERSION A ARREGLO
    //
    // Convierte el objeto en un arreglo asociativo.
    //
    // Los nombres de las claves corresponden
    // al contrato establecido en el Spec Kit.
    // =====================================================

    public function toArray(): array
    {
        return [
            'grupo_id' => $this->grupoId,
            'linea_id' => $this->lineaId
        ];
    }
}