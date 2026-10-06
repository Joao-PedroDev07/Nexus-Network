<?php
// deletar_avaliacao.php
session_start();
require_once 'conexao.php';

// Definir cabeçalho JSON
header('Content-Type: application/json');

// Verificar se o usuário está logado como cliente (login normal ou Google)
$esta_logado = isset($_SESSION['logado']) && $_SESSION['logado'] === true;
if (!$esta_logado || ($_SESSION['tipo_usuario'] ?? '') !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado.']);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

// Receber dados
$avaliacao_id = isset($_POST['avaliacao_id']) ? intval($_POST['avaliacao_id']) : 0;
$usuario_logado = intval($_SESSION['usuario_id']);

if ($avaliacao_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Avaliação inválida.']);
    exit;
}

try {
    // Buscar informações da avaliação
    $stmt = mysqli_prepare($conn, "SELECT cli_codigo, pres_codigo FROM avaliacao WHERE avl_codigo = ?");
    mysqli_stmt_bind_param($stmt, "i", $avaliacao_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Avaliação não encontrada.']);
        exit;
    }
    
    $dados = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Verificar se o usuário logado é o autor da avaliação
    if ($usuario_logado != $dados['cli_codigo']) {
        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para excluir esta avaliação.']);
        exit;
    }
    
    // Excluir a avaliação
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM avaliacao WHERE avl_codigo = ?");
    mysqli_stmt_bind_param($stmt_delete, "i", $avaliacao_id);
    
    if (mysqli_stmt_execute($stmt_delete)) {
        echo json_encode(['success' => true, 'message' => 'Avaliação excluída com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir avaliação.']);
    }
    
    mysqli_stmt_close($stmt_delete);
    
} catch (Throwable $e) {
    error_log("Erro ao excluir avaliação: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir avaliação.']);
}

mysqli_close($conn);
?>