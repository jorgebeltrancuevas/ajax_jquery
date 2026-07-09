<?php
header("Content-Type: application/json; charset=utf-8");
include "conexion.php";

$accion = $_POST["accion"] ?? $_GET["accion"] ?? "";

if ($accion == "listar") {
    $busqueda = "%" . ($_POST["busqueda"] ?? "") . "%";
    $orden = $_POST["orden"] ?? "ASC";
    $pagina = isset($_POST["pagina"]) ? intval($_POST["pagina"]) : 1;
    $limite = isset($_POST["limite"]) ? intval($_POST["limite"]) : 5;

    if ($orden != "ASC" && $orden != "DESC") {
        $orden = "ASC";
    }

    $inicio = ($pagina - 1) * $limite;

    $sqlTotal = "SELECT COUNT(*) AS total FROM usuarios
                 WHERE nombre ILIKE $1
                 OR correo ILIKE $1
                 OR telefono ILIKE $1
                 OR ciudad ILIKE $1";

    $resultadoTotal = pg_query_params($conexion, $sqlTotal, [$busqueda]);
    $total = pg_fetch_assoc($resultadoTotal)["total"];

    $sql = "SELECT * FROM usuarios
            WHERE nombre ILIKE $1
            OR correo ILIKE $1
            OR telefono ILIKE $1
            OR ciudad ILIKE $1
            ORDER BY nombre $orden
            LIMIT $2 OFFSET $3";

    $resultado = pg_query_params($conexion, $sql, [$busqueda, $limite, $inicio]);

    $usuarios = [];

    while ($fila = pg_fetch_assoc($resultado)) {
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

    if ($accion == "agregar") {
        $sql = "INSERT INTO usuarios (nombre, correo, telefono, ciudad)
                VALUES ($1, $2, $3, $4)";
        $resultado = pg_query_params($conexion, $sql, [$nombre, $correo, $telefono, $ciudad]);
    } else {
        $sql = "UPDATE usuarios SET
                nombre=$1,
                correo=$2,
                telefono=$3,
                ciudad=$4
                WHERE id=$5";
        $resultado = pg_query_params($conexion, $sql, [$nombre, $correo, $telefono, $ciudad, $id]);
    }

    if ($resultado) {
        echo json_encode(["respuesta" => "ok"]);
    } else {
        echo json_encode(["respuesta" => "error", "mensaje" => pg_last_error($conexion)]);
    }
    exit;
}

if ($accion == "eliminar") {
    $id = intval($_POST["id"] ?? 0);

    $sql = "DELETE FROM usuarios WHERE id=$1";
    $resultado = pg_query_params($conexion, $sql, [$id]);

    if ($resultado) {
        echo json_encode(["respuesta" => "ok"]);
    } else {
        echo json_encode(["respuesta" => "error", "mensaje" => pg_last_error($conexion)]);
    }
    exit;
}

echo json_encode(["respuesta" => "error", "mensaje" => "Acción no válida"]);
?>
