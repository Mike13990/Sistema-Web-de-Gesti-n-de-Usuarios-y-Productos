<?php

declare(strict_types=1);

require_once __DIR__ . "/funciones.php";

iniciar_sesion();

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido($_POST["csrf_token"] ?? null)) {
    establecer_mensaje("error", "La solicitud para cerrar sesión no es válida.");
    redireccionar("index.php");
}

destruir_sesion();
redireccionar("index.php");