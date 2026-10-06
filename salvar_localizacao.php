<?php
session_start();
include_once("conexao.php");

// Verificar se recebeu coordenadas
if(isset($_POST['lat']) && isset($_POST['lon'])) {
    $lat = floatval($_POST['lat']);
    $lon = floatval($_POST['lon']);
    
    // Salvar na sessão
    $_SESSION['user_lat'] = $lat;
    $_SESSION['user_lon'] = $lon;
    
    // Se o usuário estiver logado, atualizar no banco
    if(isset($_SESSION['usuario_id']) && ($_SESSION['tipo_usuario'] ?? '') === 'cliente') {
        $cli_codigo = intval($_SESSION['usuario_id']);
        
        $sql = "UPDATE clientes 
                SET cli_latitude = $lat, 
                    cli_longitude = $lon 
                WHERE cli_codigo = $cli_codigo";
        
        if(mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Localização atualizada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'Localização salva na sessão']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Coordenadas não recebidas']);
}
?>