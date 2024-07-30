<?php
function buscarPorPatente($patentes, $patenteBuscada) {
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
$patenteBuscada = "LJHX-56";
$resultado['id'] = buscarPorPatente($patentes, $patenteBuscada);

if ($resultado !== null) {
    echo "Patente encontrada: " . json_encode($resultado, JSON_PRETTY_PRINT);
} else {
    echo "Patente no encontrada.";
}
?>
