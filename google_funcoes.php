<?php

/**
 * Verifica se existe usuário pelo Google ID (identificador único)
 */
function verificarUsuarioPorGoogleId($conn, $google_id) {
    try {
        // Buscar nos clientes
        $sql_cliente = "SELECT cli_codigo as id, cli_nome as nome, cli_email as email, cli_foto, 'cliente' as tipo_usuario, cli_google_id 
                       FROM clientes WHERE cli_google_id = ?";
        $stmt = mysqli_prepare($conn, $sql_cliente);
        mysqli_stmt_bind_param($stmt, "s", $google_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cliente = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($cliente) {
            return $cliente;
        }
        
        // Buscar nos prestadores
        $sql_prestador = "SELECT pres_codigo as id, pres_nome as nome, pres_email as email, pres_foto, 'prestador' as tipo_usuario, pres_google_id 
                         FROM prestadores WHERE pres_google_id = ?";
        $stmt = mysqli_prepare($conn, $sql_prestador);
        mysqli_stmt_bind_param($stmt, "s", $google_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $prestador = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $prestador ?: false;
        
    } catch (Exception $e) {
        error_log("Erro em verificarUsuarioPorGoogleId: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica se o usuário já existe no sistema por email (cadastro tradicional)
 */
function verificarUsuarioExistente($conn, $email) {
    try {
        // Buscar nos clientes
        $sql_cliente = "SELECT cli_codigo as id, cli_nome as nome, cli_email as email, cli_foto, 'cliente' as tipo_usuario, cli_google_id 
                       FROM clientes WHERE cli_email = ?";
        $stmt = mysqli_prepare($conn, $sql_cliente);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cliente = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($cliente) {
            return $cliente;
        }
        
        // Buscar nos prestadores
        $sql_prestador = "SELECT pres_codigo as id, pres_nome as nome, pres_email as email, pres_foto, 'prestador' as tipo_usuario, pres_google_id 
                         FROM prestadores WHERE pres_email = ?";
        $stmt = mysqli_prepare($conn, $sql_prestador);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $prestador = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $prestador ?: false;
        
    } catch (Exception $e) {
        error_log("Erro em verificarUsuarioExistente: " . $e->getMessage());
        return false;
    }
}

/**
 * Vincula uma conta existente (cadastro tradicional) com Google ID
 */
function vincularContaGoogle($conn, $usuario, $google_id, $picture = null) {
    try {
        // Mantém a foto que o usuário já tinha; só usa a do Google se não houver nenhuma
        if ($usuario['tipo_usuario'] == 'cliente') {
            $sql = "UPDATE clientes SET cli_google_id = ?, cli_foto = COALESCE(NULLIF(cli_foto, ''), ?) WHERE cli_codigo = ?";
        } else {
            $sql = "UPDATE prestadores SET pres_google_id = ?, pres_foto = COALESCE(NULLIF(pres_foto, ''), ?) WHERE pres_codigo = ?";
        }
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $google_id, $picture, $usuario['id']);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $resultado;
        
    } catch (Exception $e) {
        error_log("Erro em vincularContaGoogle: " . $e->getMessage());
        return false;
    }
}

/**
 * Cria sessão para usuário logado via Google (CORRIGIDO)
 */
function criarSessaoGoogle($usuario) {
    // PADRONIZAÇÃO: Usar os mesmos nomes que o header.php espera
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_foto'] = $usuario['foto'] ?? $usuario['cli_foto'] ?? $usuario['pres_foto'] ?? 'assets/img/default-avatar.png';
    $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
    $_SESSION['logado'] = true;
    $_SESSION['login_method'] = 'google';
    
    // ADICIONAR TAMBÉM AS VARIÁVEIS ESPECÍFICAS (para compatibilidade com perfil_cliente.php)
    if ($usuario['tipo_usuario'] == 'cliente') {
        $_SESSION['cliente_id'] = $usuario['id'];
        $_SESSION['cli_codigo'] = $usuario['id'];
    } else {
        $_SESSION['prestador_id'] = $usuario['id'];
        $_SESSION['pres_codigo'] = $usuario['id'];
    }
    
    // Limpar dados temporários do Google se existirem
    unset($_SESSION['google_temp']);
    
    session_regenerate_id(true);
    
    // DEBUG
    error_log("=== SESSÃO CRIADA ===");
    error_log("usuario_id: " . $_SESSION['usuario_id']);
    error_log("usuario_nome: " . $_SESSION['usuario_nome']);
    error_log("usuario_foto: " . $_SESSION['usuario_foto']);
    error_log("tipo_usuario: " . $_SESSION['tipo_usuario']);
}

/**
 * Obtém redirecionamento baseado no tipo de usuário
 */
function obterRedirecionamentoGoogle($tipo_usuario) {
    return ($tipo_usuario == 'cliente') ? 'index.php' : 'index.php';
}

/**
 * Salva usuário cliente via Google no banco (COM COORDENADAS)
 */
function salvarClienteGoogle($conn, $dados_google, $dados_adicionais) {
    try {
        // 🔍 DEBUG - Log dos dados recebidos
        error_log("=== salvarClienteGoogle CHAMADA ===");
        error_log("Latitude recebida: " . ($dados_adicionais['latitude'] ?? 'VAZIO'));
        error_log("Longitude recebida: " . ($dados_adicionais['longitude'] ?? 'VAZIO'));
        error_log("Cidade: " . ($dados_adicionais['cidade'] ?? 'VAZIO'));
        error_log("Estado: " . ($dados_adicionais['estado'] ?? 'VAZIO'));
        
        // Limpar CPF e telefone
        $cpf_limpo = preg_replace('/\D/', '', $dados_adicionais['cpf']);
        $telefone_limpo = preg_replace('/\D/', '', $dados_adicionais['telefone']);
        
        // Converter latitude e longitude para float ou NULL
        $latitude = !empty($dados_adicionais['latitude']) ? floatval($dados_adicionais['latitude']) : null;
        $longitude = !empty($dados_adicionais['longitude']) ? floatval($dados_adicionais['longitude']) : null;
        
        error_log("Latitude convertida: " . ($latitude !== null ? $latitude : 'NULL'));
        error_log("Longitude convertida: " . ($longitude !== null ? $longitude : 'NULL'));
        
        $sql = "INSERT INTO clientes (
            cli_nome, cli_email, cli_telefone, cli_cpf, cli_genero, 
            cli_cidade, cli_estado, cli_datanasc, cli_google_id,
            cli_foto, cli_latitude, cli_longitude
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
        }
        
        // Bind com os tipos corretos (ssssssssssdd) - 10 strings + 2 decimais
        mysqli_stmt_bind_param($stmt, "ssssssssssdd", 
            $dados_google['name'],
            $dados_google['email'],
            $telefone_limpo,
            $cpf_limpo,
            $dados_adicionais['genero'],
            $dados_adicionais['cidade'],
            $dados_adicionais['estado'],
            $dados_adicionais['data_nascimento'],
            $dados_google['google_id'],
            $dados_google['picture'],
            $latitude,
            $longitude
        );
        
        $resultado = mysqli_stmt_execute($stmt);
        
        if (!$resultado) {
            error_log(" Erro ao executar INSERT: " . mysqli_error($conn));
            throw new Exception("Erro ao inserir cliente: " . mysqli_error($conn));
        }
        
        $insert_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        error_log(" Cliente inserido com sucesso! ID: " . $insert_id);
        
        // RETORNAR ESTRUTURA COMPLETA E CORRETA
        return [
            'id' => $insert_id,
            'nome' => $dados_google['name'],
            'email' => $dados_google['email'],
            'foto' => $dados_google['picture'],
            'cli_foto' => $dados_google['picture'],
            'tipo_usuario' => 'cliente'
        ];
        
    } catch (Exception $e) {
        error_log(" Erro em salvarClienteGoogle: " . $e->getMessage());
        return false;
    }
}

/**
 * Salva usuário prestador via Google no banco (COM COORDENADAS)
 */
function salvarPrestadorGoogle($conn, $dados_google, $dados_adicionais) {
    try {
        
        // Limpar CPF e telefone
        $cpf_limpo = preg_replace('/\D/', '', $dados_adicionais['cpf']);
        $telefone_limpo = preg_replace('/\D/', '', $dados_adicionais['telefone']);
        
        // Converter latitude e longitude para float ou NULL
        $latitude = !empty($dados_adicionais['latitude']) ? floatval($dados_adicionais['latitude']) : null;
        $longitude = !empty($dados_adicionais['longitude']) ? floatval($dados_adicionais['longitude']) : null;
        
        // Valores padrão para campos opcionais
        $profissao = !empty($dados_adicionais['profissao']) ? $dados_adicionais['profissao'] : '';
        $descricao = !empty($dados_adicionais['descricao']) ? $dados_adicionais['descricao'] : '';
        $preco_min = !empty($dados_adicionais['preco_min']) ? floatval($dados_adicionais['preco_min']) : 0.00;
        $preco_max = !empty($dados_adicionais['preco_max']) ? floatval($dados_adicionais['preco_max']) : 0.00;
        
        error_log("Latitude convertida: " . ($latitude !== null ? $latitude : 'NULL'));
        error_log("Longitude convertida: " . ($longitude !== null ? $longitude : 'NULL'));
        
        $sql = "INSERT INTO prestadores (
            pres_nome, pres_email, pres_telefone, prestador_cpf, pres_genero, 
            pres_cidade, pres_estado, pres_datanasc, pres_google_id,
            pres_foto, pres_profissao, pres_descricao, 
            pres_precomin, pres_precomax, pres_latitude, pres_longitude
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
        }
        
        // Bind com 16 parâmetros (12 strings + 4 decimais)
        mysqli_stmt_bind_param($stmt, "ssssssssssssdddd", 
            $dados_google['name'],
            $dados_google['email'],
            $telefone_limpo,
            $cpf_limpo,
            $dados_adicionais['genero'],
            $dados_adicionais['cidade'],
            $dados_adicionais['estado'],
            $dados_adicionais['data_nascimento'],
            $dados_google['google_id'],
            $dados_google['picture'],
            $profissao,
            $descricao,
            $preco_min,
            $preco_max,
            $latitude,
            $longitude
        );
        
        $resultado = mysqli_stmt_execute($stmt);
        
        if (!$resultado) {
            error_log(" Erro ao executar INSERT: " . mysqli_error($conn));
            throw new Exception("Erro ao inserir prestador: " . mysqli_error($conn));
        }
        
        $insert_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        error_log("Prestador inserido com sucesso! ID: " . $insert_id);
        
        // RETORNAR ESTRUTURA COMPLETA E CORRETA
        return [
            'id' => $insert_id,
            'nome' => $dados_google['name'],
            'email' => $dados_google['email'],
            'foto' => $dados_google['picture'],
            'pres_foto' => $dados_google['picture'],
            'tipo_usuario' => 'prestador'
        ];
        
    } catch (Exception $e) {
        error_log(" Erro em salvarPrestadorGoogle: " . $e->getMessage());
        return false;
    }
}

/**
 * Valida dados básicos do Google
 */
function validarDadosGoogle($dados_temp) {
    return !empty($dados_temp['email']) && 
           !empty($dados_temp['name']) && 
           !empty($dados_temp['google_id']);
}
?>