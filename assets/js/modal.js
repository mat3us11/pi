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

            infoUsuario.textContent = `Nome: ${nome}`;
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
