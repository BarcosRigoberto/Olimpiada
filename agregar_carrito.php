<?php
session_start();
$id = $_GET['id'];
$cantidad = $_GET['cantidad'] ?? 1;

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (isset($_SESSION['carrito'][$id])) {
    $_SESSION['carrito'][$id] += $cantidad;
} else {
    $_SESSION['carrito'][$id] = $cantidad;
}

header("Location: carrito.php");
?>
