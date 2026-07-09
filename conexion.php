<?php

$databaseUrl = getenv("DATABASE_URL");

if (!$databaseUrl) {
    die("Error: No se encontró la variable DATABASE_URL.");
}

$conexion = pg_connect($databaseUrl);

if (!$conexion) {
    die("Error de conexión con PostgreSQL.");
}

pg_query($conexion, "
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    ciudad VARCHAR(100) NOT NULL
);
");

$resultado = pg_query($conexion, "SELECT COUNT(*) AS total FROM usuarios");
$total = pg_fetch_assoc($resultado)["total"];

if ($total == 0) {
    pg_query($conexion, "
    INSERT INTO usuarios (nombre, correo, telefono, ciudad) VALUES
    ('Jorge Pérez','jorge@gmail.com','3312345678','Guadalajara'),
    ('Ana López','ana@gmail.com','3311111111','Zapopan'),
    ('Carlos Ramírez','carlos@gmail.com','3322222222','Tlaquepaque'),
    ('María Torres','maria@gmail.com','3333333333','Tonalá'),
    ('Luis Hernández','luis@gmail.com','3344444444','Guadalajara');
    ");
}

?>
