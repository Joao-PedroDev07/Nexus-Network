<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "nexus network");

$email = isset($_POST['email']) ? $_POST['email'] : '';
$codigo = isset($_POST['codigo']) ? $_POST['codigo'] : '';
$mensagemErro = '';

if ($email != '' && $codigo != '') {
    mysqli_query($conn, "DELETE FROM reset_codigos WHERE expiracao < NOW()");

    $email_esc = mysqli_real_escape_string($conn, $email);
    $codigo_esc = mysqli_real_escape_string($conn, $codigo);

    $sql = "SELECT * FROM reset_codigos WHERE pres_email = '$email_esc' AND codigo = '$codigo_esc' AND expiracao > NOW()";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        mysqli_query($conn, "DELETE FROM reset_codigos WHERE pres_email = '$email_esc'");

        // Guarda o email na sessão para liberar o acesso à redefinição
        $_SESSION['email_validado'] = $email;

        // Redireciona para redefinir senha
        header("Location: redefinir_senha.php");
        exit;
    } else {
        $mensagemErro = "Código inválido ou expirado.";
    }
}

// Se não veio email pelo POST, tenta pegar por GET pra preencher o form
if ($email == '' && isset($_GET['email'])) {
    $email = $_GET['email'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - Nexus Network</title>
    <link rel="stylesheet" href="../assets/css/verificar_codigo1.css">
</head>
<body>

<div class="container">
    <div class="logo">
        <h1>Nexus <span class="logo-accent">Network</span></h1>
    </div>
    
    <p class="subtitle">Digite o código enviado para seu e-mail</p>

    <?php if ($mensagemErro != ''): ?>
        <div class="alert-error">
            <?php echo htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form action="verificar_codigo1.php" method="POST">
        <div class="input-group">
            <label for="email">E-mail</label>
            <input type="email" 
                   id="email"
                   name="email" 
                   required 
                   placeholder="seu@email.com"
                   value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="input-group">
            <label for="codigo">Código de Verificação</label>
            <input type="text" 
                   id="codigo"
                   name="codigo" 
                   required 
                   placeholder="Digite o código recebido" 
                   value="">
        </div>

        <button type="submit">Verificar Código</button>
    </form>

    <div class="info-box">
        <strong>Não recebeu o código?</strong> Verifique sua caixa de spam ou aguarde alguns minutos.
    </div>
     <div class="back-link">
            <a href="../login.php">← Voltar</a>
        </div>
</div>


</body>
</html>