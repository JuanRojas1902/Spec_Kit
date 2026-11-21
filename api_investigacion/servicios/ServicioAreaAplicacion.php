<?php
declare(strict_types=1);

require_once __DIR__ . '/IServicioAreaAplicacion.php';
require_once __DIR__ . '/../repositorios/IRepositorioAreaAplicacion.php';
require_once __DIR__ . '/../modelos/AreaAplicacion.php';
require_once __DIR__ . '/../excepciones/ValidacionExcepcion.php';
require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

class ServicioAreaAplicacion implements IServicioAreaAplicacion 
{
    private IRepositorioAreaAplicacion $repositorio;

    public function __construct(IRepositorioAreaAplicacion $repositorio) {
        $this->repositorio = $repositorio;
    }

    public function listar(int $limite): array { return $this->repositorio->listar($limite); }

    public function obtenerPorId(int $id): AreaAplicacion {
        $entidad = $this->repositorio->obtenerPorId($id);
        if ($entidad === null) throw new NoEncontradoExcepcion();
        return $entidad;
    }

    public function crear(array $datos): void {
        $errores = $this->validarObligatorios($datos, ['id', 'nombre']);
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $entidad = new AreaAplicacion((int)$datos['id'], $datos['nombre']);
        $this->repositorio->crear($entidad);
    }

    public function reemplazar(int $id, array $datos): void {
        $errores = $this->validarObligatorios($datos, ['nombre']);
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $actual = $this->obtenerPorId($id);
        $entidad = new AreaAplicacion($actual->getId(), $datos['nombre']);
        $this->repositorio->actualizar($entidad);
    }

    public function cambiarParcial(int $id, array $datos): void {
        if (empty($datos)) throw new InvalidArgumentException("El cuerpo de la petición no puede estar vacío.");

        $actual = $this->obtenerPorId($id);
        $nombre = $datos['nombre'] ?? $actual->getNombre();

        if (isset($datos['nombre']) && trim((string)$datos['nombre']) === '') {
            throw new ValidacionExcepcion(["El campo nombre no puede estar vacío."]);
        }

        $entidad = new AreaAplicacion($actual->getId(), $nombre);
        $this->repositorio->actualizar($entidad);
    }

    public function retirar(int $id): void {
        if (!$this->repositorio->retirar($id)) throw new NoEncontradoExcepcion();
    }

    private function validarObligatorios(array $datos, array $requeridos): array {
        $errores = [];
        foreach ($requeridos as $campo) {
            if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
                $errores[] = "El campo $campo es obligatorio.";
            }
        }
        return $errores;
    }
}