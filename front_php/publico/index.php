<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Investigación</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">API Investigación</a>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Áreas de Conocimiento</h2>
            <!-- Botón que abrirá el modal para crear (lo programaremos en el JS) -->
            <button class="btn btn-primary" id="btnNuevo">Nueva Área</button>
        </div>

        <!-- Tabla de datos -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Gran Área</th>
                            <th>Área</th>
                            <th>Disciplina</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTabla">
                        <!-- Aquí JavaScript inyectará las filas dinámicamente -->
                        <tr><td colspan="5" class="text-center">Cargando datos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar (Oculto por defecto) -->
    <div class="modal fade" id="modalArea" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Nueva Área</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formularioArea">
                        <div class="mb-3">
                            <label class="form-label">ID (Código)</label>
                            <input type="number" class="form-control" id="campoId" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gran Área</label>
                            <input type="text" class="form-control" id="campoGranArea" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Área</label>
                            <input type="text" class="form-control" id="campoArea" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Disciplina</label>
                            <input type="text" class="form-control" id="campoDisciplina" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardar">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap y nuestro archivo lógico -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="app.js"></script>
</body>
</html>