<?php

declare(strict_types=1);

require_once __DIR__ . "/configuracion.php";

function iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $conexion_segura = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";

    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "img-src 'self' https: data:; "
        . "style-src 'self'; script-src 'self'; "
        . "form-action 'self'; frame-ancestors 'self'; base-uri 'self'"
    );

    ini_set("session.use_strict_mode", "1");
    ini_set("session.use_only_cookies", "1");
    session_name("nodo_altura_sesion");
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $conexion_segura,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
    session_start();
}

function escapar(?string $valor): string
{
    return htmlspecialchars($valor ?? "", ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function redireccionar(string $ruta): never
{
    header("Location: " . $ruta);
    exit();
}

function establecer_mensaje(string $tipo, string $texto): void
{
    $_SESSION["mensajes"][] = [
        "tipo" => $tipo,
        "texto" => $texto
    ];
}

function mostrar_mensajes(): void
{
    $mensajes = $_SESSION["mensajes"] ?? [];
    unset($_SESSION["mensajes"]);

    foreach ($mensajes as $mensaje) {
        $tipo = in_array($mensaje["tipo"], ["exito", "error", "aviso"], true)
            ? $mensaje["tipo"]
            : "aviso";
        echo '<div class="mensaje ' . escapar($tipo) . '" role="status">'
            . escapar($mensaje["texto"])
            . '</div>';
    }
}

function token_csrf(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function campo_csrf(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . escapar(token_csrf())
        . '">';
}

function csrf_valido(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION["csrf_token"])
        && hash_equals($_SESSION["csrf_token"], $token);
}

function destruir_sesion(): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $parametros = session_get_cookie_params();
        setcookie(session_name(), "", [
            "expires" => time() - 42000,
            "path" => $parametros["path"],
            "domain" => $parametros["domain"],
            "secure" => (bool) $parametros["secure"],
            "httponly" => (bool) $parametros["httponly"],
            "samesite" => "Lax"
        ]);
    }

    session_destroy();
}

function requerir_sesion(string $ruta_login): void
{
    if (!isset($_SESSION["id_usuario"])) {
        establecer_mensaje("error", "Debe iniciar sesión para acceder a esa página.");
        redireccionar($ruta_login);
    }

    $ultima_actividad = (int) ($_SESSION["ultima_actividad"] ?? time());

    if ((time() - $ultima_actividad) > TIEMPO_MAXIMO_SESION) {
        destruir_sesion();
        iniciar_sesion();
        establecer_mensaje("aviso", "La sesión terminó por inactividad. Inicie sesión nuevamente.");
        redireccionar($ruta_login);
    }

    $_SESSION["ultima_actividad"] = time();
}

function sincronizar_usuario_sesion(mysqli $conexion, string $ruta_login): array
{
    $id_usuario = (int) $_SESSION["id_usuario"];
    $sql = "SELECT id_usuario, nombre, usuario, tipo, estado
            FROM usuarios
            WHERE id_usuario = ?
            LIMIT 1";
    $consulta = $conexion->prepare($sql);
    $consulta->bind_param("i", $id_usuario);
    $consulta->execute();
    $resultado = $consulta->get_result();
    $usuario = $resultado->fetch_assoc();
    $consulta->close();

    if (!$usuario || $usuario["estado"] !== "Activo") {
        destruir_sesion();
        iniciar_sesion();
        establecer_mensaje("error", "La cuenta ya no está disponible.");
        redireccionar($ruta_login);
    }

    $_SESSION["nombre"] = $usuario["nombre"];
    $_SESSION["usuario"] = $usuario["usuario"];
    $_SESSION["tipo"] = $usuario["tipo"];

    return $usuario;
}

function requerir_roles(array $roles_permitidos, string $ruta_panel): void
{
    $tipo = $_SESSION["tipo"] ?? "";

    if (!in_array($tipo, $roles_permitidos, true)) {
        establecer_mensaje("error", "No tiene permiso para realizar esa acción.");
        redireccionar($ruta_panel);
    }
}

