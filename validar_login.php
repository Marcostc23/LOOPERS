<?php
session_start();
require "conexion.php";

// Comprobar que se reciben datos del formulario
if (!isset($_POST['usuario']) || !isset($_POST['password'])) {
    die("No se han enviado los datos del formulario.");
}

$usuario = $_POST['usuario'];
$password = $_POST['password'];

// Consulta
$sql = "SELECT * FROM usuarios 
        WHERE usuario = '$usuario'
        AND password = '$password'";


$resultado = $conexion->query($sql);

if ($resultado->num_rows == 1) {
    $_SESSION['usuario'] = $usuario;
    header("Location: panel.php");
    exit();
} else {
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>Usuario o contraseña incorrectos</h2>";
    echo "<p style='text-align:center;'><a href='login.php'>Volver al login</a></p>";
}
?>
