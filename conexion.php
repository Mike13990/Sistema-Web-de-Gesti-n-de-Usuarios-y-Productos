<?php

declare(strict_types=1);

define("APP_NOMBRE", "ConectaPro");
define("APP_ESLOGAN", "Tecnología útil para ideas en movimiento.");
define("TIEMPO_MAXIMO_SESION", 1800);

mysqli_report(MYSQLI_REPORT_OFF);

$servidor = "127.0.0.1";
$usuario_bd = "root";
$password_bd = "";
$base_datos = "sistema_usuarios";

$conexion = new mysqli(
    $servidor,
    $usuario_bd,
    $password_bd,
    $base_datos,
    3306
);

if ($conexion->connect_error) {
    error_log("Error MySQL: " . $conexion->connect_error);
    http_response_code(500);
    die("No fue posible conectar con la base de datos.");
}

if (!$conexion->set_charset("utf8mb4")) {
    error_log("Error de codificación: " . $conexion->error);
    http_response_code(500);
    die("No fue posible configurar la conexión.");
}