<?php
session_start();
header('Content-Type: application/json');
include_once("conexao.php");

// Verificar se usuário está logado
$usuario_logado = isset($_SESSION['logado']) && $_SESSION['logado'] === true;
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : '';
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '';

if (!$usuario_logado) {
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

// Verifica se a conversa pertence ao usuário logado
function usuarioParticipaDoChat($conn, $chat_codigo, $usuario_id, $tipo_usuario) {
    $coluna = ($tipo_usuario === 'cliente') ? 'cli_codigo' : 'pres_codigo';
    $stmt = mysqli_prepare($conn, "SELECT chat_codigo FROM chat WHERE chat_codigo = ? AND $coluna = ?");
    mysqli_stmt_bind_param($stmt, "ii", $chat_codigo, $usuario_id);
    mysqli_stmt_execute($stmt);
    $participa = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
    mysqli_stmt_close($stmt);
    return $participa;
}

$chat_param = intval($_POST['chat_codigo'] ?? $_GET['chat_codigo'] ?? 0);
if ($chat_param > 0 && !usuarioParticipaDoChat($conn, $chat_param, intval($usuario_id), $tipo_usuario)) {
    echo json_encode(['success' => false, 'error' => 'Conversa não encontrada']);
    exit;
}

// Ação: Enviar mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $chat_codigo = isset($_POST['chat_codigo']) ? intval($_POST['chat_codigo']) : 0;
    $message = trim($_POST['message'] ?? '');
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Mensagem vazia']);
        exit;
    }
    
    if ($chat_codigo <= 0) {
        echo json_encode(['success' => false, 'error' => 'Chat inválido']);
        exit;
    }
    
    // Inserir mensagem
    $stmt = mysqli_prepare($conn, "INSERT INTO mensagens (chat_codigo, remetente_id, remetente_tipo, conteudo, data_envio) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "iiss", $chat_codigo, $usuario_id, $tipo_usuario, $message);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Mensagem enviada']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao salvar mensagem']);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

// Ação: Buscar mensagens
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $chat_codigo = isset($_GET['chat_codigo']) ? intval($_GET['chat_codigo']) : 0;
    $last_id = intval($_GET['last_id'] ?? 0);
    
    if ($chat_codigo <= 0) {
        echo json_encode(['success' => false, 'error' => 'Chat inválido']);
        exit;
    }
    
    $stmt = mysqli_prepare($conn, "SELECT msg_id, remetente_id, remetente_tipo, conteudo, data_envio FROM mensagens WHERE chat_codigo = ? AND msg_id > ? ORDER BY msg_id ASC LIMIT 50");
    mysqli_stmt_bind_param($stmt, "ii", $chat_codigo, $last_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'msg_id' => intval($row['msg_id']),
            'remetente_id' => intval($row['remetente_id']),
            'remetente_tipo' => $row['remetente_tipo'],
            'conteudo' => htmlspecialchars($row['conteudo']),
            'data_envio' => $row['data_envio']
        ];
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

// Ação: Marcar mensagens como lidas
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'mark_read') {
    $chat_codigo = isset($_GET['chat_codigo']) ? intval($_GET['chat_codigo']) : 0;
    
    if ($chat_codigo <= 0) {
        echo json_encode(['success' => false, 'error' => 'Chat inválido']);
        exit;
    }
    
    // Determinar qual tipo de mensagem marcar como lida
    // Cliente marca mensagens do prestador como lidas
    // Prestador marca mensagens do cliente como lidas
    $remetente_tipo_para_marcar = ($tipo_usuario === 'cliente') ? 'prestador' : 'cliente';
    
    $stmt = mysqli_prepare($conn, "UPDATE mensagens SET lida = 1 WHERE chat_codigo = ? AND remetente_tipo = ? AND lida = 0");
    mysqli_stmt_bind_param($stmt, "is", $chat_codigo, $remetente_tipo_para_marcar);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Mensagens marcadas como lidas']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao marcar mensagens']);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Ação inválida']);
?>