function nombre_rol(string $tipo): string
{
    $nombres = [
        "administrador" => "Administrador",
        "usuario" => "Usuario",
        "visitante" => "Visitante"
    ];

    return $nombres[$tipo] ?? "Sin rol";
}

function validar_usuario(string $usuario): bool
{
    return preg_match('/^[a-z0-9._-]{4,30}$/', $usuario) === 1;
}

function validar_password(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Za-z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1;
}

function validar_datos_producto(
    string $codigo,
    string $nombre,
    string $categoria,
    string $descripcion,
    string $precio,
    string $cantidad,
    string $imagen,
    string $estado
): array {
    $errores = [];

    if (preg_match('/^[A-Z0-9-]{3,20}$/', $codigo) !== 1) {
        $errores[] = "El código debe tener entre 3 y 20 caracteres y usar solo mayúsculas, números o guiones.";
    }
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $errores[] = "El nombre del producto debe contener entre 3 y 100 caracteres.";
    }
    if (strlen($categoria) < 2 || strlen($categoria) > 60) {
        $errores[] = "La categoría debe contener entre 2 y 60 caracteres.";
    }
    if (strlen($descripcion) < 10 || strlen($descripcion) > 600) {
        $errores[] = "La descripción debe contener entre 10 y 600 caracteres.";
    }
    if (!is_numeric($precio) || (float) $precio <= 0 || (float) $precio > 99999999.99) {
        $errores[] = "Ingrese un precio válido mayor que cero.";
    }

    $cantidad_valida = filter_var($cantidad, FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 0, "max_range" => 999999]
    ]);
    if ($cantidad_valida === false) {
        $errores[] = "La cantidad debe ser un número entero entre 0 y 999999.";
    }
    if (!validar_imagen_producto($imagen)) {
        $errores[] = "La imagen debe ser una URL http/https o una ruta local dentro de assets/img/productos.";
    }
    if (!in_array($estado, ["Activo", "Inactivo"], true)) {
        $errores[] = "Seleccione un estado válido.";
    }

    return $errores;
}

function validar_imagen_producto(string $imagen): bool
{
    if ($imagen === "") {
        return true;
    }

    if (str_contains($imagen, "..")) {
        return false;
    }

    if (preg_match('/^assets\/img\/productos\/[a-zA-Z0-9._\/-]+$/', $imagen) === 1) {
        return true;
    }

    if (!filter_var($imagen, FILTER_VALIDATE_URL)) {
        return false;
    }

    $esquema = strtolower((string) parse_url($imagen, PHP_URL_SCHEME));
    return in_array($esquema, ["http", "https"], true);
}

function ruta_imagen_producto(?string $imagen, string $prefijo = ""): string
{
    $imagen = $imagen ?: "assets/img/productos/sin-imagen.svg";

    if (preg_match('/^https?:\/\//i', $imagen) === 1) {
        return $imagen;
    }

    return $prefijo . ltrim($imagen, "/");
}

function id_valido(mixed $valor): int
{
    $id = filter_var($valor, FILTER_VALIDATE_INT, [
        "options" => ["min_range" => 1]
    ]);

    return $id === false ? 0 : (int) $id;
}

function es_ultimo_administrador_activo(mysqli $conexion, int $id_usuario): bool
{
    $sql_usuario = "SELECT tipo, estado FROM usuarios WHERE id_usuario = ? LIMIT 1";
    $consulta_usuario = $conexion->prepare($sql_usuario);
    $consulta_usuario->bind_param("i", $id_usuario);
    $consulta_usuario->execute();
    $usuario = $consulta_usuario->get_result()->fetch_assoc();
    $consulta_usuario->close();

    if (!$usuario || $usuario["tipo"] !== "administrador" || $usuario["estado"] !== "Activo") {
        return false;
    }

    $resultado = $conexion->query(
        "SELECT COUNT(*) AS total
         FROM usuarios
         WHERE tipo = 'administrador' AND estado = 'Activo'"
    );
    $total = (int) $resultado->fetch_assoc()["total"];

    return $total <= 1;
}
