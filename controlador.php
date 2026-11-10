<?php
//El dir es para estar seguro buscandolo
require_once __DIR__ . '/productoFisico.php';
require_once __DIR__ .'/productoDigital.php';

//comprobar si alguien entra directamente a comprobar
   if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
        http_response_code(405);
        exit('Abre el indice y pulsa a comprar');
   }

   $datosProductos = $_POST['productos'] ?? null;
   if (!is_string($datosProductos) || trim($datosProductos) === '') {
        http_response_code(400);
        exit('El carrito esta vacio. <a href="Index.html">Volver al carrito</a>');
   }

//mirar por si acaso cliente
    $cliente = $_POST['cliente'] ?? 'Cliente Demo';
    if (!is_string($cliente)){
        $cliente = 'Cliente Demo';
    }

//Datos
    $lineasFactura = [];
    $subtotal = 0.0;
    $pesoTotal = 0.0;
    $hayProductosFisicos = false;

//El comprobar errores los errores
    try {
        //Separo por "|" y por ":"
        $productos = explode('|', $datosProductos);

        foreach ($productos as $textoProducto) {
            $campos = explode(':', $textoProducto);
            if (count($campos) !== 6) {
                throw new InvalidArgumentException('Un producto sin los seis valores');
            }
            //Saca los datos del campo
            [$tipo, $sku, $nombre, $precioTexto, $cantidadTexto, $extra] = $campos;
                //limpio por si acaso
                $tipo = strtoupper(trim($tipo));
                $sku = trim($sku);
                $nombre = trim($nombre);
                $precioTexto = trim($precioTexto);
                $cantidadTexto = trim($cantidadTexto);
                $extra = trim($extra);

                //Comprobar valores para que no sean negativos
                if ($tipo !== 'F' && $tipo !== 'D') 
                    {
                        throw new InvalidArgumentException('El tipo debe ser F o D.');
                    }
                if ($sku === '') 
                    {
                        throw new InvalidArgumentException('El SKU no puede estar vacio.');
                    }
                if ($nombre === '') 
                    {
                        throw new InvalidArgumentException('El nombre no puede estar vacio.');
                    }
                if  (!is_numeric($precioTexto) || !is_finite((float) $precioTexto) || (float) $precioTexto < 0) 
                    {
                        throw new InvalidArgumentException('El precio debe ser un numero no negativo.');
                    }
                    $precio = (float) $precioTexto;
                //Validar que es positivo la cantidad
                $cantidad = filter_var($cantidadTexto , FILTER_VALIDATE_INT);
                    if ($cantidad === false || $cantidad <= 0 ){
                        throw new InvalidArgumentException('El valor no puede ser negativo');
                    }
                    //crea un objeto segun el tipo segun F y D 
                if  ($tipo === 'F')
                {
                    $pesoTexto = preg_replace('/\s*kg\s*$/i', '', $extra);
                    $pesoTexto = str_replace(',', '.', $pesoTexto);
                    if ( !is_numeric($pesoTexto) || !is_finite((float) $pesoTexto) || (float) $pesoTexto < 0 )
                    {
                      throw new InvalidArgumentException('El peso debe ser un numero no negativo y puede incluir la unidad kg.');
                    }
                    $peso = (float) $pesoTexto;
                    $producto = new ProductoFisico($sku, $nombre, $precio, 'F', $peso);
                    $pesoTotal += $peso * $cantidad;
                    $hayProductosFisicos = true;
                    $detalleExtra = 'Fisico - peso: ' . $peso . ' kg';
                }   else {
                    if ($extra === '') {
                        throw new InvalidArgumentException('La licencia no puede estar vacia.');
                    }
                    $producto = new productoDigital($sku, $nombre, $precio, 'D', $extra);
                    $detalleExtra = 'Digital - licencia: ' . $extra;
                }
                //guardar el importe y guardar los datos para dibujarlo para después
                $importe = $precio * $cantidad;
                $subtotal += $importe;
                $lineasFactura[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'extra' => $detalleExtra,
                    'importe' => $importe
                ];
        }
        // Comprobar si se ha añadido alguna linea.
        if (count($lineasFactura) === 0) {
            throw new InvalidArgumentException('El carrito no tiene productos.');
        }
        // Formulas del ejercicio.
        if ($hayProductosFisicos) {
            $envio = 5 + (1.5 * $pesoTotal);
        } else {
            $envio = 0.0;
        }
        $iva = $subtotal * 0.21;
        $total = $subtotal + $iva + $envio;

    } catch (InvalidArgumentException $e) {

        http_response_code(400);
        echo '<h1>No se pudo procesar el pedido</h1>';
        echo '<p>' . htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        ) . '</p>';
        echo '<p><a href="Index.html">Volver al inicio</a></p>';
        exit;
    }
    

    //Mostrar texto sin interpretar como html 
    function escapar(string $texto): string
        {
            return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        }
?>
<!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Factura ElectroExpress</title>
    </head>
    <body>
        <h1>Factura ElectroExpress</h1>
        <p>Cliente: <?= escapar($cliente) ?></p>
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th>Tipo / extra</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lineasFactura as $linea): ?>
                <?php $producto = $linea['producto']; ?>
                    <tr>
                        <td><?= escapar($producto->getSKU()) ?></td>
                        <td><?= escapar($producto->getNombre()) ?></td>
                        <td><?= escapar($linea['extra']) ?></td>
                        <td><?= $linea['cantidad'] ?></td>
                        <td><?= number_format($producto->getPrecio(), 2, ',', '.') ?> EUR</td>
                        <td><?= number_format($linea['importe'], 2, ',', '.') ?> EUR</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <section class="summary">
            <p>Peso total de productos fisicos: <?= number_format($pesoTotal, 2, ',', '.') ?> kg</p>
            <p>Subtotal: <?= number_format($subtotal, 2, ',', '.') ?> EUR</p>
            <p>Gastos de envio: <?= number_format($envio, 2, ',', '.') ?> EUR</p>
            <p>IVA (21 %): <?= number_format($iva, 2, ',', '.') ?> EUR</p>
            <h2>Total: <?= number_format($total, 2, ',', '.') ?> EUR</h2>
        </section>
        <button type="button" onclick="window.print()">Imprimir factura</button>
        <a href="Index.html">Volver</a>
    </body>
    </html>
