<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Objetivos de Desarrollo Sostenible</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2>ODS</h2>
            <button class="btn btn-primary" id="btnNuevo">Nuevo ODS</button>
        </div>
        <table class="table table-hover card shadow-sm">
            <thead class="table-dark"><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Acciones</th></tr></thead>
            <tbody id="cuerpoTabla"></tbody>
        </table>
    </div>

    <div class="modal fade" id="modalOds" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 id="modalTitulo">Nuevo ODS</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="formularioOds">
                        <div class="mb-3"><label>ID</label><input type="number" id="campoId" class="form-control" required></div>
                        <div class="mb-3"><label>Nombre</label><input type="text" id="campoNombre" class="form-control" required></div>
                        <div class="mb-3"><label>Categoría</label><input type="text" id="campoCategoria" class="form-control" required></div>
                    </form>
                </div>
                <div class="modal-footer"><button class="btn btn-success" id="btnGuardar">Guardar</button></div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="objetivo_desarrollo_sostenible.js"></script>
</body>
</html>