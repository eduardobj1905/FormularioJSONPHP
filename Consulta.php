<?php
$facturas = json_decode(file_get_contents("facturas_guardadas/facturas_totales.json"), true);
$id = $_GET['id'] ?? '';
$campo = $_GET['campo'] ?? '';
if (isset($facturas[$id]['emisor'][$campo])) {
   echo  $facturas[$id]['emisor'][$campo];
} else {
    echo "Error: No se encontró el $campo para la factura con ID $id.";
}
?>