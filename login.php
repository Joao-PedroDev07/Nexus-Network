<?php
session_start();
include_once("conexao.php");
require_once("login_funcoes.php");

$erros = [];
$sucesso = '';

// Verifica se o usuário já está logado
if (usuarioJaLogado()) {
    redirecionarUsuario($_SESSION['tipo_usuario']);
}

// Inicializa dados vazios para evitar erros de undefined
$dados = [
    'email' => '',
    'senha' => '',
    'tipo_usuario' => ''
];

// Processar formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Verifica conexão
    try {
        verificarConexao($conn);
    } catch (Exception $e) {
        $erros[] = $e->getMessage();
    }
    
    if (empty($erros)) {
        // Capturar e sanitizar dados
        $dados = [
            'email' => sanitize_string(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? ''),
            'senha' => filter_input(INPUT_POST, 'senha') ?? '',
            'tipo_usuario' => sanitize_string(filter_input(INPUT_POST, 'tipo_usuario') ?? '')
        ];
        
        // Processa o login
        $resultado = processarLogin($conn, $dados);
        
        if ($resultado['success']) {
            // Redireciona para a página apropriada
            header("Location: " . $resultado['redirect']);
            exit;
        } else {
            $erros[] = $resultado['error'];
        }
    }
}

// Verifica se há mensagem de erro via GET (para compatibilidade)
if (isset($_GET['erro'])) {
    $erros[] = urldecode($_GET['erro']);
}

// Mensagem de sucesso enviada pelo cadastro (cad_clientes.php / cad_prestadores.php)
if (isset($_GET['sucesso'])) {
    $sucesso = urldecode($_GET['sucesso']);
}
?>

<!DOCTYPE html> 
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Login - Nexus Network</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    
    <!-- Favicons -->
    <link href="assets/img/logo3.png" rel="icon">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Login CSS -->
    <link rel="stylesheet" href="assets/css/login.css">

    <!-- CSS E JS DO HEADER -->
    <link rel="stylesheet" href="assets/css/header.css">

    <script src="assets/js/header.js" defer></script>
    
    <link rel="icon" href="assets/img/logo-transparente.png">
</head>
<body class="account-page">

    <script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        const container = input.parentElement;
        const toggleBtn = container.querySelector('.toggle-password');
        if (!toggleBtn) return;
        
        const icon = toggleBtn.querySelector('i');
        if (!icon) return;
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

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
                                <div class="col-md-12 col-lg-6 login-right">
                                    <div class="login-header">
                                        <h3><center>Login</center></h3>
                                    </div>

                                    <!-- Mensagens de erro e sucesso -->
                                    <?php if (!empty($erros)): ?>
                                        <div class="alert alert-danger alert-dismissible">
                                            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
                                            <ul style="margin: 0; padding-left: 20px;">
                                                <?php foreach ($erros as $erro): ?>
                                                    <li><?= htmlspecialchars($erro) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($sucesso)): ?>
                                        <div class="alert alert-success alert-dismissible">
                                            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
                                            <?= htmlspecialchars($sucesso) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Login Form -->
                                    <form method="post" class="login-form">
                                        
                                        <div class="form-group form-focus">
                                            <select id="tipo_usuario" name="tipo_usuario" class="form-control floating" required>
                                                <option value="">Selecione seu tipo de usuário</option>
                                                <option value="cliente" <?= $dados['tipo_usuario'] == 'cliente' ? 'selected' : '' ?>>Cliente</option>
                                                <option value="prestador" <?= $dados['tipo_usuario'] == 'prestador' ? 'selected' : '' ?>>Prestador</option>
                                            </select>
                                            <label class="focus-label">Tipo de Usuário</label>
                                        </div>

                                        <div class="form-group form-focus">
                                            <input type="email" class="form-control floating" name="email" 
                                                   value="<?= htmlspecialchars($dados['email'] ?? '') ?>" 
                                                   placeholder=" " required>
                                            <label class="focus-label">E-mail</label>
                                        </div>

                                            <div class="form-group form-focus">
                                            <input type="password" id="senha" name="senha" class="form-control floating" 
                                                placeholder=" " required>
                                            <label class="focus-label">Senha</label>
                                            <button type="button" class="toggle-password" onclick="togglePassword('senha')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>

                                        <div class="text-right">
                                            <a class="forgot-link" href="esqueceu_senha.php">Esqueceu a senha?</a>
                                        </div>
                                        
                                        <button class="btn btn-primary btn-block btn-lg login-btn" type="submit">
                                            Entrar
                                        </button>
                                        
                                        <div class="login-or">
                                            <span class="or-line"></span>
                                            <span class="span-or">ou</span>
                                        </div>
                                        
                                        <!-- Botão Google Centralizado -->
                                        <div class="social-login-wrapper">
                                            <a href="login_google.php" class="btn-google-full">
                                                <i class="fab fa-google"></i>
                                                <span>Continuar com Google</span>
                                            </a>
                                        </div>
                                        
                                        <div class="text-center dont-have">
                                            Não tem uma conta? 
                                            <div class="signup-options">
                                                <a href="cad_clientes.php">Cadastrar como Cliente</a> 
                                                <a href="cad_prestadores.php">Cadastrar como Prestador</a>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Login Form -->

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
    <script src="assets/js/script.js"></script>
    
    <!-- Scripts -->
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Adicionar classe floating quando o input tem valor
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control.floating');
            inputs.forEach(input => {
                // Verificar se já tem valor
                if (input.value) {
                    input.classList.add('has-value');
                }
                
                // Adicionar evento para mudanças
                input.addEventListener('input', function() {
                    if (this.value) {
                        this.classList.add('has-value');
                    } else {
                        this.classList.remove('has-value');
                    }
                });
            });
        });

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        if (!input) {
            console.error('Input não encontrado:', inputId);
            return;
        }
        
        const container = input.parentElement;
        if (!container) {
            console.error('Container não encontrado');
            return;
        }
        
        const toggleBtn = container.querySelector('.toggle-password');
        if (!toggleBtn) {
            console.error('Botão toggle não encontrado');
            return;
        }
        
        const icon = toggleBtn.querySelector('i');
        if (!icon) {
            console.error('Ícone não encontrado');
            return;
        }
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Adicionar classe floating quando o input tem valor
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.form-control.floating');
        inputs.forEach(input => {
            // Verificar se já tem valor
            if (input.value) {
                input.classList.add('has-value');
            }
            
            // Adicionar evento para mudanças
            input.addEventListener('input', function() {
                if (this.value) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });
    });
    </script>
    
</body>
</html>