<?php
session_start();
include_once("conexao.php");
require_once("foto_helper.php");

// Verificar se usuário está logado
$usuario_logado = isset($_SESSION['logado']) && $_SESSION['logado'] === true;
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : '';
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : '';
$nome_usuario = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : '';

if (!$usuario_logado) {
    header("Location: login.php");
    exit;
}

// Buscar conversas do usuário
$conversas = [];

if ($tipo_usuario === 'cliente') {
    // Cliente busca conversas com prestadores
    $sql = "SELECT 
                c.chat_codigo,
                c.pres_codigo as contato_id,
                p.pres_nome as contato_nome,
                p.pres_foto as contato_foto,
                p.pres_profissao as contato_info,
                m.conteudo as ultima_mensagem,
                m.data_envio as ultima_data,
                m.remetente_tipo,
                (SELECT COUNT(*) FROM mensagens WHERE chat_codigo = c.chat_codigo AND remetente_tipo = 'prestador' AND lida = 0) as nao_lidas
            FROM chat c
            INNER JOIN prestadores p ON c.pres_codigo = p.pres_codigo
            LEFT JOIN (
                SELECT chat_codigo, conteudo, data_envio, remetente_tipo
                FROM mensagens m1
                WHERE msg_id = (
                    SELECT MAX(msg_id) 
                    FROM mensagens m2 
                    WHERE m2.chat_codigo = m1.chat_codigo
                )
            ) m ON c.chat_codigo = m.chat_codigo
            WHERE c.cli_codigo = ?
            ORDER BY m.data_envio DESC, c.chat_codigo DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    
} else {
    // Prestador busca conversas com clientes
    $sql = "SELECT 
                c.chat_codigo,
                c.cli_codigo as contato_id,
                cl.cli_nome as contato_nome,
                cl.cli_foto as contato_foto,
                '' as contato_info,
                m.conteudo as ultima_mensagem,
                m.data_envio as ultima_data,
                m.remetente_tipo,
                (SELECT COUNT(*) FROM mensagens WHERE chat_codigo = c.chat_codigo AND remetente_tipo = 'cliente' AND lida = 0) as nao_lidas
            FROM chat c
            INNER JOIN clientes cl ON c.cli_codigo = cl.cli_codigo
            LEFT JOIN (
                SELECT chat_codigo, conteudo, data_envio, remetente_tipo
                FROM mensagens m1
                WHERE msg_id = (
                    SELECT MAX(msg_id) 
                    FROM mensagens m2 
                    WHERE m2.chat_codigo = m1.chat_codigo
                )
            ) m ON c.chat_codigo = m.chat_codigo
            WHERE c.pres_codigo = ?
            ORDER BY m.data_envio DESC, c.chat_codigo DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $conversas[] = $row;
}

mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Conversas - Nexus Network</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/lista-chat.css">
</head>
<body>
    <?php include_once("header.php"); ?>
    
    <div class="chat-list-container">
        <div class="chat-list-wrapper">
            <div class="chat-list-header">
                <h1><i class="fas fa-comments"></i> Minhas Conversas</h1>
                <p>Gerencie suas conversas com <?php echo $tipo_usuario === 'cliente' ? 'prestadores' : 'clientes'; ?></p>
            </div>

            <?php if (empty($conversas)): ?>
                <div class="no-chats">
                    <i class="fas fa-inbox"></i>
                    <h2>Nenhuma conversa ainda</h2>
                    <p>Quando você iniciar uma conversa, ela aparecerá aqui.</p>
                    <?php if ($tipo_usuario === 'cliente'): ?>
                        <a href="search.php" class="btn-search-services">
                            <i class="fas fa-search"></i> Buscar Serviços
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="chats-grid">
                    <?php foreach ($conversas as $conversa): ?>
                        <?php
                            $foto = htmlspecialchars(fotoOuPadrao($conversa['contato_foto']));
                            $nome = htmlspecialchars($conversa['contato_nome']);
                            $info = htmlspecialchars($conversa['contato_info']);
                            // Trunca antes de escapar, sem quebrar acentos (UTF-8)
                            $ultima_msg = !empty($conversa['ultima_mensagem'])
                                ? htmlspecialchars(mb_strimwidth($conversa['ultima_mensagem'], 0, 53, '...', 'UTF-8'))
                                : 'Nenhuma mensagem ainda';
                            $nao_lidas = intval($conversa['nao_lidas']);
                            
                            // Formatar data
                            $data_msg = '';
                            if (!empty($conversa['ultima_data'])) {
                                $timestamp = strtotime($conversa['ultima_data']);
                                $hoje = strtotime('today');
                                $ontem = strtotime('yesterday');
                                
                                if ($timestamp >= $hoje) {
                                    $data_msg = date('H:i', $timestamp);
                                } elseif ($timestamp >= $ontem) {
                                    $data_msg = 'Ontem';
                                } else {
                                    $data_msg = date('d/m/Y', $timestamp);
                                }
                            }
                            
                            // Determinar se é mensagem enviada ou recebida
                            $eh_enviada = ($tipo_usuario === $conversa['remetente_tipo']);
                            
                            // Link do chat
                            $link_chat = $tipo_usuario === 'cliente' 
                                ? "chat.php?pres_codigo=" . $conversa['contato_id']
                                : "chat.php?cli_codigo=" . $conversa['contato_id'];
                        ?>
                        
                        <a href="<?php echo $link_chat; ?>" class="chat-card <?php echo $nao_lidas > 0 ? 'has-unread' : ''; ?>">
                            <div class="chat-card-avatar">
                                <img src="<?php echo $foto; ?>" alt="<?php echo $nome; ?>">
                                <?php if ($nao_lidas > 0): ?>
                                    <span class="unread-badge"><?php echo $nao_lidas; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="chat-card-content">
                                <div class="chat-card-header">
                                    <h3 class="chat-card-name"><?php echo $nome; ?></h3>
                                    <?php if (!empty($data_msg)): ?>
                                        <span class="chat-card-time"><?php echo $data_msg; ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($info)): ?>
                                    <p class="chat-card-info">
                                        <i class="fas fa-briefcase"></i> <?php echo $info; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="chat-card-message">
                                    <?php if ($eh_enviada): ?>
                                        <i class="fas fa-reply"></i>
                                    <?php endif; ?>
                                    <?php echo $ultima_msg; ?>
                                </p>
                            </div>
                            
                            <div class="chat-card-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="assets/js/header.js"></script>
</body>
</html>