<?php

session_start();

include_once("conexao.php");
require_once("cad_clientes_funcoes.php");
require_once("google_funcoes.php");
$erros = [];
$sucesso = '';
$is_google_signup = false;
$dados_google = null;

if (isset($_GET['google']) && $_GET['google'] == '1') {
    if (!isset($_SESSION['google_temp']) || !validarDadosGoogle($_SESSION['google_temp'])) {
        header('Location: login.php?erro=' . urlencode('Sessão inválida'));
        exit;
    }
    $is_google_signup = true;
    $dados_google = $_SESSION['google_temp'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if ($is_google_signup) {
        $dados = [
            'nome' => $dados_google['name'],
            'datanasc' => sanitize_string(filter_input(INPUT_POST, 'data_nascimento') ?? ''),
            'email' => $dados_google['email'],
            'telefone' => sanitize_string(filter_input(INPUT_POST, 'telefone') ?? ''),
            'cpf' => sanitize_string(filter_input(INPUT_POST, 'cpf') ?? ''),
            'genero' => sanitize_string(filter_input(INPUT_POST, 'genero') ?? ''),
            'cidade' => sanitize_string(filter_input(INPUT_POST, 'cidade') ?? ''),
            'estado' => sanitize_string(filter_input(INPUT_POST, 'estado') ?? ''),
            'latitude' => sanitize_string(filter_input(INPUT_POST, 'latitude') ?? ''),
            'longitude' => sanitize_string(filter_input(INPUT_POST, 'longitude') ?? ''),
            'senha' => ''
        ];
    } else {
        $dados = [
            'nome' => sanitize_string(filter_input(INPUT_POST, 'nome') ?? ''),
            'datanasc' => sanitize_string(filter_input(INPUT_POST, 'data_nascimento') ?? ''),
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: '',
            'telefone' => sanitize_string(filter_input(INPUT_POST, 'telefone') ?? ''),
            'cpf' => sanitize_string(filter_input(INPUT_POST, 'cpf') ?? ''),
            'genero' => sanitize_string(filter_input(INPUT_POST, 'genero') ?? ''),
            'cidade' => sanitize_string(filter_input(INPUT_POST, 'cidade') ?? ''),
            'estado' => sanitize_string(filter_input(INPUT_POST, 'estado') ?? ''),
            'latitude' => sanitize_string(filter_input(INPUT_POST, 'latitude') ?? ''),
            'longitude' => sanitize_string(filter_input(INPUT_POST, 'longitude') ?? ''),
            'senha' => filter_input(INPUT_POST, 'senha') ?? ''
        ];
    }
    
    // VALIDAÇÕES
    $dados_validacao = $dados;
    if ($is_google_signup) {
        unset($dados_validacao['senha']);
    }
    // Remover latitude/longitude da validação de campos obrigatórios
    unset($dados_validacao['latitude']);
    unset($dados_validacao['longitude']);
    
    if (!validarCamposObrigatorios($dados_validacao)) {
        $erros[] = "Todos os campos são obrigatórios!";
    }
    
    if (!$is_google_signup && !validarEmail($dados['email'])) {
        $erros[] = "Email inválido!";
    }
    
    if (!empty($dados['cpf']) && !validarCPF($dados['cpf'])) {
        $erros[] = "CPF inválido! Verifique se digitou corretamente.";
    }
    
    if (!validarIdade($dados['datanasc'])) {
        $erros[] = "Você deve ter pelo menos 18 anos para se cadastrar!";
    }
    
    if (!validarTelefone($dados['telefone'])) {
        $erros[] = "Telefone deve ter 10 ou 11 dígitos!";
    }
    
    if (!$is_google_signup && emailJaExiste($conn, $dados['email'])) {
        $erros[] = "Este email já está cadastrado!";
    }
    
    if (cpfJaExiste($conn, $dados['cpf'])) {
        $erros[] = "Este CPF já está cadastrado!";
    }
    
    $resultado_foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $resultado_foto = processarFoto($_FILES['foto']);
        if (isset($resultado_foto['erro'])) {
            $erros[] = $resultado_foto['erro'];
        }
    }
    
    // ✅ PROCESSAR CADASTRO
    if (empty($erros)) {
        if ($is_google_signup) {
            $dados_adicionais = [
                'telefone' => $dados['telefone'],
                'cpf' => $dados['cpf'],
                'genero' => $dados['genero'],
                'cidade' => $dados['cidade'],
                'estado' => $dados['estado'],
                'data_nascimento' => $dados['datanasc'],
                'latitude' => $dados['latitude'],
                'longitude' => $dados['longitude']
            ];
            
            $usuario_criado = salvarClienteGoogle($conn, $dados_google, $dados_adicionais);
            
            if ($usuario_criado) {
                //  GARANTIR estrutura completa antes de criar sessão
                if (!isset($usuario_criado['foto'])) {
                    $usuario_criado['foto'] = $dados_google['picture'] ?? 'assets/img/default-avatar.png';
                }
                
                criarSessaoGoogle($usuario_criado);
                
                header('Location: index.php');
                exit;
            } else {
                $erros[] = "Erro ao salvar no banco de dados. Tente novamente.";
            }
        } else {
            // CADASTRO NORMAL (não Google)
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
            $dados['foto'] = $resultado_foto['sucesso'] ?? null;
            
            if (InserirCliente($conn, $dados)) {
                header('Location: login.php?sucesso=' . urlencode('Cadastro realizado com sucesso! Faça login para continuar.'));
                exit;
            } else {
                $erros[] = "Erro ao salvar no banco de dados. Tente novamente.";
            }
        }
    }
}

