<link rel="stylesheet" type="text/css" href="indstyle.css">
<?php
session_start();
require_once 'header.php';

include 'conexion.php';
?>
<div class="carrito">
<h2>Tu carrito</h2>
<?php
$total = 0;

if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $res = $conn->query("SELECT * FROM paquetes WHERE id = $id");
        $row = $res->fetch_assoc();
        $subtotal = $row['precio'] * $cantidad;
        $total += $subtotal;

        echo "{$row['nombre']} - $cantidad x {$row['precio']} = $subtotal <br>";
    }
    echo "<h3>Total: $$total</h3>";
    echo "<a href='finalizar_compra.php'>Finalizar compra</a>";
} else {
    echo "Tu carrito está vacío.";
}
?>
</div>

<style>
    .carrito {
    margin-top:5px;
    text-align: center;
     max-width: 450px;
    margin: 100px auto;
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    font-family: 'Poppins', sans-serif;
}
</style>