<?php
declare(strict_types=1);

require_once __DIR__ . '/IServicioAreaConocimiento.php';
require_once __DIR__ . '/../repositorios/IRepositorioAreaConocimiento.php';
require_once __DIR__ . '/../modelos/AreaConocimiento.php';
require_once __DIR__ . '/../excepciones/ValidacionExcepcion.php';
require_once __DIR__ . '/../excepciones/NoEncontradoExcepcion.php';

/**
 * Fase 4: Servicio de Área de Conocimiento
 * Qué hace: Valida reglas de negocio y coordina con el repositorio.
 * Por qué está aquí: Mantiene al controlador limpio de reglas y al repositorio limpio de validaciones.
 */
class ServicioAreaConocimiento implements IServicioAreaConocimiento 
{
    private IRepositorioAreaConocimiento $repositorio;

    // REGLA FASE 4: Recibe la interfaz del repositorio por inyección en el constructor[cite: 18].
    public function __construct(IRepositorioAreaConocimiento $repositorio) 
    {
        $this->repositorio = $repositorio;
    }

    public function listar(int $limite): array 
    {
        return $this->repositorio->listar($limite);
    }

    public function obtenerPorId(int $id): AreaConocimiento 
    {
        $area = $this->repositorio->obtenerPorId($id);
        if ($area === null) {
            // REGLA FASE 4: Lanza excepción si no encuentra el registro[cite: 18].
            throw new NoEncontradoExcepcion();
        }
        return $area;
    }

    public function crear(array $datos): void 
    {
        // En POST, el 'id' es obligatorio porque nosotros lo enviamos (no es auto-incremental)[cite: 16].
        $errores = $this->validarCamposObligatorios($datos, ['id', 'gran_area', 'area', 'disciplina']);
        
        if (count($errores) > 0) {
            throw new ValidacionExcepcion($errores);
        }

        $area = new AreaConocimiento(
            (int)$datos['id'],
            $datos['gran_area'],
            $datos['area'],
            $datos['disciplina']
        );

        $this->repositorio->crear($area);
    }

    public function reemplazar(int $id, array $datos): void 
    {
        // REGLA PUT: Exige TODOS los campos[cite: 14]. No exigimos 'id' en el cuerpo porque viene de la URL.
        $errores = $this->validarCamposObligatorios($datos, ['gran_area', 'area', 'disciplina']);
        
        if (count($errores) > 0) {
            throw new ValidacionExcepcion($errores);
        }

        $area = $this->obtenerPorId($id); // Si no existe, lanza NoEncontradoExcepcion

        $areaActualizada = new AreaConocimiento(
            $area->getId(),
            $datos['gran_area'],
            $datos['area'],
            $datos['disciplina']
        );

        $this->repositorio->actualizar($areaActualizada);
    }

    public function cambiarParcial(int $id, array $datos): void 
    {
        // REGLA PATCH: Si el cuerpo está vacío, es un error[cite: 16].
        if (empty($datos)) {
            // Un array vacío sin datos a cambiar lanza error de validación que el controlador traducirá a 400.
            throw new InvalidArgumentException("El cuerpo de la petición no puede estar vacío.");
        }

        $area = $this->obtenerPorId($id); // Si no existe, lanza NoEncontradoExcepcion
        
        // Solo reemplazamos lo que haya llegado
        $granArea = $datos['gran_area'] ?? $area->getGranArea();
        $areaNombre = $datos['area'] ?? $area->getArea();
        $disciplina = $datos['disciplina'] ?? $area->getDisciplina();

        // Validamos que los campos enviados no vengan vacíos
        $errores = [];
        foreach ($datos as $campo => $valor) {
            if (trim((string)$valor) === '') {
                $errores[] = "El campo $campo no puede estar vacío.";
            }
        }

        if (count($errores) > 0) {
            throw new ValidacionExcepcion($errores);
        }

        $areaActualizada = new AreaConocimiento($area->getId(), $granArea, $areaNombre, $disciplina);
        $this->repositorio->actualizar($areaActualizada);
    }

    public function retirar(int $id): void 
    {
        $exito = $this->repositorio->retirar($id);
        if (!$exito) {
            throw new NoEncontradoExcepcion();
        }
    }

    /**
     * Función auxiliar para validar que los campos exigidos vengan en el arreglo y no estén vacíos.
     */
    private function validarCamposObligatorios(array $datos, array $requeridos): array 
    {
        $errores = [];
        foreach ($requeridos as $campo) {
            if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
                // Mensaje exacto en español requerido por el Spec Kit[cite: 13, 16]
                $errores[] = "El campo $campo es obligatorio.";
            }
        }
        return $errores;
    }
}