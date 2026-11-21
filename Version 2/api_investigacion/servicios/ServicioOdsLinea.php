<?php

declare(strict_types=1);

/**
 * ARCHIVO: ServicioOdsLinea.php
 *
 * QUE ES:
 * Servicio encargado de administrar las operaciones
 * de relacion entre objetivos de desarrollo sostenible
 * y lineas de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite centralizar las validaciones y las reglas
 * de negocio, manteniendo la independencia entre
 * el controlador y el repositorio.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No comprueba previamente las llaves foraneas.
 * No modifica relaciones existentes.
 * No genera respuestas para el usuario.
 */


require_once __DIR__ . '/IServicioOdsLinea.php';

require_once __DIR__ . '/../repositorios/IRepositorioOdsLinea.php';

require_once __DIR__ . '/../modelos/OdsLinea.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';


class ServicioOdsLinea
    implements IServicioOdsLinea
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // El servicio depende de la interfaz del repositorio
    // y no directamente de su implementacion concreta.
    // =====================================================

    private IRepositorioOdsLinea $repositorio;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe el repositorio mediante inyeccion
    // de dependencias.
    // =====================================================

    public function __construct(
        IRepositorioOdsLinea $repositorio
    ) {
        $this->repositorio = $repositorio;
    }


    // =====================================================
    // 3. LISTAR RELACIONES
    //
    // Recupera las relaciones registradas entre
    // objetivos de desarrollo sostenible y lineas
    // de investigacion.
    //
    // El limite predeterminado es de 1000 registros.
    // =====================================================

    public function obtenerTodos(
        int $limite = 1000
    ): array {

        if ($limite <= 0) {

            throw new InvalidArgumentException(
                'El límite debe ser mayor que cero.'
            );

        }

        return $this->repositorio->obtenerTodos($limite);
    }


    // =====================================================
    // 4. ASIGNAR RELACION
    //
    // Valida las dos claves y construye un objeto
    // OdsLinea para entregarlo al repositorio.
    //
    // MariaDB se encarga de comprobar la existencia
    // de las llaves foraneas y evitar duplicados.
    // =====================================================

    public function asignar(
        int $odsId,
        int $lineaId
    ): int {

        // Validar las dos claves.

        $this->validarClaves(
            $odsId,
            $lineaId
        );


        // Construir el objeto que representa la relacion.

        $odsLinea = new OdsLinea(
            $odsId,
            $lineaId
        );


        // Solicitar al repositorio la insercion.

        return $this->repositorio->asignar(
            $odsLinea
        );
    }


    // =====================================================
    // 5. RETIRAR RELACION
    //
    // Valida las dos claves de la llave primaria
    // compuesta y solicita al repositorio el retiro.
    //
    // Si no se elimina ninguna fila, significa
    // que la relacion solicitada no existe.
    // =====================================================

    public function retirar(
        int $odsId,
        int $lineaId
    ): int {

        // Validar las dos claves.

        $this->validarClaves(
            $odsId,
            $lineaId
        );


        // Solicitar al repositorio el retiro.

        $filasAfectadas = $this->repositorio->retirar(
            $odsId,
            $lineaId
        );


        // Comprobar si la relacion existia.

        if ($filasAfectadas === 0) {

            throw new NoEncontradoExcepcion(
                'La pareja solicitada no existe.'
            );

        }


        // Devolver la cantidad de filas eliminadas.

        return $filasAfectadas;
    }


    // =====================================================
    // 6. VALIDAR CLAVES
    //
    // Comprueba que las dos claves sean numeros
    // enteros positivos dentro del rango de INT.
    //
    // No consulta la base de datos.
    // =====================================================

    private function validarClaves(
        int $odsId,
        int $lineaId
    ): void {

        if (
            $odsId <= 0 ||
            $odsId > 2147483647
        ) {

            throw new InvalidArgumentException(
                'El identificador del objetivo de desarrollo sostenible debe ser un entero positivo válido.'
            );

        }


        if (
            $lineaId <= 0 ||
            $lineaId > 2147483647
        ) {

            throw new InvalidArgumentException(
                'El identificador de la línea debe ser un entero positivo válido.'
            );

        }
    }
}