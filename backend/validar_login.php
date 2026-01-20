<?php
session_start();
// Cambiado: Ahora busca el archivo en la misma carpeta, no en 'includes'
require "conexion.php"; 

if (!isset($_POST['usuario']) || !isset($_POST['password'])) {
    die("No se han enviado los datos del formulario.");
}

$usuario = $_POST['usuario'];
$password = $_POST['password'];

// Usamos una consulta preparada para mayor seguridad
$stmt = $conexion->prepare("SELECT nombre, rol FROM usuarios WHERE usuario = ? AND password = ?");
$stmt->bind_param("ss", $usuario, $password);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 1) {
    $datos_usuario = $resultado->fetch_assoc();
    
    $_SESSION['usuario'] = $usuario;
    $_SESSION['nombre']  = $datos_usuario['nombre'];
    $_SESSION['rol']     = $datos_usuario['rol'];

    // Asegúrate de que el archivo de destino se llame exactamente así o cámbialo aquí:
    header("Location: panel.php"); 
    exit();
} else {
    echo "<h2 style='color:red;text-align:center;margin-top:50px;font-family:sans-serif;'>Usuario o contraseña incorrectos</h2>";
    echo "<p style='text-align:center;'><a href='../frontend/index.html'>Volver al login</a></p>";
}
?>