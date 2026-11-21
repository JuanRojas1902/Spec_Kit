const API_URL = 'http://localhost:8111/api/universidad';
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modal = new bootstrap.Modal(document.getElementById('modalUniversidad'));
const campoId = document.getElementById('campoId');
const campoNombre = document.getElementById('campoNombre');
const campoTipo = document.getElementById('campoTipo');
const campoCiudad = document.getElementById('campoCiudad');
let modoEdicion = false;

async function cargarTabla() {
    const res = await fetch(API_URL);
    if (res.status === 204) return cuerpoTabla.innerHTML = '<tr><td colspan="5">No hay datos</td></tr>';
    const json = await res.json();
    cuerpoTabla.innerHTML = '';
    json.datos.forEach(u => {
        cuerpoTabla.innerHTML += `<tr>
            <td>${u.id}</td><td>${u.nombre}</td><td>${u.tipo}</td><td>${u.ciudad}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="abrirEditar(${u.id}, '${u.nombre}', '${u.tipo}', '${u.ciudad}')">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminar(${u.id})">Eliminar</button>
            </td>
        </tr>`;
    });
}

document.getElementById('btnNuevo').onclick = () => {
    modoEdicion = false; campoId.disabled = false;
    document.getElementById('formularioUniversidad').reset();
    document.getElementById('modalTitulo').innerText = 'Nueva Universidad';
    modal.show();
};

window.abrirEditar = (id, nombre, tipo, ciudad) => {
    modoEdicion = true; campoId.disabled = true;
    campoId.value = id; campoNombre.value = nombre; campoTipo.value = tipo; campoCiudad.value = ciudad;
    document.getElementById('modalTitulo').innerText = 'Editar Universidad';
    modal.show();
};

document.getElementById('btnGuardar').onclick = async () => {
    const id = campoId.value;
    const datos = { id: parseInt(id), nombre: campoNombre.value, tipo: campoTipo.value, ciudad: campoCiudad.value };
    const url = modoEdicion ? `${API_URL}/${id}` : API_URL;
    
    await fetch(url, { method: modoEdicion ? 'PUT' : 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(datos) });
    modal.hide(); cargarTabla();
};

window.eliminar = async (id) => { if (confirm('¿Eliminar?')) { await fetch(`${API_URL}/${id}`, { method: 'DELETE' }); cargarTabla(); } };
document.addEventListener('DOMContentLoaded', cargarTabla);