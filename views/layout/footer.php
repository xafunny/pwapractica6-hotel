<?php defined('APP') or exit; ?>
</main>

<?php if (Auth::check()): ?>
<footer class="pie"><div class="container">Hotel Mirador · Sistema de reservas</div></footer>
<?php endif; ?>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toasts"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php foreach ($js ?? [] as $archivo): ?>
<script src="<?= asset('js/' . $archivo) ?>"></script>
<?php endforeach; ?>
</body>
</html>
