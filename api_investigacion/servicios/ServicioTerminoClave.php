<?php
declare(strict_types=1);

require_once __DIR__ . '/IServicioTerminoClave.php';
require_once __DIR__ . '/../repositorios/IRepositorioTerminoClave.php';
require_once __DIR__ . '/../modelos/TerminoClave.php';
require_once __DIR__ . '/../excepciones/ValidacionExcepcion.php';
require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

class ServicioTerminoClave implements IServicioTerminoClave 
{
    private IRepositorioTerminoClave $repositorio;

    public function __construct(IRepositorioTerminoClave $repositorio) {
        $this->repositorio = $repositorio;
    }

    public function listar(int $limite): array { return $this->repositorio->listar($limite); }

    public function obtenerPorId(string $termino): TerminoClave {
        $entidad = $this->repositorio->obtenerPorId($termino);
        if ($entidad === null) throw new NoEncontradoExcepcion();
        return $entidad;
    }

    public function crear(array $datos): void {
        // EXPLICACIÓN CLASE: El término inglés es el ÚNICO campo opcional del proyecto. No lo validamos como obligatorio[cite: 9].
        $errores = $this->validarObligatorios($datos, ['termino']);
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $ingles = $datos['termino_ingles'] ?? null;
        $entidad = new TerminoClave($datos['termino'], $ingles);
        $this->repositorio->crear($entidad);
    }

    public function reemplazar(string $termino, array $datos): void {
        // EXPLICACIÓN CLASE: Según el contrato, en PUT solo se envía termino_ingles porque el "termino" ya va en la URL[cite: 10].
        $actual = $this->obtenerPorId($termino);
        $ingles = $datos['termino_ingles'] ?? null;
        
        $entidad = new TerminoClave($actual->getTermino(), $ingles);
        $this->repositorio->actualizar($entidad);
    }

    public function cambiarParcial(string $termino, array $datos): void {
        if (empty($datos)) throw new InvalidArgumentException("El cuerpo de la petición no puede estar vacío.");

        $actual = $this->obtenerPorId($termino);
        $ingles = array_key_exists('termino_ingles', $datos) ? $datos['termino_ingles'] : $actual->getTerminoIngles();

        $entidad = new TerminoClave($actual->getTermino(), $ingles);
        $this->repositorio->actualizar($entidad);
    }

    public function retirar(string $termino): void {
        if (!$this->repositorio->retirar($termino)) throw new NoEncontradoExcepcion();
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