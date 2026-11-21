<?php

declare(strict_types=1);

/*
 * PROYECTO DE INVESTIGACION - V3
 *
 * ARCHIVO: IServicioUsuariosV3.php
 *
 * QUE ES:
 * Contrato de las operaciones del servicio
 * de administracion de usuarios.
 *
 * POR QUE:
 * El controlador debe depender de una interfaz.
 *
 * QUE NO HACE:
 * No ejecuta SQL.
 * No responde HTTP.
 * No contiene HTML.
 */

interface IServicioUsuariosV3
{
    public function listar(): array;

    public function obtenerPorId(int $id): ?array;

    /*
     * Validar los datos y registrar un usuario
     * con el rol CONSULTA.
     *
     * Devuelve el ID del nuevo usuario.
     */
    public function crear(array $datos): int;
}