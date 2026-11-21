<?php
/**
 * ARCHIVO: front_php/vistas/navegacion.php
 * QUÉ HACE: Renderiza la barra de navegación superior con diseño institucional, adaptativo y enlaces completos.
 */
$vistaActual = $_GET['vista'] ?? 'inicio';
?>
<!-- Hoja de Estilos Personalizados -->
<style>
    .bg-verde-institucional {
        background-color: #1390b6 !important;
    }
    .btn-destacado {
        background-color: #ffc107;
        color: #212529;
        font-weight: bold;
    }
    .btn-destacado:hover {
        background-color: #e0a800;
        color: #212529;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-verde-institucional shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-4" href="index.php?vista=inicio">
            🔎 Sistema de Investigación
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNavegacion" aria-controls="menuNavegacion" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuNavegacion">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= ($vistaActual === 'inicio') ? 'active fw-bold text-warning' : '' ?>" href="index.php?vista=inicio">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($vistaActual === 'docentes') ? 'active fw-bold text-warning' : '' ?>" href="index.php?vista=docentes">Docentes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (str_contains($vistaActual, 'grupo')) ? 'active fw-bold text-warning' : '' ?>" href="index.php?vista=grupos">Grupos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (str_contains($vistaActual, 'semillero')) ? 'active fw-bold text-warning' : '' ?>" href="index.php?vista=semilleros">Semilleros</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (str_contains($vistaActual, 'proyecto')) ? 'active fw-bold text-warning' : '' ?>" href="index.php?vista=proyectos">Proyectos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (str_contains($vistaActual, 'linea')) ? 'active fw-bold text-warning' : '' ?>" href="index.php?vista=lineas">Líneas</a>
                </li>
                <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                    <a class="btn btn-outline-light btn-sm text-danger bg-white fw-bold px-3 py-2" href="index.php?vista=logout">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>