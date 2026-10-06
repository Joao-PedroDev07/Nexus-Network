<?php
// config/database.php - Usando a mesma configuração que já funciona no seu projeto

$servidor = "localhost";
$usuario = "root";
$senha = "";
$dbname = "nexus network";

$conn = mysqli_connect($servidor, $usuario, $senha, $dbname);

if (!$conn) {
    error_log('Erro na conexão mysqli: ' . mysqli_connect_error());
    http_response_code(500);
    echo json_encode(['error' => 'Erro de conexão com o banco de dados']);
    exit;
}

mysqli_set_charset($conn, 'utf8mb4');

// Criar também conexão PDO usando as mesmas configurações
try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$dbname;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log('Erro na conexão PDO: ' . $e->getMessage());
    // Se PDO falhar, vamos usar apenas mysqli
    $pdo = null;
}
?>