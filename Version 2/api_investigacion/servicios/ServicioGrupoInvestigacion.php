<?php

declare(strict_types=1);

/**
 * ARCHIVO: ServicioGrupoInvestigacion.php
 *
 * QUE ES:
 * Servicio encargado de coordinar las operaciones
 * de los grupos de investigacion y sus semilleros.
 *
 * POR QUE ESTA AQUI:
 * Permite separar las operaciones del negocio
 * del acceso a la base de datos y del controlador.
 *
 * QUE NO HACE:
 * No ejecuta consultas SQL.
 * No establece conexiones con MariaDB.
 * No ejecuta procedimientos almacenados directamente.
 * No administra transacciones.
 * No repite las reglas de integridad de la base de datos.
 */


require_once __DIR__ . '/IServicioGrupoInvestigacion.php';

require_once __DIR__ . '/../repositorios/IRepositorioGrupoInvestigacion.php';

require_once __DIR__ . '/../modelos/GrupoInvestigacion.php';


class ServicioGrupoInvestigacion
    implements IServicioGrupoInvestigacion
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // El servicio depende de una interfaz y no
    // directamente del repositorio concreto de MariaDB.
    // =====================================================

    private IRepositorioGrupoInvestigacion $repositorio;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe el repositorio mediante inyeccion
    // de dependencias.
    // =====================================================

    public function __construct(
        IRepositorioGrupoInvestigacion $repositorio
    ) {
        $this->repositorio = $repositorio;
    }


    // =====================================================
    // 3. LISTAR GRUPOS DE INVESTIGACION
    //
    // Solicita al repositorio todos los grupos activos.
    // El listado solamente contiene el encabezado.
    // =====================================================

    public function obtenerTodos(): array
    {
        return $this->repositorio->obtenerTodos();
    }


    // =====================================================
    // 4. CONSULTAR UN GRUPO
    //
    // Recupera el encabezado del grupo junto con
    // todos sus semilleros activos.
    //
    // Si el grupo no existe, el repositorio traduce
    // la excepcion generada por el procedimiento.
    // =====================================================

    public function obtenerPorId(
        int $id
    ): GrupoInvestigacion {

        $this->validarId($id);

        return $this->repositorio->obtenerPorId($id);
    }


    // =====================================================
    // 5. CREAR GRUPO DE INVESTIGACION
    //
    // Construye un objeto con los datos del grupo
    // y sus semilleros.
    //
    // El identificador es null porque MariaDB
    // lo genera automaticamente.
    //
    // No se comprueba previamente la existencia
    // de la universidad. La base defiende la integridad.
    // =====================================================

    public function crear(
        string $gruplac,
        string $categoria,
        string $convocatoria,
        string $ambito,
        int $universidadId,
        array $detalle
    ): GrupoInvestigacion {

        $grupo = new GrupoInvestigacion(
            null,
            $gruplac,
            $categoria,
            $convocatoria,
            $ambito,
            $universidadId,
            $detalle
        );

        return $this->repositorio->crear($grupo);
    }


    // =====================================================
    // 6. REEMPLAZAR GRUPO DE INVESTIGACION
    //
    // Construye un objeto con el identificador existente,
    // el nuevo encabezado y todos los semilleros.
    //
    // El procedimiento almacenado se encarga de retirar
    // los semilleros anteriores y registrar los nuevos.
    // =====================================================

    public function reemplazar(
        int $id,
        string $gruplac,
        string $categoria,
        string $convocatoria,
        string $ambito,
        int $universidadId,
        array $detalle
    ): GrupoInvestigacion {

        $this->validarId($id);

        $grupo = new GrupoInvestigacion(
            $id,
            $gruplac,
            $categoria,
            $convocatoria,
            $ambito,
            $universidadId,
            $detalle
        );

        return $this->repositorio->reemplazar($grupo);
    }


    // =====================================================
    // 7. RETIRAR GRUPO DE INVESTIGACION
    //
    // Solicita el borrado logico del grupo.
    //
    // El procedimiento almacenado verifica si existen
    // semilleros activos.
    //
    // Esta regla no se repite en el servicio.
    // =====================================================

    public function retirar(int $id): int
    {
        $this->validarId($id);

        return $this->repositorio->retirar($id);
    }


    // =====================================================
    // 8. VALIDAR IDENTIFICADOR
    //
    // El identificador debe ser un entero positivo
    // dentro del rango permitido por MariaDB.
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