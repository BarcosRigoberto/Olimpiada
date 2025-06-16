<?php
$host = "localhost";
$usuario = "root";
$contrasena = ""; // cambiar si tenés clave
$bd = "pagviajes";

$conn = new mysqli($host, $usuario, $contrasena, $bd);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>