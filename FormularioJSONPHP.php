<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
    $uuid = rand(1,100);
    $fechaEmision = date('d/m/Y');
    $fechaFinCobro = date('d/m/Y',strtotime('+1 month'));

    //Recogemos los datos del emisor y del receptor
    $emisor = [
        "nombre" => $_POST['emi_name'] ?? '',
        "cif" => $_POST['emi_cif'] ?? '',
        "direccion" => $_POST['emi_adress'] ?? '',
        "zip" => $_POST['emi_zip_code'] ?? '',
        "ciudad" => $_POST['emi_city'] ?? '',
        "estado" => $_POST['emi_state'] ?? '',
        "pais" => $_POST['emi_country'] ?? '',
        "email" => $_POST['emi_email'] ?? '',
        "telefono" => $_POST['emi_phone'] ?? ''
    ];

    $receptor = [
        "nombre" => $_POST['rec_name'] ?? '',
        "cif" => $_POST['rec_cif'] ?? '',
        "direccion" => $_POST['rec_adress'] ?? '',
        "zip" => $_POST['rec_zip_code'] ?? '',
        "ciudad" => $_POST['rec_city'] ?? '',
        "estado" => $_POST['rec_state'] ?? '',
        "pais" => $_POST['rec_country'] ?? '',
        "email" => $_POST['rec_email'] ?? '',
        "telefono" => $_POST['rec_phone'] ?? ''
    ];

    // Procesamos Conceptos
    $conceptos = [];
    $totalSinImpuestos = 0;
    if (isset($_POST['desc'])) {
        foreach ($_POST['desc'] as $key => $desc) {
            $precio = floatval($_POST['price'][$key]);
            $cantidad = floatval($_POST['quant'][$key]);
            $subtotal = $precio * $cantidad;
            $totalSinImpuestos += $subtotal;

            $conceptos[] = [
                "descripcion" => $desc,
                "precio" => $precio,
                "cantidad" => $cantidad,
                "subtotal" => number_format($subtotal, 2, '.', '')
            ];
        }
    }
    // Procesamos Impuestos
    $impuestosArr = [];
    $totalImpuestos = 0;
    if (isset($_POST['imp_nom'])) {
        foreach ($_POST['imp_nom'] as $key => $nom) {
            $pct = floatval($_POST['imp_pct'][$key]);
            $importe = $totalSinImpuestos * ($pct / 100);
            $totalImpuestos += $importe;

            $impuestosArr[] = [
                "nombre" => $nom,
                "porcentaje" => $pct,
                "cantidadCalculada" => number_format($importe, 2, '.', '')
            ];
        }
    }
    // Estructura Final
    $factura = [
        $uuid => [
            "emisor" => $emisor,
            "receptor" => $receptor,
            "conceptos" => $conceptos,
            "impuestos" => $impuestosArr,
            "totales" => [
                "totalSinImpuestos" => number_format($totalSinImpuestos, 2, '.', ''),
                "cantidadDelImpuesto" => number_format($totalImpuestos, 2, '.', ''),
                "totalFinal" => number_format($totalSinImpuestos + $totalImpuestos, 2, '.', '')
            ],
            "fecha_emision" => $fechaEmision,
            "fecha_fin_cobro" => $fechaFinCobro,
            "estado_factura" => rand(1, 5)
        ]
    ];


