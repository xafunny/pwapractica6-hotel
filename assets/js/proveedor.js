// Muestra el total de la oferta mientras el proveedor escribe el precio
'use strict';

document.querySelectorAll('form.oferta').forEach(f => {
  const precio = f.elements.precio_unitario;
  const calcular = () => {
    f.querySelector('[data-total]').textContent = Hotel.dinero((parseFloat(precio.value) || 0) * f.dataset.cantidad);
  };
  precio.addEventListener('input', calcular);
  calcular();
});
