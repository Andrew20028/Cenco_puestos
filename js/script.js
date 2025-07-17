const modal = document.getElementById("modal");
const overlay = document.getElementById("overlay");
const formulario = document.getElementById("formulario");
const nicknameInput = document.getElementById("nickname");
const puestoInput = document.getElementById("puesto-id");
const puestoDisplay = document.getElementById("puesto-display");
const fechasInput = document.getElementById("fechas");
const calendario = flatpickr("#calendario", {
    mode: "multiple",
    dateFormat: "Y-m-d",
    minDate: "today",
    locale: "es",
    inline: true,
    onChange: function(selectedDates, dateStr, instance) {
        fechasInput.value = dateStr; // Actualiza el campo oculto con las fechas seleccionadas
    },
    disable: [], // Inicialmente no deshabilitar nada
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        if (fp.config.disable.includes(dStr)) {
            dayElem.classList.add('occupied-day'); // Clase para estilizar días ocupados
        }
    }
});

let currentButton = null;
let infoPopup = null;

function cerrarModal() {
    modal.classList.remove("show");
    overlay.classList.remove("show");
    calendario.clear(); // Limpiar las fechas seleccionadas
}

function cerrarInfoPopup() {
    if (infoPopup) {
        infoPopup.remove();
        infoPopup = null;
    }
}

function mostrarInfoPuesto(btn, puestoInfo) {
    cerrarInfoPopup();
    
    const puestoNumero = btn.textContent.trim();
    const data = puestoInfo?.data || { reservas: [], fechas_ocupadas: [] }; // Manejo de undefined
    
    infoPopup = document.createElement('div');
    infoPopup.className = 'info-popup';
    infoPopup.innerHTML = `
        <div class="info-content">
            <h3>Puesto N°${puestoNumero}</h3>
            <div class="ocupacion-info">
                <strong>Reservas:</strong>
                <ul>
                    ${data.reservas.length > 0 ? 
                        data.reservas.map(reserva => `
                            <li>
                                ${reserva.nickname}: ${reserva.fecha}
                                <button class="btn-eliminar-fecha" onclick="eliminarFechaEspecifica('${reserva.fecha}')">Eliminar</button>
                            </li>
                        `).join('') :
                        '<li>No hay reservas</li>'}
                </ul>
            </div>
            <button class="btn-reservar-disponible" onclick="mostrarModalReserva(currentButton, currentButton.puestoInfo)">
                Reservar fechas
            </button>
            <button class="btn-eliminar-reserva" onclick="confirmarEliminacion()">
                Eliminar todas mis reservas
            </button>
            <button class="btn-cerrar" onclick="cerrarInfoPopup()">×</button>
        </div>
    `;
    
    const rect = btn.getBoundingClientRect();
    infoPopup.style.position = 'fixed';
    infoPopup.style.left = rect.right + 10 + 'px';
    infoPopup.style.top = rect.top + 'px';
    infoPopup.style.zIndex = '1001';
    
    document.body.appendChild(infoPopup);
    const popupRect = infoPopup.getBoundingClientRect();
    if (popupRect.right > window.innerWidth) {
        infoPopup.style.left = rect.left - popupRect.width - 10 + 'px';
    }
    if (popupRect.bottom > window.innerHeight) {
        infoPopup.style.top = window.innerHeight - popupRect.height - 10 + 'px';
    }
    
    currentButton = btn;
    currentButton.puestoInfo = puestoInfo;
}

function confirmarEliminacion() {
    if (confirm('¿Estás seguro de que quieres eliminar todas tus reservas de este puesto?')) {
        eliminarCupo();
    }
}

function updatePuestos() {
    const formData = new FormData();
    formData.append('action', 'get_occupied_all'); // Nueva acción para todas las ocupaciones
    
    fetch('registrar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('Respuesta cruda (updatePuestos):', text);
        return JSON.parse(text);
    })
    .then(data => {
        console.log('Puestos ocupados:', data);
        document.querySelectorAll(".numero").forEach(btn => {
            btn.classList.remove("ocupado");
            btn.title = "";
        });
        data.forEach(reserva => {
            const btn = Array.from(document.querySelectorAll('.numero')).find(b => 
                b.textContent.trim() === reserva.puesto.toString()
            );
            if (btn && reserva.ocupado) { // Verificar si el puesto está ocupado
                btn.classList.add("ocupado");
                btn.title = `Ocupado por ${reserva.nickname}`;
            }
        });
    })
    .catch(error => {
        console.error('Error al cargar puestos:', error);
    });
}

