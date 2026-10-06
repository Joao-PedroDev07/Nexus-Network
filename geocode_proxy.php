<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$cidade = $_GET['cidade'] ?? '';
$estado = $_GET['estado'] ?? '';

if (empty($cidade) || empty($estado)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Parâmetros inválidos']);
    exit;
}

$url = "https://nominatim.openstreetmap.org/search?" . http_build_query([
    'city' => $cidade,
    'state' => $estado,
    'country' => 'Brazil',
    'format' => 'json',
    'limit' => 1
]);

$options = [
    'http' => [
        'header' => "User-Agent: NexusNetwork/1.0\r\n"
    ]
];

$context = stream_context_create($options);
$resultado = @file_get_contents($url, false, $context);

if ($resultado === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Falha na geocodificação']);
    exit;
}

echo $resultado;
?>