<?php
declare(strict_types=1);

require_once __DIR__ . '/IServicioObjetivoDesarrolloSostenible.php';
require_once __DIR__ . '/../repositorios/IRepositorioObjetivoDesarrolloSostenible.php';
require_once __DIR__ . '/../modelos/ObjetivoDesarrolloSostenible.php';
require_once __DIR__ . '/../excepciones/ValidacionExcepcion.php';
require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

class ServicioObjetivoDesarrolloSostenible implements IServicioObjetivoDesarrolloSostenible 
{
    private IRepositorioObjetivoDesarrolloSostenible $repositorio;

    public function __construct(IRepositorioObjetivoDesarrolloSostenible $repositorio) {
        $this->repositorio = $repositorio;
    }

    public function listar(int $limite): array { return $this->repositorio->listar($limite); }

    public function obtenerPorId(int $id): ObjetivoDesarrolloSostenible {
        $entidad = $this->repositorio->obtenerPorId($id);
        if ($entidad === null) throw new NoEncontradoExcepcion();
        return $entidad;
    }

    public function crear(array $datos): void {
        $errores = $this->validarObligatorios($datos, ['id', 'nombre', 'categoria']);
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $entidad = new ObjetivoDesarrolloSostenible((int)$datos['id'], $datos['nombre'], $datos['categoria']);
        $this->repositorio->crear($entidad);
    }

    public function reemplazar(int $id, array $datos): void {
        $errores = $this->validarObligatorios($datos, ['nombre', 'categoria']);
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $actual = $this->obtenerPorId($id);
        $entidad = new ObjetivoDesarrolloSostenible($actual->getId(), $datos['nombre'], $datos['categoria']);
        $this->repositorio->actualizar($entidad);
    }

    public function cambiarParcial(int $id, array $datos): void {
        if (empty($datos)) throw new InvalidArgumentException("El cuerpo de la petición no puede estar vacío.");

        $actual = $this->obtenerPorId($id);
        $nombre = $datos['nombre'] ?? $actual->getNombre();
        $categoria = $datos['categoria'] ?? $actual->getCategoria();

        $errores = [];
        foreach ($datos as $campo => $valor) {
            if (trim((string)$valor) === '') $errores[] = "El campo $campo no puede estar vacío.";
        }
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $entidad = new ObjetivoDesarrolloSostenible($actual->getId(), $nombre, $categoria);
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