<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - Nexus Network</title>
    
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/contato.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/img/logo-transparente.png">
    <link rel="stylesheet" href="assets/css/global-fixes.css">
    
    <style>
        /* Estilos para mensagens de feedback */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 16px;
            display: none;
            animation: slideDown 0.3s ease;
        }
        
        .alert.show {
            display: block;
        }
        
        .alert-success {
            background: rgba(32, 205, 141, 0.1);
            border: 1px solid rgba(32, 205, 141, 0.3);
            color: #20cd8d;
        }
        
        .alert-error {
            background: rgba(255, 82, 82, 0.1);
            border: 1px solid rgba(255, 82, 82, 0.3);
            color: #ff5252;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
            margin-left: 8px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php include_once("header.php"); ?>

    <!-- Hero Section -->
    <section class="hero-contato">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Entre em <span class="highlight">Contato</span></h1>
                <p class="hero-subtitle">Estamos sempre prontos para ouvir você. Envie uma mensagem e retornaremos em breve</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="contact-main">
        <div class="container">
            <div class="contact-wrapper">
                <!-- Contact Form -->
                <div class="contact-form-section">
                    <h2>Envie uma mensagem</h2>
                    
                    <!-- Mensagem de feedback -->
                    <div id="alertSuccess" class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Mensagem enviada com sucesso! Retornaremos em breve.
                    </div>
                    
                    <div id="alertError" class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <span id="errorMessage"></span>
                    </div>
                    
                    <form class="contact-form" id="contactForm">
                        <div class="form-group">
                            <label for="nome">Nome completo</label>
                            <input 
                                type="text" 
                                id="nome" 
                                name="nome" 
                                required
                                placeholder="Seu nome"
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required
                                placeholder="seu@email.com"
                            >
                        </div>

                        <div class="form-group">
                            <label for="telefone">Telefone (opcional)</label>
                            <input 
                                type="tel" 
                                id="telefone" 
                                name="telefone" 
                                placeholder="(11) 9999-9999"
                            >
                        </div>

                        <div class="form-group">
                            <label for="assunto">Assunto</label>
                            <select id="assunto" name="assunto" required>
                                <option value="">Selecione um assunto</option>
                                <option value="duvida">Dúvida sobre a plataforma</option>
                                <option value="problema">Reportar um problema</option>
                                <option value="sugestao">Sugestão de melhoria</option>
                                <option value="parceria">Proposta de parceria</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="mensagem">Mensagem</label>
                            <textarea 
                                id="mensagem" 
                                name="mensagem" 
                                rows="6" 
                                required
                                placeholder="Conte-nos mais detalhes sobre sua mensagem..."
                            ></textarea>
                        </div>

                        <div class="form-checkbox">
                            <input type="checkbox" id="privacidade" name="privacidade" required>
                            <label for="privacidade">Concordo com a política de privacidade</label>
                        </div>

                        <button type="submit" class="btn-submit" id="btnSubmit">
                            <span id="btnText">Enviar Mensagem</span>
                        </button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="contact-info-section">
                    <h2>Informações de contato</h2>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Endereço</h4>
                            <p>
                                Rua da Inovação, 123<br>
                                São Paulo, SP - 01310-100<br>
                                Brasil
                            </p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h4>Telefone</h4>
                            <p>
                                <a href="tel:+551133334444">(11) 3333-4444</a><br>
                                <small>Seg-Sex: 8h às 20h</small>
                            </p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p>
                                <a href="mailto:nexustcc5@gmail.com">nexustcc5@gmail.com</a><br>
                                <small>Resposta em até 24h</small>
                            </p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h4>Horário de funcionamento</h4>
                            <p>
                                <strong>Segunda a Sexta:</strong> 8h - 20h<br>
                                <strong>Sábado:</strong> 9h - 17h<br>
                                <strong>Domingo:</strong> Fechado
                            </p>
                        </div>
                    </div>

                    <div class="social-section">
                        <h4>Siga-nos nas redes sociais</h4>
                        <div class="social-links">
                            <a href="#" class="social-icon" title="Facebook">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="#" class="social-icon" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-icon" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-icon" title="LinkedIn">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="map-container">
            <!-- Mapa do Google Maps - São Paulo, SP -->
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3657.1974892603283!2d-46.65593368502207!3d-23.561414484682923!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94ce59c8da0aa315%3A0xd59f9431f2c9776a!2sAv.%20Paulista%2C%20S%C3%A3o%20Paulo%20-%20SP!5e0!3m2!1spt-BR!2sbr!4v1635363636363!5m2!1spt-BR!2sbr"
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy"
                title="Localização Nexus Network">
            </iframe>
        </div>
    </section>

    <!-- FAQ Quick Links -->
    <section class="faq-quick">
        <div class="container">
            <h2 class="section-title">Dúvidas frequentes?</h2>
            <p class="section-subtitle">Confira nossa base de conhecimento antes de entrar em contato</p>
            
            <div class="faq-quick-grid">
                <a href="suporte.php#faq" class="faq-quick-card">
                    <i class="fas fa-question-circle"></i>
                    <h4>Acessar FAQ</h4>
                    <p>Respostas para as perguntas mais comuns</p>
                </a>

                <a href="sobre.php" class="faq-quick-card">
                    <i class="fas fa-info-circle"></i>
                    <h4>Sobre Nexus Network</h4>
                    <p>Conheça mais sobre nossa empresa</p>
                </a>

                <a href="suporte.php" class="faq-quick-card">
                    <i class="fas fa-headset"></i>
                    <h4>Centro de Suporte</h4>
                    <p>Acesse nossa central de atendimento</p>
                </a>

        </div>
    </section>

    <?php include_once("footer.php"); ?>

    <script src="assets/js/header.js"></script>
    <script>
        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = this;
            const btnSubmit = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnText');
            const alertSuccess = document.getElementById('alertSuccess');
            const alertError = document.getElementById('alertError');
            const errorMessage = document.getElementById('errorMessage');
            
            // Ocultar alertas
            alertSuccess.classList.remove('show');
            alertError.classList.remove('show');
            
            // Desabilitar botão e mostrar loading
            btnSubmit.disabled = true;
            btnText.innerHTML = 'Enviando <span class="loading-spinner"></span>';
            
            try {
                const formData = new FormData(form);
                
                const response = await fetch('enviar_contato.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Verificar se a resposta é válida
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Resposta inválida do servidor');
                }
                
                const data = await response.json();
                
                if (data.sucesso) {
                    // Sucesso
                    alertSuccess.classList.add('show');
                    form.reset();
                    
                    // Scroll para o topo do formulário
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Ocultar mensagem após 5 segundos
                    setTimeout(() => {
                        alertSuccess.classList.remove('show');
                    }, 5000);
                    
                } else {
                    // Erro
                    const erros = data.erro && data.erro.length > 0 
                        ? data.erro.join('<br>') 
                        : 'Erro ao enviar mensagem.';
                    errorMessage.innerHTML = erros;
                    alertError.classList.add('show');
                    
                    // Scroll para o topo do formulário
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                
            } catch (error) {
                console.error('Erro completo:', error);
                errorMessage.innerHTML = 'Erro ao enviar mensagem. Verifique sua conexão e tente novamente.<br><small>Se o problema persistir, entre em contato por telefone ou email.</small>';
                alertError.classList.add('show');
                
                // Scroll para o topo do formulário
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } finally {
                // Reabilitar botão
                btnSubmit.disabled = false;
                btnText.textContent = 'Enviar Mensagem';
            }
        });
        
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
            
            e.target.value = value;
        });
    </script>
</body>
</html>