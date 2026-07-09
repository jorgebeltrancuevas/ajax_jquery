<?php

$servidor = getenv("MYSQLHOST");
$usuario = getenv("MYSQLUSER");
$password = getenv("MYSQLPASSWORD");
$baseDatos = getenv("MYSQLDATABASE");
$puerto = getenv("MYSQLPORT");

$conexion = new mysqli($servidor, $usuario, $password, $baseDatos, $puerto);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");

?>