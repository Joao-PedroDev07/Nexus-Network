<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Network - Conectamos você ao profissional ideal</title>
    
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/header.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="assets/img/logo-transparente.png">
</head>
<body>
    <?php include_once("header.php"); ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Conectamos você ao 
                        <span class="highlight">profissional ideal</span>
                    </h1>
                    <p class="hero-description">
                        Encontre prestadores de serviços qualificados e confiáveis perto de você. 
                        Receba orçamentos, compare preços e contrate com segurança.
                    </p>
                </div>

                <!-- Search Section -->
                <div class="search-container">
                    <form id="searchForm" action="search.php" method="GET" class="search-box">
                        <div class="search-inputs-wrapper">
                            <div class="search-input-group">
                                <i class="fas fa-search search-icon"></i>
                                <input 
                                    type="text" 
                                    name="search"
                                    id="searchInput"
                                    class="search-input" 
                                    placeholder="Qual serviço você precisa?"
                                    autocomplete="off"
                                >
                            </div>
                            <div class="location-input-group">
                                <i class="fas fa-map-marker-alt location-icon"></i>
                                <input 
                                    type="text" 
                                    name="location"
                                    id="locationInput"
                                    class="location-input" 
                                    placeholder="Sua localização"
                                    autocomplete="off"
                                    readonly
                                >
                            </div>
                        </div>
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Popular Services -->
                <div class="popular-services">
                    <p class="popular-title">Serviços populares:</p>
                    <div class="service-tags">
                        <span class="service-tag" onclick="buscarServico('Limpeza residencial')">Limpeza residencial</span>
                        <span class="service-tag" onclick="buscarServico('Eletricista')">Eletricista</span>
                        <span class="service-tag" onclick="buscarServico('Encanador')">Encanador</span>
                        <span class="service-tag" onclick="buscarServico('Pintor')">Pintor</span>
                        <span class="service-tag" onclick="buscarServico('Jardineiro')">Jardineiro</span>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-cards">
                    <div class="floating-card card-1">
                        <div class="card-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="card-content">
                            <h4>Profissionais Qualificados</h4>
                            <p>Verificamos todos os prestadores</p>
                        </div>
                    </div>
                    
                    <div class="floating-card card-2">
                        <div class="card-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="card-content">
                            <h4>Pagamento Seguro</h4>
                            <p>Sua transação protegida</p>
                        </div>
                    </div>
                    
                    <div class="floating-card card-3">
                        <div class="card-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="card-content">
                            <h4>Avaliações Reais</h4>
                            <p>Feedback de outros clientes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="how-it-works" id="como-funciona">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Como funciona?</h2>
                <p class="section-subtitle">Em apenas 3 passos simples, você encontra o profissional ideal</p>
            </div>

            <div class="steps-grid">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="step-title">Descreva seu projeto</h3>
                    <p class="step-description">
                        Conte-nos o que precisa fazer e onde. É rápido e gratuito!
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="step-title">Receba propostas</h3>
                    <p class="step-description">
                        Profissionais interessados enviarão orçamentos personalizados para você.
                    </p>
                </div>

                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="step-title">Escolha e contrate</h3>
                    <p class="step-description">
                        Compare perfis, avaliações e preços. Escolha o melhor e contrate com segurança.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories" id="servicos">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Principais categorias</h2>
                <p class="section-subtitle">Encontre profissionais qualificados em diversas áreas</p>
            </div>

            <div class="categories-grid">
                <div class="category-card" onclick="buscarCategoria('limpeza')">
                    <div class="category-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="category-title">Casa & Jardim</h3>
                    <p class="category-description">Limpeza, jardinagem, manutenção</p>
                    <span class="category-count">150+ profissionais</span>
                </div>

                <div class="category-card" onclick="buscarCategoria('reformas')">
                    <div class="category-icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <h3 class="category-title">Reformas</h3>
                    <p class="category-description">Pintura, elétrica, hidráulica</p>
                    <span class="category-count">200+ profissionais</span>
                </div>

                <div class="category-card" onclick="buscarCategoria('tecnologia')">
                    <div class="category-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <h3 class="category-title">Tecnologia</h3>
                    <p class="category-description">Informática, redes, suporte</p>
                    <span class="category-count">80+ profissionais</span>
                </div>

                <div class="category-card" onclick="buscarCategoria('educacao')">
                    <div class="category-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="category-title">Educação</h3>
                    <p class="category-description">Aulas particulares, idiomas</p>
                    <span class="category-count">120+ profissionais</span>
                </div>

                <div class="category-card" onclick="buscarCategoria('beleza')">
                    <div class="category-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h3 class="category-title">Beleza & Bem-estar</h3>
                    <p class="category-description">Estética, massagem, saúde</p>
                    <span class="category-count">90+ profissionais</span>
                </div>

                <div class="category-card" onclick="buscarCategoria('eventos')">
                    <div class="category-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3 class="category-title">Eventos</h3>
                    <p class="category-description">Fotografia, decoração, música</p>
                    <span class="category-count">110+ profissionais</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Carousel -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">O que nossos clientes dizem</h2>
                <p class="section-subtitle">Depoimentos reais de quem já usou nossos serviços</p>
            </div>

            <div class="carousel-container">
                <button class="carousel-btn prev-btn" onclick="previousSlide()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="carousel-wrapper">
                    <div class="carousel-track" id="carouselTrack">
                        <div class="testimonial-card active">
                            <div class="testimonial-content">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <p class="testimonial-text">
                                    Encontrei um eletricista excelente através da plataforma. Serviço rápido, preço justo e profissional muito competente. Recomendo!
                                </p>
                                <div class="testimonial-author">
                                    <strong>Maria Silva</strong>
                                    <span>São Paulo, SP</span>
                                </div>
                            </div>
                        </div>

                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <p class="testimonial-text">
                                    Plataforma muito fácil de usar. Consegui contratar um pintor em poucos minutos. O trabalho ficou perfeito e o preço foi ótimo!
                                </p>
                                <div class="testimonial-author">
                                    <strong>João Santos</strong>
                                    <span>Rio de Janeiro, RJ</span>
                                </div>
                            </div>
                        </div>

                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <p class="testimonial-text">
                                    Excelente experiência! Contratei uma empresa de limpeza e ficou tudo impecável. Super recomendo a Nexus Network!
                                </p>
                                <div class="testimonial-author">
                                    <strong>Ana Costa</strong>
                                    <span>Belo Horizonte, MG</span>
                                </div>
                            </div>
                        </div>

                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <p class="testimonial-text">
                                    Consegui um jardineiro profissional que transformou meu quintal. Processo simples e resultado incrível!
                                </p>
                                <div class="testimonial-author">
                                    <strong>Carlos Mendes</strong>
                                    <span>Brasília, DF</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="carousel-btn next-btn" onclick="nextSlide()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="carousel-indicators">
                <span class="indicator active" onclick="goToSlide(0)"></span>
                <span class="indicator" onclick="goToSlide(1)"></span>
                <span class="indicator" onclick="goToSlide(2)"></span>
                <span class="indicator" onclick="goToSlide(3)"></span>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Pronto para começar?</h2>
                <p class="cta-description">
                    Junte-se a milhares de pessoas que já encontraram o profissional ideal
                </p>
                <div class="cta-buttons">
                    <a href="search.php" class="btn-primary">Buscar profissionais</a>
                    <a href="cad_prestadores.php" class="btn-secondary">Oferecer serviços</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">NexusNetwork</h3>
                <p class="footer-description">
                    A plataforma que conecta você ao profissional ideal para qualquer projeto.
                </p>
                <div class="social-links">
                    <a href="https://www.instagram.com/nexus_network.oficial?igsh=MmJxMDM4ZmczazEx" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="https://x.com/NexusNetwo65763" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.linkedin.com/in/nexus-network-889452398/" class="social-link"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h4 class="footer-subtitle">Para clientes</h4>
                <ul class="footer-links">
                    <li><a href="sobre.php#como-funciona">Como funciona</a></li>
                    <li><a href="search.php">Buscar profissionais</a></li>
                    <li><a href="suporte.php">Central de ajuda</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-subtitle">Para profissionais</h4>
                <ul class="footer-links">
                    <li><a href="cad_prestadores.php">Cadastre-se</a></li>
                    <li><a href="sobre.php">Como funciona</a></li>
                    <li><a href="suporte.php">Suporte</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-subtitle">Empresa</h4>
                <ul class="footer-links">
                    <li><a href="sobre.php">Sobre nós</a></li>
                    <li><a href="#">Carreiras</a></li>
                    <li><a href="#">Imprensa</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; 2025 NexusNetwork. Todos os direitos reservados.</p>
                <div class="footer-legal">
                    <a href="#">Termos de uso</a>
                    <a href="#">Política de privacidade</a>
                    <a href="#">Cookies</a>
                </div>
            </div>
        </div>
    </div>
