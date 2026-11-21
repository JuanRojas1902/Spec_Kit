<?php

declare(strict_types=1);

/**
 * ARCHIVO: RepositorioDocenteMariaDB.php
 *
 * QUE ES:
 * Repositorio encargado de almacenar, consultar,
 * actualizar y retirar docentes en MariaDB.
 *
 * POR QUE ESTA AQUI:
 * Permite separar el acceso a la base de datos
 * de las reglas de negocio y del controlador.
 *
 * QUE NO HACE:
 * No procesa peticiones HTTP.
 * No genera respuestas JSON.
 * No administra formularios ni pantallas.
 * No comprueba previamente las llaves foraneas.
 */


// Importamos la interfaz, el modelo y el traductor de errores.

require_once __DIR__ . '/IRepositorioDocente.php';

require_once __DIR__ . '/../modelos/Docente.php';

require_once __DIR__ . '/errores_de_integridad.php';


class RepositorioDocenteMariaDB implements IRepositorioDocente
{
    // =====================================================
    // 1. ATRIBUTO
    // Almacena la conexion con la base de datos.
    // =====================================================

    private PDO $conexion;


    // =====================================================
    // 2. CONSTRUCTOR
    //
    // Recibe la conexion mediante inyeccion de dependencias.
    // El repositorio no crea su propia conexion.
    // =====================================================

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }


    // =====================================================
    // 3. LISTAR DOCENTES
    //
    // Consulta exclusivamente los docentes activos.
    // El limite controla la cantidad maxima de registros.
    // =====================================================

    public function obtenerTodos(int $limite): array
    {
        $sql = '
            SELECT cedula, nombres, apellidos
            FROM docente
            WHERE activo = 1
            ORDER BY cedula
            LIMIT ?
        ';

        $sentencia = $this->conexion->prepare($sql);

        // Indicamos que el limite debe ser un numero entero.

        $sentencia->bindValue(1, $limite, PDO::PARAM_INT);

        $sentencia->execute();

        $filas = $sentencia->fetchAll(PDO::FETCH_ASSOC);

        $docentes = [];

        // Convertimos cada fila en un objeto Docente.

        foreach ($filas as $fila) {

            $docentes[] = new Docente(
                (int) $fila['cedula'],
                $fila['nombres'],
                $fila['apellidos']
            );

        }

        return $docentes;
    }


    // =====================================================
    // 4. CONSULTAR UN DOCENTE
    //
    // Busca un docente activo mediante su cedula.
    // Si no existe, devuelve null.
    // =====================================================

    public function obtenerPorId(int $cedula): ?Docente
    {
        $sql = '
            SELECT cedula, nombres, apellidos
            FROM docente
            WHERE cedula = ?
              AND activo = 1
        ';

        $sentencia = $this->conexion->prepare($sql);

        $sentencia->execute([$cedula]);

        $fila = $sentencia->fetch(PDO::FETCH_ASSOC);

        if ($fila === false) {
            return null;
        }

        return new Docente(
            (int) $fila['cedula'],
            $fila['nombres'],
            $fila['apellidos']
        );
    }


    // =====================================================
    // 5. CREAR DOCENTE
    //
    // Registra un nuevo docente.
    // MariaDB se encarga de verificar la llave primaria.
    // Si la cedula esta repetida, se traduce el error.
    // =====================================================

    public function crear(Docente $docente): int
    {
        $sql = '
            INSERT INTO docente (
                cedula,
                nombres,
                apellidos
            )
            VALUES (?, ?, ?)
        ';

        try {

            $sentencia = $this->conexion->prepare($sql);

            $sentencia->execute([
                $docente->getCedula(),
                $docente->getNombres(),
                $docente->getApellidos()
            ]);

            return $sentencia->rowCount();

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 6. REEMPLAZAR DOCENTE
    //
    // Actualiza todos los campos modificables.
    // La cedula y el estado activo no se modifican.
    // =====================================================

    public function reemplazar(Docente $docente): int
    {
        $sql = '
            UPDATE docente
            SET nombres = ?,
                apellidos = ?
            WHERE cedula = ?
              AND activo = 1
        ';

        try {

            $sentencia = $this->conexion->prepare($sql);

            $sentencia->execute([
                $docente->getNombres(),
                $docente->getApellidos(),
                $docente->getCedula()
            ]);

            return $sentencia->rowCount();

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 7. ACTUALIZAR PARCIALMENTE
    //
    // Modifica exclusivamente los campos recibidos.
    // Utiliza una lista blanca de columnas permitidas.
    // No permite modificar la cedula ni el estado activo.
    // =====================================================

    public function actualizarParcial(
        int $cedula,
        array $campos
    ): int {

        $columnas = [];

        $valores = [];


        // Si se recibieron los nombres, se agregan
        // a las columnas que deben actualizarse.

        if (array_key_exists('nombres', $campos)) {

            $columnas[] = 'nombres = ?';

            $valores[] = $campos['nombres'];

        }


        // Si se recibieron los apellidos, se agregan
        // a las columnas que deben actualizarse.

        if (array_key_exists('apellidos', $campos)) {

            $columnas[] = 'apellidos = ?';

            $valores[] = $campos['apellidos'];

        }


        // Evitamos construir una consulta sin columnas.
        // El servicio debe validar este caso previamente.

        if (empty($columnas)) {

            throw new InvalidArgumentException(
                'No hay campos para actualizar.'
            );

        }


        // La cedula corresponde al ultimo parametro.

        $valores[] = $cedula;


        // implode une las columnas con una coma.
        // Los nombres de las columnas son fijos y seguros.

        $sql = '
            UPDATE docente
            SET ' . implode(', ', $columnas) . '
            WHERE cedula = ?
              AND activo = 1
        ';


        try {

            $sentencia = $this->conexion->prepare($sql);

            $sentencia->execute($valores);

            return $sentencia->rowCount();

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 8. RETIRAR DOCENTE
    //
    // Realiza un borrado logico.
    // El docente permanece almacenado en MariaDB,
    // pero deja de aparecer en los listados activos.
    // =====================================================

    public function retirar(int $cedula): int
    {
        $sql = '
            UPDATE docente
            SET activo = 0
            WHERE cedula = ?
              AND activo = 1
        ';

        try {

            $sentencia = $this->conexion->prepare($sql);

            $sentencia->execute([$cedula]);

            return $sentencia->rowCount();

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }
}