<?php
/**
 * Helper para gerenciar fotos de perfil (locais e URLs externas)
 */

/**
 * Verifica se a foto é uma URL externa ou arquivo local
 */
function ehUrlExterna($caminho) {
    return filter_var($caminho, FILTER_VALIDATE_URL) !== false;
}

/**
 * Verifica se a foto existe (URL externa ou arquivo local)
 */
function fotoExiste($caminho) {
    if (empty($caminho)) {
        return false;
    }
    
    // Se for URL externa, considera que existe
    if (ehUrlExterna($caminho)) {
        return true;
    }
    
    // Se for arquivo local, verifica se existe
    return file_exists($caminho);
}

/**
 * Caminho do avatar padrão (usado quando o usuário não tem foto)
 */
define('FOTO_PADRAO', 'assets/img/default-avatar.png');

/**
 * Retorna a foto informada se ela existir, ou o avatar padrão
 */
function fotoOuPadrao($caminho) {
    return fotoExiste($caminho) ? $caminho : FOTO_PADRAO;
}

/**
 * Retorna o caminho da foto ou placeholder padrão
 */
function obterFotoPerfil($dados_usuario, $tipo_usuario = 'cliente') {
    $campo_foto = ($tipo_usuario == 'cliente') ? 'cli_foto' : 'pres_foto';

    // Verificar múltiplos campos possíveis
    $foto = $dados_usuario[$campo_foto] ??
            $dados_usuario['foto'] ??
            $dados_usuario['usuario_foto'] ??
            null;

    return fotoOuPadrao($foto);
}

/**
 * Remove foto local (não remove URLs externas)
 */
function removerFotoLocal($caminho) {
    if (empty($caminho)) {
        return false;
    }
    
    // Não remove URLs externas
    if (ehUrlExterna($caminho)) {
        return true;
    }
    
    // Remove arquivo local
    if (file_exists($caminho)) {
        return unlink($caminho);
    }
    
    return false;
}

/**
 * Valida se a URL da foto do Google é válida
 */
function validarFotoGoogle($url) {
    if (empty($url)) {
        return false;
    }
    
    // Verificar se é URL válida
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // Verificar se é do Google
    $dominios_validos = [
        'googleusercontent.com',
        'ggpht.com',
        'googleapis.com'
    ];
    
    foreach ($dominios_validos as $dominio) {
        if (strpos($url, $dominio) !== false) {
            return true;
        }
    }
    
    return false;
}
?>