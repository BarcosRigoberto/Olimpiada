<?php
include("conexion.php");

//Variables para cargar datos
$idV= -1;
$nombreV = "";
$apellidoV = "";
$emailV = "";
$contraseñaV = "";


if (isset($_POST['fid_usuario'])) {

	$sqlPHP = "SELECT * FROM usuario WHERE id = ". $_POST['fid_usuario'];

	$queryPHP = mysqli_query($conn, $sqlPHP) or die (mysqli_error($conn)." SQL: ".$sqlPHP);
	if ($row = mysqli_fetch_array($queryPHP)) {
		$idV= $row["id"];
		$nombreV = $row["nombre"];
		$apellidoV = $row["apellido"];
		$emailV = $row["email"];
		$contraseñaV = $row["contraseña"];


	}
	mysqli_close($conn);
}






?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Datos del Usuario</title>
</head>
<body>
	<h1>Usuario</h1>
	<form action="usuario_guardar.php" method="POST">
		  <input type="hidden" id="id" name="id" value="<?php echo $idV?> ">	
		  <label for="nombre">Nombre:</label><br>
  		  <input type="text" id="nombre" name="nombre" value="<?php echo $nombreV?>"><br>
  		  <label for="apellido">Apellido:</label><br>
  		  <input type="text" id="apellido" name="apellido" value="<?php echo $apellidoV?>"><br>
  		  <label for="email">Email:</label><br>
  		  <input type="text" id="email" name="email" value="<?php echo $emailV?>"><br><br>
  		  <label for="contraseña">contraseña:</label><br>
  		  <input type="text" id="contraseña" name="contraseña" value="<?php echo $contraseñaV?>"><br><br>
  		  <input type="submit" value="Enviar">
	</form> 


</body>
</html>