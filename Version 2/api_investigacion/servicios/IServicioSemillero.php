<?php

declare(strict_types=1);

/**
 * ARCHIVO: IServicioSemillero.php
 *
 * QUE ES:
 * Interfaz que define las operaciones del servicio
 * de semilleros de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite que el controlador dependa de una abstraccion
 * y no directamente de una implementacion concreta.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No crea, modifica ni retira semilleros.
 * No procesa peticiones HTTP.
 * No implementa directamente las operaciones.
 */


require_once __DIR__ . '/../modelos/Semillero.php';


interface IServicioSemillero
{
    // =====================================================
    // 1. LISTAR SEMILLEROS
    //
    // Devuelve un arreglo de objetos Semillero.
    //
    // Solamente se recuperan los semilleros activos.
    //
    // El limite predeterminado es de 1000 registros,
    // tal como establece el contrato de la API.
    // =====================================================

    public function obtenerTodos(
        int $limite = 1000
    ): array;


    // =====================================================
    // 2. CONSULTAR UN SEMILLERO POR ID
    //
    // Devuelve un objeto Semillero correspondiente
    // al identificador solicitado.
    //
    // Si el semillero no existe o esta retirado,
    // la implementacion del servicio debe generar
    // una excepcion NoEncontradoExcepcion.
    // =====================================================

    public function obtenerPorId(
        int $id
    ): Semillero;
}