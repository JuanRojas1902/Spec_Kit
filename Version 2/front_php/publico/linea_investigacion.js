const API_URL = 'http://localhost:8111/api/linea_investigacion';
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modal = new bootstrap.Modal(document.getElementById('modalLinea'));
const campoId = document.getElementById('campoId');
const campoNombre = document.getElementById('campoNombre');
const campoDesc = document.getElementById('campoDesc');
let modoEdicion = false;

async function cargarTabla() {
    const res = await fetch(API_URL);
    if (res.status === 204) return cuerpoTabla.innerHTML = '<tr><td colspan="4">No hay datos</td></tr>';
    const json = await res.json();
    cuerpoTabla.innerHTML = '';
    json.datos.forEach(l => {
        cuerpoTabla.innerHTML += `<tr>
            <td>${l.id}</td><td>${l.nombre}</td><td>${l.descripcion}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="abrirEditar(${l.id}, '${l.nombre}', '${l.descripcion}')">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminar(${l.id})">Eliminar</button>
            </td>
        </tr>`;
    });
}

document.getElementById('btnNuevo').onclick = () => {
    modoEdicion = false; document.getElementById('formularioLinea').reset();
    document.getElementById('modalTitulo').innerText = 'Nueva Línea';
    modal.show();
};

window.abrirEditar = (id, nombre, desc) => {
    modoEdicion = true; campoId.value = id;
    campoNombre.value = nombre; campoDesc.value = desc;
    document.getElementById('modalTitulo').innerText = 'Editar Línea';
    modal.show();
};

document.getElementById('btnGuardar').onclick = async () => {
    // Al crear no enviamos ID, al editar sí usamos el ID oculto para la URL
    const datos = { nombre: campoNombre.value, descripcion: campoDesc.value };
    const url = modoEdicion ? `${API_URL}/${campoId.value}` : API_URL;
    
    await fetch(url, { method: modoEdicion ? 'PUT' : 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(datos) });
    modal.hide(); cargarTabla();
};

window.eliminar = async (id) => {
    if (confirm('¿Eliminar?')) { await fetch(`${API_URL}/${id}`, { method: 'DELETE' }); cargarTabla(); }
};

document.addEventListener('DOMContentLoaded', cargarTabla);