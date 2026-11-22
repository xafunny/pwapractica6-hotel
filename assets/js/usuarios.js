// Usuarios: activar/desactivar y mostrar campos según el rol
'use strict';

document.querySelectorAll('[data-activo]').forEach(sw => {
  sw.addEventListener('change', async () => {
    try {
      await Hotel.enviar('index.php?r=usuarios/estado', { id: sw.dataset.activo });
      Hotel.aviso(sw.checked ? 'Usuario activado' : 'Usuario desactivado', sw.checked ? 'success' : 'secondary');
    } catch (err) {
      sw.checked = !sw.checked;
      Hotel.aviso(err.message, 'danger');
    }
  });
});

// cédula solo para clientes, empresa solo para proveedores
const rol = document.getElementById('rolUsuario');
const mostrarCampos = () => document.querySelectorAll('.solo-rol').forEach(c => c.classList.toggle('d-none', c.dataset.rol !== rol.value));
rol.addEventListener('change', mostrarCampos);
rol.form.addEventListener('modal-listo', mostrarCampos);
