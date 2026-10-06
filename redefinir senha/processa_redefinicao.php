<?php
session_start();

// Só acessa quem validou o código de verificação
if (!isset($_SESSION['email_validado'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Nexus Network</title>
    <link rel="stylesheet" href="../assets/css/processa_redefinicao.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Redefinir Senha</h1>
            <p>Crie uma nova senha segura para sua conta</p>
        </div>

        <div class="content">
            <?php

            if (!isset($_SESSION['email_validado'])) {
                header("Location: ../login.php");
                exit;
            }

            $email = $_SESSION['email_validado'];

            // Se o formulário foi enviado
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nova_senha = isset($_POST['nova_senha']) ? $_POST['nova_senha'] : '';
                $confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';

                if ($nova_senha === '' || $confirmar_senha === '') {
                    echo '<div class="message error">Preencha todos os campos.</div>';
                } elseif ($nova_senha !== $confirmar_senha) {
                    echo '<div class="message error">As senhas não coincidem.</div>';
                } else {
                    $conn = mysqli_connect("localhost", "root", "", "nexus network");

                    if (!$conn) {
                        echo '<div class="message error">Erro de conexão: ' . mysqli_connect_error() . '</div>';
                    } else {
                        $senha_com_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                        $stmt = $conn->prepare("UPDATE prestadores SET pres_senha = ? WHERE pres_email = ?");
                        if (!$stmt) {
                            echo '<div class="message error">Erro na preparação da query: ' . $conn->error . '</div>';
                        } else {
                            $dados = [
                                'senha' => $senha_com_hash,
                                'email' => $email
                            ];

                            $stmt->bind_param("ss", $dados['senha'], $dados['email']);

                            if ($stmt->execute()) {
                                unset($_SESSION['email_validado']);
                                session_destroy();
                                echo '<div class="message success">Senha alterada com sucesso! Você pode fazer login novamente.</div>';
                                echo '<a href="../login.php" class="btn-back">Voltar</a>';
                            } else {
                                echo '<div class="message error">Erro ao atualizar a senha: ' . $stmt->error . '</div>';
                            }

                            $stmt->close();
                        }
                        $conn->close();
                    }
                }
            }

            // Se ainda não foi processado, mostra o formulário
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (isset($nova_senha) && isset($confirmar_senha) && ($nova_senha === '' || $confirmar_senha === '' || $nova_senha !== $confirmar_senha))) {
            ?>
                <div class="email-info">
                    <strong>Email: <?php echo htmlspecialchars($email); ?></strong>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nova_senha">Nova Senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" required placeholder="Digite sua nova senha">
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Nova Senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Digite novamente sua nova senha">
                    </div>

                    <button type="submit" class="btn-submit">Redefinir Senha</button>
                </form>

                <div class="password-requirements">
                    <h3>Dicas para uma senha segura:</h3>
                    <ul>
                        <li>Use pelo menos 8 caracteres</li>
                        <li>Misture letras maiúsculas e minúsculas</li>
                        <li>Inclua números e caracteres especiais</li>
                        <li>Evite informações pessoais óbvias</li>
                    </ul>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</body>
</html>