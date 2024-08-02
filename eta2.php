<?php
$mysqli = new mysqli('ls-8ce02ad0b7ea586d393e375c25caa3488acb80a5.cylsiewx0zgx.us-east-1.rds.amazonaws.com', 'dbmasteruser', ':&T``E~r:r!$1c6d:m143lzzvGJ$NuP;', 'laReina');
if ($mysqli->connect_errno > 0) {
    die("Error en la conexión" . $mysqli->connect_error);
}

$place = $_GET['paradero'];

$hash = 'e6da71644ac219ce6effb9666ff4082e';

$consulta = "SELECT * FROM laReina.paradero where codigo='$place'";

$result = mysqli_query($mysqli, $consulta);

if (!$result || mysqli_num_rows($result) == 0) {
    die("No se encontraron resultados para el paradero especificado.");
}

$row = mysqli_fetch_array($result);


$lat1 = $row['lat'];
$lng1 = $row['lon'];

$r1 = $row['r1'];
$r2 = $row['r2'];
$r3 = $row['r3'];
$r4 = $row['r4'];
$r5 = $row['r5'];

$routes = array($r1, $r2, $r3, $r4, $r5);

$name_routes = [9744, 9745, 9746, 9747, 9748];
$j = 0;
for ($i = 0; $i <= 4; $i++) {
    if ($routes[$i] == 1) {
        $rutas[$j] = ($name_routes[$i]);
        $j++;
    }
}

// Definición de funciones
function calculateDistance($lat1, $lng1, $lat2, $lng2)
{
    // Radio de la Tierra en kilómetros
    $earthRadius = 6371;

    // Convertir las latitudes y longitudes de grados a radianes
    $lat1 = deg2rad($lat1);
    $lng1 = deg2rad($lng1);
    $lat2 = deg2rad($lat2);
    $lng2 = deg2rad($lng2);

    // Diferencias de latitud y longitud
    $dLat = $lat2 - $lat1;
    $dLng = $lng2 - $lng1;

    // Aplicar la fórmula del haversine
    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos($lat1) * cos($lat2) *
        sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // Calcular la distancia
    $distance = $earthRadius * $c;

    return $distance;
}

function calculateTimeInMinutes($distance, $speed)
{
    // Tiempo en horas
    $timeInHours = $distance / $speed;
    // Convertir a minutos y limitar a 2 decimales
    $timeInMinutes = round($timeInHours * 60, 2);
    return $timeInMinutes;
}

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/list',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '{"hash":"' . $hash . '"}',
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json, text/plain, */*',
        'Accept-Language: es-419,es;q=0.9,en;q=0.8',
        'Connection: keep-alive',
        'Content-Type: application/json',
        'Cookie: _ga=GA1.2.728367267.1665672802; locale=es; _gid=GA1.2.967319985.1673009696; _gat=1; session_key=5d7875e2bf96b5966225688ddea8f098',
        'Origin: http://www.trackermasgps.com',
        'Referer: http://www.trackermasgps.com/',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36'
    ),
));

$listado = curl_exec($curl);

$json = json_decode($listado);

$array = $json->list;
$k=0;

foreach ($array as $item) {
    $id_tracker = $item->id;
    $group_id = $item->group_id;
    $patente = $item->label;
    
    foreach ($rutas as $rutaa) {
        if ($rutaa == $group_id) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/get_state',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{"hash": "' . $hash . '", "tracker_id": ' . $id_tracker . '}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response2 = curl_exec($curl);
            curl_close($curl);

            $array = json_decode($response2);
            $lat2 = $array->state->gps->location->lat;
            $lng2 = $array->state->gps->location->lng;
            $speed = $array->state->gps->speed;
            $movement_status = $array->state->movement_status;
            $ignicion = $array->state->inputs[0];
            $direccion = $array->state->gps->heading;
            $connection_status = $array->state->connection_status;

            $speedMedia = 22; // Velocidad promedio en km/h

            $distance = calculateDistance($lat1, $lng1, $lat2, $lng2);
            $timeInMinutes = calculateTimeInMinutes($distance, $speedMedia);

            $json2 = array(
                'patente' => $patente,
                'lat' => $lat2,
                'lng' => $lng2,
                'speed' => $speed,
                'direccion' => $direccion,
                'movement_status' => $movement_status,
                'ignicion' => $ignicion,
                'distance' => $distance,
                'eta' => $timeInMinutes,
                'ruta' => $rutaa-9743
            );

           $total[$k]=$json2;
           $k++;
        }

        
    }


}

echo json_encode($total);
?>
