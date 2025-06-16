<?php
include 'conexion.php';
$resultado = $conn->query("SELECT * FROM paquetes");
?>
<h2>Paquetes disponibles</h2>
<?php while($p = $resultado->fetch_assoc()): ?>
  <div style="border:1px solid #ccc; margin:10px; padding:10px;">
    <h3><?= $p['nombre'] ?></h3>
    <p><?= $p['descripcion'] ?></p>
    <p><strong>Precio:</strong> $<?= $p['precio'] ?></p>
    <a href="agregar_carrito.php?id=<?= $p['id'] ?>">Agregar al carrito</a>
  </div>
<?php endwhile; ?>
