<?php
session_start();
include_once("conexao.php");
require_once("foto_helper.php");

// Verificar se usuário está logado através do header.php
$usuario_logado = isset($_SESSION['logado']) && $_SESSION['logado'] === true;
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : '';
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '';
$nome_usuario = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : '';
$foto_usuario = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : 'assets/img/default-avatar.png';

if (!$usuario_logado) {
    header("Location: login.php");
    exit;
}

// Definir quem é o destinatário
$destinatario_id = null;
$destinatario_nome = '';
$destinatario_foto = '';
$destinatario_tipo = '';

if ($tipo_usuario === 'cliente') {
    // Cliente conversando com prestador
    $pres_codigo = isset($_GET['pres_codigo']) ? intval($_GET['pres_codigo']) : null;
    
    if (!$pres_codigo) {
        echo "<script>alert('Selecione um prestador para conversar.'); window.location='index.php';</script>";
        exit;
    }
    
    // Buscar informações do prestador
    $stmt_dest = mysqli_prepare($conn, "SELECT pres_nome, pres_foto FROM prestadores WHERE pres_codigo = ?");
    mysqli_stmt_bind_param($stmt_dest, "i", $pres_codigo);
    mysqli_stmt_execute($stmt_dest);
    $result_dest = mysqli_stmt_get_result($stmt_dest);
    
    if (mysqli_num_rows($result_dest) > 0) {
        $dest_info = mysqli_fetch_assoc($result_dest);
        $destinatario_id = $pres_codigo;
        $destinatario_nome = $dest_info['pres_nome'];
        $destinatario_foto = $dest_info['pres_foto'];
        $destinatario_tipo = 'prestador';
    }
    mysqli_stmt_close($stmt_dest);

    // Prestador não existe (ex.: conta excluída)
    if (!$destinatario_id) {
        echo "<script>alert('Prestador não encontrado.'); window.location='lista-chat.php';</script>";
        exit;
    }

    // Buscar ou criar chat
    $stmt_chat = mysqli_prepare($conn, "SELECT chat_codigo FROM chat WHERE cli_codigo = ? AND pres_codigo = ?");
    mysqli_stmt_bind_param($stmt_chat, "ii", $usuario_id, $pres_codigo);
    mysqli_stmt_execute($stmt_chat);
    $result_chat = mysqli_stmt_get_result($stmt_chat);
    
    if (mysqli_num_rows($result_chat) > 0) {
        $row_chat = mysqli_fetch_assoc($result_chat);
        $chat_codigo = $row_chat['chat_codigo'];
    } else {
        // Criar novo chat
        $stmt_new = mysqli_prepare($conn, "INSERT INTO chat (cli_codigo, pres_codigo) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt_new, "ii", $usuario_id, $pres_codigo);
        mysqli_stmt_execute($stmt_new);
        $chat_codigo = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_new);
    }
    mysqli_stmt_close($stmt_chat);
    
    // Marcar mensagens do prestador como lidas
    $stmt_mark = mysqli_prepare($conn, "UPDATE mensagens SET lida = 1 WHERE chat_codigo = ? AND remetente_tipo = 'prestador' AND lida = 0");
    mysqli_stmt_bind_param($stmt_mark, "i", $chat_codigo);
    mysqli_stmt_execute($stmt_mark);
    mysqli_stmt_close($stmt_mark);
    
} else {
    // Prestador conversando com cliente
    $cli_codigo = isset($_GET['cli_codigo']) ? intval($_GET['cli_codigo']) : null;
    
    if (!$cli_codigo) {
        echo "<script>alert('Selecione um cliente para conversar.'); window.location='index.php';</script>";
        exit;
    }
    
    // Buscar informações do cliente
    $stmt_dest = mysqli_prepare($conn, "SELECT cli_nome, cli_foto FROM clientes WHERE cli_codigo = ?");
    mysqli_stmt_bind_param($stmt_dest, "i", $cli_codigo);
    mysqli_stmt_execute($stmt_dest);
    $result_dest = mysqli_stmt_get_result($stmt_dest);
    
    if (mysqli_num_rows($result_dest) > 0) {
        $dest_info = mysqli_fetch_assoc($result_dest);
        $destinatario_id = $cli_codigo;
        $destinatario_nome = $dest_info['cli_nome'];
        $destinatario_foto = $dest_info['cli_foto'] ?: 'assets/img/default-avatar.png';
        $destinatario_tipo = 'cliente';
    }
    mysqli_stmt_close($stmt_dest);

    // Cliente não existe (ex.: conta excluída)
    if (!$destinatario_id) {
        echo "<script>alert('Cliente não encontrado.'); window.location='lista-chat.php';</script>";
        exit;
    }

    // Buscar ou criar chat
    $stmt_chat = mysqli_prepare($conn, "SELECT chat_codigo FROM chat WHERE cli_codigo = ? AND pres_codigo = ?");
    mysqli_stmt_bind_param($stmt_chat, "ii", $cli_codigo, $usuario_id);
    mysqli_stmt_execute($stmt_chat);
    $result_chat = mysqli_stmt_get_result($stmt_chat);
    
    if (mysqli_num_rows($result_chat) > 0) {
        $row_chat = mysqli_fetch_assoc($result_chat);
        $chat_codigo = $row_chat['chat_codigo'];
    } else {
        // Criar novo chat
        $stmt_new = mysqli_prepare($conn, "INSERT INTO chat (cli_codigo, pres_codigo) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt_new, "ii", $cli_codigo, $usuario_id);
        mysqli_stmt_execute($stmt_new);
        $chat_codigo = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_new);
    }
    mysqli_stmt_close($stmt_chat);
    
    // Marcar mensagens do cliente como lidas
    $stmt_mark = mysqli_prepare($conn, "UPDATE mensagens SET lida = 1 WHERE chat_codigo = ? AND remetente_tipo = 'cliente' AND lida = 0");
    mysqli_stmt_bind_param($stmt_mark, "i", $chat_codigo);
    mysqli_stmt_execute($stmt_mark);
    mysqli_stmt_close($stmt_mark);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Nexus Network</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }

        .chat-wrapper {
            max-width: 1200px;
            margin: 80px auto 20px;
            padding: 20px;
        }

        .chat-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: 600px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 2px solid #000000;
        }

        .chat-header {
            background: #000000;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #20cd8d;
        }

        .chat-header-info h2 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .chat-header-info p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: white;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.sent {
            align-items: flex-end;
        }

        .message.received {
            align-items: flex-start;
        }

        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
        }

        .message.sent .message-content {
            background: #20cd8d;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.received .message-content {
            background: #000000;
            color: white;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .message-time {
            font-size: 0.75rem;
            color: #666;
            margin-top: 5px;
        }

        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 2px solid #000000;
            display: flex;
            gap: 10px;
        }

        .chat-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #000000;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .chat-input:focus {
            border-color: #20cd8d;
            box-shadow: 0 0 0 3px rgba(32, 205, 141, 0.1);
        }

        .send-button {
            background: #20cd8d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .send-button:hover {
            background: #1ab379;
            transform: scale(1.05);
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .no-messages {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #20cd8d;
            border-radius: 4px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #1ab379;
        }
    </style>
</head>
<body>
    <?php include_once("header.php"); ?>
    
    <div class="chat-wrapper">
        <div class="chat-container">
            <div class="chat-header">
                <img src="<?= htmlspecialchars(fotoOuPadrao($destinatario_foto)) ?>" alt="<?= htmlspecialchars($destinatario_nome) ?>">
                <div class="chat-header-info">
                    <h2><?= htmlspecialchars($destinatario_nome) ?></h2>
                    <p><i class="fas fa-circle" style="font-size: 8px; color: #4ade80;"></i> Online</p>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="loading" id="loading">
                    <i class="fas fa-spinner fa-spin"></i> Carregando mensagens...
                </div>
            </div>

            <div class="chat-input-container">
                <input 
                    type="text" 
                    id="messageInput" 
                    class="chat-input" 
                    placeholder="Digite sua mensagem..."
                    maxlength="1000"
                >
                <button onclick="sendMessage()" class="send-button">
                    <i class="fas fa-paper-plane"></i>
                    Enviar
                </button>
            </div>
        </div>
    </div>
    <script src="assets/js/header.js"></script>
    <script>
        const chatCodigo = <?= intval($chat_codigo) ?>;
        const userType = <?= json_encode($tipo_usuario) ?>;
        const userId = <?= intval($usuario_id) ?>;
        let lastMessageId = 0;

        loadMessages();
        setInterval(loadMessages, 2000);

        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function loadMessages() {
            fetch('chat_api.php?action=get&chat_codigo=' + chatCodigo + '&last_id=' + lastMessageId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages.length > 0) {
                        const messagesContainer = document.getElementById('chatMessages');
                        const loading = document.getElementById('loading');
                        
                        if (loading) {
                            loading.remove();
                        }

                        data.messages.forEach(msg => {
                            if (msg.msg_id > lastMessageId) {
                                addMessageToChat(msg);
                                lastMessageId = msg.msg_id;
                            }
                        });

                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        
                        // Marcar mensagens como lidas
                        markMessagesAsRead();
                    } else if (data.messages && data.messages.length === 0 && lastMessageId === 0) {
                        const loading = document.getElementById('loading');
                        if (loading) {
                            loading.innerHTML = '<div class="no-messages"><i class="far fa-comment-dots"></i><p>Nenhuma mensagem ainda. Inicie a conversa!</p></div>';
                        }
                    }
                })
                .catch(error => console.error('Erro:', error));
        }

        function addMessageToChat(msg) {
            const messagesContainer = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            
            const isSent = (userType === msg.remetente_tipo && userId == msg.remetente_id);
            messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
            
            const date = new Date(msg.data_envio);
            const timeStr = date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${msg.conteudo}
                </div>
                <div class="message-time">
                    ${timeStr}
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
        }

        function markMessagesAsRead() {
            fetch('chat_api.php?action=mark_read&chat_codigo=' + chatCodigo)
                .catch(error => console.error('Erro ao marcar como lida:', error));
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message === '') {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'send');
            formData.append('chat_codigo', chatCodigo);
            formData.append('message', message);

            fetch('chat_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    loadMessages();
                } else {
                    alert('Erro: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar mensagem');
            });
        }
    </script>
</body>
</html>