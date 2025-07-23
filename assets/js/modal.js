const hamburgerBtn = document.getElementById('hamburgerBtn');
const mobileModal = document.getElementById('mobileModal');
const hamburgerIcon = document.getElementById('hamburgerIcon');

let menuOpen = false;

hamburgerBtn.addEventListener('click', () => {
    menuOpen = !menuOpen;
    if (menuOpen) {
        mobileModal.classList.add('active');
        mobileModal.setAttribute('aria-hidden', 'false');
        // Troca ícone pro xizinho X
        hamburgerIcon.className = 'ph ph-x';
        hamburgerBtn.setAttribute('aria-label', 'Fechar menu');
    } else {
        mobileModal.classList.remove('active');
        mobileModal.setAttribute('aria-hidden', 'true');
        // Volta icone pro hamburger
        hamburgerIcon.className = 'ph ph-list';
        hamburgerBtn.setAttribute('aria-label', 'Abrir menu');
    }
});

// Fecha menu clicando fora
window.addEventListener('click', (e) => {
    if (
        menuOpen &&
        !mobileModal.contains(e.target) &&
        e.target !== hamburgerBtn &&
        !hamburgerBtn.contains(e.target)
    ) {
        menuOpen = false;
        mobileModal.classList.remove('active');
        mobileModal.setAttribute('aria-hidden', 'true');
        hamburgerIcon.className = 'ph ph-list';
        hamburgerBtn.setAttribute('aria-label', 'Abrir menu');
    }
});


// Modal perfil
  const btnEditar = document.querySelector('.btn-perfil[href="#"]');
  const modal = document.getElementById('modal-editar');
  const fechar = document.getElementById('fechar-modal');

  btnEditar.addEventListener('click', function (e) {
    e.preventDefault();
    modal.style.display = 'block';
  });

  fechar.addEventListener('click', function () {
    modal.style.display = 'none';
  });

  window.addEventListener('click', function (e) {
    if (e.target == modal) {
      modal.style.display = 'none';
    }
  });

  function handleFileChange(input) {
  const nova_foto = input.files[0]?.name || "Nenhum arquivo escolhido";
  document.getElementById("file-name").textContent = nova_foto;
}
