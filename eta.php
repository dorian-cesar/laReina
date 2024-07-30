<?php
$mysqli = new mysqli('ls-8ce02ad0b7ea586d393e375c25caa3488acb80a5.cylsiewx0zgx.us-east-1.rds.amazonaws.com', 'dbmasteruser', ':&T``E~r:r!$1c6d:m143lzzvGJ$NuP;', 'laReina');
if ($mysqli->connect_errno > 0) {
    die("Error en la conexión" . $mysqli->connect_error);
}


$place = $_GET['paradero'];

$patenteBuscada = $_GET['patente'];

$hash = 'e6da71644ac219ce6effb9666ff4082e';

$consulta = "SELECT * FROM laReina.paradero where codigo='$place'";

$resutaldo = mysqli_query($mysqli, $consulta);

function buscarPorPatente($patentes, $patenteBuscada)
{
    foreach ($patentes as $patente) {
        if ($patente["patente"] === $patenteBuscada) {
            return $patente;
        }
    }
    return null; // Devuelve null si no se encuentra la patente
}

$patentes = [
    [
        "id" => 10283522,
        "patente" => "LYBH-16"
    ],
    [
        "id" => 10283526,
        "patente" => "LJHX-56"
    ],
    [
        "id" => 10283527,
        "patente" => "DWVY-96"
    ],
    [
        "id" => 10283528,
        "patente" => "DWVY-95"
    ],
    [
        "id" => 10283529,
        "patente" => "LJHX-55"
    ],
    [
        "id" => 10283530,
        "patente" => "LJHX-57"
    ]
];

// Ejemplo de uso

$id = buscarPorPatente($patentes, $patenteBuscada);

$id_trcaker = json_encode($id['id']);





$data = mysqli_fetch_array($resutaldo);

$lat1 = $data['lat'];
$lng1 = $data['lon'];


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
    CURLOPT_POSTFIELDS => '{"hash": "' . $hash . '", "tracker_id": ' . $id_trcaker . '}',
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

$speedMedia = 22; // Velocidad promedio en km/h

$distance = calculateDistance($lat1, $lng1, $lat2, $lng2);

$timeInMinutes = calculateTimeInMinutes($distance, $speedMedia);

$json2= array(


    //'id' => $id,
    'patente' => $patenteBuscada,
    'lat' => $lat2,
    'lng' => $lng2,
    'speed' => $speed,
    'direccion' => $direccion,
    'movement_status' => $movement_status,
    'ignicion' => $ignicion,
    'distance' => $distance,
    'eta' => $timeInMinutes

);

echo json_encode($json2);
