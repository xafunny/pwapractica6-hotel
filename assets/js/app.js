// Funciones comunes: fetch, avisos y formularios
'use strict';

const Hotel = {
  token: document.querySelector('meta[name="csrf-token"]')?.content,

  async enviar(url, datos) {
    const form = new FormData();
    for (const k in datos) form.append(k, datos[k]);
    const res = await fetch(url, { method: 'POST', body: form, headers: { Accept: 'application/json', 'X-CSRF-Token': this.token } });
    const json = await res.json().catch(() => ({}));
    if (!res.ok || !json.ok) throw new Error(json.error || 'No se pudo completar la acción');
    return json;
  },

  aviso(texto, tipo = 'success') {
    const t = document.createElement('div');
    t.className = `toast text-bg-${tipo} border-0`;
    t.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div>';
    t.querySelector('.toast-body').textContent = texto;
    document.getElementById('toasts').append(t);
    new bootstrap.Toast(t, { delay: 3000 }).show();
    t.addEventListener('hidden.bs.toast', () => t.remove());
  },

  dinero: n => '$' + Number(n).toFixed(2),
};

// validación + contraseñas iguales
document.querySelectorAll('form.needs-validation').forEach(f => {
  f.addEventListener('submit', e => {
    f.querySelectorAll('[data-igual]').forEach(c => {
      c.setCustomValidity(c.value === document.getElementById(c.dataset.igual).value ? '' : 'No coincide');
    });
    if (!f.checkValidity()) {
      e.preventDefault();
      f.querySelector(':invalid')?.focus();
    }
    f.classList.add('was-validated');
  });
});

document.querySelectorAll('form[data-confirmar]').forEach(f => {
  f.addEventListener('submit', e => {
    if (!confirm(f.dataset.confirmar)) e.preventDefault();
  });
});

// modales que sirven para crear y editar (data-datos trae el registro)
document.querySelectorAll('.modal form[data-nuevo]').forEach(f => {
  const modal = f.closest('.modal');
  modal.addEventListener('show.bs.modal', e => {
    const datos = e.relatedTarget?.dataset.datos ? JSON.parse(e.relatedTarget.dataset.datos) : null;
    f.reset();
    f.classList.remove('was-validated');
    f.elements.id.value = '';
    if (datos) for (const k in datos) if (f.elements[k]) f.elements[k].value = datos[k] ?? '';
    f.querySelectorAll('[data-obligatorio-nuevo]').forEach(c => { c.required = !datos; });
    f.querySelectorAll('.solo-editar').forEach(c => c.classList.toggle('d-none', !datos));
    f.querySelectorAll('.solo-nuevo').forEach(c => c.classList.toggle('d-none', !!datos));
    modal.querySelector('.modal-title').textContent = datos ? f.dataset.editar : f.dataset.nuevo;
    f.dispatchEvent(new Event('modal-listo'));
  });
});

document.querySelectorAll('.aviso').forEach(a => setTimeout(() => bootstrap.Alert.getOrCreateInstance(a).close(), 5000));
