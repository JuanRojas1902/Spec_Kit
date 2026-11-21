<?php
declare(strict_types=1);

require_once __DIR__ . '/IServicioUniversidad.php';
require_once __DIR__ . '/../repositorios/IRepositorioUniversidad.php';
require_once __DIR__ . '/../modelos/Universidad.php';
require_once __DIR__ . '/../excepciones/ValidacionExcepcion.php';
require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

class ServicioUniversidad implements IServicioUniversidad 
{
    private IRepositorioUniversidad $repositorio;

    public function __construct(IRepositorioUniversidad $repositorio) {
        $this->repositorio =$repositorio;
    }

    public function listar(int $limite): array {
        return $this->repositorio->listar($limite);
    }

    public function obtenerPorId(int $id): Universidad {$entidad = $this->repositorio->obtenerPorId($id);
        if ($entidad === null) {
            throw new NoEncontradoExcepcion(); // EXPLICACIÓN CLASE: Si el repo dice que no existe, lanzamos error aquí.
        }
        return $entidad;
    }

    public function crear(array $datos): void {
        // EXPLICACIÓN CLASE: En el POST (crear), el ID es obligatorio porque nosotros lo dictamos, no la base de datos.
        $errores = $this->validarObligatorios($datos, ['id', 'nombre', 'tipo', 'ciudad']);
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $entidad = new Universidad((int)$datos['id'],$datos['nombre'], $datos['tipo'],$datos['ciudad']);
        $this->repositorio->crear($entidad);
    }

    public function reemplazar(int $id, array$datos): void {
        // EXPLICACIÓN CLASE: En el PUT (reemplazar), NO exigimos el 'id' en el cuerpo porque ya viene en la URL.
        $errores = $this->validarObligatorios($datos, ['nombre', 'tipo', 'ciudad']);
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $actual = $this->obtenerPorId($id); // Falla y lanza excepción si no existe
        $entidad = new Universidad($actual->getId(),$datos['nombre'], $datos['tipo'],$datos['ciudad']);
        $this->repositorio->actualizar($entidad);
    }

    public function cambiarParcial(int $id, array$datos): void {
        // EXPLICACIÓN CLASE: En PATCH, si mandan un JSON vacío `{}`, rechazamos la petición con error 400[cite: 16].
        if (empty($datos)) throw new InvalidArgumentException("El cuerpo de la petición no puede estar vacío.");

        $actual =$this->obtenerPorId($id);$nombre = $datos['nombre'] ?? $actual->getNombre();
        $tipo =$datos['tipo'] ?? $actual->getTipo();$ciudad = $datos['ciudad'] ?? $actual->getCiudad();

        $errores = [];
        foreach ($datos as $campo =>$valor) {
            if (trim((string)$valor) === '')$errores[] = "El campo $campo no puede estar vacío.";
        }
        if (count($errores) > 0) throw new ValidacionExcepcion($errores);

        $entidad = new Universidad($actual->getId(),$nombre, $tipo,$ciudad);
        $this->repositorio->actualizar($entidad);
    }

    public function retirar(int $id): void {
        if (!$this->repositorio->retirar($id)) {
            throw new NoEncontradoExcepcion();
        }
    }

    private function validarObligatorios(array $datos, array $requeridos): array {$errores = [];
        foreach ($requeridos as$campo) {
            if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {$errores[] = "El campo $campo es obligatorio."; // Mensaje exacto exigido por el Spec[cite: 16].
            }
        }
        return $errores;
    }
}