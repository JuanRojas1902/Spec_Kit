<?php

declare(strict_types=1);

/**
 * ARCHIVO: IServicioParticipaSemillero.php
 *
 * QUE ES:
 * Interfaz que define las operaciones del servicio
 * de relacion entre docentes y semilleros de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite que el controlador dependa de una abstraccion
 * y no directamente de una implementacion concreta.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba directamente las llaves foraneas.
 * No modifica relaciones existentes.
 * No implementa directamente las reglas de negocio.
 */


require_once __DIR__ . '/../modelos/ParticipaSemillero.php';


interface IServicioParticipaSemillero
{
    // =====================================================
    // 1. LISTAR RELACIONES
    //
    // Devuelve un arreglo de objetos ParticipaSemillero.
    //
    // El limite predeterminado es de 1000 registros.
    // El limite debe ser un numero entero positivo.
    // =====================================================

    public function obtenerTodos(
        int $limite = 1000
    ): array;


    // =====================================================
    // 2. ASIGNAR RELACION
    //
    // Recibe la cedula del docente y el identificador
    // del semillero de investigacion.
    //
    // MariaDB comprueba que las llaves foraneas existan
    // y que la pareja no se encuentre repetida.
    //
    // Devuelve la cantidad de filas insertadas.
    // =====================================================

    public function asignar(
        int $docenteCedula,
        int $semilleroId
    ): int;


    // =====================================================
    // 3. RETIRAR RELACION
    //
    // Recibe las dos claves de la llave primaria compuesta.
    //
    // La relacion se elimina fisicamente de la tabla.
    //
    // Si la pareja no existe, la implementacion
    // del servicio genera NoEncontradoExcepcion.
    //
    // Devuelve la cantidad de filas eliminadas.
    // =====================================================

    public function retirar(
        int $docenteCedula,
        int $semilleroId
    ): int;
}