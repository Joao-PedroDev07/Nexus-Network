<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

function retornarJSON($sucesso, $erro = []) {
    echo json_encode([
        'sucesso' => $sucesso,
        'erro' => $erro
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Incluir conexão com banco de dados
    require_once 'conexao.php'; // ou o arquivo onde está sua conexão
    
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        retornarJSON(false, ['Método de requisição inválido']);
    }
    
    // Receber dados
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
    $assunto = isset($_POST['assunto']) ? trim($_POST['assunto']) : '';
    $mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
    
    $erro = [];
    
    // Validações
    if (empty($nome)) {
        $erro[] = 'Nome é obrigatório';
    } elseif (strlen($nome) < 3) {
        $erro[] = 'Nome deve ter no mínimo 3 caracteres';
    }
    
    if (empty($email)) {
        $erro[] = 'Email é obrigatório';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro[] = 'Email inválido';
    }
    
    if (empty($assunto)) {
        $erro[] = 'Assunto é obrigatório';
    }
    
    if (empty($mensagem)) {
        $erro[] = 'Mensagem é obrigatória';
    } elseif (strlen($mensagem) < 10) {
        $erro[] = 'Mensagem deve ter no mínimo 10 caracteres';
    }
    
    if (!empty($erro)) {
        retornarJSON(false, $erro);
    }
    
    // Preparar SQL com prepared statement
    $sql = "INSERT INTO contatos (nome, email, telefone, assunto, mensagem, data_envio, status) 
            VALUES (?, ?, ?, ?, ?, NOW(), 'novo')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nome, $email, $telefone, $assunto, $mensagem);
    
    if ($stmt->execute()) {
        retornarJSON(true);
    } else {
        error_log('Erro ao inserir contato: ' . $stmt->error);
        retornarJSON(false, ['Erro ao salvar mensagem. Tente novamente.']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log('Erro no processamento de contato: ' . $e->getMessage());
    retornarJSON(false, ['Erro ao processar sua solicitação. Tente novamente.']);
}