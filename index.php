<?php

declare(strict_types=1);

require_once __DIR__ . "/funciones.php";
require_once __DIR__ . "/conexion.php";

iniciar_sesion();

if (isset($_SESSION["id_usuario"])) {
    sincronizar_usuario_sesion($conexion, "login.php");
}

$busqueda = trim($_GET["buscar"] ?? "");

if ($busqueda !== "") {
    $termino = "%" . $busqueda . "%";
    $sql = "SELECT id_producto, codigo, nombre, categoria, descripcion, precio,
                   cantidad, imagen, estado
            FROM productos
            WHERE estado = 'Activo'
              AND (nombre LIKE ? OR categoria LIKE ? OR codigo LIKE ?)
            ORDER BY nombre";
    $consulta = $conexion->prepare($sql);
    $consulta->bind_param("sss", $termino, $termino, $termino);
    $consulta->execute();
    $productos = $consulta->get_result();
} else {
    $productos = $conexion->query(
        "SELECT id_producto, codigo, nombre, categoria, descripcion, precio,
                cantidad, imagen, estado
         FROM productos
         WHERE estado = 'Activo'
         ORDER BY nombre"
    );
}

$resumen = $conexion->query(
    "SELECT COUNT(*) AS total_productos,
            COALESCE(SUM(cantidad), 0) AS total_unidades
     FROM productos
     WHERE estado = 'Activo'"
)->fetch_assoc();

$titulo_pagina = "Inicio";
$ruta_base = "";
$pagina_actual = "inicio";
require __DIR__ . "/includes/encabezado.php";
?>

<main id="contenido">
    <section class="hero">
        <div class="hero-contenido">
            <span class="etiqueta-superior">Comercio tecnológico guatemalteco</span>
            <h1>Herramientas bien elegidas para estudiar, crear y trabajar mejor.</h1>
            <p>
                En Nodo Altura 502 reunimos accesorios funcionales para espacios de estudio,
                trabajo remoto y pequeños negocios. Menos ruido, mejores decisiones y soporte cercano.
            </p>
            <div class="grupo-botones">
                <a class="boton" href="#productos">Explorar productos</a>
                <?php if (isset($_SESSION["id_usuario"])) { ?>
                    <a class="boton boton-secundario" href="panel.php">Ir a mi panel</a>
                <?php } else { ?>
                    <a class="boton boton-secundario" href="login.php">Acceso de usuarios</a>
                <?php } ?>
            </div>
        </div>

        <div class="hero-visual" aria-label="Resumen del catálogo">
            <img src="assets/img/logo.svg" alt="Logotipo de Nodo Altura 502">
            <div class="dato-hero">
                <strong><?php echo (int) $resumen["total_productos"]; ?></strong>
                <span>productos activos</span>
            </div>
            <div class="dato-hero">
                <strong><?php echo (int) $resumen["total_unidades"]; ?></strong>
                <span>unidades disponibles</span>
            </div>
        </div>
    </section>

    <section class="seccion empresa" id="empresa">
        <div>
            <span class="etiqueta-superior">Nuestra forma de trabajar</span>
            <h2>Tecnología útil, sin llenar el escritorio de cosas innecesarias</h2>
        </div>
        <div class="valores-grid">
            <article>
                <span>01</span>
                <h3>Selección práctica</h3>
                <p>Cada producto responde a una necesidad concreta de estudio, movilidad o productividad.</p>
            </article>
            <article>
                <span>02</span>
                <h3>Inventario claro</h3>
                <p>La disponibilidad publicada se actualiza desde el mismo sistema de gestión.</p>
            </article>
            <article>
                <span>03</span>
                <h3>Atención cercana</h3>
                <p>Orientación sencilla para elegir la herramienta correcta según el uso real.</p>
            </article>
        </div>
    </section>

    <section class="seccion" id="productos">
        <div class="cabecera-seccion">
            <div>
                <span class="etiqueta-superior">Catálogo público</span>
                <h2>Productos disponibles</h2>
                <p>Los visitantes pueden consultar el catálogo; solamente el administrador puede modificarlo.</p>
            </div>

            <form class="formulario-busqueda" method="GET" action="index.php#productos">
                <label class="solo-lectura" for="buscar">Buscar producto</label>
                <input id="buscar" type="search" name="buscar" value="<?php echo escapar($busqueda); ?>" placeholder="Nombre, categoría o código">
                <button type="submit">Buscar</button>
                <?php if ($busqueda !== "") { ?>
                    <a href="index.php#productos">Limpiar</a>
                <?php } ?>
            </form>
        </div>

        <?php mostrar_mensajes(); ?>

        <?php if ($productos->num_rows > 0) { ?>
            <div class="productos-grid">
                <?php while ($producto = $productos->fetch_assoc()) { ?>
                    <article class="tarjeta-producto">
                        <div class="producto-imagen">
                            <img src="<?php echo escapar(ruta_imagen_producto($producto["imagen"])); ?>" alt="<?php echo escapar($producto["nombre"]); ?>" loading="lazy">
                            <span class="insignia-categoria"><?php echo escapar($producto["categoria"]); ?></span>
                        </div>
                        <div class="producto-contenido">
                            <small><?php echo escapar($producto["codigo"]); ?></small>
                            <h3><?php echo escapar($producto["nombre"]); ?></h3>
                            <p><?php echo escapar($producto["descripcion"]); ?></p>
                            <div class="producto-pie">
                                <strong>Q<?php echo number_format((float) $producto["precio"], 2); ?></strong>
                                <?php if ((int) $producto["cantidad"] > 0) { ?>
                                    <span class="estado estado-activo"><?php echo (int) $producto["cantidad"]; ?> disponibles</span>
                                <?php } else { ?>
                                    <span class="estado estado-inactivo">Agotado</span>
                                <?php } ?>
                            </div>
                        </div>
                    </article>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="estado-vacio">
                <h3>No encontramos productos</h3>
                <p>Pruebe con otra palabra o quite el filtro de búsqueda.</p>
            </div>
        <?php } ?>
    </section>
</main>

<?php
if (isset($consulta)) {
    $consulta->close();
}
$conexion->close();
require __DIR__ . "/includes/pie.php";
?>
