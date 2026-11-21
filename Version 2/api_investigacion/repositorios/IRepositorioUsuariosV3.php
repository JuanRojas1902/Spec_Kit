<?php

declare(strict_types=1);

/*
 * PROYECTO DE INVESTIGACION - V3
 *
 * ARCHIVO: IRepositorioUsuariosV3.php
 *
 * QUE ES:
 * Contrato para consultar usuarios del sistema.
 *
 * POR QUE:
 * Permite que la capa de servicios no dependa
 * directamente de MariaDB.
 *
 * QUE NO HACE:
 * No ejecuta SQL.
 * No maneja HTTP.
 * No comprueba permisos JWT.
 */

interface IRepositorioUsuariosV3
{
    public function listar(): array;

    public function obtenerPorId(int $id): ?array;

    /*
     * Registra una cuenta con el rol CONSULTA.
     * Recibe un hash, nunca una contrasena sin cifrar.
     */
    public function crearConRolConsulta(
        string $username,
        string $email,
        string $nombreCompleto,
        string $passwordHash
    ): int;
}