</footer>

    <!-- Scripts -->
    <script src="assets/js/header.js"></script>
    
    <script>
        // Carousel functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.testimonial-card');
        const indicators = document.querySelectorAll('.indicator');
        const totalSlides = slides.length;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
            
            currentSlide = index;
        }

        function nextSlide() {
            const next = (currentSlide + 1) % totalSlides;
            showSlide(next);
        }

        function previousSlide() {
            const prev = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(prev);
        }

        function goToSlide(index) {
            showSlide(index);
        }

        setInterval(nextSlide, 5000);

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        window.addEventListener('scroll', () => {
            const header = document.querySelector('.universal-header');
            if (header) {
                if (window.scrollY > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }
        });

        // Detectar localização do usuário
        function detectarLocalizacao() {
            const locationInput = document.getElementById('locationInput');
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        
                        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`, {
                            headers: {
                                'User-Agent': 'NexusNetwork/1.0'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.address) {
                                const cidade = data.address.city || data.address.town || data.address.village || '';
                                const estado = data.address.state || '';
                                locationInput.value = cidade ? `${cidade}, ${estado}` : 'Localização detectada';
                            } else {
                                locationInput.value = 'Usar minha localização';
                            }
                        })
                        .catch(() => {
                            locationInput.value = 'Usar minha localização';
                        });
                    },
                    function(error) {
                        console.log('Erro ao obter localização:', error);
                        locationInput.value = 'Todas as regiões';
                    }
                );
            } else {
                locationInput.value = 'Todas as regiões';
            }
        }

        function buscarServico(servico) {
            document.getElementById('searchInput').value = servico;
            document.getElementById('searchForm').submit();
        }

        function buscarCategoria(categoria) {
            const categorias = {
                'limpeza': 'Limpeza',
                'reformas': 'Pintor Eletricista Encanador',
                'tecnologia': 'Programador',
                'educacao': 'Professora',
                'beleza': 'Manicure Cabeleireiro',
                'eventos': 'Fotógrafo DJ'
            };
            
            document.getElementById('searchInput').value = categorias[categoria] || '';
            document.getElementById('searchForm').submit();
        }

        window.addEventListener('DOMContentLoaded', detectarLocalizacao);
    </script>
</body>
</html>