<?php
/**
 * Ferramenta de manutenção: lista e exclui perfis de teste do banco.
 *
 * Considera "perfil de teste" quem tem:
 *   - nenhuma foto, ou foto cujo arquivo não existe mais em uploads/fotos;
 *   - nome com uma palavra só (ex.: "dfdf", "Joao") ou com números (ex.: "123");
 *   - data de nascimento inválida (ex.: 0000-00-00, 1198-12-14).
 *
 * Acesse http://localhost/<pasta-do-projeto>/limpar_perfis_teste.php,
 * confira a lista, desmarque quem deve ficar e clique em "Excluir selecionados".
 * Por segurança, só funciona no próprio computador (localhost).
 * Depois da limpeza, este arquivo pode ser apagado.
 */
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Acesso permitido apenas pelo localhost.');
}

include_once("conexao.php");
require_once("foto_helper.php");

// Retorna os motivos pelos quais o perfil parece ser de teste (vazio = perfil OK)
function motivosPerfilTeste($nome, $foto, $datanasc) {
    $motivos = [];
    $nome = trim(html_entity_decode((string) $nome, ENT_QUOTES, 'UTF-8'));

    if (empty($foto)) {
        $motivos[] = 'sem foto';
    } elseif (!fotoExiste($foto)) {
        $motivos[] = 'arquivo da foto não existe';
    }

    if (preg_match('/\d/', $nome)) {
        $motivos[] = 'nome com números';
    } elseif (!preg_match('/\S+\s+\S+/u', $nome)) {
        $motivos[] = 'nome incompleto';
    }

    $ano = intval(substr((string) $datanasc, 0, 4));
    if ($ano < 1900) {
        $motivos[] = 'data de nascimento inválida';
    }

    return $motivos;
}

// Executa um comando preparado com um parâmetro inteiro
function executarComCodigo($conn, $sql, $codigo) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "i", $codigo);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Remove a foto local se nenhum outro perfil usar o mesmo arquivo
function removerFotoSeSemUso($conn, $foto) {
    if (empty($foto) || ehUrlExterna($foto) || !file_exists($foto)) {
        return;
    }
    $stmt = mysqli_prepare($conn, "SELECT (SELECT COUNT(*) FROM clientes WHERE cli_foto = ?) + (SELECT COUNT(*) FROM prestadores WHERE pres_foto = ?) AS total");
    mysqli_stmt_bind_param($stmt, "ss", $foto, $foto);
    mysqli_stmt_execute($stmt);
    $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
    mysqli_stmt_close($stmt);
    if ($total == 0) {
        @unlink($foto);
    }
}

// Exclui um perfil e tudo que depende dele (mensagens, chats, avaliações, códigos de senha)
function excluirPerfil($conn, $tipo, $codigo) {
    $col = ($tipo === 'cliente') ? 'cli_codigo' : 'pres_codigo';
    $tabela = ($tipo === 'cliente') ? 'clientes' : 'prestadores';
    $campo_foto = ($tipo === 'cliente') ? 'cli_foto' : 'pres_foto';
    $tabela_reset = ($tipo === 'cliente') ? 'reset_codigos_clientes' : 'reset_codigos';
    $campo_email = ($tipo === 'cliente') ? 'cli_email' : 'pres_email';

    $stmt = mysqli_prepare($conn, "SELECT $campo_foto AS foto FROM $tabela WHERE $col = ?");
    mysqli_stmt_bind_param($stmt, "i", $codigo);
    mysqli_stmt_execute($stmt);
    $perfil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$perfil) {
        return false;
    }

    mysqli_begin_transaction($conn);
    try {
        executarComCodigo($conn, "DELETE FROM mensagens WHERE chat_codigo IN (SELECT chat_codigo FROM chat WHERE $col = ?)", $codigo);
        executarComCodigo($conn, "DELETE FROM chat WHERE $col = ?", $codigo);
        executarComCodigo($conn, "DELETE FROM avaliacao WHERE $col = ?", $codigo);
        executarComCodigo($conn, "DELETE FROM $tabela_reset WHERE $campo_email = (SELECT $campo_email FROM $tabela WHERE $col = ?)", $codigo);
        executarComCodigo($conn, "DELETE FROM $tabela WHERE $col = ?", $codigo);
        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        error_log("Erro ao excluir perfil de teste: " . $e->getMessage());
        return false;
    }

    removerFotoSeSemUso($conn, $perfil['foto']);
    return true;
}

// Processa a exclusão
$resultado = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['cliente', 'prestador'] as $tipo) {
        foreach ((array) ($_POST[$tipo] ?? []) as $codigo) {
            $ok = excluirPerfil($conn, $tipo, intval($codigo));
            $resultado[] = ($ok ? 'Excluído' : 'ERRO ao excluir') . " $tipo #" . intval($codigo);
        }
    }
}

// Lista os candidatos
$candidatos = [];
$consultas = [
    'cliente' => "SELECT cli_codigo AS id, cli_nome AS nome, cli_email AS email, cli_foto AS foto, cli_datanasc AS datanasc FROM clientes ORDER BY cli_codigo",
    'prestador' => "SELECT pres_codigo AS id, pres_nome AS nome, pres_email AS email, pres_foto AS foto, pres_datanasc AS datanasc FROM prestadores ORDER BY pres_codigo",
];
foreach ($consultas as $tipo => $sql) {
    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $motivos = motivosPerfilTeste($row['nome'], $row['foto'], $row['datanasc']);
        $row['tipo'] = $tipo;
        $row['motivos'] = $motivos;
        $candidatos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Limpar perfis de teste - Nexus Network</title>
    <style>
        body { font-family: Arial, sans-serif; background: #0a0a0a; color: #eee; padding: 24px; }
        h1 { color: #20cd8d; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #1a1a1a; }
        tr.teste td { background: #2a1414; }
        .ok { color: #20cd8d; }
        .msg { background: #1a1a1a; border-left: 4px solid #20cd8d; padding: 10px; margin: 4px 0; }
        button { background: #dc3545; color: #fff; border: 0; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-size: 15px; margin-top: 16px; }
    </style>
</head>
<body>
    <h1>Perfis de teste</h1>
    <p>Os perfis marcados em vermelho parecem ser de teste e já vêm selecionados. Desmarque quem deve ficar.<br>
       A exclusão remove também as conversas, mensagens e avaliações do perfil.</p>

    <?php foreach ($resultado as $linha): ?>
        <div class="msg"><?= htmlspecialchars($linha) ?></div>
    <?php endforeach; ?>

    <form method="post" onsubmit="return confirm('Excluir definitivamente os perfis selecionados?');">
        <table>
            <tr><th></th><th>Tipo</th><th>#</th><th>Nome</th><th>E-mail</th><th>Motivo</th></tr>
            <?php foreach ($candidatos as $c): $teste = !empty($c['motivos']); ?>
                <tr class="<?= $teste ? 'teste' : '' ?>">
                    <td><input type="checkbox" name="<?= $c['tipo'] ?>[]" value="<?= intval($c['id']) ?>" <?= $teste ? 'checked' : '' ?>></td>
                    <td><?= $c['tipo'] ?></td>
                    <td><?= intval($c['id']) ?></td>
                    <td><?= htmlspecialchars($c['nome'], ENT_QUOTES, 'UTF-8', false) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= $teste ? htmlspecialchars(implode(', ', $c['motivos'])) : '<span class="ok">OK</span>' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit">Excluir selecionados</button>
    </form>
</body>
</html>
