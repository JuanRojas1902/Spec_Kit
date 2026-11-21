<?php

declare(strict_types=1);

/*
 * PROYECTO DE INVESTIGACION - VERSION 3
 *
 * QUE ES:
 * Interfaz del repositorio de autenticacion.
 *
 * POR QUE:
 * Separa las consultas de usuarios y roles
 * de las reglas de autenticacion.
 *
 * QUE NO HACE:
 * No ejecuta SQL.
 * No verifica contrasenas.
 * No genera JWT.
 */

interface IRepositorioAutenticacion
{
    public function buscarUsuarioActivo(
        string $username
    ): ?array;

    public function obtenerRolesActivos(
        int $usuarioId
    ): array;
}