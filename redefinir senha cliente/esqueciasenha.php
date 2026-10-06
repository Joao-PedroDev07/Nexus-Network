<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="../assets/css/esqueciasenha.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1>Esqueceu sua senha?</h1>
            <p class="subtitle">Sem problemas! Digite seu e-mail e enviaremos um código para redefinir sua senha.</p>
        </div>

        <?php
        // Erro devolvido por enviar_codigo.php (e-mail inválido ou não cadastrado)
        if (!empty($_GET['erro'])) {
            echo '<div class="error-message">' . htmlspecialchars($_GET['erro']) . '</div>';
        }
        ?>

        <form action="enviar_codigo.php" method="POST">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="seu@email.com">
            </div>
            <button type="submit">Enviar Código</button>
        </form>

        <div class="back-link">
            <a href="../login.php">← Voltar</a>
        </div>
    </div>
</body>
</html>