<?php

declare(strict_types=1);

/**
 * ARCHIVO: ServicioSemilleroLinea.php
 *
 * QUE ES:
 * Servicio encargado de administrar las operaciones
 * de relacion entre semilleros y lineas de investigacion.
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


require_once __DIR__ . '/IServicioSemilleroLinea.php';

require_once __DIR__ . '/../repositorios/IRepositorioSemilleroLinea.php';

require_once __DIR__ . '/../modelos/SemilleroLinea.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';


class ServicioSemilleroLinea
    implements IServicioSemilleroLinea
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // El servicio depende de la interfaz del repositorio
    // y no directamente de su implementacion concreta.
    // =====================================================

    private IRepositorioSemilleroLinea $repositorio;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe el repositorio mediante inyeccion
    // de dependencias.
    // =====================================================

    public function __construct(
        IRepositorioSemilleroLinea $repositorio
    ) {
        $this->repositorio = $repositorio;
    }


    // =====================================================
    // 3. LISTAR RELACIONES
    //
    // Recupera las relaciones registradas entre
    // semilleros y lineas de investigacion.
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
    // SemilleroLinea para entregarlo al repositorio.
    //
    // MariaDB se encarga de comprobar la existencia
    // de las llaves foraneas y evitar duplicados.
    // =====================================================

    public function asignar(
        int $semilleroId,
        int $lineaId
    ): int {

        // Validar las dos claves.

        $this->validarClaves(
            $semilleroId,
            $lineaId
        );


        // Construir el objeto que representa la relacion.

        $semilleroLinea = new SemilleroLinea(
            $semilleroId,
            $lineaId
        );


        // Solicitar al repositorio la insercion.

        return $this->repositorio->asignar(
            $semilleroLinea
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
        int $semilleroId,
        int $lineaId
    ): int {

        // Validar las dos claves.

        $this->validarClaves(
            $semilleroId,
            $lineaId
        );


        // Solicitar al repositorio el retiro.

        $filasAfectadas = $this->repositorio->retirar(
            $semilleroId,
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
        int $semilleroId,
        int $lineaId
    ): void {

        if (
            $semilleroId <= 0 ||
            $semilleroId > 2147483647
        ) {

            throw new InvalidArgumentException(
                'El identificador del semillero debe ser un entero positivo válido.'
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