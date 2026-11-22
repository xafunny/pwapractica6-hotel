// Reservas: llenar el modal de confirmación y botones de check-in / check-out / cancelar
'use strict';

// al tocar "Reservar" se pasa la habitación elegida al modal
document.getElementById('modalReserva')?.addEventListener('show.bs.modal', e => {
  const h = JSON.parse(e.relatedTarget.dataset.habitacion);
  document.getElementById('rHabitacion').value = h.id;
  document.getElementById('rTitulo').textContent = `Habitación ${h.numero} · ${h.tipo}`;
  document.getElementById('rTotal').textContent = Hotel.dinero(h.total);
});

const textos = { Hospedado: '¿Registrar la llegada del huésped?', Finalizada: '¿Registrar la salida del huésped?', Cancelada: '¿Seguro que quieres cancelar la reserva?' };
const colores = { Hospedado: 'success', Finalizada: 'secondary', Cancelada: 'danger' };

document.querySelectorAll('[data-estado][data-reserva]').forEach(btn => {
  btn.addEventListener('click', async () => {
    const estado = btn.dataset.estado;
    if (!confirm(textos[estado])) return;
    btn.disabled = true;
    try {
      await Hotel.enviar('index.php?r=reservas/estado', { id: btn.dataset.reserva, estado });
      Hotel.aviso(estado === 'Hospedado' ? 'Check-in registrado' : estado === 'Finalizada' ? 'Check-out registrado' : 'Reserva cancelada');
      if (btn.hasAttribute('data-recargar')) return setTimeout(() => location.reload(), 600);

      // en las tablas solo se actualiza la fila
      const fila = btn.closest('tr, .movimiento');
      const celda = fila.querySelector('[data-etiqueta]');
      if (celda) celda.innerHTML = `<span class="badge text-bg-${colores[estado]}">${estado}</span>`;
      btn.remove();
    } catch (err) {
      Hotel.aviso(err.message, 'danger');
      btn.disabled = false;
    }
  });
});