// Inicializar $dados se for cadastro Google e ainda não existe
if ($is_google_signup && !isset($dados)) {
    $dados = [
        'nome' => $dados_google['name'],
        'email' => $dados_google['email'],
        'datanasc' => '',
        'telefone' => '',
        'cpf' => '',
        'genero' => '',
        'cidade' => '',
        'estado' => '',
        'latitude' => '',
        'longitude' => '',
        'senha' => ''
    ];
}
?>
<!DOCTYPE html> 
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Cadastro Cliente - Nexus Network</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    
    <link href="assets/img/logo3.png" rel="icon">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cad_cliente.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <script src="assets/js/header.js" defer></script>
    <link rel="icon" href="assets/img/logo-transparente.png">
</head>
<body class="account-page">
    <?php include_once("header.php"); ?>
    
    <div class="main-wrapper">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="account-content">
                            <div class="row align-items-center justify-content-center">
                                <div class="col-md-12 col-lg-6 login-right">
                                    <div class="login-header">
                                        <h3>
                                        <center>
                                            <?php if ($is_google_signup): ?>
                                                Complete seu Perfil - Cliente
                                            <?php else: ?>
                                                Registro do Cliente
                                            <?php endif; ?>
                                        </h3>
                                          <?php if ($is_google_signup): ?>
                                            <p>Olá, <strong><?php echo htmlspecialchars($dados_google['name']); ?></strong>! Complete as informações abaixo:</p>
                                        <?php else: ?>
                                            <a href="cad_prestadores.php" class="prestador">Prestador?</a>
                                        <?php endif; ?>
                                    </div>
                                  
                                    
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
                                    
                                    <form method="post" enctype="multipart/form-data">
                                        <!-- Campos ocultos para latitude e longitude -->
                                        <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars($dados['latitude'] ?? '') ?>">
                                        <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars($dados['longitude'] ?? '') ?>">
                                        
                                        <?php if (!$is_google_signup): ?>
                                        <div class="photo-upload-group">
                                            <label class="photo-upload-label" for="foto">Escolha uma foto:</label>
                                            
                                            <div class="photo-upload-wrapper">
                                                <input type="file" 
                                                    name="foto" 
                                                    id="foto" 
                                                    class="photo-upload-input" 
                                                    accept="image/*">
                                                
                                                <div class="photo-upload-content">
                                                    <i class="fas fa-camera photo-upload-icon"></i>
                                                    <div class="photo-upload-text">Clique para escolher uma foto</div>
                                                    <div class="photo-upload-hint">JPG, PNG até 5MB</div>
                                                </div>
                                            </div>
                                            
                                            <div class="photo-selected" id="photoSelected">
                                                <i class="fas fa-check"></i> Foto selecionada: <span id="fileName"></span>
                                            </div>
                                        </div>

                                         <?php endif; ?>
                                        <div class="form-group form-focus">
                                            <input type="text" class="form-control floating" name="nome" 
                                                   value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" 
                                                   <?= $is_google_signup ? 'readonly' : 'required' ?>>
                                            <label class="focus-label">Nome Completo</label>
                                            <?php if ($is_google_signup): ?>
                                                <small class="text-muted">Informação obtida do Google</small>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="data_nascimento">Data de Nascimento:</label>
                                            <input type="date" id="data_nascimento" name="data_nascimento" 
                                                   class="form-control" value="<?= htmlspecialchars($dados['datanasc'] ?? '') ?>" required>
                                        </div>
                                        
                                        <div class="form-group form-focus">
                                            <input type="email" class="form-control floating" name="email" 
                                                   value="<?= htmlspecialchars($dados['email'] ?? '') ?>" 
                                                   <?= $is_google_signup ? 'readonly' : 'required' ?>>
                                            <label class="focus-label">E-mail</label>
                                            <?php if ($is_google_signup): ?>
                                                <small class="text-muted">Informação obtida do Google</small>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="form-group form-focus">
                                            <input type="text" class="form-control floating" name="telefone" 
                                                   id="telefone" placeholder="(11) 99999-9999" 
                                                   value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>" required>
                                            <label class="focus-label">Telefone</label>
                                        </div>
                                        
                                        <div class="form-group form-focus">
                                            <input type="text" class="form-control floating" name="cpf" 
                                                   id="cpf" placeholder="000.000.000-00" maxlength="14" 
                                                   value="<?= htmlspecialchars($dados['cpf'] ?? '') ?>" required>
                                            <label class="focus-label">CPF</label>
                                            <div class="cpf-help">Digite apenas números ou com formatação (000.000.000-00)</div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="genero">Gênero:</label>
                                            <select id="genero" name="genero" class="form-control" required>
                                                <option value="">Selecione...</option>
                                                <option value="Masculino" <?= isset($dados['genero']) && $dados['genero'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                                <option value="Feminino" <?= isset($dados['genero']) && $dados['genero'] == 'Feminino' ? 'selected' : '' ?>>Feminino</option>
                                                <option value="Outro" <?= isset($dados['genero']) && $dados['genero'] == 'Outro' ? 'selected' : '' ?>>Outro</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="estado">Estado:</label>
                                            <select id="estado" name="estado" class="form-control" onchange="buscarCidades()" required>
                                                <option value="">Selecione um estado</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="cidade">Cidade:</label>
                                            <select id="cidade" name="cidade" class="form-control" 
                                                    data-value="<?= htmlspecialchars($dados['cidade'] ?? '') ?>"
                                                    disabled required>
                                                <option value="">Selecione uma cidade</option>
                                            </select>
                                        </div>
                                        
                                        <?php if (!$is_google_signup): ?>
                                        <div class="form-group">
                                            <label for="password">Senha:</label>
                                            <div class="input-container">
                                                <input type="password" id="password" name="senha" 
                                                    class="form-control" placeholder="Digite sua senha" required>
                                                <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <span class="validation-icon" id="passwordIcon"></span>
                                            </div>
                                            <div class="password-strength" id="passwordStrength">
                                                <div class="strength-bar">
                                                    <div class="strength-fill" id="strengthFill"></div>
                                                </div>
                                                <div class="strength-text" id="strengthText">Força da senha</div>
                                                <ul class="requirements">
                                                    <li id="req-length">Pelo menos 8 caracteres</li>
                                                    <li id="req-uppercase">Uma letra maiúscula</li>
                                                    <li id="req-lowercase">Uma letra minúscula</li>
                                                    <li id="req-number">Um número</li>
                                                    <li id="req-special">Um caractere especial (!@#$%^&*)</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="confirmPassword">Confirmar Senha:</label>
                                            <div class="input-container">
                                                <input type="password" id="confirmPassword" name="confirmPassword" 
                                                    class="form-control" placeholder="Confirme sua senha" required>
                                                <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <span class="validation-icon" id="confirmIcon"></span>
                                            </div>
                                            <div class="password-match" id="passwordMatch"></div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="text-right">
                                            <?php if ($is_google_signup): ?>
                                                <a class="forgot-link" href="escolher-tipo.php">Voltar</a>
                                            <?php else: ?>
                                                <a class="forgot-link" href="login.php">já tem uma conta?</a>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <button class="btn btn-primary btn-block btn-lg login-btn" type="submit">
                                            <?= $is_google_signup ? 'Finalizar Cadastro' : 'Criar' ?>
                                        </button>
                                        
                                        <?php if (!$is_google_signup): ?>
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
                                    <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/script.js"></script>

    <script>
// Função para buscar coordenadas da cidade
async function buscarCoordenadas(cidade, estado) {
    try {
        console.log('🔍 Buscando coordenadas para:', cidade, estado);
        
        const geoResponse = await fetch(
            `geocode_proxy.php?cidade=${encodeURIComponent(cidade)}&estado=${encodeURIComponent(estado)}`
        );
        
        if (!geoResponse.ok) {
            throw new Error('Erro na resposta do proxy');
        }
        
        const geoData = await geoResponse.json();
        
        if (geoData && geoData.length > 0) {
            const latitude = geoData[0].lat;
            const longitude = geoData[0].lon;
            
            document.getElementById('latitude').value = latitude;
            document.getElementById('longitude').value = longitude;
            
            console.log(' Coordenadas salvas:', {
                cidade: cidade,
                estado: estado,
                latitude: latitude,
                longitude: longitude
            });
        } else {
            console.warn(' Coordenadas não encontradas para esta cidade');
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
        }
    } catch (error) {
        console.error(' Erro ao buscar coordenadas:', error);
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
    }
}

// Função para buscar cidades (chamada pelo onchange do select estado)
function buscarCidades() {
    const estado = document.getElementById('estado').value;
    const cidadeSelect = document.getElementById('cidade');
    
    if (!cidadeSelect) return;
    
    cidadeSelect.disabled = true;
    cidadeSelect.innerHTML = '<option value="">Carregando...</option>';
    
    // Limpar coordenadas ao trocar de estado
    document.getElementById('latitude').value = '';
    document.getElementById('longitude').value = '';
    
    if (estado === "") {
        cidadeSelect.innerHTML = '<option value="">Selecione um estado primeiro</option>';
        cidadeSelect.disabled = false;
        return;
    }
    
    const cidadeAtual = cidadeSelect.dataset.value || '';
    
    fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${estado}/municipios`)
        .then(response => response.json())
        .then(cidades => {
            cidadeSelect.innerHTML = '<option value="">Selecione uma cidade</option>';
            cidades.forEach(cidade => {
                const option = document.createElement('option');
                option.value = cidade.nome;
                option.text = cidade.nome;
                if (cidade.nome === cidadeAtual) {
                    option.selected = true;
                    setTimeout(() => {
                        buscarCoordenadas(cidade.nome, estado);
                    }, 500);
                }
                cidadeSelect.appendChild(option);
            });
            cidadeSelect.disabled = false;
        })
        .catch(error => {
            console.error('Erro ao carregar cidades:', error);
            cidadeSelect.innerHTML = '<option value="">Erro ao carregar cidades</option>';
            cidadeSelect.disabled = false;
        });
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Máscara do CPF
    function mascaraCPF(input) {
        let valor = input.value.replace(/\D/g, '');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        input.value = valor;
    }
    
    // Máscara do telefone 
    function mascaraTelefone(input) {
        let valor = input.value.replace(/\D/g, '');
        if (valor.length > 11) {
            valor = valor.substring(0, 11);
        }
        if (valor.length <= 10) {
            valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
            valor = valor.replace(/(\d)(\d{4})$/, '$1-$2');
        } else {
            valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
            valor = valor.replace(/(\d)(\d{4})$/, '$1-$2');
        }
        input.value = valor;
    }
    
    <?php if (!$is_google_signup): ?>
    // Upload de foto
    const fotoInput = document.getElementById('foto');
    const photoSelected = document.getElementById('photoSelected');
    const fileName = document.getElementById('fileName');
    
    if (fotoInput && photoSelected && fileName) {
        fotoInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                fileName.textContent = file.name;
                photoSelected.style.display = 'flex';
            } else {
                photoSelected.style.display = 'none';
                fileName.textContent = '';
            }
        });
    }
    
    // Função para toggle de senha
    window.togglePassword = function(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        const container = input.parentElement;
        if (!container) return;
        
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
    };
    <?php endif; ?>

    // Aplicar máscaras
    const cpfInput = document.getElementById('cpf');
    const telefoneInput = document.getElementById('telefone');
    
    if (cpfInput) {
        cpfInput.addEventListener('input', function() {
            mascaraCPF(this);
        });
    }
    
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function() {
            mascaraTelefone(this);
        });
    }
    
    // Carrega os estados
    const estadoSelect = document.getElementById('estado');
    if (estadoSelect) {
        fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome')
            .then(response => response.json())
            .then(estados => {
                const valorAtual = estadoSelect.value;
                
                estados.forEach(estado => {
                    let option = document.createElement('option');
                    option.value = estado.sigla;
                    option.text = estado.nome;
                    if (estado.sigla === valorAtual) {
                        option.selected = true;
                    }
                    estadoSelect.appendChild(option);
                });
                
                if (valorAtual) {
                    buscarCidades();
                }
            })
            .catch(error => {
                console.error('Erro ao carregar estados:', error);
            });
    }
    
    // Event listener para quando o usuário selecionar uma cidade
    const cidadeSelect = document.getElementById('cidade');
    if (cidadeSelect) {
        cidadeSelect.addEventListener('change', function() {
            const cidadeNome = this.value;
            const estadoSigla = document.getElementById('estado').value;
            
            if (cidadeNome && estadoSigla) {
                buscarCoordenadas(cidadeNome, estadoSigla);
            } else {
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
            }
        });
    }
    
});
</script>

    <?php if (!$is_google_signup): ?>
        <script src="assets/js/password-validation.js"></script>
    <?php endif; ?>
</body>
</html>