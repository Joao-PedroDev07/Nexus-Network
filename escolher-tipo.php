<?php
session_start();
require_once 'conexao.php';
require_once 'google_funcoes.php';

// Verificar se há dados do Google na sessão
if (!isset($_SESSION['google_temp']) || !validarDadosGoogle($_SESSION['google_temp'])) {
    header('Location: login.php?erro=' . urlencode('Sessão inválida'));
    exit;
}

$dados_google = $_SESSION['google_temp'];
$erros = [];
$sucesso = '';

// Processar formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_usuario = filter_input(INPUT_POST, 'tipo_usuario');
    
    if (empty($tipo_usuario) || !in_array($tipo_usuario, ['cliente', 'prestador'])) {
        $erros[] = 'Por favor, selecione o tipo de usuário.';
    }
    
    if (empty($erros)) {
        // Salvar tipo escolhido na sessão
        $_SESSION['google_temp']['tipo_escolhido'] = $tipo_usuario;
        
        // IMPORTANTE: Garantir que os dados essenciais estão na sessão
        // antes de redirecionar para o formulário de cadastro
        if (!isset($_SESSION['google_temp']['email'])) {
            $_SESSION['google_temp']['email'] = $dados_google['email'] ?? '';
        }
        if (!isset($_SESSION['google_temp']['name'])) {
            $_SESSION['google_temp']['name'] = $dados_google['name'] ?? '';
        }
        if (!isset($_SESSION['google_temp']['picture'])) {
            $_SESSION['google_temp']['picture'] = $dados_google['picture'] ?? '';
        }
        if (!isset($_SESSION['google_temp']['id'])) {
            $_SESSION['google_temp']['id'] = $dados_google['id'] ?? '';
        }
        
        // Log para debug (remover em produção)
        error_log("Google temp data: " . json_encode($_SESSION['google_temp']));
        
        if ($tipo_usuario == 'cliente') {
            header('Location: cad_clientes.php?google=1');
        } else {
            header('Location: cad_prestadores.php?google=1');
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escolha o Tipo de Usuário - Nexus Network</title>
    
    <!-- Favicons -->
    <link href="assets/img/logo3.png" rel="icon">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    
    <!-- CSS DO HEADER -->
    <link rel="stylesheet" href="assets/css/header.css">
    
    <!-- Escolha Tipo CSS -->
    <link rel="stylesheet" href="assets/css/escolher_tipo.css">

    <!-- JS DO HEADER -->
    <script src="assets/js/header.js" defer></script>
</head>
<body class="account-page">

    <!-- Header -->
    <?php include_once("header.php"); ?>
    <!-- /Header -->

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        
        <!-- Page Content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                    
                        <!-- Account Content -->
                        <div class="account-content">
                            <div class="row align-items-center justify-content-center">
                                <div class="col-md-12 col-lg-8 choice-container">
                                    
                                    <!-- Welcome Header -->
                                    <div class="welcome-header">
                                        <div class="welcome-icon">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <h2>Bem-vindo ao Nexus Network!</h2>
                                        <p class="user-name">Olá, <strong><?php echo htmlspecialchars($dados_google['name']); ?></strong></p>
                                        <p class="subtitle">Para completar seu cadastro, escolha como você deseja usar nossa plataforma:</p>
                                    </div>

                                    <!-- Mensagens de erro -->
                                    <?php if (!empty($erros)): ?>
                                        <div class="alert alert-danger alert-dismissible">
                                            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <ul>
                                                <?php foreach ($erros as $erro): ?>
                                                    <li><?php echo htmlspecialchars($erro); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Choice Form -->
                                    <form method="POST" action="" class="choice-form">
                                        <div class="user-type-selection">
                                            
                                            <!-- Cliente Option -->
                                            <div class="type-option">
                                                <input type="radio" id="cliente" name="tipo_usuario" value="cliente" required>
                                                <label for="cliente" class="type-card">
                                                    <div class="card-header">
                                                        <div class="type-icon">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div class="check-icon">
                                                            <i class="fas fa-check-circle"></i>
                                                        </div>
                                                    </div>
                                                    <h3>Cliente</h3>
                                                    <p>Encontre e contrate prestadores de serviços qualificados para suas necessidades</p>
                                                    <div class="card-features">
                                                        <span><i class="fas fa-check"></i> Busca avançada</span>
                                                        <span><i class="fas fa-check"></i> Avaliações confiáveis</span>
                                                        <span><i class="fas fa-check"></i> Chat direto</span>
                                                    </div>
                                                </label>
                                            </div>
                                            
                                            <!-- Prestador Option -->
                                            <div class="type-option">
                                                <input type="radio" id="prestador" name="tipo_usuario" value="prestador" required>
                                                <label for="prestador" class="type-card">
                                                    <div class="card-header">
                                                        <div class="type-icon">
                                                            <i class="fas fa-briefcase"></i>
                                                        </div>
                                                        <div class="check-icon">
                                                            <i class="fas fa-check-circle"></i>
                                                        </div>
                                                    </div>
                                                    <h3>Prestador</h3>
                                                    <p>Ofereça seus serviços profissionais e encontre novos clientes na plataforma</p>
                                                    <div class="card-features">
                                                        <span><i class="fas fa-check"></i> Perfil profissional</span>
                                                        <span><i class="fas fa-check"></i> Gestão de propostas</span>
                                                        <span><i class="fas fa-check"></i> Pagamento seguro</span>
                                                    </div>
                                                </label>
                                            </div>
                                            
                                        </div>
                                        
                                        <button type="submit" class="submit-btn">
                                            <span>Continuar</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </form>
                                    <!-- /Choice Form -->
                                    
                                    <div class="back-link">
                                        <a href="login.php">
                                            <i class="fas fa-arrow-left"></i>
                                            Voltar ao Login
                                        </a>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <!-- /Account Content -->
                            
                    </div>
                </div>

            </div>

        </div>        
        <!-- /Page Content -->

    </div>
    <!-- /Main Wrapper -->

    <!-- jQuery -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <!-- Script para animações -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar animação de entrada
            const cards = document.querySelectorAll('.type-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.animation = 'slideInUp 0.6s ease-out forwards';
                }, index * 100);
            });
            
            // Adicionar efeito de hover suave
            const radioInputs = document.querySelectorAll('input[type="radio"]');
            radioInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Remover seleção de todos os cards
                    cards.forEach(c => c.classList.remove('selected'));
                    
                    // Adicionar seleção ao card escolhido
                    if (this.checked) {
                        this.nextElementSibling.classList.add('selected');
                    }
                });
            });
        });
    </script>
    
</body>
</html>