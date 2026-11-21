const API_URL = 'http://localhost:8111/api/termino_clave';
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modal = new bootstrap.Modal(document.getElementById('modalTermino'));
const campoTermino = document.getElementById('campoTermino');
const campoIngles = document.getElementById('campoIngles');
let modoEdicion = false;

async function cargarTabla() {
    const res = await fetch(API_URL);
    if (res.status === 204) return cuerpoTabla.innerHTML = '<tr><td colspan="3">No hay datos</td></tr>';
    const json = await res.json();
    cuerpoTabla.innerHTML = '';
    json.datos.forEach(t => {
        cuerpoTabla.innerHTML += `<tr>
            <td>${t.termino}</td><td>${t.termino_ingles || ''}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="abrirEditar('${t.termino}', '${t.termino_ingles || ''}')">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminar('${t.termino}')">Eliminar</button>
            </td>
        </tr>`;
    });
}

document.getElementById('btnNuevo').onclick = () => {
    modoEdicion = false; campoTermino.disabled = false;
    document.getElementById('formularioTermino').reset();
    document.getElementById('modalTitulo').innerText = 'Nuevo Término';
    modal.show();
};

window.abrirEditar = (termino, ingles) => {
    modoEdicion = true; campoTermino.disabled = true;
    campoTermino.value = termino; campoIngles.value = ingles;
    document.getElementById('modalTitulo').innerText = 'Editar Término';
    modal.show();
};

document.getElementById('btnGuardar').onclick = async () => {
    const termino = campoTermino.value;
    // En PUT solo enviamos el término en inglés, el ID va en la URL
    const datos = modoEdicion ? { termino_ingles: campoIngles.value } : { termino: termino, termino_ingles: campoIngles.value };
    const url = modoEdicion ? `${API_URL}/${termino}` : API_URL;
    
    await fetch(url, { method: modoEdicion ? 'PUT' : 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(datos) });
    modal.hide(); cargarTabla();
};

window.eliminar = async (termino) => {
    if (confirm('¿Eliminar?')) { await fetch(`${API_URL}/${termino}`, { method: 'DELETE' }); cargarTabla(); }
};

document.addEventListener('DOMContentLoaded', cargarTabla);