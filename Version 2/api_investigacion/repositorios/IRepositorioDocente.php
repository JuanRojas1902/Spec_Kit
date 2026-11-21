<?php

declare(strict_types=1);

/**
 * ARCHIVO: IRepositorioDocente.php
 *
 * QUE ES:
 * Interfaz que define las operaciones de almacenamiento
 * y consulta de los docentes.
 *
 * POR QUE ESTA AQUI:
 * Permite que el servicio dependa de una abstraccion
 * y no directamente de una implementacion de MariaDB.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con la base de datos.
 * No implementa reglas de negocio.
 * No procesa peticiones HTTP.
 */

// Importamos el modelo que representa al docente.

require_once __DIR__ . '/../modelos/Docente.php';


interface IRepositorioDocente
{
    // =====================================================
    // 1. LISTAR DOCENTES
    //
    // Devuelve un arreglo de docentes activos.
    // El limite indica la cantidad maxima de registros.
    // =====================================================

    public function obtenerTodos(int $limite): array;


    // =====================================================
    // 2. CONSULTAR UN DOCENTE
    //
    // Busca un docente activo mediante su cedula.
    // Devuelve null si no encuentra el registro.
    // =====================================================

    public function obtenerPorId(int $cedula): ?Docente;


    // =====================================================
    // 3. CREAR DOCENTE
    //
    // Recibe un objeto Docente para almacenarlo.
    // Devuelve la cantidad de filas afectadas.
    // =====================================================

    public function crear(Docente $docente): int;


    // =====================================================
    // 4. REEMPLAZAR DOCENTE
    //
    // Actualiza todos los campos modificables del docente.
    // La cedula permanece sin cambios.
    // =====================================================

    public function reemplazar(Docente $docente): int;


    // =====================================================
    // 5. ACTUALIZAR PARCIALMENTE
    //
    // Modifica exclusivamente los campos recibidos.
    // No permite modificar la cedula ni el estado activo.
    // =====================================================

    public function actualizarParcial(
        int $cedula,
        array $campos
    ): int;


    // =====================================================
    // 6. RETIRAR DOCENTE
    //
    // Realiza un borrado logico.
    // La fila permanece almacenada en la base de datos.
    // =====================================================

    public function retirar(int $cedula): int;
}