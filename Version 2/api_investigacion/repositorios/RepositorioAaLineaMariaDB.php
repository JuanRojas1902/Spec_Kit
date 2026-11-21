<?php

declare(strict_types=1);

/**
 * ARCHIVO: RepositorioAaLineaMariaDB.php
 *
 * QUE ES:
 * Repositorio encargado de administrar las relaciones
 * entre areas de aplicacion y lineas de investigacion
 * en MariaDB.
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


require_once __DIR__ . '/IRepositorioAaLinea.php';

require_once __DIR__ . '/../modelos/AaLinea.php';

require_once __DIR__ . '/errores_de_integridad.php';


class RepositorioAaLineaMariaDB
    implements IRepositorioAaLinea
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
    // la tabla aa_linea.
    //
    // Esta tabla no contiene una columna activo,
    // porque utiliza borrado fisico.
    // =====================================================

    public function obtenerTodos(int $limite): array
    {
        try {

            $sql = 'SELECT area_aplicacion_id, linea_id
                    FROM aa_linea
                    ORDER BY area_aplicacion_id ASC,
                             linea_id ASC
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


            // Convertir cada fila en un objeto AaLinea.

            foreach ($filas as $fila) {

                $relaciones[] = new AaLinea(
                    (int) $fila['area_aplicacion_id'],
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
    // Inserta una pareja de area de aplicacion
    // y linea de investigacion.
    //
    // MariaDB comprueba:
    //
    // - Que la pareja no exista previamente.
    // - Que el area de aplicacion exista.
    // - Que la linea de investigacion exista.
    //
    // No realizamos consultas previas para comprobarlo.
    // =====================================================

    public function asignar(
        AaLinea $aaLinea
    ): int {

        try {

            $sql = 'INSERT INTO aa_linea (
                        area_aplicacion_id,
                        linea_id
                    )
                    VALUES (?, ?)';


            // Preparar la consulta.

            $sentencia = $this->conexion->prepare($sql);


            // Ejecutar la insercion con las dos claves.

            $sentencia->execute([
                $aaLinea->getAreaAplicacionId(),
                $aaLinea->getLineaId()
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
        int $areaAplicacionId,
        int $lineaId
    ): int {

        try {

            $sql = 'DELETE FROM aa_linea
                    WHERE area_aplicacion_id = ?
                    AND linea_id = ?';


            // Preparar la consulta.

            $sentencia = $this->conexion->prepare($sql);


            // Ejecutar la eliminacion de la pareja.

            $sentencia->execute([
                $areaAplicacionId,
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