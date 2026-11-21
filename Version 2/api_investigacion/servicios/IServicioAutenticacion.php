<?php

declare(strict_types=1);

/*
 * PROYECTO DE INVESTIGACION - V3
 *
 * QUE ES:
 * Contrato del servicio de autenticacion.
 *
 * POR QUE:
 * Permite que el controlador dependa de una interfaz.
 *
 * QUE NO HACE:
 * No ejecuta SQL, no conoce HTTP y no genera JWT.
 */

interface IServicioAutenticacion
{
    public function iniciarSesion(
        string $username,
        string $password
    ): ?array;
}