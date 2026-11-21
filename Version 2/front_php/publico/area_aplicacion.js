const API_URL = 'http://localhost:8111/api/area_aplicacion';
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modal = new bootstrap.Modal(document.getElementById('modalArea'));
const campoId = document.getElementById('campoId');
const campoNombre = document.getElementById('campoNombre');
let modoEdicion = false;

async function cargarTabla() {
    const res = await fetch(API_URL);
    if (res.status === 204) return cuerpoTabla.innerHTML = '<tr><td colspan="3">No hay datos</td></tr>';
    const json = await res.json();
    cuerpoTabla.innerHTML = '';
    json.datos.forEach(a => {
        cuerpoTabla.innerHTML += `<tr>
            <td>${a.id}</td><td>${a.nombre}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="abrirEditar(${a.id}, '${a.nombre}')">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminar(${a.id})">Eliminar</button>
            </td>
        </tr>`;
    });
}

document.getElementById('btnNuevo').onclick = () => {
    modoEdicion = false; campoId.disabled = false;
    document.getElementById('formularioArea').reset();
    document.getElementById('modalTitulo').innerText = 'Nueva Área de Aplicación';
    modal.show();
};

window.abrirEditar = (id, nombre) => {
    modoEdicion = true; campoId.disabled = true;
    campoId.value = id; campoNombre.value = nombre;
    document.getElementById('modalTitulo').innerText = 'Editar Área de Aplicación';
    modal.show();
};

document.getElementById('btnGuardar').onclick = async () => {
    const id = campoId.value;
    const datos = { id: parseInt(id), nombre: campoNombre.value };
    const url = modoEdicion ? `${API_URL}/${id}` : API_URL;
    
    await fetch(url, { method: modoEdicion ? 'PUT' : 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(datos) });
    modal.hide(); cargarTabla();
};

window.eliminar = async (id) => { if (confirm('¿Eliminar?')) { await fetch(`${API_URL}/${id}`, { method: 'DELETE' }); cargarTabla(); } };
document.addEventListener('DOMContentLoaded', cargarTabla);