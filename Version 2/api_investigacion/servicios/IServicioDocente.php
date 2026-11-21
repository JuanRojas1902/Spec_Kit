<?php

declare(strict_types=1);

/**
 * ARCHIVO: IServicioDocente.php
 *
 * QUE ES:
 * Interfaz que define las operaciones del servicio de docentes.
 *
 * POR QUE ESTA AQUI:
 * Permite que el controlador dependa de una abstraccion
 * y no directamente de una clase concreta.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No implementa reglas de negocio.
 * No genera respuestas HTTP.
 */

require_once __DIR__ . '/../modelos/Docente.php';


interface IServicioDocente
{
    // =====================================================
    // 1. LISTAR DOCENTES
    //
    // Devuelve los docentes activos.
    // El limite predeterminado es de 1000 registros,
    // tal como establece el contrato de la API.
    // =====================================================

    public function obtenerTodos(int $limite = 1000): array;


    // =====================================================
    // 2. CONSULTAR UN DOCENTE
    //
    // Devuelve el docente identificado por su cedula.
    // Si no existe, el servicio lanza una excepcion.
    // =====================================================

    public function obtenerPorId(int $cedula): Docente;


    // =====================================================
    // 3. CREAR DOCENTE
    //
    // Recibe los datos necesarios para registrar un docente.
    // Devuelve la cantidad de filas afectadas.
    // =====================================================

    public function crear(
        int $cedula,
        string $nombres,
        string $apellidos
    ): int;


    // =====================================================
    // 4. REEMPLAZAR DOCENTE
    //
    // Reemplaza todos los campos modificables.
    // La cedula permanece sin cambios.
    // =====================================================

    public function reemplazar(
        int $cedula,
        string $nombres,
        string $apellidos
    ): int;


    // =====================================================
    // 5. ACTUALIZAR PARCIALMENTE
    //
    // Modifica solamente los campos recibidos.
    // El servicio verifica que existan campos para cambiar.
    // =====================================================

    public function actualizarParcial(
        int $cedula,
        array $campos
    ): int;


    // =====================================================
    // 6. RETIRAR DOCENTE
    //
    // Solicita el borrado logico del docente.
    // No elimina fisicamente el registro.
    // =====================================================

    public function retirar(int $cedula): int;
}