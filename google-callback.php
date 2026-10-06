<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
session_start();
require_once 'vendor/autoload.php';
require_once 'conexao.php';
require_once 'google_funcoes.php';
require_once 'google_config.php';

$clientID = GOOGLE_CLIENT_ID;
$clientSecret = GOOGLE_CLIENT_SECRET;
$redirectUri = GOOGLE_REDIRECT_URI;

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token["error"])) {
        header('Location: login.php?erro=' . urlencode('Erro ao autenticar com Google'));
        exit;
    }
    
    $client->setAccessToken($token['access_token']);
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $google_id = $google_account_info->id;
    $picture = $google_account_info->picture ?? 'assets/img/default-avatar.png';


    //Verificar se existe usuário com este Google ID
    $usuario_por_google_id = verificarUsuarioPorGoogleId($conn, $google_id);
    
    if ($usuario_por_google_id) {
        error_log("Usuário encontrado por Google ID");
        error_log("Dados: " . print_r($usuario_por_google_id, true));
        
        //GARANTIR que a foto está presente
        if (empty($usuario_por_google_id['foto']) && empty($usuario_por_google_id['cli_foto']) && empty($usuario_por_google_id['pres_foto'])) {
            $usuario_por_google_id['foto'] = $picture;
        }
        
        criarSessaoGoogle($usuario_por_google_id);
        $redirect = obterRedirecionamentoGoogle($usuario_por_google_id['tipo_usuario']);
        
        error_log("Redirecionando para: $redirect");
        header("Location: $redirect");
        exit;
    }
    
    // Verificar se existe usuário com este email (cadastro tradicional)
    $usuario_por_email = verificarUsuarioExistente($conn, $email);
    
    if ($usuario_por_email) {
        error_log(" Usuário encontrado por email (vinculando Google)");
        
        // Email já existe no sistema (cadastro tradicional)
        // Atualizar conta existente com Google ID
        if (vincularContaGoogle($conn, $usuario_por_email, $google_id, $picture)) {
            
            // GARANTIR que a foto está presente após vincular (mantém a foto já cadastrada)
            $foto_atual = $usuario_por_email['cli_foto'] ?? $usuario_por_email['pres_foto'] ?? '';
            $usuario_por_email['foto'] = !empty($foto_atual) ? $foto_atual : $picture;
            
            criarSessaoGoogle($usuario_por_email);
            $redirect = obterRedirecionamentoGoogle($usuario_por_email['tipo_usuario']);
            
            error_log("Redirecionando (conta vinculada) para: $redirect");
            header("Location: $redirect");
            exit;
        } else {
            error_log("Erro ao vincular conta Google");
            header('Location: login.php?erro=' . urlencode('Erro ao vincular conta Google'));
            exit;
        }
    }
    
    // TERCEIRO: Usuário completamente novo
    error_log("Novo usuário - redirecionando para escolher tipo");
    
    $_SESSION['google_temp'] = [
        'email' => $email,
        'name' => $name,
        'google_id' => $google_id,
        'picture' => $picture
    ];
    
    header('Location: escolher-tipo.php');
    exit;
    
} else {
    error_log("Código de autorização não encontrado");
    header('Location: login.php?erro=' . urlencode('Autorização negada'));
    exit;
}
?>