<?php

declare(strict_types=1);

/**
 * ARCHIVO: RepositorioGrupoInvestigacionMariaDB.php
 *
 * QUE ES:
 * Repositorio que administra el maestro-detalle
 * de grupos de investigacion y semilleros.
 *
 * POR QUE ESTA AQUI:
 * Permite ejecutar los procedimientos almacenados
 * de MariaDB sin mezclar las responsabilidades
 * del servicio y del controlador.
 *
 * QUE NO HACE:
 * No procesa peticiones HTTP.
 * No genera respuestas JSON para el usuario.
 * No comprueba previamente las llaves foraneas.
 * No administra directamente las transacciones.
 * No repite las reglas de los procedimientos.
 */

require_once __DIR__ . '/IRepositorioGrupoInvestigacion.php';

require_once __DIR__ . '/../modelos/GrupoInvestigacion.php';

require_once __DIR__ . '/errores_de_integridad.php';


class RepositorioGrupoInvestigacionMariaDB
    implements IRepositorioGrupoInvestigacion
{
    // =====================================================
    // 1. ATRIBUTO
    // Almacena la conexion con MariaDB.
    // =====================================================

    private PDO $conexion;


    // =====================================================
    // 2. CONSTRUCTOR
    // Recibe la conexion mediante inyeccion de dependencias.
    // =====================================================

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }


    // =====================================================
    // 3. LISTAR GRUPOS
    //
    // Ejecuta el procedimiento que devuelve los grupos
    // activos sin incluir sus semilleros.
    //
    // Este procedimiento no recibe un limite.
    // =====================================================

    public function obtenerTodos(): array
    {
        try {

            $sentencia = $this->conexion->prepare(
                'CALL sp_listar_grupo_investigacion()'
            );

            $sentencia->execute();

            try {

                $filas = $sentencia->fetchAll(PDO::FETCH_ASSOC);

                $grupos = [];

                foreach ($filas as $fila) {

                    $grupos[] = $this->construirGrupo($fila);

                }

                return $grupos;

            } finally {

                $sentencia->closeCursor();

            }

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 4. CONSULTAR UN GRUPO
    //
    // El procedimiento devuelve dos resultados:
    //
    // Primer resultado: encabezado del grupo.
    // Segundo resultado: semilleros asociados.
    // =====================================================

    public function obtenerPorId(
        int $id
    ): GrupoInvestigacion {

        try {

            $sentencia = $this->conexion->prepare(
                'CALL sp_consultar_grupo_investigacion(?)'
            );

            $sentencia->execute([$id]);

            try {

                return $this->leerGrupoCompleto($sentencia);

            } finally {

                $sentencia->closeCursor();

            }

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 5. CREAR GRUPO
    //
    // Envia los datos principales del grupo y sus
    // semilleros en una sola llamada.
    //
    // El detalle se convierte en un parametro JSON.
    //
    // El procedimiento administra la transaccion
    // y genera automaticamente los identificadores.
    // =====================================================

    public function crear(
        GrupoInvestigacion $grupo
    ): GrupoInvestigacion {

        $detalleJson = json_encode(
            $grupo->getDetalle(),
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        try {

            $sentencia = $this->conexion->prepare(
                'CALL sp_crear_grupo_investigacion(
                    ?, ?, ?, ?, ?, ?
                )'
            );

            $sentencia->execute([
                $grupo->getGruplac(),
                $grupo->getCategoria(),
                $grupo->getConvocatoria(),
                $grupo->getAmbito(),
                $grupo->getUniversidadId(),
                $detalleJson
            ]);

            try {

                return $this->leerGrupoCompleto($sentencia);

            } finally {

                $sentencia->closeCursor();

            }

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 6. REEMPLAZAR GRUPO
    //
    // Actualiza el encabezado y reemplaza todos
    // los semilleros asociados.
    //
    // Los semilleros anteriores se retiran logicamente.
    // Los nuevos reciben identificadores diferentes.
    // =====================================================

    public function reemplazar(
        GrupoInvestigacion $grupo
    ): GrupoInvestigacion {

        $id = $grupo->getId();

        if ($id === null) {

            throw new InvalidArgumentException(
                'Debe indicar el identificador del grupo.'
            );

        }

        $detalleJson = json_encode(
            $grupo->getDetalle(),
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        try {

            $sentencia = $this->conexion->prepare(
                'CALL sp_actualizar_grupo_investigacion(
                    ?, ?, ?, ?, ?, ?, ?
                )'
            );

            $sentencia->execute([
                $id,
                $grupo->getGruplac(),
                $grupo->getCategoria(),
                $grupo->getConvocatoria(),
                $grupo->getAmbito(),
                $grupo->getUniversidadId(),
                $detalleJson
            ]);

            try {

                return $this->leerGrupoCompleto($sentencia);

            } finally {

                $sentencia->closeCursor();

            }

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 7. RETIRAR GRUPO
    //
    // Solicita el borrado logico al procedimiento.
    //
    // Si existen semilleros activos, MariaDB lanza 45000.
    // Si el grupo no existe, MariaDB lanza 45001.
    //
    // No repetimos estas comprobaciones en PHP.
    // =====================================================

    public function retirar(int $id): int
    {
        try {

            $sentencia = $this->conexion->prepare(
                'CALL sp_eliminar_grupo_investigacion(?)'
            );

            $sentencia->execute([$id]);

            $sentencia->closeCursor();

            // Si el procedimiento termina sin excepciones,
            // significa que retiro correctamente un grupo.

            return 1;

        } catch (PDOException $error) {

            traducirErrorDeIntegridad($error);

        }
    }


    // =====================================================
    // 8. LEER GRUPO COMPLETO
    //
    // Recupera los dos resultados de un procedimiento.
    //
    // fetch(): recupera el encabezado.
    // nextRowset(): pasa al siguiente resultado.
    // fetchAll(): recupera los semilleros.
    // =====================================================

    private function leerGrupoCompleto(
        PDOStatement $sentencia
    ): GrupoInvestigacion {

        // Primer resultado: encabezado del grupo.

        $fila = $sentencia->fetch(PDO::FETCH_ASSOC);

        if ($fila === false) {

            throw new NoEncontradoExcepcion(
                'Grupo no encontrado.'
            );

        }


        // Avanzamos al segundo resultado.

        if (!$sentencia->nextRowset()) {

            throw new RuntimeException(
                'No se recibió el resultado del detalle.'
            );

        }


        // Segundo resultado: semilleros asociados.

        $filasDetalle = $sentencia->fetchAll(
            PDO::FETCH_ASSOC
        );

        $detalle = [];

        foreach ($filasDetalle as $semillero) {

            $detalle[] = [
                'id' => (int) $semillero['id'],
                'nombre' => $semillero['nombre']
            ];

        }


        // Construimos el objeto con toda la informacion.

        return $this->construirGrupo(
            $fila,
            $detalle
        );
    }


    // =====================================================
    // 9. CONSTRUIR OBJETO GRUPO
    //
    // Convierte los resultados de MariaDB en un objeto
    // del modelo GrupoInvestigacion.
    // =====================================================

    private function construirGrupo(
        array $fila,
        array $detalle = []
    ): GrupoInvestigacion {

        return new GrupoInvestigacion(
            (int) $fila['id'],
            $fila['gruplac'],
            $fila['categoria'],
            $fila['convocatoria'],
            $fila['ambito'],
            (int) $fila['universidad_id'],
            $detalle
        );
    }
}