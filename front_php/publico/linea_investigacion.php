<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Líneas de Investigación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2>Líneas de Investigación</h2>
            <button class="btn btn-primary" id="btnNuevo">Nueva Línea</button>
        </div>
        <table class="table table-hover card shadow-sm">
            <thead class="table-dark"><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr></thead>
            <tbody id="cuerpoTabla"></tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalLinea" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 id="modalTitulo">Nueva Línea</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="formularioLinea">
                        <input type="hidden" id="campoId"> <!-- ID oculto -->
                        <div class="mb-3"><label>Nombre</label><input type="text" id="campoNombre" class="form-control" required></div>
                        <div class="mb-3"><label>Descripción</label><textarea id="campoDesc" class="form-control" required></textarea></div>
                    </form>
                </div>
                <div class="modal-footer"><button class="btn btn-success" id="btnGuardar">Guardar</button></div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="linea_investigacion.js"></script>
</body>
</html>