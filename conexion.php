<?php

$host = "sql100.infinityfree.com";
$usuario = "if0_39313594";
$contrasena = "GGLMP2025";
$bd = "if0_39313594_pagviajes";

$mysqli = new mysqli($host, $usuario, $contrasena, $bd);
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8")
?>