<?php
if(!defined('EXECUTAR_SCRIPT')) {
    die('Script desabilitado por segurança. Leia as instruções no código.');
}

include_once("conexao.php");
include_once("geocode_helper.php");

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='utf-8'><title>Atualização de Coordenadas</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
echo "</head><body>";

echo "<h1>Atualizando Coordenadas dos Prestadores</h1>";
echo "<hr>";

// Buscar todos os prestadores
$sql = "SELECT pres_codigo, pres_nome, pres_cidade, pres_estado, pres_latitude, pres_longitude 
        FROM prestadores";
$result = mysqli_query($conn, $sql);

if(!$result) {
    echo "<p class='error'>Erro ao buscar prestadores: " . mysqli_error($conn) . "</p>";
    exit;
}

$total = mysqli_num_rows($result);
$atualizados = 0;
$erros = 0;
$ja_tinham = 0;

echo "<p class='info'>Total de prestadores encontrados: $total</p>";
echo "<hr>";

while($row = mysqli_fetch_assoc($result)) {
    echo "<p><strong>Processando:</strong> {$row['pres_nome']} ({$row['pres_cidade']}, {$row['pres_estado']})</p>";
    
    // Verificar se já tem coordenadas
    if(!empty($row['pres_latitude']) && !empty($row['pres_longitude'])) {
        echo "<p class='info'>→ Já possui coordenadas: {$row['pres_latitude']}, {$row['pres_longitude']}</p>";
        $ja_tinham++;
        continue;
    }
    
    // Buscar coordenadas
    $coords = obterCoordenadas($row['pres_cidade'], $row['pres_estado'], $conn);
    
    if($coords) {
        // Atualizar no banco
        $pres_codigo = $row['pres_codigo'];
        $lat = $coords['latitude'];
        $lon = $coords['longitude'];
        
        $update = "UPDATE prestadores 
                   SET pres_latitude = $lat, 
                       pres_longitude = $lon 
                   WHERE pres_codigo = $pres_codigo";
        
        if(mysqli_query($conn, $update)) {
            echo "<p class='success'>→ Coordenadas atualizadas: $lat, $lon</p>";
            $atualizados++;
        } else {
            echo "<p class='error'>→ Erro ao atualizar no banco: " . mysqli_error($conn) . "</p>";
            $erros++;
        }
    } else {
        echo "<p class='error'>→ Não foi possível obter coordenadas</p>";
        $erros++;
    }
    
    echo "<hr>";
    
    // Delay para não sobrecarregar a API (requisito do Nominatim: máx 1 req/segundo)
    sleep(1);
    
    // Flush output para mostrar progresso
    flush();
    ob_flush();
}

echo "<h2>Resumo da Atualização</h2>";
echo "<p><strong>Total processados:</strong> $total</p>";
echo "<p class='success'><strong>Atualizados com sucesso:</strong> $atualizados</p>";
echo "<p class='info'><strong>Já tinham coordenadas:</strong> $ja_tinham</p>";
echo "<p class='error'><strong>Erros:</strong> $erros</p>";

echo "<hr>";
echo "<h3>Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Verifique se todos os prestadores foram atualizados corretamente</li>";
echo "<li>Comente a linha 'define('EXECUTAR_SCRIPT', true);' neste arquivo</li>";
echo "<li>Por segurança, remova ou mova este arquivo para fora do diretório público</li>";
echo "</ol>";

echo "</body></html>";
?>