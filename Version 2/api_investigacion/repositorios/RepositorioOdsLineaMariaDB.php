<?php

declare(strict_types=1);

/**
 * ARCHIVO: RepositorioOdsLineaMariaDB.php
 *
 * QUE ES:
 * Repositorio encargado de administrar las relaciones
 * entre objetivos de desarrollo sostenible y lineas
 * de investigacion en MariaDB.
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


require_once __DIR__ . '/IRepositorioOdsLinea.php';

require_once __DIR__ . '/../modelos/OdsLinea.php';

require_once __DIR__ . '/errores_de_integridad.php';


class RepositorioOdsLineaMariaDB
    implements IRepositorioOdsLinea
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
    // la tabla ods_linea.
    //
    // Esta tabla no contiene una columna activo,
    // porque utiliza borrado fisico.
    // =====================================================

    public function obtenerTodos(int $limite): array
    {
        try {

            $sql = 'SELECT ods_id, linea_id
                    FROM ods_linea
                    ORDER BY ods_id ASC, linea_id ASC
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


            // Convertir cada fila en un objeto OdsLinea.

            foreach ($filas as $fila) {

                $relaciones[] = new OdsLinea(
                    (int) $fila['ods_id'],
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
    // Inserta una pareja de objetivo de desarrollo
    // sostenible y linea de investigacion.
    //
    // MariaDB comprueba:
    //
    // - Que la pareja no exista previamente.
    // - Que el objetivo de desarrollo sostenible exista.
    // - Que la linea de investigacion exista.
    //
    // No realizamos consultas previas para comprobarlo.
    // =====================================================

    public function asignar(
        OdsLinea $odsLinea
    ): int {

        try {

            $sql = 'INSERT INTO ods_linea (
                        ods_id,
                        linea_id
                    )
                    VALUES (?, ?)';


            // Preparar la consulta.

            $sentencia = $this->conexion->prepare($sql);


            // Ejecutar la insercion con las dos claves.

            $sentencia->execute([
                $odsLinea->getOdsId(),
                $odsLinea->getLineaId()
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
        int $odsId,
        int $lineaId
    ): int {

        try {

            $sql = 'DELETE FROM ods_linea
                    WHERE ods_id = ?
                    AND linea_id = ?';


            // Preparar la consulta.

            $sentencia = $this->conexion->prepare($sql);


            // Ejecutar la eliminacion de la pareja.

            $sentencia->execute([
                $odsId,
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