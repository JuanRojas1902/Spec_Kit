<?php
declare(strict_types=1);

require_once __DIR__ . '/IServicioLineaInvestigacion.php';
require_once __DIR__ . '/../repositorios/IRepositorioLineaInvestigacion.php';
require_once __DIR__ . '/../modelos/LineaInvestigacion.php';
require_once __DIR__ . '/../excepciones/ValidacionExcepcion.php';
require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

class ServicioLineaInvestigacion implements IServicioLineaInvestigacion 
{
    private IRepositorioLineaInvestigacion $repositorio;

    public function __construct(IRepositorioLineaInvestigacion $repositorio) {
        $this->repositorio = $repositorio;
    }

    public function listar(int $limite): array { return $this->repositorio->listar($limite); }

    public function obtenerPorId(int $id): LineaInvestigacion {
        $entidad = $this->repositorio->obtenerPorId($id);
        if ($entidad === null) throw new NoEncontradoExcepcion();
        return $entidad;
    }

    public function crear(array $datos): void {
        // EXPLICACIÓN CLASE: ¡Ojo! No pedimos el ID en los campos obligatorios. 
        // Esta es la única tabla donde MariaDB genera el ID con AUTO_INCREMENT[cite: 9, 10].
        $errores = $this->validarObligatorios($datos, ['nombre', 'descripcion']);
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        // El ID arranca en null
        $entidad = new LineaInvestigacion(null, $datos['nombre'], $datos['descripcion']);
        $this->repositorio->crear($entidad);
    }

    public function reemplazar(int $id, array $datos): void {
        $errores = $this->validarObligatorios($datos, ['nombre', 'descripcion']);
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $actual = $this->obtenerPorId($id);
        $entidad = new LineaInvestigacion($actual->getId(), $datos['nombre'], $datos['descripcion']);
        $this->repositorio->actualizar($entidad);
    }

    public function cambiarParcial(int $id, array $datos): void {
        if (empty($datos)) throw new InvalidArgumentException("El cuerpo de la petición no puede estar vacío.");

        $actual = $this->obtenerPorId($id);
        $nombre = $datos['nombre'] ?? $actual->getNombre();
        $descripcion = $datos['descripcion'] ?? $actual->getDescripcion();

        $errores = [];
        foreach ($datos as $campo => $valor) {
            if (trim((string)$valor) === '') $errores[] = "El campo $campo no puede estar vacío.";
        }
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $entidad = new LineaInvestigacion($actual->getId(), $nombre, $descripcion);
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