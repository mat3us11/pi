document.addEventListener('DOMContentLoaded', () => {
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const mobileModal = document.getElementById('mobileModal');
  const hamburgerIcon = document.getElementById('hamburgerIcon');

  let menuOpen = false;

  if (hamburgerBtn && mobileModal && hamburgerIcon) {
    hamburgerBtn.addEventListener('click', () => {
      menuOpen = !menuOpen;
      if (menuOpen) {
        mobileModal.classList.add('active');
        mobileModal.setAttribute('aria-hidden', 'false');
        hamburgerIcon.className = 'ph ph-x';
        hamburgerBtn.setAttribute('aria-label', 'Fechar menu');
      } else {
        mobileModal.classList.remove('active');
        mobileModal.setAttribute('aria-hidden', 'true');
        hamburgerIcon.className = 'ph ph-list';
        hamburgerBtn.setAttribute('aria-label', 'Abrir menu');
      }
    });

    window.addEventListener('click', (e) => {
      if (
        menuOpen &&
        !mobileModal.contains(e.target) &&
        !hamburgerBtn.contains(e.target)
      ) {
        menuOpen = false;
        mobileModal.classList.remove('active');
        mobileModal.setAttribute('aria-hidden', 'true');
        hamburgerIcon.className = 'ph ph-list';
        hamburgerBtn.setAttribute('aria-label', 'Abrir menu');
      }
    });
  }

  // Modal de edição de perfil
  const btnEditar = document.querySelector('.btn-perfil[href="#"]');
  const modal = document.getElementById('modal-editar');
  const fechar = document.getElementById('fechar-modal');

  if (btnEditar && modal && fechar) {
    btnEditar.addEventListener('click', function (e) {
      e.preventDefault();
      modal.style.display = 'block';
    });

    fechar.addEventListener('click', function () {
      modal.style.display = 'none';
    });

    window.addEventListener('click', function (e) {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });
  }

  // Mostrar nome do arquivo ao selecionar nova foto
  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) {
    fileInput.addEventListener('change', function () {
      const fileName = this.files[0]?.name || "Nenhum arquivo escolhido";
      const fileNameDisplay = document.getElementById("file-name");
      if (fileNameDisplay) {
        fileNameDisplay.textContent = fileName;
      }
    });
  }
});
