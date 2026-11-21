<?php

declare(strict_types=1);

/**
 * ARCHIVO: IServicioGrupoInvestigacion.php
 *
 * QUE ES:
 * Interfaz que define las operaciones del servicio
 * de grupos de investigacion y sus semilleros.
 *
 * POR QUE ESTA AQUI:
 * Permite que el controlador dependa de una abstraccion
 * y no directamente de una clase concreta.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No ejecuta procedimientos almacenados.
 * No administra transacciones.
 * No implementa directamente las reglas de negocio.
 */

require_once __DIR__ . '/../modelos/GrupoInvestigacion.php';


interface IServicioGrupoInvestigacion
{
    // =====================================================
    // 1. LISTAR GRUPOS DE INVESTIGACION
    //
    // Devuelve los grupos activos.
    // Solamente incluye los datos del encabezado.
    // No recibe un limite de registros.
    // =====================================================

    public function obtenerTodos(): array;


    // =====================================================
    // 2. CONSULTAR UN GRUPO
    //
    // Devuelve un grupo de investigacion junto con
    // todos sus semilleros activos.
    //
    // Si el grupo no existe o esta retirado,
    // se produce una excepcion.
    // =====================================================

    public function obtenerPorId(
        int $id
    ): GrupoInvestigacion;


    // =====================================================
    // 3. CREAR GRUPO DE INVESTIGACION
    //
    // Recibe los datos del encabezado y los semilleros.
    //
    // No recibe el identificador del grupo porque
    // MariaDB lo genera automaticamente.
    //
    // Un detalle vacio es valido.
    // =====================================================

    public function crear(
        string $gruplac,
        string $categoria,
        string $convocatoria,
        string $ambito,
        int $universidadId,
        array $detalle
    ): GrupoInvestigacion;


    // =====================================================
    // 4. REEMPLAZAR GRUPO DE INVESTIGACION
    //
    // Recibe el identificador del grupo, todos los datos
    // del encabezado y el nuevo detalle.
    //
    // Reemplaza completamente los semilleros anteriores.
    // Devuelve el grupo actualizado.
    // =====================================================

    public function reemplazar(
        int $id,
        string $gruplac,
        string $categoria,
        string $convocatoria,
        string $ambito,
        int $universidadId,
        array $detalle
    ): GrupoInvestigacion;


    // =====================================================
    // 5. RETIRAR GRUPO DE INVESTIGACION
    //
    // Solicita el borrado logico del grupo.
    //
    // El procedimiento almacenado se encarga de verificar
    // que el grupo no tenga semilleros activos.
    //
    // Devuelve la cantidad de grupos retirados.
    // =====================================================

    public function retirar(int $id): int;
}