<?php

declare(strict_types=1);

/**
 * ARCHIVO: ServicioSemillero.php
 *
 * QUE ES:
 * Servicio encargado de coordinar las operaciones
 * de consulta de los semilleros de investigacion.
 *
 * POR QUE ESTA AQUI:
 * Permite separar las validaciones del negocio
 * de las consultas a la base de datos.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No crea, modifica ni retira semilleros.
 * No genera respuestas para el usuario.
 */


require_once __DIR__ . '/IServicioSemillero.php';

require_once __DIR__ . '/../repositorios/IRepositorioSemillero.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';


class ServicioSemillero implements IServicioSemillero
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // El servicio depende de una interfaz y no
    // directamente del repositorio de MariaDB.
    // =====================================================

    private IRepositorioSemillero $repositorio;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe el repositorio mediante inyeccion
    // de dependencias.
    // =====================================================

    public function __construct(
        IRepositorioSemillero $repositorio
    ) {
        $this->repositorio = $repositorio;
    }


    // =====================================================
    // 3. LISTAR SEMILLEROS
    //
    // Solicita al repositorio los semilleros activos.
    //
    // El limite predeterminado es de 1000 registros.
    // El limite debe ser un numero entero positivo.
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
    // 4. CONSULTAR UN SEMILLERO POR ID
    //
    // Valida el identificador y solicita al repositorio
    // el semillero correspondiente.
    //
    // Si el semillero no existe o esta retirado,
    // genera una excepcion de negocio.
    // =====================================================

    public function obtenerPorId(int $id): Semillero
    {
        $this->validarId($id);


        // Consultamos el semillero mediante el repositorio.

        $semillero = $this->repositorio->obtenerPorId($id);


        // Si el repositorio devuelve null,
        // el semillero no existe o esta retirado.

        if ($semillero === null) {

            throw new NoEncontradoExcepcion(
                'Semillero no encontrado.'
            );

        }


        // Devolvemos el objeto encontrado.

        return $semillero;
    }


    // =====================================================
    // 5. VALIDAR IDENTIFICADOR
    //
    // Comprueba que el identificador sea positivo
    // y se encuentre dentro del rango permitido
    // por el tipo de dato INT de la base.
    // =====================================================

    private function validarId(int $id): void
    {
        if ($id <= 0 || $id > 2147483647) {

            throw new InvalidArgumentException(
                'El identificador debe ser un número entero positivo válido.'
            );

        }
    }
}