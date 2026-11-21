<?php

declare(strict_types=1);

/**
 * ARCHIVO: ServicioDocente.php
 *
 * QUE ES:
 * Servicio que contiene las reglas de negocio
 * relacionadas con los docentes investigadores.
 *
 * POR QUE ESTA AQUI:
 * Permite separar las reglas de negocio del almacenamiento
 * y de la comunicacion con el usuario.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con la base de datos.
 * No genera respuestas JSON.
 * No administra pantallas ni formularios.
 */


// Importamos las interfaces, el modelo y la excepcion.

require_once __DIR__ . '/IServicioDocente.php';

require_once __DIR__ . '/../repositorios/IRepositorioDocente.php';

require_once __DIR__ . '/../modelos/Docente.php';

require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';


class ServicioDocente implements IServicioDocente
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // El servicio depende de una interfaz y no directamente
    // del repositorio concreto de MariaDB.
    // =====================================================

    private IRepositorioDocente $repositorio;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe el repositorio mediante inyeccion de
    // dependencias.
    // =====================================================

    public function __construct(
        IRepositorioDocente $repositorio
    ) {
        $this->repositorio = $repositorio;
    }


    // =====================================================
    // 3. LISTAR DOCENTES
    //
    // El limite debe ser mayor que cero.
    // El valor predeterminado es de 1000 registros.
    // =====================================================

    public function obtenerTodos(int $limite = 1000): array
    {
        if ($limite <= 0) {

            throw new InvalidArgumentException(
                'El límite debe ser mayor que cero.'
            );

        }

        return $this->repositorio->obtenerTodos($limite);
    }


    // =====================================================
    // 4. CONSULTAR UN DOCENTE
    //
    // Busca un docente mediante su cedula.
    // Si no existe o esta retirado, lanza una excepcion.
    // =====================================================

    public function obtenerPorId(int $cedula): Docente
    {
        $this->validarCedula($cedula);

        $docente = $this->repositorio->obtenerPorId($cedula);

        if ($docente === null) {

            throw new NoEncontradoExcepcion(
                'Docente no encontrado.'
            );

        }

        return $docente;
    }


    // =====================================================
    // 5. CREAR DOCENTE
    //
    // Construye el objeto Docente y solicita al repositorio
    // que lo almacene.
    //
    // No comprueba previamente si la cedula existe.
    // La base de datos se encarga de esa restriccion.
    // =====================================================

    public function crear(
        int $cedula,
        string $nombres,
        string $apellidos
    ): int {

        $this->validarCedula($cedula);

        $docente = new Docente(
            $cedula,
            $nombres,
            $apellidos
        );

        return $this->repositorio->crear($docente);
    }


    // =====================================================
    // 6. REEMPLAZAR DOCENTE
    //
    // Reemplaza todos los campos modificables.
    // La cedula permanece sin cambios.
    // =====================================================

    public function reemplazar(
        int $cedula,
        string $nombres,
        string $apellidos
    ): int {

        $this->validarCedula($cedula);

        $docente = new Docente(
            $cedula,
            $nombres,
            $apellidos
        );

        $filasAfectadas = $this->repositorio->reemplazar(
            $docente
        );

        return $this->verificarActualizacion(
            $cedula,
            $filasAfectadas
        );
    }


    // =====================================================
    // 7. ACTUALIZAR PARCIALMENTE
    //
    // Modifica exclusivamente los campos permitidos
    // que fueron recibidos.
    //
    // No permite cambiar la cedula ni el estado activo.
    // =====================================================

    public function actualizarParcial(
        int $cedula,
        array $campos
    ): int {

        $this->validarCedula($cedula);

        // Lista de campos autorizados para modificar.

        $camposPermitidos = [
            'nombres' => true,
            'apellidos' => true
        ];


        // Conservamos solamente los campos autorizados.

        $camposValidos = array_intersect_key(
            $campos,
            $camposPermitidos
        );


        // Una actualizacion parcial necesita al menos
        // un campo modificable.

        if (empty($camposValidos)) {

            throw new InvalidArgumentException(
                'Debe indicar al menos un campo para actualizar.'
            );

        }


        $filasAfectadas = $this->repositorio->actualizarParcial(
            $cedula,
            $camposValidos
        );

        return $this->verificarActualizacion(
            $cedula,
            $filasAfectadas
        );
    }


    // =====================================================
    // 8. RETIRAR DOCENTE
    //
    // Solicita al repositorio realizar el borrado logico.
    //
    // Se permite retirar un docente que participa en
    // grupos o semilleros, conservando sus relaciones.
    // =====================================================

    public function retirar(int $cedula): int
    {
        $this->validarCedula($cedula);

        $filasAfectadas = $this->repositorio->retirar(
            $cedula
        );

        if ($filasAfectadas === 0) {

            throw new NoEncontradoExcepcion(
                'El docente no existe o ya fue retirado.'
            );

        }

        return $filasAfectadas;
    }


    // =====================================================
    // 9. VALIDAR CEDULA
    //
    // Regla de negocio:
    // La cedula debe ser un numero entero mayor que cero.
    // =====================================================

    private function validarCedula(int $cedula): void
    {
        if ($cedula <= 0) {

            throw new InvalidArgumentException(
                'La cédula debe ser un número mayor que cero.'
            );

        }
    }


    // =====================================================
    // 10. VERIFICAR RESULTADO DE UNA ACTUALIZACION
    //
    // Una actualizacion puede afectar cero filas cuando
    // los datos nuevos son iguales a los existentes.
    //
    // Por eso, despues de intentar actualizar, comprobamos
    // si realmente existe el docente activo.
    // =====================================================

    private function verificarActualizacion(
        int $cedula,
        int $filasAfectadas
    ): int {

        if ($filasAfectadas === 0) {

            $docente = $this->repositorio->obtenerPorId(
                $cedula
            );

            if ($docente === null) {

                throw new NoEncontradoExcepcion(
                    'Docente no encontrado.'
                );

            }
        }

        return $filasAfectadas;
    }
}