const API_URL = 'http://localhost:8111/api/objetivo_desarrollo_sostenible';
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modal = new bootstrap.Modal(document.getElementById('modalOds'));
const campoId = document.getElementById('campoId');
const campoNombre = document.getElementById('campoNombre');
const campoCategoria = document.getElementById('campoCategoria');
let modoEdicion = false;

async function cargarTabla() {
    const res = await fetch(API_URL);
    if (res.status === 204) return cuerpoTabla.innerHTML = '<tr><td colspan="4">No hay datos</td></tr>';
    const json = await res.json();
    cuerpoTabla.innerHTML = '';
    json.datos.forEach(o => {
        cuerpoTabla.innerHTML += `<tr>
            <td>${o.id}</td><td>${o.nombre}</td><td>${o.categoria}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="abrirEditar(${o.id}, '${o.nombre}', '${o.categoria}')">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminar(${o.id})">Eliminar</button>
            </td>
        </tr>`;
    });
}

document.getElementById('btnNuevo').onclick = () => {
    modoEdicion = false; campoId.disabled = false;
    document.getElementById('formularioOds').reset();
    document.getElementById('modalTitulo').innerText = 'Nuevo ODS';
    modal.show();
};

window.abrirEditar = (id, nombre, categoria) => {
    modoEdicion = true; campoId.disabled = true;
    campoId.value = id; campoNombre.value = nombre; campoCategoria.value = categoria;
    document.getElementById('modalTitulo').innerText = 'Editar ODS';
    modal.show();
};

document.getElementById('btnGuardar').onclick = async () => {
    const id = campoId.value;
    const datos = { id: parseInt(id), nombre: campoNombre.value, categoria: campoCategoria.value };
    const url = modoEdicion ? `${API_URL}/${id}` : API_URL;
    
    await fetch(url, { method: modoEdicion ? 'PUT' : 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(datos) });
    modal.hide(); cargarTabla();
};

window.eliminar = async (id) => { if (confirm('¿Eliminar?')) { await fetch(`${API_URL}/${id}`, { method: 'DELETE' }); cargarTabla(); } };
document.addEventListener('DOMContentLoaded', cargarTabla);