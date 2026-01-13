<?php
$host = "127.0.0.1";
$user = "root"; // Usuario por defecto de Laragon
$pass = "";     // Contraseña por defecto de Laragon (vacía)
$db   = "retrogroove_db";

try {
    $conexion = new mysqli($host, $user, $pass, $db);
    $conexion->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}