$json_data = json_encode($factura, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $filename = "factura_" . $uuid . "_" . ($receptor['nombre']) . ".json";

    $archivoFacturas = "facturas_totales.json"; 
    $contenedorFacturas = "facturas_guardadas";    
    $rutaTotal = $contenedorFacturas . "/" . $archivoFacturas; 

    
    if (!file_exists($contenedorFacturas)) {
        mkdir($contenedorFacturas, 0777, true);
    }

    $todasLasFacturas = [];
    if (file_exists($rutaTotal)) {
        $contenido = file_get_contents($rutaTotal);
        $todasLasFacturas = json_decode($contenido, true) ?: [];
    }

    $todasLasFacturas[$uuid] = $factura[$uuid];

    $json_final = json_encode($todasLasFacturas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents($rutaTotal, $json_final)) {
        echo "<script>alert('Factura $uuid registrada.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tarea Factura JSON</title>
    <style>
        body { font-family: Arial,
             sans-serif; margin: 30px;
              line-height: 1.6;
            display: flex;
            flex-direction: column;
            align-items: center; }
    
    .bloque { 
        border: 1px solid #ccc; 
        padding: 20px; 
        margin-bottom: 20px; 
        width: 600px; 
    }

    h3 { margin-top: 0; color: #333; }
    label { 
        display: block; 
        font-weight: bold; 
        margin: 15px 0 5px 0; 
        border-bottom: 1px solid #eee;
    }

    input { 
        padding: 6px; 
        margin: 4px 2px; 
        border: 1px solid #999;
        border-radius: 3px;
    }

    .bloque input[type="text"] { width: 28%; }

    .fila { margin-top: 10px; display: flex; gap: 5px; }
    .fila input { flex: 1; width: auto; }
    .description { flex: 3; } 
    button { 
        margin-top: 10px; 
        padding: 5px 15px; 
        cursor: pointer; 
        background: #eee; 
        border: 1px solid #888; 
    }
    button:hover { background: #ddd; }
    
    </style>
</head>
<body>

    <h2>Nueva Factura</h2>

    <form method = "POST" action ="">
    <div class="bloque">
        <h3>Identificación</h3>
        <label>Emisor:</label>
        <input type="text" name="emi_name" placeholder="Name" required>
        <input type="text" name="emi_cif" placeholder="CIF" required>
        <input type="text" name="emi_adress" placeholder="Adress" required>
        <input type="text" name="emi_zip_code" placeholder="ZIP" required>
        <input type="text" name="emi_city" placeholder="City" required>
        <input type="text" name="emi_state" placeholder="State" required>
        <input type="text" name="emi_country" placeholder="Country" required>
        <input type="text" name="emi_email" placeholder="Email">
        <input type="text" name="emi_phone" placeholder="Phone">
        <br>
       <label>Receptor:</label>
       <input type="text" name="rec_name" placeholder="Name" required>
       <input type="text" name="rec_cif" placeholder="CIF" required>
       <input type="text" name="rec_adress" placeholder="Adress" required>
       <input type="text" name="rec_zip_code" placeholder="ZIP" required>
       <input type="text" name="rec_city" placeholder="City" required>
       <input type="text" name="rec_state" placeholder="State" required>
       <input type="text" name="rec_country" placeholder="Country" required>
       <input type="text" name="rec_email" placeholder="Email" required>
       <input type="text" name="rec_phone" placeholder="Phone">
<br>
    </div>

    <div class="bloque">
        <h3>Conceptos</h3>
        <div id="lista-c">
             <div class="fila">
        <input type="text" name="desc[]" class="description" placeholder="Description" required>
        <input type="number" name="price[]" class="price" placeholder="Price" min="0.01" step="0.01"required>
        <input type="number" name="quant[]" class="quant" placeholder="Quantity" min="1" required>
    </div>
    </div>
   <button type="button" onclick="addConcepto()">+ Añadir Concepto</button>    </div>

    <div class="bloque">
        <h3>Impuestos / Descuentos</h3>
        <div id="lista-i">
        <div class="fila">
            <input type="text" name="imp_nom[]" placeholder="Nombre (IVA/IRPF)" required>
            <input type="number" name="imp_pct[]" placeholder="%" required>
        </div>
    </div>
    <button type="button" onclick="addImpuesto()">+ Añadir Impuesto</button>
</div>
    <br>
<button type="submit">
    GENERAR FACTURA
</button>    </form>

    <script>


   function addConcepto() {
    var d = document.createElement('div'); 
    d.className = 'fila';

    d.innerHTML = `
        <input type="text" name="desc[]" class="description" placeholder="Description" required>
        <input type="number" name="price[]" class="price" placeholder="Price" min="0.01" step="0.01" required>
        <input type="number" name="quant[]" class="quant" placeholder="Quantity" min="1" required>
    `;

    document.getElementById('lista-c').appendChild(d);
}

        function addImpuesto() {
    var d = document.createElement('div'); 
    d.className = 'fila';
    d.innerHTML = `
        <input type="text" name="imp_nom[]" class="imp_nom" placeholder="Nombre (IVA/IRPF)" required> 
        <input type="number" name="imp_pct[]" class="imp_pct" placeholder="%" required>
    `;
    document.getElementById('lista-i').appendChild(d);
}
</script>
</body>
</html>
