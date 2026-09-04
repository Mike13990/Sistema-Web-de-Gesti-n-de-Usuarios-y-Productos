<?php

declare(strict_types=1);

require_once __DIR__ . "/../funciones.php";

iniciar_sesion();
requerir_sesion("../login.php");

require_once __DIR__ . "/../conexion.php";
$usuario_actual = sincronizar_usuario_sesion($conexion, "../login.php");
requerir_roles(["administrador"], "../panel.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    establecer_mensaje("error", "La eliminación solo se acepta desde el formulario correspondiente.");
    redireccionar("listar.php");
}

if (!csrf_valido($_POST["csrf_token"] ?? null)) {
    establecer_mensaje("error", "La solicitud de eliminación no es válida.");
    redireccionar("listar.php");
}

$id_producto = id_valido($_POST["id_producto"] ?? null);

if ($id_producto === 0) {
    establecer_mensaje("error", "El producto solicitado no es válido.");
    redireccionar("listar.php");
}

$buscar = $conexion->prepare("SELECT nombre FROM productos WHERE id_producto = ? LIMIT 1");
$buscar->bind_param("i", $id_producto);
$buscar->execute();
$producto = $buscar->get_result()->fetch_assoc();
$buscar->close();

if (!$producto) {
    establecer_mensaje("error", "El producto ya no existe.");
    redireccionar("listar.php");
}

$eliminar = $conexion->prepare("DELETE FROM productos WHERE id_producto = ?");
$eliminar->bind_param("i", $id_producto);

if ($eliminar->execute() && $eliminar->affected_rows === 1) {
    establecer_mensaje("exito", "El producto " . $producto["nombre"] . " fue eliminado.");
} else {
    error_log("Error al eliminar producto: " . $eliminar->error);
    establecer_mensaje("error", "No fue posible eliminar el producto.");
}

$eliminar->close();
$conexion->close();
redireccionar("listar.php");

