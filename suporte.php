<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte - Nexus Network</title>
    
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/suporte.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/img/logo-transparente.png">
    <link rel="stylesheet" href="assets/css/global-fixes.css">
</head>
<body>
    <?php include_once("header.php"); ?>

    <!-- Hero Section -->
    <section class="hero-suporte">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Central de <span class="highlight">Suporte</span></h1>
                <p class="hero-subtitle">Estamos aqui para ajudar. Encontre respostas rápidas e fale com nossa equipe</p>
            </div>
        </div>
    </section>



    <!-- Support Channels -->
    <section class="support-channels">
        <div class="container">
            <h2 class="section-title">Como podemos ajudar?</h2>
            
            <div class="channels-grid">
                <div class="channel-card">
                    <div class="channel-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h3>FAQ</h3>
                    <p>Perguntas frequentes respondidas por nossa equipe</p>
                    <a href="#faq" class="channel-link">Acessar FAQ <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="channel-card">
                    <div class="channel-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email</h3>
                    <p>Envie sua dúvida para nossa equipe</p>
                    <a href="mailto:nexustcc5@gmail.com.br" class="channel-link"> nexustcc5@gmail.com <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="channel-card">
                    <div class="channel-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>Telefone</h3>
                    <p>Ligue para nossa central de atendimento</p>
                    <a href="tel:+551133334444" class="channel-link">(18) 3333-4444 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section" id="faq">
        <div class="container">
            <h2 class="section-title">Perguntas Frequentes</h2>

            <div class="faq-tabs">
                <button class="tab-btn active" data-category="clientes">Para Clientes</button>
                <button class="tab-btn" data-category="profissionais">Para Profissionais</button>
                <button class="tab-btn" data-category="pagamento">Pagamento</button>
                <button class="tab-btn" data-category="seguranca">Segurança</button>
            </div>

            <!-- Clientes -->
            <div class="faq-content active" data-category="clientes">
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Como faço para encontrar profissionais?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Acesse a página inicial, descreva seu projeto no campo de busca, indique sua localização e clique em "Buscar". Você verá uma lista de profissionais qualificados na sua área.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>É cobrada alguma taxa para usar a plataforma?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>A busca e visualização de profissionais é totalmente gratuita. Você só paga ao contratar um profissional. A taxa varia de acordo com o serviço, mas é sempre informada antes da confirmação.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Posso desistir de um projeto depois de contratado?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Você pode cancelar até 24 horas antes do início agendado sem custos adicionais. Depois disso, a política de cancelamento depende dos termos acordados com o profissional.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Como avaliações dos profissionais funcionam?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Após a conclusão do trabalho, ambas as partes podem avaliar mutuamente. As avaliações são baseadas em critérios como qualidade, profissionalismo e cumprimento de prazos.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>O que fazer se não estiver satisfeito com o trabalho?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Você pode abrir uma disputa na plataforma dentro de 7 dias após a conclusão. Nossa equipe analisará o caso e tomará as medidas necessárias para resolver a situação.</p>
                    </div>
                </div>
            </div>

            <!-- Profissionais -->
            <div class="faq-content" data-category="profissionais">
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Como me cadastro como profissional?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Clique em "Oferecer serviços" na página inicial. Preencha seu perfil com informações sobre sua experiência, especialidades e documentos de verificação. Após aprovação, você já pode receber projetos.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Quais documentos preciso para me cadastrar?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Você precisa de RG/CNH, CPF e comprovante de endereço. Para profissionais que trabalham em setores específicos, podem ser solicitados certificados ou licenças adicionais.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Como recebo os pagamentos dos trabalhos?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Os pagamentos são transferidos para sua conta bancária após 48 horas da conclusão do trabalho e aprovação do cliente. A taxa de processamento é de 10% sobre o valor do serviço.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Posso recusar um projeto depois de aceitar?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Você pode recusar até 2 vezes por mês sem penalidades. Acima disso, pode afetar sua taxa de aceitação e visibilidade de novos projetos. Para cancelamentos na última hora, aplica-se uma multa.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Como melhorar minhas chances de ser contratado?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Mantenha um perfil completo com foto profissional, descrição detalhada de serviços e portfólio. Responda rapidamente às solicitações, cumpra prazos e mantenha boas avaliações.</p>
                    </div>
                </div>
            </div>

            <!-- Pagamento -->
            <div class="faq-content" data-category="pagamento">
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Quais formas de pagamento são aceitas?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Aceitamos cartão de crédito, débito, transferência bancária e carteiras digitais como Google Pay e Apple Pay. Para profissionais, o pagamento é sempre feito via transferência bancária.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>O dinheiro fica retido em algum momento?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sim, o cliente paga quando contrata o serviço. O dinheiro fica em custódia até que o profissional conclua o trabalho e o cliente o aprove, garantindo segurança para ambas as partes.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Há reembolso se eu desistir?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sim, você recebe reembolso total se cancelar até 24 horas antes. Cancelamentos posteriores podem ter descontos conforme a política de cancelamento do profissional.</p>
                    </div>
                </div>
            </div>

            <!-- Segurança -->
            <div class="faq-content" data-category="seguranca">
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Como você verifica os profissionais?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Todos os profissionais passam por um processo de verificação que inclui validação de documentos, verificação de antecedentes e análise de qualificações. Avaliações de clientes também são monitoradas.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Meus dados estão seguros na plataforma?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sim, utilizamos criptografia SSL de 256 bits e seguimos as normas LGPD. Seus dados pessoais e bancários são protegidos com os mais altos padrões de segurança.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h4>O que fazer se suspeitar de fraude?</h4>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Reporte imediatamente clicando no botão "Denunciar" no perfil ou contato. Nossa equipe de segurança investigará e tomará medidas. Você também pode enviar um email para seguranca@nexusnetwork.com.br</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <h2 class="section-title">Ainda tem dúvidas?</h2>
            <p class="section-subtitle">Nosso time de suporte está sempre pronto para ajudar</p>
            
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p><a href="mailto:nexustcc5@gmail.com">nexustcc5@gmail.com</a></p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h4>Telefone</h4>
                        <p><a href="tel:+551133334444">(18) 3333-4444</a></p>
                        <small>Seg-Sex: 8h às 20h</small>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Tempo de resposta</h4>
                        <p>Respondemos em até 24h</p>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once("footer.php"); ?>
    </section>

    <script src="assets/js/header.js"></script>
    <script>
        // FAQ Toggle
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                const item = this.parentElement;
                item.classList.toggle('active');
            });
        });

        // Tab Switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.faq-content').forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                document.querySelector(`.faq-content[data-category="${category}"]`).classList.add('active');
            });
        });

        // Search FAQ
        document.getElementById('searchFaq').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.faq-item').forEach(item => {
                const question = item.querySelector('h4').textContent.toLowerCase();
                const answer = item.querySelector('p').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        function openLiveChat() {
            alert('Chat ao vivo indisponível no momento. Envie um email para suporte@nexusnetwork.com.br');
        }
    </script>
</body>
</html>