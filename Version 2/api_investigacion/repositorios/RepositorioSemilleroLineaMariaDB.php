<?php

declare(strict_types=1);

/**
 * ARCHIVO: RepositorioSemilleroLineaMariaDB.php
 *
 * QUE ES:
 * Repositorio encargado de administrar las relaciones
 * entre semilleros y lineas de investigacion en MariaDB.
 *
 * POR QUE ESTA AQUI:
 * Permite separar las operaciones de almacenamiento
 * de las responsabilidades del servicio y controlador.
 *
 * QUE NO HACE:
 * No procesa peticiones HTTP.
 * No genera respuestas para el usuario.
 * No comprueba previamente las llaves foraneas.
 * No modifica relaciones existentes.
 * No establece directamente la conexion con MariaDB.
 */


require_once __DIR__ . '/IRepositorioSemilleroLinea.php';

require_once __DIR__ . '/../modelos/SemilleroLinea.php';

require_once __DIR__ . '/errores_de_integridad.php';


class RepositorioSemilleroLineaMariaDB
    implements IRepositorioSemilleroLinea
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
    // 3. LISTAR RELACIONES
    //
    // Recupera las parejas registradas en
    // la tabla semillero_linea.
    //
    // Esta tabla no contiene una columna activo,
    // porque utiliza borrado fisico.
    // =====================================================

    public function obtenerTodos(int $limite): array
    {
        try {

            $sql = 'SELECT semillero_id, linea_id
                    FROM semillero_linea
                    ORDER BY semillero_id ASC, linea_id ASC
                    LIMIT ?';


            // Preparar la consulta.

            $sentencia = $this->conexion->prepare($sql);


            // Asignar el limite como numero entero.

            $sentencia->bindValue(
                1,
                $limite,
                PDO::PARAM_INT
            );


            // Ejecutar la consulta.

            $sentencia->execute();


            // Recuperar los registros encontrados.

            $filas = $sentencia->fetchAll(PDO::FETCH_ASSOC);

            $sentencia->closeCursor();


            // Arreglo para almacenar los objetos.

            $relaciones = [];


            // Convertir cada fila en un objeto
            // SemilleroLinea.

            foreach ($filas as $fila) {

                $relaciones[] = new SemilleroLinea(
                    (int) $fila['semillero_id'],
                    (int) $fila['linea_id']
                );

            }

            return $relaciones;

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 4. ASIGNAR RELACION
    //
    // Inserta una pareja de semillero y linea.
    //
    // MariaDB comprueba:
    //
    // - Que la pareja no exista previamente.
    // - Que el semillero exista.
    // - Que la linea de investigacion exista.
    //
    // No realizamos consultas previas para comprobarlo.
    // =====================================================

    public function asignar(
        SemilleroLinea $semilleroLinea
    ): int {

        try {

            $sql = 'INSERT INTO semillero_linea (
                        semillero_id,
                        linea_id
                    )
                    VALUES (?, ?)';


            // Preparar la consulta.

            $sentencia = $this->conexion->prepare($sql);


            // Ejecutar la insercion con las dos claves.

            $sentencia->execute([
                $semilleroLinea->getSemilleroId(),
                $semilleroLinea->getLineaId()
            ]);


            // Obtener la cantidad de filas insertadas.

            $filas = $sentencia->rowCount();

            $sentencia->closeCursor();

            return $filas;

        } catch (PDOException $error) {

            // Traducir los errores de integridad.
            //
            // 1062: Pareja duplicada.
            // 1452: Llave foranea inexistente.

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 5. RETIRAR RELACION
    //
    // Elimina fisicamente una pareja utilizando
    // las dos claves de la llave primaria compuesta.
    //
    // Si la pareja no existe, rowCount devuelve cero.
    // El servicio se encargara de manejar ese resultado.
    // =====================================================

    public function retirar(
        int $semilleroId,
        int $lineaId
    ): int {

        try {

            $sql = 'DELETE FROM semillero_linea
                    WHERE semillero_id = ?
                    AND linea_id = ?';


            // Preparar la consulta.

            $sentencia = $this->conexion->prepare($sql);


            // Ejecutar la eliminacion de la pareja.

            $sentencia->execute([
                $semilleroId,
                $lineaId
            ]);


            // Obtener la cantidad de filas eliminadas.

            $filas = $sentencia->rowCount();

            $sentencia->closeCursor();

            return $filas;

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }
}