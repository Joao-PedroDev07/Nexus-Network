<?php

// Função para sanitizar string 
function sanitize_string($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

//Validar CPF
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/\D/', '', $cpf);
    
    // Verifica se tem exatamente 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Calcula o primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    if ($dv1 != intval($cpf[9])) {
        return false;
    }
    
    // Calcula o segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    if ($dv2 != intval($cpf[10])) {
        return false;
    }
    
    return true;
}

// Verifica se a idade é maior que 18
function validarIdade($data_nascimento) {
    if (empty($data_nascimento)) return false;
    
    try {
        $hoje = new DateTime();
        $nascimento = new DateTime($data_nascimento);
        
        // Verifica se a data não é no futuro
        if ($nascimento > $hoje) {
            return false;
        }
        
        $idade = $hoje->diff($nascimento)->y;
        
        return $idade >= 18;
    } catch (Exception $e) {
        return false; // Data inválida
    }
}

//Corrigi o formato telefone
function validarTelefone($telefone) {
    $telefone_limpo = preg_replace('/\D/', '', $telefone);
    
    // Verifica se tem 10 ou 11 dígitos
    if (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 11) {
        return false;
    }
    
    // Se tem 11 dígitos, o terceiro deve ser 9 (celular)
    if (strlen($telefone_limpo) == 11) {
        if ($telefone_limpo[2] != '9') {
            return false;
        }
    }
    
    // Verifica se não são todos os dígitos iguais
    if (preg_match('/(\d)\1{9,10}/', $telefone_limpo)) {
        return false;
    }
    
    return true;
}

// Validação de email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Verificar se email já existe no banco (PRESTADORES)
function emailJaExiste($conn, $email) {
    $sql = "SELECT pres_email FROM prestadores WHERE pres_email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_num_rows($result) > 0;
}

// Função para verificar CPF existente (PRESTADORES)
function cpfJaExiste($conn, $cpf) {
    // Remove caracteres não numéricos antes de consultar
    $cpf_limpo = preg_replace('/\D/', '', $cpf);
    
    $sql = "SELECT prestador_cpf FROM prestadores WHERE prestador_cpf = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $cpf_limpo);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_num_rows($result) > 0;
}

// Processar upload de foto
function processarFoto($arquivo_foto) {
    if (!isset($arquivo_foto) || $arquivo_foto['error'] != 0) {
        return null; // Sem foto enviada
    }
    
    $foto = $arquivo_foto;
    
    // Verificar tipo de arquivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $foto['tmp_name']);
    finfo_close($finfo);
    
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime_type, $tipos_permitidos)) {
        return ['erro' => 'Apenas arquivos de imagem são permitidos!'];
    }
    
    // Verificar tamanho (5MB máximo)
    if ($foto['size'] > 5 * 1024 * 1024) {
        return ['erro' => 'Arquivo muito grande! Máximo 5MB.'];
    }
    
    // Criar nome único
    $extensao = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $nome_foto = uniqid() . "_" . time() . "." . $extensao;
    $caminho_foto = "uploads/fotos/" . $nome_foto;
    
    // Criar pasta se não existir
    if (!is_dir("uploads/fotos")) {
        mkdir("uploads/fotos", 0755, true);
    }
    
    // Mover arquivo
    if (!move_uploaded_file($foto["tmp_name"], $caminho_foto)) {
        return ['erro' => 'Erro ao salvar a imagem!'];
    }
    
    return ['sucesso' => $caminho_foto];
}

// Inserir PRESTADOR no banco usando prepared statement (Evita SQL Injection)
function inserirPrestador($conn, $dados) {

    $cpf_limpo = preg_replace('/\D/', '', $dados['cpf']);
    $telefone_limpo = preg_replace('/\D/', '', $dados['telefone']);
    

    error_log("Telefone original: " . ($dados['telefone'] ?? 'VAZIO'));
    error_log("Telefone limpo: " . $telefone_limpo);
    error_log("CPF limpo: " . $cpf_limpo);
    
    if (empty($telefone_limpo)) {
        error_log("ERRO: Telefone está vazio após limpeza!");
        return false;
    }
    
    // Garantir valores padrão para campos opcionais
    $descricao = !empty($dados['descricao']) ? $dados['descricao'] : '';
    $preco_min = !empty($dados['preco_min']) ? $dados['preco_min'] : 0.00;
    $preco_max = !empty($dados['preco_max']) ? $dados['preco_max'] : 0.00;
    $latitude = !empty($dados['latitude']) ? $dados['latitude'] : null;
    $longitude = !empty($dados['longitude']) ? $dados['longitude'] : null;

    $sql = "INSERT INTO prestadores (
                pres_nome, pres_datanasc, pres_email, pres_telefone, pres_genero, 
                pres_profissao, pres_estado, pres_cidade, pres_senha, prestador_cpf, 
                pres_foto, pres_latitude, pres_longitude, pres_descricao, pres_precomin, pres_precomax
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        error_log("Erro ao preparar statement: " . mysqli_error($conn));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "ssssssssssssssdd", 
        $dados['nome'], 
        $dados['datanasc'], 
        $dados['email'], 
        $telefone_limpo, 
        $dados['genero'], 
        $dados['profissao'], 
        $dados['estado'], 
        $dados['cidade'], 
        $dados['senha'], 
        $cpf_limpo, 
        $dados['foto'],
        $latitude,
        $longitude,
        $descricao,
        $preco_min,
        $preco_max
    );
    
    $resultado = mysqli_stmt_execute($stmt);
    
    if (!$resultado) {
        error_log("Erro ao inserir prestador: " . mysqli_error($conn));
        error_log("Erro mysqli_stmt: " . mysqli_stmt_error($stmt));
    } else {
        error_log("✅ Prestador inserido com sucesso!");
    }
    
    mysqli_stmt_close($stmt);
    
    return $resultado;
}

// Validar todos os campos obrigatórios (para cadastro normal)
function validarCamposObrigatorios($dados) {
   
    $campos = ['nome', 'datanasc', 'email', 'telefone', 'cpf', 'genero', 'profissao', 'cidade', 'estado'];

    foreach ($campos as $campo) {
        if (empty($dados[$campo])) {
            error_log("Campo obrigatório vazio: {$campo}");
            return false;
        }
    }

    // Senha é obrigatória apenas se estiver no array (cadastro normal, não Google)
    if (isset($dados['senha']) && empty($dados['senha'])) {
        error_log("Senha vazia no cadastro normal");
        return false;
    }

    return true;
}
?>