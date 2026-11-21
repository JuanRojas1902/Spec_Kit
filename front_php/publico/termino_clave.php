<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Términos Clave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2>Términos Clave</h2>
            <button class="btn btn-primary" id="btnNuevo">Nuevo Término</button>
        </div>
        <table class="table table-hover card shadow-sm">
            <thead class="table-dark"><tr><th>Término</th><th>Término (Inglés)</th><th>Acciones</th></tr></thead>
            <tbody id="cuerpoTabla"></tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalTermino" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 id="modalTitulo">Nuevo Término</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="formularioTermino">
                        <div class="mb-3"><label>Término (Español - Llave Primaria)</label><input type="text" id="campoTermino" class="form-control" required></div>
                        <div class="mb-3"><label>Término (Inglés - Opcional)</label><input type="text" id="campoIngles" class="form-control"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" id="btnGuardar">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="termino_clave.js"></script>
</body>
</html>