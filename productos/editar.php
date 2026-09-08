<?php

declare(strict_types=1);

require_once __DIR__ . "/../funciones.php";

iniciar_sesion();
requerir_sesion("../login.php");

require_once __DIR__ . "/../conexion.php";

$usuario_actual = sincronizar_usuario_sesion(
    $conexion,
    "../login.php"
);

requerir_roles(
    ["administrador"],
    "../panel.php"
);

$id_producto = id_valido(
    $_POST["id_producto"] ?? $_GET["id"] ?? null
);

if ($id_producto === 0) {
    establecer_mensaje(
        "error",
        "El producto solicitado no es válido."
    );

    redireccionar("listar.php");
}

$buscar = $conexion->prepare(
    "SELECT
        id_producto,
        codigo,
        nombre,
        categoria,
        descripcion,
        precio,
        cantidad,
        imagen,
        estado
     FROM productos
     WHERE id_producto = ?
     LIMIT 1"
);

$buscar->bind_param(
    "i",
    $id_producto
);

$buscar->execute();

$producto = $buscar
    ->get_result()
    ->fetch_assoc();

$buscar->close();

if (!$producto) {
    establecer_mensaje(
        "error",
        "El producto ya no existe."
    );

    redireccionar("listar.php");
}

$codigo = $producto["codigo"];
$nombre = $producto["nombre"];
$categoria = $producto["categoria"];
$descripcion = $producto["descripcion"];
$precio = $producto["precio"];
$cantidad = (string) $producto["cantidad"];
$imagen_actual = (string) ($producto["imagen"] ?? "");
$imagen = $imagen_actual;
$imagen_url = "";
$estado = $producto["estado"];
$errores = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo = strtoupper(
        trim($_POST["codigo"] ?? "")
    );

    $nombre = trim(
        $_POST["nombre"] ?? ""
    );

    $categoria = trim(
        $_POST["categoria"] ?? ""
    );

    $descripcion = trim(
        $_POST["descripcion"] ?? ""
    );

    $precio = trim(
        $_POST["precio"] ?? ""
    );

    $cantidad = trim(
        $_POST["cantidad"] ?? ""
    );

    $imagen_url = trim(
        $_POST["imagen_url"] ?? ""
    );

    $estado = $_POST["estado"] ?? "";

    if (!csrf_valido($_POST["csrf_token"] ?? null)) {
        $errores[] = "La solicitud no es válida. Actualice la página e inténtelo nuevamente.";
    }

    $errores = array_merge(
        $errores,
        validar_datos_producto(
            $codigo,
            $nombre,
            $categoria,
            $descripcion,
            $precio,
            $cantidad,
            $imagen_actual,
            $estado
        )
    );
    if (!$errores) {
        $resultado_imagen = procesar_imagen_producto(
            $_FILES["imagen_archivo"] ?? [],
            $imagen_url,
            $imagen_actual
        );

        if ($resultado_imagen["error"] !== null) {
            $errores[] = $resultado_imagen["error"];
        } else {
            $imagen = $resultado_imagen["imagen"];
        }
    }

    /*
     * Actualizar el producto solamente cuando
     * no existen errores.
     */
    if (!$errores) {
        $precio_numero = (float) $precio;
        $cantidad_numero = (int) $cantidad;
        $actualizado_por = (int) $usuario_actual["id_usuario"];

        $sql = "UPDATE productos
                SET codigo = ?,
                    nombre = ?,
                    categoria = ?,
                    descripcion = ?,
                    precio = ?,
                    cantidad = ?,
                    imagen = ?,
                    estado = ?,
                    actualizado_por = ?
                WHERE id_producto = ?";

        $consulta = $conexion->prepare($sql);

        $consulta->bind_param(
            "ssssdissii",
            $codigo,
            $nombre,
            $categoria,
            $descripcion,
            $precio_numero,
            $cantidad_numero,
            $imagen,
            $estado,
            $actualizado_por,
            $id_producto
        );

        if ($consulta->execute()) {
            $consulta->close();

            establecer_mensaje(
                "exito",
                "El producto fue actualizado correctamente."
            );

            redireccionar("listar.php");
        }

        if ($consulta->errno === 1062) {
            $errores[] = "Ya existe otro producto con ese código.";
        } else {
            error_log(
                "Error al actualizar producto: "
                . $consulta->error
            );

            $errores[] = "No fue posible actualizar el producto.";
        }

        $consulta->close();
    }
}

$titulo_pagina = "Editar producto";
$ruta_base = "../";
$pagina_actual = "productos";

require __DIR__ . "/../includes/encabezado.php";
?>

