<?php
require_once __DIR__ . "/foto_helper.php";

// Verificar se o usuário está logado
$usuario_logado = isset($_SESSION['logado']) && $_SESSION['logado'] === true;
$nome_usuario = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : '';
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : '';
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '';
// Usa o avatar padrão se o usuário não tiver foto ou se o arquivo não existir
$foto_usuario = fotoOuPadrao($_SESSION['usuario_foto'] ?? '');
?>
<!-- Header Universal Nexus Network - Desktop Simples -->
<header class="universal-header">
    <nav class="universal-navbar">
        <div class="universal-nav-container">
            <!-- Logo/Brand -->
            <div class="universal-brand">
                <a href="index.php" class="brand-link">
                    <div class="brand-container">
                        <div class="brand-icon">
                            <img src="assets/img/logo3.jpg" alt="Nexus Network" class="site-logo">
                        </div>
                        <div class="brand-text">
                            <h1 class="brand-title">Nexus Network</h1>
                            <span class="brand-subtitle">Conectando serviços</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Navegação Principal -->
            <div class="nav-menu">
                <ul class="nav-menu-list">
                    <li class="nav-menu-item">
                        <a href="index.php" class="nav-menu-link">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-menu-item">
                        <a href="sobre.php" class="nav-menu-link">
                            <i class="fas fa-info-circle"></i> Sobre
                        </a>
                    </li>
                    <li class="nav-menu-item">
                        <a href="index.php#servicos" class="nav-menu-link">
                            <i class="fas fa-briefcase"></i> Serviços
                        </a>
                    </li>
                    <li class="nav-menu-item dropdown">
                        <a href="#" class="nav-menu-link dropdown-toggle">
                            <i class="fas fa-chevron-down dropdown-arrow"></i> Mais
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="index.php#como-funciona" class="dropdown-link">
                                <i class="fas fa-question-circle"></i> Como funciona
                            </a></li>
                            <li><a href="suporte.php" class="dropdown-link">
                                <i class="fas fa-headset"></i> Suporte
                            </a></li>
                            <li><a href="contato.php" class="dropdown-link">
                                <i class="fas fa-envelope"></i> Contato
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <!-- Botões de AÇÃO -->
            <div class="nav-actions">
                <?php if ($usuario_logado): ?>
                    <!-- Menu do usuário logado -->
                    <div class="user-menu dropdown">
                        <button class="btn-user dropdown-toggle">
                            <img src="<?php echo htmlspecialchars($foto_usuario); ?>" alt="<?php echo htmlspecialchars($nome_usuario); ?>" class="user-avatar">
                            <span class="user-name"><?php echo htmlspecialchars(explode(' ', $nome_usuario)[0]); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu user-dropdown-menu">
                            <?php if ($tipo_usuario === 'cliente'): ?>
                                <li><a href="perfil_cliente.php" class="dropdown-link">
                                    <i class="fas fa-user"></i> Meu Perfil
                                </a></li>
                                <li><a href="search.php" class="dropdown-link">
                                    <i class="fas fa-search"></i> Buscar Serviços
                                </a></li>
                                <li><a href="lista-chat.php" class="dropdown-link">
                                    <i class="fas fa-calendar"></i> Conversas
                                </a></li>
                            <?php else: ?>
                                <li><a href="perfil_prestador.php" class="dropdown-link">
                                    <i class="fas fa-briefcase"></i> Meu Perfil
                                </a></li>
                                <li><a href="lista-chat.php" class="dropdown-link">
                                    <i class="fas fa-calendar"></i> Conversas
                                </a></li>
                            <?php endif; ?>
                            <li class="dropdown-divider"></li>
                            <li><a href="logout.php" class="dropdown-link logout-link">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Botões padrão (não logado) -->
                    <a href="login.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </a>
                
                    <a href="cad_prestadores.php" class="btn-register">
                        <i class="fas fa-user-plus"></i> Seja um Prestador
                    </a>
                <?php endif; ?>
            </div>
        </div>


        
                <!--API VLibras-->

        <div vw class="enabled">
            <div vw-access-button class="active"></div>
            <div vw-plugin-wrapper>
                <div class="vw-plugin-top-wrapper"></div>
            </div>
        </div>
        <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
        <script>
            new window.VLibras.Widget('https://vlibras.gov.br/app');

            // Verifica se o VLibras estava ativado anteriormente
            window.addEventListener('load', () => {
                const vlibrasAtivo = localStorage.getItem('vlibrasAtivo');
                
                if (vlibrasAtivo === 'true') {
                    // Aguarda o VLibras carregar completamente
                    setTimeout(() => {
                        const btnVLibras = document.querySelector('[vw-access-button]');
                        if (btnVLibras && !btnVLibras.classList.contains('active')) {
                            btnVLibras.click();
                        }
                    }, 1000);
                }
            });

            // Monitora quando o usuário ativa/desativa o VLibras
            const observer = new MutationObserver(() => {
                const btnVLibras = document.querySelector('[vw-access-button]');
                if (btnVLibras) {
                    const estaAtivo = btnVLibras.classList.contains('active');
                    localStorage.setItem('vlibrasAtivo', estaAtivo);
                }
            });

            // Observa mudanças no botão do VLibras
            setTimeout(() => {
                const btnVLibras = document.querySelector('[vw-access-button]');
                if (btnVLibras) {
                    observer.observe(btnVLibras, { 
                        attributes: true, 
                        attributeFilter: ['class'] 
                    });
                }
            }, 1000);
        </script>


                <!--API de acessibilidade para cegos-->

        <div class="acessibilidade-container">
            <button id="btnLeitura" data-status="Leitura Automática: Desativado" title="Ativar/Desativar leitura ao passar o mouse">🔊</button>
        </div>

        <script>
            let leituraAtiva = false;
            let timeoutLeitura = null;

            // Verifica se a leitura estava ativada anteriormente
            function inicializarLeitura() {
                const estadoSalvo = localStorage.getItem('leituraAutomaticaAtiva');
                
                if (estadoSalvo === 'true') {
                    leituraAtiva = true;
                    atualizarInterfaceLeitura();
                    ativarLeituraHover();
                } else {
                    leituraAtiva = false;
                    atualizarInterfaceLeitura();
                }
            }

            // Atualiza a interface do botão
            function atualizarInterfaceLeitura() {
                const btnLeitura = document.getElementById('btnLeitura');
                
                if (leituraAtiva) {
                    btnLeitura.classList.add('ativo');
                    btnLeitura.setAttribute('data-status', 'Leitura Automática: Ativado');
                } else {
                    btnLeitura.classList.remove('ativo');
                    btnLeitura.setAttribute('data-status', 'Leitura Automática: Desativado');
                }
            }

            // Botão para ativar/desativar a leitura automática
            document.getElementById('btnLeitura').addEventListener('click', () => {
                leituraAtiva = !leituraAtiva;
                
                // Salva o estado no localStorage
                localStorage.setItem('leituraAutomaticaAtiva', leituraAtiva);
                
                atualizarInterfaceLeitura();
                
                if (leituraAtiva) {
                    ativarLeituraHover();
                } else {
                    desativarLeituraHover();
                    window.speechSynthesis.cancel(); // Para qualquer leitura em andamento
                }
            });

            function ativarLeituraHover() {
                // Adiciona evento a todos os elementos de texto da página
                document.body.addEventListener('mouseover', lerElemento);
            }

            function desativarLeituraHover() {
                document.body.removeEventListener('mouseover', lerElemento);
            }

            function lerElemento(e) {
                if (!leituraAtiva) return;

                const elemento = e.target;
                
                // Ignora elementos que não devem ser lidos
                if (elemento.tagName === 'SCRIPT' || 
                    elemento.tagName === 'STYLE' || 
                    elemento.classList.contains('acessibilidade-container') ||
                    elemento.id === 'btnLeitura' ||
                    elemento.id === 'statusLeitura' ||
                    elemento.closest('[vw]')) {
                    return;
                }

                // Cancela o timeout anterior se houver
                if (timeoutLeitura) {
                    clearTimeout(timeoutLeitura);
                }

                // Aguarda 500ms antes de ler (evita leitura acidental)
                timeoutLeitura = setTimeout(() => {
                    const texto = elemento.textContent.trim();
                    
                    // Lê apenas se tiver texto e não for muito longo
                    if (texto && texto.length > 0 && texto.length < 500) {
                        window.speechSynthesis.cancel(); // Cancela leitura anterior
                        
                        const msg = new SpeechSynthesisUtterance(texto);
                        msg.lang = "pt-BR";
                        msg.rate = 1;
                        msg.volume = 1;
                        window.speechSynthesis.speak(msg);
                    }
                }, 500);
            }

            // Inicializa a leitura ao carregar a página
            inicializarLeitura();

            // Para a leitura quando sair da página
            window.addEventListener('beforeunload', () => {
                window.speechSynthesis.cancel();
            });
        </script>

    </nav>
</header>