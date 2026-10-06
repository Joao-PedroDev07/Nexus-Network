<?php
session_start();

if (!isset($_SESSION['email_validado'])) {
    header("Location: verificar_codigo1.php");
    exit;
}

$email = $_SESSION['email_validado'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="../assets/css/redefinir_senha.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon"></div>
            <h1>Redefinir Senha</h1>
            <p class="subtitle">Crie uma nova senha segura para sua conta</p>
        </div>

        <form action="processa_redefinicao.php" method="POST">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            
            <div class="input-group">
                <input type="password" name="nova_senha" required placeholder="Nova Senha">
            </div>
            
            <div class="input-group">
                <input type="password" name="confirmar_senha" required placeholder="Confirmar Senha">
            </div>

            <div class="requirements">
                <p>Requisitos da senha:</p>
                <ul>
                    <li>Mínimo de 8 caracteres</li>
                    <li>Letras maiúsculas e minúsculas</li>
                    <li>Pelo menos um número</li>
                    <li>Caractere especial recomendado</li>
                </ul>
            </div>
            
            <button type="submit">Alterar senha</button>
            <div class="back-link">
            <a href="../login.php">← Voltar</a>
        </div>
        </form>
    </div>
</body>
</html>
