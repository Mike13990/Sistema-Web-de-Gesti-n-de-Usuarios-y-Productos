function procesar_imagen_producto(
    array $archivo,
    string $url,
    string $imagen_actual = ""
): array {
    $url = trim($url);
    $error = (int) ($archivo["error"] ?? UPLOAD_ERR_NO_FILE);

    if ($url !== "" && $error !== UPLOAD_ERR_NO_FILE) {
        return [
            "imagen" => $imagen_actual,
            "error" => "Use un archivo o una URL, no ambos."
        ];
    }

    if ($url !== "") {
        if (
            !filter_var($url, FILTER_VALIDATE_URL)
            || parse_url($url, PHP_URL_SCHEME) !== "https"
        ) {
            return [
                "imagen" => $imagen_actual,
                "error" => "La URL debe comenzar con https://."
            ];
        }

        return [
            "imagen" => $url,
            "error" => null
        ];
    }

    if ($error === UPLOAD_ERR_NO_FILE) {
        return [
            "imagen" => $imagen_actual,
            "error" => null
        ];
    }

    if ($error !== UPLOAD_ERR_OK) {
        return [
            "imagen" => $imagen_actual,
            "error" => "Error al recibir el archivo."
        ];
    }

    $temporal = $archivo["tmp_name"];
    $tamano = (int) $archivo["size"];

    if ($tamano <= 0 || $tamano > 5 * 1024 * 1024) {
        return [
            "imagen" => $imagen_actual,
            "error" => "El PNG no puede superar los 5 MB."
        ];
    }

    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($temporal);

    if ($tipo !== "image/png") {
        return [
            "imagen" => $imagen_actual,
            "error" => "El archivo debe ser una imagen PNG."
        ];
    }

    $carpeta = __DIR__ . "/assets/img/productos/subidos";

    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }

    $nombre = bin2hex(random_bytes(8)) . ".png";
    $destino = $carpeta . "/" . $nombre;

    if (!move_uploaded_file($temporal, $destino)) {
        return [
            "imagen" => $imagen_actual,
            "error" => "No fue posible guardar la imagen."
        ];
    }

    return [
        "imagen" => "assets/img/productos/subidos/" . $nombre,
        "error" => null
    ];
}