<main id="contenido" class="contenedor-formulario">
    <section class="formulario formulario-amplio">

        <div class="cabecera-formulario">
            <span class="etiqueta-superior">
                Producto #<?php echo $id_producto; ?>
            </span>

            <h1>Editar producto</h1>

            <p>
                Los cambios de un producto activo se reflejarán
                en el catálogo publico.
            </p>
        </div>

        <?php if ($errores) { ?>
            <div class="mensaje error" role="alert">
                <strong>Revise la información:</strong>

                <ul>
                    <?php foreach ($errores as $error) { ?>
                        <li>
                            <?php echo escapar($error); ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <form
            method="POST"
            action="editar.php"
            enctype="multipart/form-data"
        >
            <?php echo campo_csrf(); ?>

            <input
                type="hidden"
                name="id_producto"
                value="<?php echo $id_producto; ?>"
            >

            <div class="formulario-dos-columnas">
                <div class="campo">
                    <label for="codigo">
                        Código interno
                    </label>

                    <input
                        id="codigo"
                        type="text"
                        name="codigo"
                        value="<?php echo escapar($codigo); ?>"
                        minlength="3"
                        maxlength="20"
                        pattern="[A-Z0-9-]+"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="categoria">
                        Categoría
                    </label>

                    <input
                        id="categoria"
                        type="text"
                        name="categoria"
                        value="<?php echo escapar($categoria); ?>"
                        minlength="2"
                        maxlength="60"
                        required
                    >
                </div>
            </div>

            <div class="campo">
                <label for="nombre">
                    Nombre del producto
                </label>

                <input
                    id="nombre"
                    type="text"
                    name="nombre"
                    value="<?php echo escapar($nombre); ?>"
                    minlength="3"
                    maxlength="100"
                    required
                >
            </div>

            <div class="campo">
                <label for="descripcion">
                    Descripción
                </label>

                <textarea
                    id="descripcion"
                    name="descripcion"
                    minlength="10"
                    maxlength="600"
                    rows="5"
                    required
                ><?php echo escapar($descripcion); ?></textarea>
            </div>

            <div class="formulario-tres-columnas">
                <div class="campo">
                    <label for="precio">
                        Precio (Q)
                    </label>

                    <input
                        id="precio"
                        type="number"
                        name="precio"
                        value="<?php echo escapar((string) $precio); ?>"
                        min="0.01"
                        max="99999999.99"
                        step="0.01"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="cantidad">
                        Cantidad
                    </label>

                    <input
                        id="cantidad"
                        type="number"
                        name="cantidad"
                        value="<?php echo escapar($cantidad); ?>"
                        min="0"
                        max="999999"
                        step="1"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="estado">
                        Estado
                    </label>

                    <select
                        id="estado"
                        name="estado"
                        required
                    >
                        <option
                            value="Activo"
                            <?php echo $estado === "Activo" ? "selected" : ""; ?>
                        >
                            Activo
                        </option>

                        <option
                            value="Inactivo"
                            <?php echo $estado === "Inactivo" ? "selected" : ""; ?>
                        >
                            Inactivo
                        </option>
                    </select>
                </div>
            </div>

            <div class="campo">
                <label for="imagen_url">
                    URL de una nueva imagen
                    <span class="opcional">(opcional)</span>
                </label>

                <input
                    id="imagen_url"
                    type="url"
                    name="imagen_url"
                    value="<?php echo escapar($imagen_url); ?>"
                    maxlength="500"
                    placeholder="https://sitio.com/imagen.png"
                >
            </div>

            <div class="campo">
                <label for="imagen_archivo">
                    O subir un archivo PNG
                    <span class="opcional">(opcional)</span>
                </label>

                <input
                    id="imagen_archivo"
                    type="file"
                    name="imagen_archivo"
                    accept="image/png"
                >

                <small>
                    Utilice solamente una opción. Si deja ambas vacías,
                    se conservará la imagen actual.
                </small>
            </div>

            <div class="vista-imagen">
                <span>Imagen actual</span>

                <img
                    src="<?php echo escapar(
                        ruta_imagen_producto($imagen, "../")
                    ); ?>"
                    alt="Vista previa de <?php echo escapar($nombre); ?>"
                >
            </div>

            <div class="acciones-formulario">
                <a
                    class="boton boton-secundario"
                    href="listar.php"
                >
                    Cancelar
                </a>

                <button
                    class="boton btn-editar"
                    type="submit"
                >
                    Guardar cambios
                </button>
            </div>
        </form>
    </section>
</main>

<?php

$conexion->close();

require __DIR__ . "/../includes/pie.php";