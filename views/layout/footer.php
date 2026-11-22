<?php defined('APP') or exit; ?>
</main>

<footer class="pie">
  <div class="container d-flex flex-wrap justify-content-between gap-2">
    <span>Hotel Mirador · Sistema de reservas</span>
    <span>Jhoao Guala · Danna Idrovo · Ckarlos Daza · Madelen Toledo · Keyla Duran · Diego Cobeña</span>
  </div>
</footer>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toasts"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php foreach ($js ?? [] as $archivo): ?>
<script src="<?= asset('js/' . $archivo) ?>"></script>
<?php endforeach; ?>
</body>
</html>
