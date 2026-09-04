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

$id_usuario = id_valido($_POST["id_usuario"] ?? null);

if ($id_usuario === 0) {
    establecer_mensaje("error", "El usuario solicitado no es válido.");
    redireccionar("listar.php");
}

if ($id_usuario === (int) $usuario_actual["id_usuario"]) {
    establecer_mensaje("error", "No puede eliminar la cuenta con la que inició sesión.");
    redireccionar("listar.php");
}

if (es_ultimo_administrador_activo($conexion, $id_usuario)) {
    establecer_mensaje("error", "No se puede eliminar el único administrador activo.");
    redireccionar("listar.php");
}

$buscar = $conexion->prepare("SELECT nombre FROM usuarios WHERE id_usuario = ? LIMIT 1");
$buscar->bind_param("i", $id_usuario);
$buscar->execute();
$usuario = $buscar->get_result()->fetch_assoc();
$buscar->close();

if (!$usuario) {
    establecer_mensaje("error", "El usuario ya no existe.");
    redireccionar("listar.php");
}

$eliminar = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
$eliminar->bind_param("i", $id_usuario);

if ($eliminar->execute() && $eliminar->affected_rows === 1) {
    establecer_mensaje("exito", "La cuenta de " . $usuario["nombre"] . " fue eliminada.");
} else {
    error_log("Error al eliminar usuario: " . $eliminar->error);
    establecer_mensaje("error", "No fue posible eliminar el usuario.");
}

$eliminar->close();
$conexion->close();
redireccionar("listar.php");