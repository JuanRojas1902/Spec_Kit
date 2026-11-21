<?php

declare(strict_types=1);

/**
 * ARCHIVO: RepositorioSemilleroMariaDB.php
 *
 * QUE ES:
 * Repositorio encargado de consultar los semilleros
 * de investigacion almacenados en MariaDB.
 *
 * POR QUE ESTA AQUI:
 * Permite separar las consultas a la base de datos
 * de las operaciones del servicio y del controlador.
 *
 * QUE NO HACE:
 * No crea semilleros.
 * No modifica semilleros.
 * No retira semilleros.
 * No implementa reglas de negocio.
 * No procesa peticiones HTTP.
 */


require_once __DIR__ . '/IRepositorioSemillero.php';

require_once __DIR__ . '/../modelos/Semillero.php';


class RepositorioSemilleroMariaDB
    implements IRepositorioSemillero
{
    // =====================================================
    // 1. ATRIBUTO
    //
    // Almacena la conexion PDO con MariaDB.
    // =====================================================

    private PDO $conexion;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe la conexion mediante inyeccion
    // de dependencias.
    // =====================================================

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }


    // =====================================================
    // 3. LISTAR SEMILLEROS ACTIVOS
    //
    // Recupera los semilleros cuyo campo activo es 1.
    //
    // El parametro limite establece la cantidad
    // maxima de registros que se pueden consultar.
    // =====================================================

    public function obtenerTodos(int $limite): array
    {
        $sql = 'SELECT id, nombre, grupo_id
                FROM semillero
                WHERE activo = 1
                ORDER BY id ASC
                LIMIT ?';


        // Preparamos la consulta.

        $sentencia = $this->conexion->prepare($sql);


        // Indicamos que el limite es un numero entero.

        $sentencia->bindValue(
            1,
            $limite,
            PDO::PARAM_INT
        );


        // Ejecutamos la consulta.

        $sentencia->execute();


        // Recuperamos los registros encontrados.

        $filas = $sentencia->fetchAll(PDO::FETCH_ASSOC);

        $sentencia->closeCursor();


        // Arreglo que almacenara los objetos Semillero.

        $semilleros = [];


        // Convertimos cada fila en un objeto.

        foreach ($filas as $fila) {

            $semilleros[] = $this->construirSemillero(
                $fila
            );

        }

        return $semilleros;
    }


    // =====================================================
    // 4. CONSULTAR UN SEMILLERO POR ID
    //
    // Busca un semillero activo mediante su llave primaria.
    //
    // Si no existe o esta retirado, devuelve null.
    // =====================================================

    public function obtenerPorId(int $id): ?Semillero
    {
        $sql = 'SELECT id, nombre, grupo_id
                FROM semillero
                WHERE id = ?
                AND activo = 1';


        // Preparamos la consulta.

        $sentencia = $this->conexion->prepare($sql);


        // Asignamos el identificador como entero.

        $sentencia->bindValue(
            1,
            $id,
            PDO::PARAM_INT
        );


        // Ejecutamos la consulta.

        $sentencia->execute();


        // Recuperamos el registro encontrado.

        $fila = $sentencia->fetch(PDO::FETCH_ASSOC);

        $sentencia->closeCursor();


        // Si no existe un registro activo,
        // devolvemos null al servicio.

        if ($fila === false) {

            return null;

        }


        // Convertimos el registro en un objeto Semillero.

        return $this->construirSemillero($fila);
    }


    // =====================================================
    // 5. CONSTRUIR OBJETO SEMILLERO
    //
    // Convierte una fila recuperada de MariaDB
    // en una instancia del modelo Semillero.
    // =====================================================

    private function construirSemillero(
        array $fila
    ): Semillero {

        return new Semillero(
            (int) $fila['id'],
            $fila['nombre'],
            (int) $fila['grupo_id']
        );
    }
}