<?php
session_start();
include_once("conexao.php");
require_once("perfil_cliente_funcoes.php");
require_once("foto_helper.php");

// COMPATÍVEL COM GOOGLE E LOGIN TRADICIONAL
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

// Prestador logado não pode abrir o perfil de cliente (os IDs são de tabelas diferentes)
if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] !== 'cliente') {
    header('Location: perfil_prestador.php');
    exit;
}

// Priorizar usuario_id (padrão), mas aceitar cliente_id (legado)
$cliente_id = $_SESSION['usuario_id'] ?? $_SESSION['cliente_id'];

$erros = [];
$sucesso = '';

// Buscar dados do cliente
$dados_cliente = buscarDadosCliente($conn, $cliente_id);

// Conta não existe mais (ex.: excluída) - encerra a sessão
if (!$dados_cliente) {
    session_destroy();
    header('Location: login.php?erro=' . urlencode('Conta não encontrada. Faça login novamente.'));
    exit;
}

$foto_perfil = obterFotoPerfil($dados_cliente, 'cliente');

// Usuário do Google não tem senha cadastrada
$tem_senha = !empty($dados_cliente['cli_senha']);

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['acao'])) {
        
        if ($_POST['acao'] == 'atualizar_dados') {
            // Capturar e sanitizar dados
            $dados_atualizacao = [
                'nome' => sanitize_string(filter_input(INPUT_POST, 'nome') ?? ''),
                'telefone' => sanitize_string(filter_input(INPUT_POST, 'telefone') ?? ''),
                'genero' => sanitize_string(filter_input(INPUT_POST, 'genero') ?? ''),
                'cidade' => sanitize_string(filter_input(INPUT_POST, 'cidade') ?? ''),
                'estado' => sanitize_string(filter_input(INPUT_POST, 'estado') ?? ''),
            ];
            
            // Validações
            if (empty($dados_atualizacao['nome'])) {
                $erros[] = "Nome é obrigatório!";
            }
            
            if (!validarTelefone($dados_atualizacao['telefone'])) {
                $erros[] = "Telefone deve ter 10 ou 11 dígitos!";
            }
            
            // Se não houver erros, atualizar
            if (empty($erros)) {
                if (atualizarDadosCliente($conn, $cliente_id, $dados_atualizacao)) {
                    $sucesso = "Dados atualizados com sucesso!";
                    $dados_cliente = buscarDadosCliente($conn, $cliente_id);
                    $_SESSION['usuario_nome'] = $dados_cliente['cli_nome'];
                } else {
                    $erros[] = "Erro ao atualizar dados!";
                }
            }
        }
        
        elseif ($_POST['acao'] == 'atualizar_foto') {
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $resultado_foto = processarFoto($_FILES['foto']);
                if (isset($resultado_foto['erro'])) {
                    $erros[] = $resultado_foto['erro'];
                } else {
                    // USAR HELPER PARA REMOVER FOTO ANTIGA
                    removerFotoLocal($dados_cliente['cli_foto']);
                    
                    if (atualizarFotoCliente($conn, $cliente_id, $resultado_foto['sucesso'])) {
                        $_SESSION['usuario_foto'] = $resultado_foto['sucesso']; 
                        $sucesso = "Foto atualizada com sucesso!";
                        $dados_cliente = buscarDadosCliente($conn, $cliente_id);
                        $foto_perfil = obterFotoPerfil($dados_cliente, 'cliente');
                    } else {
                        $erros[] = "Erro ao salvar nova foto!";
                    }
                }
            } else {
                $erros[] = "Nenhuma foto foi selecionada!";
            }
        }
        
        elseif ($_POST['acao'] == 'excluir_conta') {
            // VERIFICAR SE USUÁRIO TEM SENHA (Google não tem)
            if (!$tem_senha) {
                // Usuário do Google - excluir sem validação de senha
                if (excluirContaCliente($conn, $cliente_id)) {
                    removerFotoLocal($dados_cliente['cli_foto']);
                    session_destroy();
                    header('Location: login.php');
                    exit;
                } else {
                    $erros[] = "Erro ao excluir conta!";
                }
            } else {
                // Usuário tradicional - validar senha
                $senha_confirmacao = filter_input(INPUT_POST, 'senha_confirmacao') ?? '';
                
                if (empty($senha_confirmacao)) {
                    $erros[] = "Digite sua senha para confirmar!";
                } elseif (!password_verify($senha_confirmacao, $dados_cliente['cli_senha'])) {
                    $erros[] = "Senha incorreta!";
                } else {
                    if (excluirContaCliente($conn, $cliente_id)) {
                        removerFotoLocal($dados_cliente['cli_foto']);
                        session_destroy();
                        header('Location: login.php');
                        exit;
                    } else {
                        $erros[] = "Erro ao excluir conta!";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Network - Meu Perfil</title>
    <link rel="stylesheet" href="assets/css/perfil_cliente.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="profile-page">

    <!-- Header -->
    <?php include_once("header.php"); ?>
    <!-- /Header -->
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="profile-wrapper">
                
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if ($foto_perfil && $foto_perfil != FOTO_PADRAO): ?>
                            <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto do Perfil">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <button class="change-photo-btn" onclick="document.getElementById('foto-input').click()">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <div class="profile-info">
                        <h1><?= htmlspecialchars($dados_cliente['cli_nome']) ?></h1>
                        <p><?= htmlspecialchars($dados_cliente['cli_email']) ?></p>
                        <div class="profile-stats">
                            <div class="stat">
                                <span class="stat-label">Membro desde</span>
                                <span class="stat-value">
                                    <?= date('m/Y', strtotime($dados_cliente['cli_data_cadastro'])) ?>
                                </span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">Localização</span>
                                <span class="stat-value">
                                    <?= htmlspecialchars($dados_cliente['cli_cidade']) ?>, <?= htmlspecialchars($dados_cliente['cli_estado']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (!empty($erros)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="alert-content">
                            <ul>
                                <?php foreach ($erros as $erro): ?>
                                    <li><?= htmlspecialchars($erro) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.style.display='none'">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($sucesso)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div class="alert-content">
                            <?= htmlspecialchars($sucesso) ?>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.style.display='none'">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Profile Content -->
                <div class="profile-content">
                    
                    <!-- Personal Information -->
                    <div class="profile-section">
                        <div class="section-header">
                            <h2><i class="fas fa-user-edit"></i> Informações Pessoais</h2>
                            <button class="btn-secondary" onclick="toggleEditMode('dados')">
                                <i class="fas fa-edit"></i>
                                Editar
                            </button>
                        </div>
                        
                        <form method="post" class="profile-form" id="form-dados">
                            <input type="hidden" name="acao" value="atualizar_dados">
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Nome Completo</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-user"></i>
                                        <input type="text" 
                                               name="nome" 
                                               value="<?= htmlspecialchars($dados_cliente['cli_nome']) ?>" 
                                               readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>E-mail</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" 
                                               value="<?= htmlspecialchars($dados_cliente['cli_email']) ?>" 
                                               readonly 
                                               disabled>
                                    </div>
                                    <small class="form-hint">O e-mail não pode ser alterado</small>
                                </div>

                                <div class="form-group">
                                    <label>Telefone</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-phone"></i>
                                        <input type="text" 
                                               name="telefone" 
                                               value="<?= htmlspecialchars($dados_cliente['cli_telefone']) ?>" 
                                               readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>CPF</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-id-card"></i>
                                        <input type="text" 
                                               value="<?= htmlspecialchars($dados_cliente['cli_cpf']) ?>" 
                                               readonly 
                                               disabled>
                                    </div>
                                    <small class="form-hint">O CPF não pode ser alterado</small>
                                </div>

                                <div class="form-group">
                                    <label>Data de Nascimento</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-calendar"></i>
                                        <input type="date" 
                                               value="<?= htmlspecialchars($dados_cliente['cli_datanasc']) ?>" 
                                               readonly 
                                               disabled>
                                    </div>
                                    <small class="form-hint">A data de nascimento não pode ser alterada</small>
                                </div>

                                <div class="form-group">
                                    <label>Gênero</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-venus-mars"></i>
                                        <select name="genero" disabled>
                                            <option value="Masculino" <?= $dados_cliente['cli_genero'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                            <option value="Feminino" <?= $dados_cliente['cli_genero'] == 'Feminino' ? 'selected' : '' ?>>Feminino</option>
                                            <option value="Outro" <?= $dados_cliente['cli_genero'] == 'Outro' ? 'selected' : '' ?>>Outro</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Estado</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <select name="estado" id="estado" onchange="buscarCidades()" disabled>
                                            <option value="<?= htmlspecialchars($dados_cliente['cli_estado']) ?>">
                                                <?= htmlspecialchars($dados_cliente['cli_estado']) ?>
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Cidade</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-city"></i>
                                        <select name="cidade" id="cidade" disabled>
                                            <option value="<?= htmlspecialchars($dados_cliente['cli_cidade']) ?>">
                                                <?= htmlspecialchars($dados_cliente['cli_cidade']) ?>
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions" style="display: none;" id="actions-dados">
                                <button type="button" class="btn-secondary" onclick="cancelEdit('dados')">
                                    <i class="fas fa-times"></i>
                                    Cancelar
                                </button>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i>
                                    Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Section -->
                    <div class="profile-section">
                        <div class="section-header">
                            <h2><i class="fas fa-shield-alt"></i> Segurança</h2>
                        </div>
                        
                        <div class="security-options">
                            <div class="security-item">
                                <div class="security-info">
                                    <h3>Redefinir Senha</h3>
                                    <p>Altere sua senha para manter sua conta segura</p>
                                </div>
                                <a href="redefinir senha cliente/esqueciasenha.php" class="btn-outline">
                                    <i class="fas fa-key"></i>
                                    Alterar Senha
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="profile-section danger-zone">
                        <div class="section-header">
                            <h2><i class="fas fa-exclamation-triangle"></i> Zona de Perigo</h2>
                        </div>
                        
                        <div class="danger-content">
                            <div class="danger-info">
                                <h3>Excluir Conta</h3>
                                <p>Uma vez excluída, sua conta não poderá ser recuperada. Esta ação é irreversível.</p>
                            </div>
                            <button class="btn-danger" onclick="showDeleteModal()">
                                <i class="fas fa-trash"></i>
                                Excluir Conta
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Exclusão -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h3>
                <button class="modal-close" onclick="hideDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Atenção!</strong> Esta ação não pode ser desfeita.</p>
                <p>Todos os seus dados serão permanentemente removidos.</p>
                
                <form method="post" id="form-excluir">
                    <input type="hidden" name="acao" value="excluir_conta">
                    <?php if ($tem_senha): ?>
                    <div class="form-group">
                        <label>Digite sua senha para confirmar:</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="senha_confirmacao" placeholder="Sua senha atual" required>
                        </div>
                    </div>
                    <?php else: ?>
                    <p><small>Sua conta está vinculada ao Google, por isso não é necessário digitar senha.</small></p>
                    <?php endif; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="hideDeleteModal()">
                    Cancelar
                </button>
                <button type="submit" form="form-excluir" class="btn-danger">
                    <i class="fas fa-trash"></i>
                    Confirmar Exclusão
                </button>
            </div>
        </div>
    </div>

    <!-- Form oculto para upload de foto -->
    <form method="post" enctype="multipart/form-data" style="display: none;" id="form-foto">
        <input type="hidden" name="acao" value="atualizar_foto">
        <input type="file" id="foto-input" name="foto" accept="image/*" onchange="document.getElementById('form-foto').submit()">
    </form>

    <!-- Scripts -->
    <script src="assets/js/header.js"></script>
    <script src="assets/js/perfil_cliente.js"></script>
</body>
</html>