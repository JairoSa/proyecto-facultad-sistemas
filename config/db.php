<?php
$host = "localhost";
$user = "web_admin"; // El usuario que creaste
$pass = "2521"; // La contraseña que elegiste
$dbname = "facultad_sistemas";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>