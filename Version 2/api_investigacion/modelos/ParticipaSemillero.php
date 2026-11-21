<?php

declare(strict_types=1);

/**
 * ARCHIVO: ParticipaSemillero.php
 *
 * QUE ES:
 * Modelo que representa la relacion entre un docente
 * y un semillero de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite representar mediante un objeto de PHP
 * la participacion de los docentes en los semilleros
 * de investigacion.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba la existencia de llaves foraneas.
 * No administra directamente las relaciones.
 * No procesa peticiones HTTP.
 */

class ParticipaSemillero
{
    // =====================================================
    // 1. ATRIBUTOS
    //
    // Los dos atributos conforman la llave primaria
    // compuesta de la tabla participa_semillero.
    // =====================================================

    private int $docenteCedula;

    private int $semilleroId;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Inicializa las dos claves que identifican
    // la participacion del docente en el semillero.
    // =====================================================

    public function __construct(
        int $docenteCedula,
        int $semilleroId
    ) {
        $this->docenteCedula = $docenteCedula;

        $this->semilleroId = $semilleroId;
    }


    // =====================================================
    // 3. GETTERS
    //
    // Permiten consultar los atributos privados.
    //
    // No se implementan setters porque las relaciones
    // de las tablas puente no se modifican.
    // =====================================================

    public function getDocenteCedula(): int
    {
        return $this->docenteCedula;
    }


    public function getSemilleroId(): int
    {
        return $this->semilleroId;
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
            'docente_cedula' => $this->docenteCedula,
            'semillero_id' => $this->semilleroId
        ];
    }
}