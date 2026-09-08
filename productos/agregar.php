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

$codigo = "";
$nombre = "";
$categoria = "";
$descripcion = "";
$precio = "";
$cantidad = "0";
$imagen = "";
$imagen_url = "";
$estado = "Activo";
$errores = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo = strtoupper(trim($_POST["codigo"] ?? ""));
    $nombre = trim($_POST["nombre"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = trim($_POST["precio"] ?? "");
    $cantidad = trim($_POST["cantidad"] ?? "");
    $imagen_url = trim($_POST["imagen_url"] ?? "");
    $estado = $_POST["estado"] ?? "";

    if (!csrf_valido($_POST["csrf_token"] ?? null)) {
        $errores[] = "La solicitud no es válida. Actualice la página e inténtelo nuevamente.";
    }

    /*
     * Se validan los datos normales del producto.
     * La imagen se valida aparte.
     */
    $errores = array_merge(
        $errores,
        validar_datos_producto(
            $codigo,
            $nombre,
            $categoria,
            $descripcion,
            $precio,
            $cantidad,
            "",
            $estado
        )
    );

    /*
     * Procesar archivo o URL de imagen.
     */
    if (!$errores) {
        $resultado_imagen = procesar_imagen_producto(
            $_FILES["imagen_archivo"] ?? [],
            $imagen_url
        );

        if ($resultado_imagen["error"] !== null) {
            $errores[] = $resultado_imagen["error"];
        } else {
            $imagen = $resultado_imagen["imagen"];
        }
    }

    /*
     * Guardar el producto si no existen errores.
     */
    if (!$errores) {
        $precio_numero = (float) $precio;
        $cantidad_numero = (int) $cantidad;
        $creado_por = (int) $usuario_actual["id_usuario"];
        $actualizado_por = $creado_por;

        $sql = "INSERT INTO productos (
                    codigo,
                    nombre,
                    categoria,
                    descripcion,
                    precio,
                    cantidad,
                    imagen,
                    estado,
                    creado_por,
                    actualizado_por
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
            $creado_por,
            $actualizado_por
        );

        if ($consulta->execute()) {
            $consulta->close();

            establecer_mensaje(
                "exito",
                "El producto fue registrado correctamente."
            );

            redireccionar("listar.php");
        }

        if ($consulta->errno === 1062) {
            $errores[] = "Ya existe un producto con ese código.";
        } else {
            error_log(
                "Error al registrar producto: "
                . $consulta->error
            );

            $errores[] = "No fue posible guardar el producto.";
        }

        $consulta->close();
    }
}

$titulo_pagina = "Agregar producto";
$ruta_base = "../";
$pagina_actual = "productos";

require __DIR__ . "/../includes/encabezado.php";
?>

<main id="contenido" class="contenedor-formulario">
    <section class="formulario formulario-amplio">

        <div class="cabecera-formulario">
            <span class="etiqueta-superior">
                Nuevo registro de inventario
            </span>

            <h1>Agregar producto</h1>

            <p>
                Los productos activos aparecerán inmediatamente
                en el catálogo público.
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
            action="agregar.php"
            enctype="multipart/form-data"
        >
            <?php echo campo_csrf(); ?>

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
                        placeholder="NA-001"
                        required
                    >

                    <small>
                        Mayúsculas, números y guiones.
                    </small>
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

                <small>
                    Explique el uso o la característica principal
                    del producto.
                </small>
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
                        value="<?php echo escapar($precio); ?>"
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
                    URL de imagen
                </label>

                <input
                    id="imagen_url"
                    type="url"
                    name="imagen_url"
                    value="<?php echo escapar($imagen_url); ?>"
                    maxlength="500"
                    placeholder="https://sitio.com/imagen.jpg"
                >
            </div>

            <div class="campo">
                <label for="imagen_archivo">
                    O seleccionar archivo
                </label>

                <input
                    id="imagen_archivo"
                    type="file"
                    name="imagen_archivo"
                    accept="image/jpg"
                >

                <small>
                    Utilice una URL o seleccione un archivo,
                    pero no ambas opciones.
                </small>
            </div>

            <div class="acciones-formulario">
                <a
                    class="boton boton-secundario"
                    href="listar.php"
                >
                    Cancelar
                </a>

                <button
                    class="boton btn-agregar"
                    type="submit"
                >
                    Guardar producto
                </button>
            </div>
        </form>
    </section>
</main>

<?php

$conexion->close();

require __DIR__ . "/../includes/pie.php";