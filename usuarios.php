<?php
header("Content-Type: application/json; charset=utf-8");
include "conexion.php";

$accion = $_POST["accion"] ?? $_GET["accion"] ?? "";

if ($accion == "listar") {
    $busqueda = $_POST["busqueda"] ?? "";
    $orden = $_POST["orden"] ?? "ASC";
    $pagina = isset($_POST["pagina"]) ? intval($_POST["pagina"]) : 1;
    $limite = isset($_POST["limite"]) ? intval($_POST["limite"]) : 5;

    if ($orden != "ASC" && $orden != "DESC") {
        $orden = "ASC";
    }

    $inicio = ($pagina - 1) * $limite;
    $busqueda = $conexion->real_escape_string($busqueda);

    $sqlTotal = "SELECT COUNT(*) AS total FROM usuarios
                 WHERE nombre LIKE '%$busqueda%'
                 OR correo LIKE '%$busqueda%'
                 OR telefono LIKE '%$busqueda%'
                 OR ciudad LIKE '%$busqueda%'";

    $resultadoTotal = $conexion->query($sqlTotal);
    $total = $resultadoTotal->fetch_assoc()["total"];

    $sql = "SELECT * FROM usuarios
            WHERE nombre LIKE '%$busqueda%'
            OR correo LIKE '%$busqueda%'
            OR telefono LIKE '%$busqueda%'
            OR ciudad LIKE '%$busqueda%'
            ORDER BY nombre $orden
            LIMIT $inicio, $limite";

    $resultado = $conexion->query($sql);
    $usuarios = [];

    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }

    echo json_encode([
        "total" => $total,
        "usuarios" => $usuarios
    ]);
    exit;
}

if ($accion == "agregar" || $accion == "editar") {
    $id = intval($_POST["id"] ?? 0);
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $ciudad = trim($_POST["ciudad"] ?? "");

    if (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,}$/", $nombre)) {
        echo json_encode(["respuesta" => "error", "mensaje" => "Nombre inválido"]);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["respuesta" => "error", "mensaje" => "Correo inválido"]);
        exit;
    }

    if (!preg_match("/^\d{10}$/", $telefono)) {
        echo json_encode(["respuesta" => "error", "mensaje" => "Teléfono inválido"]);
        exit;
    }

    if ($ciudad == "") {
        echo json_encode(["respuesta" => "error", "mensaje" => "Ciudad obligatoria"]);
        exit;
    }

    $nombre = $conexion->real_escape_string($nombre);
    $correo = $conexion->real_escape_string($correo);
    $telefono = $conexion->real_escape_string($telefono);
    $ciudad = $conexion->real_escape_string($ciudad);

    if ($accion == "agregar") {
        $sql = "INSERT INTO usuarios (nombre, correo, telefono, ciudad)
                VALUES ('$nombre', '$correo', '$telefono', '$ciudad')";
    } else {
        $sql = "UPDATE usuarios SET
                nombre='$nombre',
                correo='$correo',
                telefono='$telefono',
                ciudad='$ciudad'
                WHERE id=$id";
    }

    if ($conexion->query($sql)) {
        echo json_encode(["respuesta" => "ok"]);
    } else {
        echo json_encode(["respuesta" => "error", "mensaje" => $conexion->error]);
    }
    exit;
}

if ($accion == "eliminar") {
    $id = intval($_POST["id"] ?? 0);

    $sql = "DELETE FROM usuarios WHERE id=$id";

    if ($conexion->query($sql)) {
        echo json_encode(["respuesta" => "ok"]);
    } else {
        echo json_encode(["respuesta" => "error", "mensaje" => $conexion->error]);
    }
    exit;
}

echo json_encode(["respuesta" => "error", "mensaje" => "Acción no válida"]);
?>