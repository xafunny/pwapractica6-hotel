// Poner o quitar una habitación de mantenimiento sin recargar
'use strict';

document.querySelectorAll('[data-mantenimiento]').forEach(sw => {
  sw.addEventListener('change', async () => {
    try {
      const r = await Hotel.enviar('index.php?r=habitaciones/estado', { id: sw.dataset.mantenimiento });
      const color = r.estado === 'Disponible' ? 'success' : 'warning';
      sw.closest('tr').querySelector('[data-situacion]').innerHTML = `<span class="badge text-bg-${color}">${r.estado}</span>`;
      Hotel.aviso(r.estado === 'Disponible' ? 'Habitación de vuelta en servicio' : 'Habitación en mantenimiento', color);
    } catch (err) {
      sw.checked = !sw.checked;
      Hotel.aviso(err.message, 'danger');
    }
  });
});
