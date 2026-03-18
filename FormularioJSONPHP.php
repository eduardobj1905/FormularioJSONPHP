<?php
$mostrarPreview = false;
if($_SERVER['REQUEST_METHOD']==='POST'){
    $mostrarPreview = true;
    $uuid = rand(1,100);
    $fechaEmision = date('d/m/Y');
    $fechaFinCobro = date('d/m/Y',strtotime('+1 month'));
    // Recogemos la posición del QR
    $posicionQR = $_POST['qr'] ?? 'left';

    //Recogemos los datos del emisor y del receptor
    $emisor = [
        "nombre_emisor" => $_POST['emi_name'] ?? '',
        "cif_emisor" => $_POST['emi_cif'] ?? '',
        "direccion_emisor" => $_POST['emi_adress'] ?? '',
        "zip_emisor" => $_POST['emi_zip_code'] ?? '',
        "ciudad_emisor" => $_POST['emi_city'] ?? '',
        "estado_emisor" => $_POST['emi_state'] ?? '',
        "pais_emisor" => $_POST['emi_country'] ?? '',
        "email_emisor" => $_POST['emi_email'] ?? '',
        "telefono_emisor" => $_POST['emi_phone'] ?? ''
    ];

    $receptor = [
        "nombre_receptor" => $_POST['rec_name'] ?? '',
        "cif_receptor" => $_POST['rec_cif'] ?? '',
        "direccion_receptor" => $_POST['rec_adress'] ?? '',
        "zip_receptor" => $_POST['rec_zip_code'] ?? '',
        "ciudad_receptor" => $_POST['rec_city'] ?? '',
        "estado_receptor" => $_POST['rec_state'] ?? '',
        "pais_receptor" => $_POST['rec_country'] ?? '',
        "email_receptor" => $_POST['rec_email'] ?? '',
        "telefono_receptor" => $_POST['rec_phone'] ?? ''
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
                "nombre_impuesto" => $nom,
                "porcentaje" => $pct,
                "cantidadCalculada" => number_format($importe, 2, '.', '')
            ];
        }
    }
    $totalFinal = $totalSinImpuestos + $totalImpuestos;
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
                "totalFinal" => number_format($totalFinal, 2, '.', '')
            ],
            "fecha_emision" => $fechaEmision,
            "fecha_fin_cobro" => $fechaFinCobro,
            "estado_factura" => rand(1, 5)
        ]
    ];
    //Introducido
    $facturaHash = md5(json_encode($factura) . $uuid);

$json_data = json_encode($factura, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $filename = "factura_" . $uuid . "_" . ($receptor['nombre_receptor']) . ".json";

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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php if (!$mostrarPreview): ?>

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
<h3>Localizacion QR</h3>
<div class="fila">
                <label><input type="radio" name="qr" value="left" checked> Izquierda</label>
                <label><input type="radio" name="qr" value="center"> Centro</label>
                <label><input type="radio" name="qr" value="right"> Derecha</label>
            </div>
    <br>
<button type="submit">
    GENERAR FACTURA
</button>    
</form>
   <?php else: ?>
    <div class="preview-factura"> 
    <div class="qr-container qr-<?php echo $posicionQR; ?>">
        <div style="margin-bottom: 5px;font-weight: bold;">
           ID FACTURA: #<?php echo $uuid; ?>
        </div>
        <img src="03243d254b92656af15137a7dd0bd76a.png" alt="QR" style="width: 100px; height: auto; border: 1px solid #eee; padding: 5px;">
    </div>

        <div class="datos-cabecera">
            <div class="info-caja">
                <strong>EMISOR</strong><br>
                <span style="font-size: 1.1em; font-weight: bold;"><?php echo $emisor['nombre_emisor']; ?></span><br>
                CIF: <?php echo $emisor['cif_emisor']; ?><br>
                <?php echo $emisor['direccion_emisor']; ?><br>
                <?php echo $emisor['zip_emisor'] . " " . $emisor['ciudad_emisor']; ?><br>
                <?php echo $emisor['estado_emisor'] . ", " . $emisor['pais_emisor']; ?><br>
                <?php if($emisor['email_emisor']) echo "Email: " . $emisor['email_emisor'] . "<br>"; ?>
                <?php if($emisor['telefono_emisor']) echo "Tel: " . $emisor['telefono_emisor']; ?>
            </div>
            
            <div class="info-caja" style="text-align: right;">
                <strong>RECEPTOR</strong><br>
                <span style="font-size: 1.1em; font-weight: bold;"><?php echo $receptor['nombre_receptor']; ?></span><br>
                CIF: <?php echo $receptor['cif_receptor']; ?><br>
                <?php echo $receptor['direccion_receptor']; ?><br>
                <?php echo $receptor['zip_receptor'] . " " . $receptor['ciudad_receptor']; ?><br>
                <?php echo $receptor['estado_receptor'] . ", " . $receptor['pais_receptor']; ?><br>
                <?php echo "Email: " . $receptor['email_receptor']; ?><br>
                <?php if($receptor['telefono_receptor']) echo "Tel: " . $receptor['telefono_receptor']; ?>
            </div>
        </div>
        <div style="width: 100%; margin-bottom: 20px; border-top: 1px solid #eee; padding-top: 10px; display: flex; justify-content: space-between; font-size: 0.9em;">
    <div>
        <strong>Fecha de Emisión:</strong> <?php echo $fechaEmision; ?>
    </div>
    <div style="color: #207ac4; font-weight: bold;">
        <strong>Fecha Límite de Pago:</strong> <?php echo $fechaFinCobro; ?>
    </div>
</div>

        <table class="tabla-preview">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th style="text-align: center;">Cant.</th>
                    <th style="text-align: right;">Precio</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($conceptos as $c): ?>
                <tr>
                    <td><?php echo $c['descripcion']; ?></td>
                    <td style="text-align: center;"><?php echo $c['cantidad']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($c['precio'], 2); ?>€</td>
                    <td style="text-align: right; font-weight: bold;"><?php echo $c['subtotal']; ?>€</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totales-caja">
            <p>Subtotal base: <?php echo number_format($totalSinImpuestos, 2); ?>€</p>
            <p>Impuestos totales: <?php echo number_format($totalImpuestos, 2); ?>€</p>
            <h2>TOTAL FACTURA: <?php echo number_format($totalFinal, 2); ?>€</h2>
        </div>

        <div class="hash-discreto">
            HASH SEGURIDAD: <?php echo $facturaHash; ?>
    </div>
    
    <br>
    <button onclick="window.location.href=window.location.href" style="padding: 10px 20px">
        ← Crear otra factura
    </button>
    <?php endif; ?>

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
