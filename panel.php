<?php
session_start();

// Si NO hay sesión → no se puede entrar
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Panel</title>
</head>
<body style="background:black;color:white;font-family:Arial;padding:20px;">

<h1>Bienvenido, <?php echo $_SESSION['usuario']; ?></h1>

<p>Has iniciado sesión correctamente.</p>

<a href="logout.php" style="color:#00eaff;">Cerrar sesión</a>

</body>
</html>
