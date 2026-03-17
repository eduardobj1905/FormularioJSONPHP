<?php
$facturas = json_decode(file_get_contents("facturas_guardadas/facturas_totales.json"), true);

$id = $_GET['id'] ?? '';
$tipoUsuario = $_GET['tipoUsuario'] ?? ''; 
$campo = $_GET['campo'] ?? '';
if (!isset($facturas[$id])) {
    exit("Error: La factura con ID $id no existe.");
}
if ($tipoUsuario && $campo) {
    echo $facturas[$id][$tipoUsuario][$campo] ?? "Campo no encontrado";
} else {
   echo "<pre>";
    echo json_encode($facturas[$id], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
}
?>