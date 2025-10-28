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

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalUsuario");
    const fechar = document.getElementById("fecharModal");
    const botoesEditar = document.querySelectorAll(".btn-editar");

    const infoUsuario = document.getElementById("infoUsuario");
    const emailUsuario = document.getElementById("emailUsuario");
    const nivelUsuario = document.getElementById("nivelUsuario");
    const fotoPerfil = document.getElementById("fotoPerfil");

    console.log('botões editar encontrados:', botoesEditar.length);

    botoesEditar.forEach(botao => {
        botao.addEventListener("click", () => {
            const nome = botao.dataset.nome || '';
            const email = botao.dataset.email || '';
            const nivel = botao.dataset.nivel || '';
            const foto = botao.dataset.foto || './assets/img/imagem-padrao.png';

            infoUsuario.textContent = `${nome}`;
            emailUsuario.textContent = email;
            nivelUsuario.textContent = nivel;

            fotoPerfil.style.backgroundImage = `url("${foto}")`;

            modal.style.display = "flex";
            modal.setAttribute('aria-hidden', 'false');
        });
    });


    if (fechar) {
        fechar.addEventListener("click", () => {
            modal.style.display = "none";
            modal.setAttribute('aria-hidden', 'true');
        });
    }


    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
            modal.setAttribute('aria-hidden', 'true');
        }
    });
});
