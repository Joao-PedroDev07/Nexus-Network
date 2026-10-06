<?php
require_once('src/PHPMailer.php');
require_once('src/SMTP.php');
require_once('src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// RECEBE DADOS DO FORMULÁRIO
$email = trim($_POST['email'] ?? '');
$codigo = (string) random_int(100000, 999999);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: esqueciasenha.php?erro=" . urlencode("E-mail inválido."));
    exit();
}

// CONEXÃO COM BANCO DE DADOS
$conn = new mysqli("localhost", "root", "", "nexus network");
$conn->set_charset("utf8mb4");

// VERIFICA SE O E-MAIL PERTENCE A UM PRESTADOR CADASTRADO
$stmt = $conn->prepare("SELECT pres_codigo FROM prestadores WHERE pres_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$prestador = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$prestador) {
    header("Location: esqueciasenha.php?erro=" . urlencode("Não encontramos um prestador com este e-mail."));
    exit();
}

// Limpa códigos expirados e códigos antigos deste e-mail
$conn->query("DELETE FROM reset_codigos WHERE expiracao < NOW()");
$stmt = $conn->prepare("DELETE FROM reset_codigos WHERE pres_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

// INSERE O CÓDIGO NA TABELA reset_codigos (expiração calculada pelo próprio banco)
$stmt = $conn->prepare("INSERT INTO reset_codigos (pres_codigo, pres_email, codigo, expiracao) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
$stmt->bind_param("iss", $prestador['pres_codigo'], $email, $codigo);
$stmt->execute();
$stmt->close();

// ENVIO DO CÓDIGO POR E-MAIL USANDO PHPMailer
$mail = new PHPMailer(true);


try {
    // $mail->SMTPDebug = 2; // Ative para debug

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'nexustcc5@gmail.com';
    $mail->Password = 'qeve fysc jqlc etfv'; // Use senha de app do Gmail!
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // SSL flexível (útil em localhost)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // REMETENTE E DESTINATÁRIO
    $mail->setFrom('nexustcc5@gmail.com', 'Nexus Network - Recuperação de Senha');
    $mail->addAddress($email); // Email do usuário

    // CONTEÚDO DO EMAIL
    $mail->isHTML(true);
    $mail->Subject = "Código de Verificação - Nexus Network";
    $mail->Body    = "<p>Código de Verificação: <strong>$codigo</strong></p><p>Válido apenas por 10 minutos.</p>";
    $mail->AltBody = "Código de Verificação: $codigo. Válido apenas por 10 minutos.";

    $mail->send();
    header("Location: verificar_codigo1.php?email=" . urlencode($email));
    exit();
} catch (Exception $e) {
    echo "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
}