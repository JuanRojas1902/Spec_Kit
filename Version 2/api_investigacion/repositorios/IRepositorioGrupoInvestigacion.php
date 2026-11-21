<?php

declare(strict_types=1);

/**
 * ARCHIVO: IRepositorioGrupoInvestigacion.php
 *
 * QUE ES:
 * Interfaz que define las operaciones de almacenamiento
 * y consulta de los grupos de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite que el servicio dependa de una abstraccion
 * y no directamente del repositorio de MariaDB.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con la base de datos.
 * No ejecuta procedimientos almacenados.
 * No administra transacciones.
 * No genera respuestas HTTP.
 */


require_once __DIR__ . '/../modelos/GrupoInvestigacion.php';


interface IRepositorioGrupoInvestigacion
{
    // =====================================================
    // 1. LISTAR GRUPOS DE INVESTIGACION
    //
    // Devuelve un arreglo de grupos activos.
    // Solo incluye los datos del encabezado.
    // No recibe un limite de registros.
    //
    // Procedimiento asociado:
    // sp_listar_grupo_investigacion
    // =====================================================

    public function obtenerTodos(): array;


    // =====================================================
    // 2. CONSULTAR UN GRUPO DE INVESTIGACION
    //
    // Devuelve un grupo junto con sus semilleros.
    //
    // Si el grupo no existe o esta retirado,
    // el repositorio traduce la excepcion de la base.
    //
    // Procedimiento asociado:
    // sp_consultar_grupo_investigacion
    // =====================================================

    public function obtenerPorId(
        int $id
    ): GrupoInvestigacion;


    // =====================================================
    // 3. CREAR GRUPO DE INVESTIGACION
    //
    // Recibe un objeto que contiene los datos del grupo
    // y los semilleros que se desean registrar.
    //
    // Devuelve el grupo completo con los identificadores
    // generados por MariaDB.
    //
    // Procedimiento asociado:
    // sp_crear_grupo_investigacion
    // =====================================================

    public function crear(
        GrupoInvestigacion $grupo
    ): GrupoInvestigacion;


    // =====================================================
    // 4. REEMPLAZAR GRUPO DE INVESTIGACION
    //
    // Reemplaza los datos del encabezado y todo el detalle.
    //
    // Los semilleros anteriores se retiran logicamente
    // y se registran los nuevos dentro de una transaccion.
    //
    // Devuelve el grupo actualizado con su nuevo detalle.
    //
    // Procedimiento asociado:
    // sp_actualizar_grupo_investigacion
    // =====================================================

    public function reemplazar(
        GrupoInvestigacion $grupo
    ): GrupoInvestigacion;


    // =====================================================
    // 5. RETIRAR GRUPO DE INVESTIGACION
    //
    // Solicita el borrado logico del grupo.
    //
    // La base de datos rechaza la operacion cuando
    // el grupo tiene semilleros activos.
    //
    // Devuelve la cantidad de grupos retirados.
    //
    // Procedimiento asociado:
    // sp_eliminar_grupo_investigacion
    // =====================================================

    public function retirar(int $id): int;
}