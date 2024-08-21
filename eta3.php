<?php

// Conexión a la base de datos
$conn = new mysqli('ls-3c0c538286def4da7f8273aa5531e0b6eee0990c.cylsiewx0zgx.us-east-1.rds.amazonaws.com','dbmasteruser','eF5D;6VzP$^7qDryBzDd,`+w(5e4*qI+','masgps');
if($conn->connect_errno > 0){
    die("Error en la conexión". $conn->connect_error);
}

$Qry = "SELECT hash FROM masgps.hash WHERE user='laReina' AND pasw='123'";
$resultado = mysqli_query($conn, $Qry);

if (!$resultado) {
    die("Error en la consulta del hash: " . mysqli_error($conn));
}

$data = mysqli_fetch_array($resultado);
$hash = $data['hash'];

date_default_timezone_set("America/Santiago");
$hoy = date("Y-m-d");

$mysqli = new mysqli('ls-8ce02ad0b7ea586d393e375c25caa3488acb80a5.cylsiewx0zgx.us-east-1.rds.amazonaws.com', 'dbmasteruser', ':&T``E~r:r!$1c6d:m143lzzvGJ$NuP;', 'laReina');
if ($mysqli->connect_errno > 0) {
    die("Error en la conexión" . $mysqli->connect_error);
}

$place = $_GET['paradero'];

$consulta = "SELECT * FROM laReina.paraderosLaReina WHERE Name='$place'";
$result = mysqli_query($mysqli, $consulta);

if (!$result || mysqli_num_rows($result) == 0) {
    die("No se encontraron resultados para el paradero especificado.");
}

$row = mysqli_fetch_array($result);
$lng1 = str_replace(',', '.', strval($row['Latitud']));
$lat1 = str_replace(',', '.', strval($row['Longitud']));

// Definición de funciones
function calculateDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6371;
    $lat1 = deg2rad($lat1);
    $lng1 = deg2rad($lng1);
    $lat2 = deg2rad($lat2);
    $lng2 = deg2rad($lng2);
    $dLat = $lat2 - $lat1;
    $dLng = $lng2 - $lng1;
    $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;
    return $distance;
}

function calculateTimeInMinutes($distance, $speed)
{
    $timeInHours = $distance / $speed;
    $timeInMinutes = round($timeInHours * 60, 2);
    return $timeInMinutes;
}

// Mapeo de group_id a nombres de ruta
$rutas = [
    9818 => 'Ruta 1: Circuito Principal',
    9819 => 'Ruta 2: Circuito Líder - 10:00 a.m. a 08:00 p.m.',
    9820 => 'Ruta 3: Circuito Consultorio - 09:00 a.m. a 05:00 p.m.',
    9821 => 'Ruta 4: Sin Asignación',
    9822 => 'Ruta 5: Servicio Especial'
];

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
        'Content-Type: application/json',
    ),
));

$listado = curl_exec($curl);
$json = json_decode($listado);
$array = $json->list;
$k = 0;

foreach ($array as $item) {
    $id_tracker = $item->id;
    $group_id = $item->group_id;
    $patente = $item->label;

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

    if ($array->state->inputs[0] == true) {
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
            'direction' => $direccion,
            'movement_status' => $movement_status,
            'ignition' => $ignicion,
            'distance' => $distance,
            'eta' => $timeInMinutes,
            'ruta' => $rutas[$group_id] ?? 'Ruta Desconocida'
        );

        $total[$k] = $json2;
        $k++;
    }
}

usort($total, function ($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

echo json_encode($total);

