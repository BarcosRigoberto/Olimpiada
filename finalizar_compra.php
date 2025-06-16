<?php
session_start();
include 'conexion.php';
$total = 0;
$items = [];

foreach ($_SESSION['carrito'] as $id => $cantidad) {
    $res = $conn->query("SELECT * FROM paquetes WHERE id = $id");
    $p = $res->fetch_assoc();
    $precio = $p['precio'];
    $total += $precio * $cantidad;
    $items[] = ["id" => $id, "cantidad" => $cantidad, "precio" => $precio];
}

$conn->query("INSERT INTO compras (session_id, total) VALUES ('" . session_id() . "', $total)");
$id_compra = $conn->insert_id;

foreach ($items as $i) {
    $conn->query("INSERT INTO compra_items (compra_id, paquete_id, cantidad, precio_unitario) VALUES ($id_compra, {$i['id']}, {$i['cantidad']}, {$i['precio']})");
}

unset($_SESSION['carrito']);
echo "<h2>Compra realizada con éxito</h2><a href='index.php'>Volver al inicio</a>";
?>
