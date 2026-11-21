<?php

declare(strict_types=1);

/**
 * ARCHIVO: errores_de_integridad.php
 *
 * QUE ES:
 * Traductor de errores de integridad de MariaDB.
 *
 * POR QUE ESTA AQUI:
 * Centraliza la traduccion de los errores de la base
 * de datos para todos los repositorios.
 *
 * QUE NO HACE:
 * No consulta la base de datos.
 * No comprueba previamente si existen los registros.
 * No genera respuestas HTTP.
 */

// Importamos las excepciones personalizadas.

require_once __DIR__ . '/../excepciones/ConflictoDeIntegridadExcepcion.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

/**
 * Traduce una excepcion de PDO a una excepcion
 * comprensible para las otras capas del sistema.
 *
 * El tipo never indica que esta funcion nunca devuelve
 * un valor, porque siempre lanza una excepcion.
 */

function traducirErrorDeIntegridad(PDOException $error): never
{
    // SQLSTATE identifica las señales de los procedimientos.

    $estadoSql = $error->errorInfo[0] ?? '';

    // Codigo especifico del error de MariaDB.

    $codigoMotor = (int) ($error->errorInfo[1] ?? 0);

    // =====================================================
    // 1. REGISTRO NO ENCONTRADO
    //
    // El procedimiento almacenado utiliza la señal 45001
    // cuando el grupo no existe o ya fue retirado.
    // =====================================================

    if ($estadoSql === '45001') {

        throw new NoEncontradoExcepcion(
            $error->errorInfo[2] ?? 'Registro no encontrado.',
            0,
            $error
        );

    }

    // =====================================================
    // 2. REGLA DE INTEGRIDAD DEL PROCEDIMIENTO
    //
    // Por ejemplo, retirar un grupo que todavia tiene
    // semilleros activos.
    // =====================================================

    if ($estadoSql === '45000') {

        throw new ConflictoDeIntegridadExcepcion(
            $error->errorInfo[2] ?? 'La operacion no esta permitida.',
            0,
            $error
        );

    }

    // =====================================================
    // 3. REGISTRO DUPLICADO
    //
    // Error 1062: una llave primaria o una pareja
    // de una tabla puente ya existe.
    // =====================================================

    if ($codigoMotor === 1062) {

        throw new ConflictoDeIntegridadExcepcion(
            'Ya existe un registro o una relación con esa clave.',
            0,
            $error
        );

    }

    // =====================================================
    // 4. LLAVE FORANEA INEXISTENTE
    //
    // Error 1452: se intenta guardar una relacion
    // con un registro que no existe.
    // =====================================================

    if ($codigoMotor === 1452) {

        throw new ConflictoDeIntegridadExcepcion(
            'Alguno de los códigos a los que apunta la ficha no existe.',
            0,
            $error
        );

    }

    // =====================================================
    // 5. EXISTEN REGISTROS DEPENDIENTES
    //
    // Error 1451: se intenta eliminar fisicamente
    // un registro del que otros dependen.
    // =====================================================

    if ($codigoMotor === 1451) {

        throw new ConflictoDeIntegridadExcepcion(
            'No se puede retirar la ficha porque hay otras fichas que dependen de ella.',
            0,
            $error
        );

    }

    // =====================================================
    // 6. ERRORES DESCONOCIDOS
    //
    // Si el error no corresponde a los casos anteriores,
    // se conserva la excepcion original.
    //
    // Asi no confundimos un fallo real del sistema
    // con un conflicto de integridad.
    // =====================================================

    throw $error;
}