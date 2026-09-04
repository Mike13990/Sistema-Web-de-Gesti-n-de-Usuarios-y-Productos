<?php

declare(strict_types=1);

require_once __DIR__ . "/configuracion.php";

mysqli_report(MYSQLI_REPORT_OFF);

$conexion = new mysqli(
    $servidor,
    $usuario_bd,
    $password_bd,
    $base_datos,
    $puerto_bd
);

if ($conexion->connect_errno) {
    error_log("Error MySQL: " . $conexion->connect_error);
    http_response_code(500);

    die(
        "No fue posible conectar con la base de datos. " .
        "Revise la configuración del proyecto."
    );
}

if (!$conexion->set_charset("utf8mb4")) {
    error_log(
        "No fue posible configurar utf8mb4: " .
        $conexion->error
    );

    http_response_code(500);
    die("No fue posible preparar la conexión con la base de datos.");
}