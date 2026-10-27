  <!-- Pie de Página Institucional -->
  <footer id="ftr">
    🌿 Conecta tu trabajo con nuevas oportunidades de recolección y convierte tu esfuerzo en beneficios. ♻️
  </footer>

  </div> <!-- /#main -->
</div> <!-- /#app -->

<script>
// Auto-reparación defensiva para garantizar que el footer siempre resida dentro de #main y nunca genere columna lateral en #app
(function() {
  function ajustarFooter() {
    var ftr = document.getElementById('ftr');
    var main = document.getElementById('main');
    if (ftr && main && ftr.parentElement !== main) {
      main.appendChild(ftr);
    }
  }
  ajustarFooter();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ajustarFooter);
  }
})();
</script>

<!-- Panel Deslizable Lateral del Perfil -->
<?php require __DIR__ . '/profile_panel.php'; ?>

<!-- Script de interactividad general (sidebar, notificaciones, perfil) -->
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
