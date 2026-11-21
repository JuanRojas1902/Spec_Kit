<?php

declare(strict_types=1);

/**
 * ARCHIVO: IRepositorioSemilleroLinea.php
 *
 * QUE ES:
 * Interfaz que define las operaciones de almacenamiento
 * de las relaciones entre semilleros y lineas
 * de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite que el servicio dependa de una abstraccion
 * y no directamente del repositorio de MariaDB.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba directamente las llaves foraneas.
 * No modifica relaciones existentes.
 * No procesa peticiones HTTP.
 */


require_once __DIR__ . '/../modelos/SemilleroLinea.php';


interface IRepositorioSemilleroLinea
{
    // =====================================================
    // 1. LISTAR RELACIONES
    //
    // Devuelve un arreglo de objetos SemilleroLinea.
    //
    // El limite determina la cantidad maxima
    // de registros que se pueden recuperar.
    //
    // Esta tabla no tiene una columna activo.
    // =====================================================

    public function obtenerTodos(int $limite): array;


    // =====================================================
    // 2. ASIGNAR RELACION
    //
    // Recibe un objeto SemilleroLinea que contiene
    // el identificador del semillero y el identificador
    // de la linea de investigacion.
    //
    // MariaDB comprueba la existencia de las llaves
    // foraneas y evita las parejas duplicadas.
    //
    // Devuelve la cantidad de filas insertadas.
    // =====================================================

    public function asignar(
        SemilleroLinea $semilleroLinea
    ): int;


    // =====================================================
    // 3. RETIRAR RELACION
    //
    // Recibe las dos claves que conforman
    // la llave primaria compuesta.
    //
    // La relacion se elimina fisicamente de la tabla.
    //
    // Devuelve la cantidad de filas eliminadas:
    //
    // 1: La relacion fue retirada.
    // 0: La relacion no existe.
    // =====================================================

    public function retirar(
        int $semilleroId,
        int $lineaId
    ): int;
}