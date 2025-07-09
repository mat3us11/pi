<header>
    <div class="container">
        <div class="left">
            <div class="top-left">
                <a href="./index.php">
                    <p class="logo">CAMP<span>VIA</span></p>
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="#"><i class="ph ph-bicycle"></i> Passeios</a></li>
                <li><a href="#"><i class="ph ph-map-trifold"></i> Roteiros de Viagem</a></li>
            </ul>
        </div>
        <div class="right">
            <?php if (!isset($ocultarBotoesHeader) || !$ocultarBotoesHeader): ?>
                <div class="top-right">
                    <a href="cadastro.php"><button class="btn btn-outline">Cadastre-se</button></a>
                    <a href="login.php"><button class="btn btn-fill">Login</button></a>
                </div>
            <?php endif; ?>
            <div class="icons">
                <i class="ph ph-clock-counter-clockwise"></i>
                <i class="ph ph-question"></i>
            </div>
        </div>
    </div>
</header>

<!-- Botão Hamburguer -->
<button class="hamburger" id="hamburgerBtn" aria-label="Abrir menu">
    <i class="ph ph-list" id="hamburgerIcon"></i>
</button>

<!-- Modal Mobile -->
<div class="mobile-modal" id="mobileModal" aria-hidden="true">
    <nav class="modal-conteudo">
        <div class="perfil">
            <div class="foto-perfil">
            </div>
            <div class="cadastro-login">
                <a href="cadastro.php"><button class="btn btn-outline">Cadastre-se</button></a>
                <a href="login.php"><button class="btn btn-fill">Login</button></a>
            </div>

        </div>
        <ul class="modal-nav">
            <li><a href=""><button class="nav-link"><i class="ph ph-map-trifold"></i> Passeios</button></li></a>
            <li><a href=""><button class="nav-link"><i class="ph ph-map-pin"></i> Roteiros</button></li></a>
            <li><a href=""><button class="nav-link"><i class="ph ph-clock-clockwise"></i> Histórico</button></li></a>
            <li><a href=""><button class="nav-link"><i class="ph ph-question"></i> Dúvidas</button></li></a>
        </ul>
    </nav>
</div>


<?php if (!isset($ocultarImagemHeader) || !$ocultarImagemHeader): ?>
    <img class="imagem-desktop" src="./assets/img/header-index.png" alt="">
<?php endif; ?>

<script src="https://unpkg.com/@phosphor-icons/web"></script>