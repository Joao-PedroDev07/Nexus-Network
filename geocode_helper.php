<?php
/**
 * Funções auxiliares para geolocalização
 */

/**
 * Obtém coordenadas de uma cidade usando API de geocodificação
 * Usa OpenStreetMap Nominatim (gratuito) ou pode ser substituído por Google Maps API
 */
function obterCoordenadas($cidade, $estado, $conn) {
    // Primeiro, verificar se já está no cache
    $cidade = mysqli_real_escape_string($conn, $cidade);
    $estado = mysqli_real_escape_string($conn, $estado);
    
    $sql_cache = "SELECT latitude, longitude FROM geocode_cache 
                  WHERE cidade = '$cidade' AND estado = '$estado'";
    $result = mysqli_query($conn, $sql_cache);
    
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return [
            'latitude' => (float)$row['latitude'],
            'longitude' => (float)$row['longitude']
        ];
    }
    
    // Se não estiver no cache, buscar da API
    $endereco = urlencode("$cidade, $estado, Brasil");
    
    // Usando Nominatim (OpenStreetMap) - Gratuito
    $url = "https://nominatim.openstreetmap.org/search?q=$endereco&format=json&limit=1";
    
    $options = [
        'http' => [
            'header' => "User-Agent: NexusNetwork/1.0\r\n",
            'timeout' => 5 // evita que a página trave se a API demorar
        ]
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if($response === FALSE) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if(!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
        $lat = (float)$data[0]['lat'];
        $lon = (float)$data[0]['lon'];
        
        // Salvar no cache
        $sql_insert = "INSERT INTO geocode_cache (cidade, estado, latitude, longitude) 
                       VALUES ('$cidade', '$estado', $lat, $lon)
                       ON DUPLICATE KEY UPDATE latitude = $lat, longitude = $lon";
        mysqli_query($conn, $sql_insert);
        
        return [
            'latitude' => $lat,
            'longitude' => $lon
        ];
    }
    
    return null;
}

/**
 * Calcula distância entre dois pontos usando fórmula de Haversine
 */
function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // Raio da Terra em km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distancia = $R * $c;
    
    return round($distancia, 2);
}

/**
 * Atualiza coordenadas de um prestador
 */
function atualizarCoordenadasPrestador($pres_codigo, $conn) {
    $sql = "SELECT pres_cidade, pres_estado FROM prestadores WHERE pres_codigo = $pres_codigo";
    $result = mysqli_query($conn, $sql);
    
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $coords = obterCoordenadas($row['pres_cidade'], $row['pres_estado'], $conn);
        
        if($coords) {
            $update = "UPDATE prestadores 
                      SET pres_latitude = {$coords['latitude']}, 
                          pres_longitude = {$coords['longitude']}
                      WHERE pres_codigo = $pres_codigo";
            mysqli_query($conn, $update);
            return true;
        }
    }
    return false;
}

/**
 * Atualiza coordenadas de todos os prestadores sem coordenadas
 */
function atualizarTodasCoordenadasPrestadores($conn) {
    $sql = "SELECT pres_codigo, pres_cidade, pres_estado 
            FROM prestadores 
            WHERE pres_latitude IS NULL OR pres_longitude IS NULL";
    
    $result = mysqli_query($conn, $sql);
    $atualizados = 0;
    
    if($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $coords = obterCoordenadas($row['pres_cidade'], $row['pres_estado'], $conn);
            
            if($coords) {
                $update = "UPDATE prestadores 
                          SET pres_latitude = {$coords['latitude']}, 
                              pres_longitude = {$coords['longitude']}
                          WHERE pres_codigo = {$row['pres_codigo']}";
                
                if(mysqli_query($conn, $update)) {
                    $atualizados++;
                }
            }
            
            // Delay para não sobrecarregar a API (requisito do Nominatim)
            sleep(1);
        }
    }
    
    return $atualizados;
}

/**
 * Obtém localização do usuário via IP (fallback)
 */
function obterLocalizacaoPorIP() {
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Para localhost, retornar coordenadas padrão (São Paulo)
    if($ip == '127.0.0.1' || $ip == '::1') {
        return [
            'latitude' => -23.5505,
            'longitude' => -46.6333,
            'cidade' => 'São Paulo',
            'estado' => 'SP'
        ];
    }
    
    // Usar API de geolocalização por IP (gratuita)
    $url = "http://ip-api.com/json/$ip";
    $context = stream_context_create(['http' => ['timeout' => 5]]);
    $response = @file_get_contents($url, false, $context);
    
    if($response) {
        $data = json_decode($response, true);
        if($data && $data['status'] == 'success') {
            return [
                'latitude' => $data['lat'],
                'longitude' => $data['lon'],
                'cidade' => $data['city'],
                'estado' => $data['regionName']
            ];
        }
    }
    
    return null;
}

/**
 * Formata distância para exibição
 */
function formatarDistancia($distancia) {
    if($distancia < 1) {
        return round($distancia * 1000) . ' m';
    } else {
        return round($distancia, 1) . ' km';
    }
}
?>