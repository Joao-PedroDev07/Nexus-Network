<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre - Nexus Network</title>
    
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/sobre.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/img/logo-transparente.png">
    <link rel="stylesheet" href="assets/css/global-fixes.css">
</head>
<body>
    <?php include_once("header.php"); ?>

    <!-- Hero Section -->
    <section class="hero-sobre">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Sobre a <span class="highlight">Nexus Network</span></h1>
                <p class="hero-subtitle">Conectando profissionais qualificados e clientes confiáveis desde 2024</p>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
        <div class="container">
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Nossa Missão</h3>
                    <p>Conectar milhares de profissionais qualificados com clientes que buscam serviços confiáveis, criando um ecossistema seguro e transparente.</p>
                </div>

                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Nossa Visão</h3>
                    <p>Ser a plataforma mais confiável e fácil de usar para contratar serviços profissionais em toda América Latina.</p>
                </div>

                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Nossos Valores</h3>
                    <p>Confiança, transparência, qualidade e compromisso com o sucesso de profissionais e clientes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3 class="stat-number">+ 50 MIL</h3>
                    <p class="stat-label">Profissionais Cadastrados</p>
                </div>

                <div class="stat-card">
                    <h3 class="stat-number">+ 150 MIL</h3>
                    <p class="stat-label">Clientes Satisfeitos</p>
                </div>

                <div class="stat-card">
                    <h3 class="stat-number"> + 500 MIL</h3>
                    <p class="stat-label">Projetos Concluídos</p>
                </div>

                <div class="stat-card">
                    <h3 class="stat-number">4.8★</h3>
                    <p class="stat-label">Avaliação Média</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">Conheça Nossa Equipe</h2>
            
            <div class="team-grid">
                <!-- Membro 1: João -->
                <div class="team-member">
                    <div class="member-image">
                        <img src="assets/img/team/joao.jpeg" alt="João Pedro Santos">
                    </div>
                    <h4>João Pedro Santos</h4>
                    <p class="member-role">Desenvolvedor</p>
                    <p class="member-bio">Empreendedor com 10 anos de experiência em tech e marketplace</p>
                </div>


                <!-- Membro 3: Nicolas -->
                <div class="team-member">
                    <div class="member-image">
                        <img src="assets/img/team/nicolas.png" alt="Nicolas Sandoval">
                    </div>
                    <h4>Nicolas Sandoval</h4>
                    <p class="member-role"> CEO</p>
                    <p class="member-bio">Desenvolvedor full-stack com expertise em plataformas escaláveis</p>
                </div>

                <!-- Membro 4: Gustavo -->
                <div class="team-member">
                    <div class="member-image">
                        <img src="assets/img/team/gustavo.png" alt="Gustavo Castro">
                    </div>
                    <h4>Gustavo Castro</h4>
                    <p class="member-role">Desenvolvedor</p>
                    <p class="member-bio">Dedicado a garantir satisfação e sucesso dos nossos usuários</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-sobre">
        <div class="container">
            <h2>Pronto para começar?</h2>
            <p>Junte-se à comunidade Nexus Network e encontre o profissional ideal</p>
            <div class="cta-buttons">
                <a href="index.php" class="btn-primary">Buscar Profissionais</a>
                <a href="contato.php" class="btn-secondary">Entre em Contato</a>
            </div>
        </div>
    </section>

    <?php include_once("footer.php"); ?>

    <script src="assets/js/header.js"></script>
    
    <!-- Script para debug de imagens -->
    <script>
        // Detecta imagens que não carregaram
        document.querySelectorAll('.member-image img').forEach(img => {
            img.onerror = function() {
                console.error('Falha ao carregar imagem:', this.src);
                this.style.display = 'none';
                this.nextElementSibling.style.display = 'flex';
            };
            
            img.onload = function() {
                console.log('Imagem carregada com sucesso:', this.src);
                this.style.display = 'block';
                this.nextElementSibling.style.display = 'none';
            };
        });
    </script>
</body>
</html>