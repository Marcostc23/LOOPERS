<?php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');

try {
    $conexion = new mysqli($host, $user, $pass, $db, $port);
    $conexion->set_charset(charset: "utf8mb4");
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}