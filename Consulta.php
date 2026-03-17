<?php
$facturas = json_decode(file_get_contents("facturas_guardadas/facturas_totales.json"), true);
$id = $_GET['id'] ?? '';
$tipoUsuario = $_GET['tipoUsuario'] ?? ''; 
$campo = $_GET['campo'] ?? '';     
if(!isset($facturas[$id])) exit("Factura no encontrada");
echo $facturas[$id][$tipoUsuario][$campo];
?>
