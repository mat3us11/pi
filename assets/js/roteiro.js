// assets/js/roteiro.js

// Botões de limpar
document.addEventListener('DOMContentLoaded', () => {
  const btnClear = document.getElementById('btn-clear');
  const btnClear2 = document.getElementById('btn-clear-2');
  const form = document.querySelector('form.explore');

  function clearFilters() {
    if (!form) return;
    form.querySelectorAll('input[type="search"]').forEach(i => i.value = '');
    // Submete vazio (GET sem filtros)
    form.submit();
  }

  if (btnClear) btnClear.addEventListener('click', clearFilters);
  if (btnClear2) btnClear2.addEventListener('click', clearFilters);
});
