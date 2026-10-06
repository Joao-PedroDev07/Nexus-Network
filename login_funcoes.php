<?php

/**
 * Sanitiza strings removendo tags HTML e caracteres especiais
 */
function sanitize_string($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida se todos os campos obrigatórios foram preenchidos
 */
function validarCamposObrigatorios($dados) {
    return !empty($dados['email']) && 
           !empty($dados['senha']) && 
           !empty($dados['tipo_usuario']);
}

/**
 * Valida formato de email
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida tipo de usuário
 */
function validarTipoUsuario($tipo) {
    return in_array($tipo, ['cliente', 'prestador']);
}

/**
 * Busca usuário no banco de dados
 */
function buscarUsuario($conn, $email, $tipo_usuario) {
    try {
        // Determina a tabela e campos baseado no tipo de usuário
        if ($tipo_usuario == 'cliente') {
            $tabela = 'clientes';
            $campos_select = "cli_codigo as id, cli_nome as nome, cli_email as email, cli_senha as senha, cli_foto as foto";
            $campo_email = "cli_email";

            $sql = "SELECT $campos_select FROM $tabela WHERE $campo_email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $usuario = mysqli_fetch_assoc($result);
            
            mysqli_stmt_close($stmt);
            
            return $usuario;
        } else {
            $tabela = 'prestadores';
            // CORREÇÃO: Ajustado os nomes dos campos para coincidir com a estrutura da tabela
            $campos_select = "pres_codigo as id, pres_nome as nome, pres_email as email, pres_senha as senha, pres_foto as foto";
            $campo_email = "pres_email";

            $sql = "SELECT $campos_select FROM $tabela WHERE $campo_email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $prestador = mysqli_fetch_assoc($result);
            
            mysqli_stmt_close($stmt);
            
            return $prestador;
        }
        
        // Prepara a consulta
        
        
    } catch (Exception $e) {
        error_log("Erro em buscarUsuario: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica se a senha está correta usando password_verify
 */
function verificarSenha($senha_informada, $senha_hash) {
    return password_verify($senha_informada, $senha_hash);
}

/**
 * Cria sessão para o usuário logado
 */
function criarSessao($usuario, $tipo_usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    // Corrigido para usar a variável correta
    $_SESSION['usuario_nome'] = $usuario['nome'];
     $_SESSION['usuario_foto'] = $usuario['foto'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['tipo_usuario'] = $tipo_usuario;
    $_SESSION['logado'] = true;

    // Caso o usuário seja do tipo prestador, você pode definir o ID de prestador
    if ($tipo_usuario == 'prestador') {
        $_SESSION['prestador_id'] = $usuario['id'];
    } else {
        $_SESSION['cliente_id'] = $usuario['id'];
    }

    // Regenera o ID da sessão por segurança
    session_regenerate_id(true);
}

/**
 * Determina a página de redirecionamento baseada no tipo de usuário
 */
function obterRedirecionamento($tipo_usuario) {
    return ($tipo_usuario == 'cliente') ? 'index.php' : 'index.php';
}

/**
 * Realiza o processo completo de login
 */
function processarLogin($conn, $dados) {
    try {
        // Validações
        if (!validarCamposObrigatorios($dados)) {
            return ['success' => false, 'error' => 'Todos os campos são obrigatórios.'];
        }
        
        if (!validarEmail($dados['email'])) {
            return ['success' => false, 'error' => 'Email inválido.'];
        }
        
        if (!validarTipoUsuario($dados['tipo_usuario'])) {
            return ['success' => false, 'error' => 'Tipo de usuário inválido.'];
        }
        
        // Busca o usuário
        $usuario = buscarUsuario($conn, $dados['email'], $dados['tipo_usuario']);
        
        if (!$usuario) {
            return ['success' => false, 'error' => 'Email ou senha incorretos.'];
        }
        
        // DEBUG: Descomente as linhas abaixo temporariamente para debug
        //error_log("Senha informada: " . $dados['senha']);
         //error_log("Hash do banco: " . $usuario['senha']);
         //error_log("Resultado da verificação: " . (verificarSenha($dados['senha'], $usuario['senha']) ? 'true' : 'false'));
        
        // Conta criada pelo Google não possui senha
        if (empty($usuario['senha'])) {
            return ['success' => false, 'error' => 'Esta conta foi criada com o Google. Use o botão "Entrar com Google".'];
        }

        // Verifica a senha
        if (!verificarSenha($dados['senha'], $usuario['senha'])) {
            return ['success' => false, 'error' => 'Email ou senha incorretos.'];
        }
        
        // Cria a sessão
        criarSessao($usuario, $dados['tipo_usuario']);
        
        // Retorna sucesso com redirecionamento
        return [
            'success' => true, 
            'redirect' => obterRedirecionamento($dados['tipo_usuario'])
        ];
        
    } catch (Exception $e) {
        error_log("Erro em processarLogin: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erro interno do sistema. Tente novamente.'];
    }
}

/**
 * Verifica se o usuário já está logado
 */
function usuarioJaLogado() {
    return isset($_SESSION['logado']) && $_SESSION['logado'] === true;
}

/**
 * Destroi a sessão do usuário
 */
function logout() {
    session_destroy();
    header("Location: login.php");
    exit;
}

/**
 * Verifica conexão com o banco de dados
 */
function verificarConexao($conn) {
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados: ' . mysqli_connect_error());
    }
    return true;
}

/**
 * Redireciona usuário baseado no seu tipo
 */
function redirecionarUsuario($tipo_usuario) {
    $redirect = obterRedirecionamento($tipo_usuario);
    header("Location: $redirect");
    exit;
}


?>