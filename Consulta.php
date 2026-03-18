<?php
$datos = json_decode(file_get_contents("facturas_guardadas/facturas_totales.json"), true);
$id = $_GET['id'] ?? '';
$campo = $_GET['campo'] ?? '';

if (!isset($datos[$id])) exit("Error: ID no existe");

$factura = $datos[$id];
$resultados = [];

if ($campo) {
    //Aqui filtramos para encontrar la seccion completa
    if (isset($factura[$campo]) && is_array($factura[$campo])) {
        echo "<pre>" . json_encode($factura[$campo], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        exit;
    }
    //Recorremos toda la factura
    foreach ($factura as $bloque => $contenido) {
        //Buscamos el campo en el primer nivel
        if ($bloque == $campo && !is_array($contenido)) {
            $resultados[] = $contenido;
        }
        //Entramos dentro de una seccion
        if (is_array($contenido)) {
            foreach ($contenido as $etiqueta => $valor) {
                //Buscamos el dato dentro de la seccion
                if ($etiqueta == $campo && !is_array($valor)) {
                    $resultados[] = $valor;
                }
                //En caso de ser una lista miramos cada dato
                if (is_array($valor) && isset($valor[$campo])) {
                    $resultados[] = $valor[$campo];
                }
            }
        }
    }

    if (!empty($resultados)) {
        echo implode(" - ", $resultados);
    } else {
        echo "No se encontró datos en : $campo";
    }

} else {
    //Si no hay campo enseñamos la factura entera
    echo "<pre>" . json_encode($factura, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
}
?>
