const API_URL = 'http://localhost:8111/api/area_conocimiento';

// Referencias del DOM
const cuerpoTabla = document.getElementById('cuerpoTabla');
const modalArea = new bootstrap.Modal(document.getElementById('modalArea'));
const formulario = document.getElementById('formularioArea');
const modalTitulo = document.getElementById('modalTitulo');

// Campos del formulario
const campoId = document.getElementById('campoId');
const campoGranArea = document.getElementById('campoGranArea');
const campoArea = document.getElementById('campoArea');
const campoDisciplina = document.getElementById('campoDisciplina');

// Bandera para saber si estamos creando o editando
let modoEdicion = false;

// 1. Cargar los datos (GET)
async function cargarTabla() {
    try {
        const respuesta = await fetch(API_URL);
        if (respuesta.status === 204) {
            cuerpoTabla.innerHTML = '<tr><td colspan="5" class="text-center">No hay registros disponibles.</td></tr>';
            return;
        }
        
        const json = await respuesta.json();
        cuerpoTabla.innerHTML = '';

        json.datos.forEach(area => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${area.id}</td>
                <td>${area.gran_area}</td>
                <td>${area.area}</td>
                <td>${area.disciplina}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="abrirModalEditar(${area.id}, '${area.gran_area}', '${area.area}', '${area.disciplina}')">Editar</button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarArea(${area.id})">Eliminar</button>
                </td>
            `;
            cuerpoTabla.appendChild(fila);
        });
    } catch (error) {
        console.error('Error al cargar:', error);
        cuerpoTabla.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos.</td></tr>';
    }
}

// 2. Botón "Nueva Área" (Prepara el formulario para POST)
document.getElementById('btnNuevo').addEventListener('click', () => {
    modoEdicion = false;
    formulario.reset();
    campoId.disabled = false; // Permitimos escribir el ID porque lo define el usuario
    modalTitulo.textContent = 'Nueva Área de Conocimiento';
    modalArea.show();
});

// 3. Botón "Editar" (Prepara el formulario para PUT)
window.abrirModalEditar = (id, granArea, area, disciplina) => {
    modoEdicion = true;
    campoId.value = id;
    campoId.disabled = true; // Bloqueamos el ID porque no se debe cambiar
    campoGranArea.value = granArea;
    campoArea.value = area;
    campoDisciplina.value = disciplina;
    modalTitulo.textContent = 'Editar Área de Conocimiento';
    modalArea.show();
};

// 4. Botón "Guardar" del Modal (Ejecuta POST o PUT)
document.getElementById('btnGuardar').addEventListener('click', async () => {
    const id = campoId.value;
    const datos = {
        id: parseInt(id),
        gran_area: campoGranArea.value,
        area: campoArea.value,
        disciplina: campoDisciplina.value
    };

    // Si es edición, la URL lleva el ID al final. Si es creación, va a la ruta base.
    const url = modoEdicion ? `${API_URL}/${id}` : API_URL;
    const metodo = modoEdicion ? 'PUT' : 'POST';

    try {
        const respuesta = await fetch(url, {
            method: metodo,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });

        if (respuesta.ok) {
            modalArea.hide(); // Cerramos la ventana
            cargarTabla();    // Recargamos la tabla para ver los cambios
        } else {
            const errorData = await respuesta.json();
            alert('Error: ' + (errorData.mensaje || 'Revisa los datos ingresados.'));
        }
    } catch (error) {
        console.error('Error al guardar:', error);
    }
});

// 5. Botón "Eliminar" (Ejecuta DELETE lógico)
window.eliminarArea = async (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta Área de Conocimiento?')) {
        try {
            const respuesta = await fetch(`${API_URL}/${id}`, { 
                method: 'DELETE' 
            });
            
            if (respuesta.ok) {
                cargarTabla();
            } else {
                alert('Error al intentar eliminar el registro.');
            }
        } catch (error) {
            console.error('Error al eliminar:', error);
        }
    }
};

// Arrancamos la tabla apenas cargue la página
document.addEventListener('DOMContentLoaded', cargarTabla);