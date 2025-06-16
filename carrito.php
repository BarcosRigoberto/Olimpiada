<?php
session_start();
include 'conexion.php';

echo "<h2>Tu carrito</h2>";
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