<?php

declare(strict_types=1);

/**
 * ARCHIVO: IRepositorioSemillero.php
 *
 * QUE ES:
 * Interfaz que define las operaciones de consulta
 * de los semilleros de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite que el servicio dependa de una abstraccion
 * y no directamente del repositorio de MariaDB.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con la base de datos.
 * No crea, modifica ni retira semilleros.
 * No procesa peticiones HTTP.
 * No genera respuestas para el usuario.
 */


require_once __DIR__ . '/../modelos/Semillero.php';


interface IRepositorioSemillero
{
    // =====================================================
    // 1. LISTAR SEMILLEROS
    //
    // Devuelve un arreglo de objetos Semillero.
    //
    // Solo deben aparecer los semilleros activos.
    // El parametro limite determina la cantidad maxima
    // de registros que se pueden recuperar.
    // =====================================================

    public function obtenerTodos(int $limite): array;


    // =====================================================
    // 2. CONSULTAR UN SEMILLERO
    //
    // Busca un semillero activo mediante su identificador.
    //
    // Si existe, devuelve un objeto Semillero.
    // Si no existe o esta retirado, devuelve null.
    // =====================================================

    public function obtenerPorId(int $id): ?Semillero;
}