function mostrarModalReserva(btn, puestoInfo = null) {
    cerrarInfoPopup();
    
    currentButton = btn;
    puestoInput.value = btn.textContent.trim();
    puestoDisplay.textContent = `N°${btn.textContent.trim()}`;
    
    calendario.clear(); // Limpiar fechas previas
    
    if (puestoInfo && puestoInfo.data && puestoInfo.data.fechas_ocupadas) {
        const occupiedDates = puestoInfo.data.fechas_ocupadas;
        calendario.set('disable', occupiedDates); // Deshabilitar fechas ocupadas
        calendario.redraw(); // Forzar actualización visual
    } else {
        calendario.set('disable', []); // Habilitar todas las fechas si no hay información
        obtenerInfoPuesto(btn.textContent.trim()).then(puestoInfo => {
            if (puestoInfo.success && puestoInfo.data.fechas_ocupadas) {
                calendario.set('disable', puestoInfo.data.fechas_ocupadas);
                calendario.redraw();
            }
        });
    }
    
    const submitBtn = formulario.querySelector('button[type="submit"]');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Guardar';
    
    modal.classList.add("show");
    overlay.classList.add("show");
}

function obtenerInfoPuesto(puesto) {
    const formData = new FormData();
    formData.append('action', 'get_puesto_info');
    formData.append('puesto', puesto);
    
    return fetch('registrar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('Respuesta cruda (obtenerInfoPuesto):', text);
        return JSON.parse(text);
    })
    .catch(error => {
        console.error('Error al obtener info del puesto:', error);
        return { success: false };
    });
}

function eliminarCupo() {
    if (currentButton) {
        const puesto = currentButton.textContent.trim();
        const nickname = nicknameInput.value.trim();
        
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('puesto', puesto);
        formData.append('nickname', nickname);
        
        fetch('registrar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentButton.classList.remove("ocupado");
                currentButton.title = "";
                cerrarInfoPopup();
                updatePuestos();
                alert("Tus reservas fueron eliminadas exitosamente");
            } else {
                alert("Error al eliminar: " + (data.error || "Desconocido"));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error al eliminar tus reservas");
        });
    }
}

function eliminarFechaEspecifica(fecha) {
    if (currentButton) {
        const puesto = currentButton.textContent.trim();
        const nickname = nicknameInput.value.trim();
        
        const formData = new FormData();
        formData.append('action', 'delete_date');
        formData.append('puesto', puesto);
        formData.append('nickname', nickname);
        formData.append('fecha', fecha);
        
        fetch('registrar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cerrarInfoPopup();
                updatePuestos();
                obtenerInfoPuesto(puesto).then(puestoInfo => {
                    if (puestoInfo.success) {
                        mostrarInfoPuesto(currentButton, puestoInfo);
                    }
                });
                alert("Fecha eliminada exitosamente");
            } else {
                alert("Error al eliminar la fecha: " + (data.error || "Desconocido"));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error al eliminar la fecha");
        });
    }
}

formulario.addEventListener("submit", function (e) {
    e.preventDefault();

    const nickname = nicknameInput.value.trim();
    const fechas = fechasInput.value.trim().split(',').filter(date => date); // Obtener fechas del campo oculto

    if (!nickname) {
        alert("Por favor ingresa un nickname");
        return;
    }

    if (fechas.length === 0) {
        alert("Por favor selecciona al menos una fecha disponible");
        return;
    }

    if (currentButton) {
        const formData = new FormData();
        formData.append('action', 'register');
        formData.append('puesto', puestoInput.value);
        formData.append('nickname', nickname);
        formData.append('fechas', fechas.join(','));

        fetch('registrar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta del servidor:', data);
            if (data.success) {
                currentButton.classList.add("ocupado");
                currentButton.title = `Ocupado por ${nickname} (${fechas.join(", ")})`;
                cerrarModal();
                updatePuestos();
                alert("Reserva guardada exitosamente");
            } else {
                alert("Error al registrar: " + (data.error || "Desconocido"));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error al procesar la reserva");
        });
    }
});

document.querySelectorAll(".numero").forEach(btn => {
    btn.addEventListener("click", async () => {
        const puestoNumero = btn.textContent.trim();
        
        await updatePuestos();
        
        if (btn.classList.contains("ocupado")) {
            const puestoInfo = await obtenerInfoPuesto(puestoNumero);
            if (puestoInfo.success) {
                mostrarInfoPuesto(btn, puestoInfo);
            } else {
                alert("Error al obtener información del puesto");
            }
        } else {
            const puestoInfo = await obtenerInfoPuesto(puestoNumero);
            mostrarModalReserva(btn, puestoInfo);
        }
    });
});

overlay.addEventListener("click", cerrarModal);

document.addEventListener('click', (e) => {
    if (infoPopup && !infoPopup.contains(e.target) && !e.target.classList.contains('numero')) {
        cerrarInfoPopup();
    }
});

document.addEventListener("DOMContentLoaded", () => {
    updatePuestos();
    setInterval(updatePuestos, 30000);
});