<?php
declare(strict_types=1);

require_once __DIR__ . '/../repositorios/RepositorioGrupoInvestigacionMariaDB.php';

class ServicioGrupoInvestigacion
{
    private RepositorioGrupoInvestigacionMariaDB $repositorio;

    public function __construct(RepositorioGrupoInvestigacionMariaDB $repositorio)
    {
        $this->repositorio = $repositorio;
    }

    public function listar(): array
    {
        return $this->repositorio->listar();
    }

    public function consultar(int $id): ?array
    {
        return $this->repositorio->consultar($id);
    }

    public function crear(array $datos): array
    {
        // Si la universidad viene vacía, nos aseguramos de que sea null
        $datos['universidad_id'] = ($datos['universidad_id'] ?? '') === '' ? null : (int) $datos['universidad_id'];
        return $this->repositorio->crear($datos);
    }

    public function actualizar(int $id, array $datos): void
    {
        // Mezclamos con los datos actuales para permitir PATCH (actualización parcial)
        $actual = $this->consultar($id);
        if (!$actual) {
            throw new Exception("El grupo no existe", 404);
        }

        $datosCompletos = [
            'gruplac'        => $datos['gruplac'] ?? $actual['gruplac'],
            'categoria'      => $datos['categoria'] ?? $actual['categoria'],
            'convocatoria'   => $datos['convocatoria'] ?? $actual['convocatoria'],
            'ambito'         => $datos['ambito'] ?? $actual['ambito'],
            'universidad_id' => ($datos['universidad_id'] ?? $actual['universidad_id']) === '' ? null : (int) ($datos['universidad_id'] ?? $actual['universidad_id'])
        ];

        $this->repositorio->actualizar($id, $datosCompletos);
    }

    public function eliminar(int $id): void
    {
        $this->repositorio->eliminar($id);
    }
}