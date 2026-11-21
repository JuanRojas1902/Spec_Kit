<?php

declare(strict_types=1);

/**
 * ARCHIVO: ServicioParticipaSemillero.php
 *
 * QUE ES:
 * Servicio encargado de administrar las operaciones
 * de relacion entre docentes y semilleros de investigacion.
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


require_once __DIR__ . '/IServicioParticipaSemillero.php';

require_once __DIR__ . '/../repositorios/IRepositorioParticipaSemillero.php';

require_once __DIR__ . '/../modelos/ParticipaSemillero.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';


class ServicioParticipaSemillero
    implements IServicioParticipaSemillero
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // El servicio depende de la interfaz del repositorio
    // y no directamente de su implementacion concreta.
    // =====================================================

    private IRepositorioParticipaSemillero $repositorio;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe el repositorio mediante inyeccion
    // de dependencias.
    // =====================================================

    public function __construct(
        IRepositorioParticipaSemillero $repositorio
    ) {
        $this->repositorio = $repositorio;
    }


    // =====================================================
    // 3. LISTAR RELACIONES
    //
    // Recupera las relaciones registradas entre
    // docentes y semilleros de investigacion.
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
    // ParticipaSemillero para entregarlo al repositorio.
    //
    // MariaDB se encarga de comprobar la existencia
    // de las llaves foraneas y evitar duplicados.
    // =====================================================

    public function asignar(
        int $docenteCedula,
        int $semilleroId
    ): int {

        // Validar las dos claves.

        $this->validarClaves(
            $docenteCedula,
            $semilleroId
        );


        // Construir el objeto que representa la relacion.

        $participaSemillero = new ParticipaSemillero(
            $docenteCedula,
            $semilleroId
        );


        // Solicitar al repositorio la insercion.

        return $this->repositorio->asignar(
            $participaSemillero
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
        int $docenteCedula,
        int $semilleroId
    ): int {

        // Validar las dos claves.

        $this->validarClaves(
            $docenteCedula,
            $semilleroId
        );


        // Solicitar al repositorio el retiro.

        $filasAfectadas = $this->repositorio->retirar(
            $docenteCedula,
            $semilleroId
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
        int $docenteCedula,
        int $semilleroId
    ): void {

        if (
            $docenteCedula <= 0 ||
            $docenteCedula > 2147483647
        ) {

            throw new InvalidArgumentException(
                'La cédula del docente debe ser un entero positivo válido.'
            );

        }


        if (
            $semilleroId <= 0 ||
            $semilleroId > 2147483647
        ) {

            throw new InvalidArgumentException(
                'El identificador del semillero debe ser un entero positivo válido.'
            );

        }
    }
}