<?php

declare(strict_types=1);

/**
 * ARCHIVO: publico/index.php
 * CAPA: Frontend / Punto de entrada publico
 *
 * QUE ES:
 * Archivo de entrada utilizado por Apache para
 * recibir las solicitudes del navegador.
 *
 * POR QUE ESTA AQUI:
 * El servidor web utiliza la carpeta publico
 * como punto de entrada del frontend.
 *
 * Este archivo conecta Apache con el enrutador
 * principal ubicado en front_php/index.php.
 *
 * QUE NO HACE:
 * No administra las sesiones directamente.
 * No ejecuta consultas SQL.
 * No se conecta con MariaDB.
 * No procesa formularios.
 * No contiene HTML.
 * No utiliza la antigua funcion plantilla().
 */


// =========================================================
// 1. IDENTIFICAR EL ENRUTADOR PRINCIPAL
// =========================================================

$enrutadorPrincipal = dirname(__DIR__) . '/index.php';


// =========================================================
// 2. VERIFICAR QUE EXISTA
// =========================================================

if (!is_file($enrutadorPrincipal)) {

    http_response_code(500);

    error_log(
        'No se encontro el enrutador principal del frontend.'
    );

    exit(
        'El sistema no está disponible temporalmente.'
    );
}


// =========================================================
// 3. ENTREGAR EL CONTROL AL ENRUTADOR PRINCIPAL
//
// El archivo front_php/index.php se encargara de:
// - Iniciar la sesion.
// - Validar las solicitudes.
// - Procesar el login.
// - Gestionar las rutas.
// - Comunicarse con la API.
// - Cargar la plantilla y las vistas.
// =========================================================

require $enrutadorPrincipal;