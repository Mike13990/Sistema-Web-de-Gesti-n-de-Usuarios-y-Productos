<?php

declare(strict_types=1);

require_once __DIR__ . "/../funciones.php";

iniciar_sesion();
requerir_sesion("../login.php");

require_once __DIR__ . "/../conexion.php";
$usuario_actual = sincronizar_usuario_sesion($conexion, "../login.php");
requerir_roles(["administrador", "usuario"], "../panel.php");

$es_administrador = $usuario_actual["tipo"] === "administrador";
$busqueda = trim($_GET["buscar"] ?? "");
$filtro_estado = $es_administrador ? ($_GET["estado"] ?? "") : "Activo";

if (!in_array($filtro_estado, ["", "Activo", "Inactivo"], true)) {
    $filtro_estado = $es_administrador ? "" : "Activo";
}

$termino = "%" . $busqueda . "%";
$sql = "SELECT id_producto, codigo, nombre, categoria, descripcion, precio,
               cantidad, imagen, estado, actualizado_en
        FROM productos
        WHERE (? = '' OR estado = ?)
          AND (? = '' OR nombre LIKE ? OR categoria LIKE ? OR codigo LIKE ?)
        ORDER BY estado, nombre
        LIMIT 200";
$consulta = $conexion->prepare($sql);
$consulta->bind_param(
    "ssssss",
    $filtro_estado,
    $filtro_estado,
    $busqueda,
    $termino,
    $termino,
    $termino
);
$consulta->execute();
$productos = $consulta->get_result();

$titulo_pagina = $es_administrador ? "Administrar productos" : "Consultar productos";
$ruta_base = "../";
$pagina_actual = "productos";
require __DIR__ . "/../includes/encabezado.php";
?>

<main id="contenido" class="contenedor-panel">
    <section class="cabecera-modulo">
        <div>
            <span class="etiqueta-superior"><?php echo $es_administrador ? "Inventario y catálogo" : "Consulta autorizada"; ?></span>
            <h1><?php echo $es_administrador ? "Administrar productos" : "Productos disponibles"; ?></h1>
            <p><?php echo $es_administrador ? "Controle la información que se publica y las existencias registradas." : "Puede consultar información, precios y existencias; las acciones de modificación están deshabilitadas para su rol."; ?></p>
        </div>
        <?php if ($es_administrador) { ?><a class="boton btn-agregar" href="agregar.php">Agregar producto</a><?php } ?>
    </section>

    <?php mostrar_mensajes(); ?>

    <form class="filtros" method="GET" action="listar.php">
        <div class="campo">
            <label for="buscar">Buscar</label>
            <input id="buscar" type="search" name="buscar" value="<?php echo escapar($busqueda); ?>" placeholder="Nombre, categoría o código">
        </div>
        <?php if ($es_administrador) { ?>
            <div class="campo">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="Activo" <?php echo $filtro_estado === "Activo" ? "selected" : ""; ?>>Activo</option>
                    <option value="Inactivo" <?php echo $filtro_estado === "Inactivo" ? "selected" : ""; ?>>Inactivo</option>
                </select>
            </div>
        <?php } ?>
        <button type="submit">Aplicar filtros</button>
        <a href="listar.php">Limpiar</a>
    </form>

    <?php if ($productos->num_rows > 0 && $es_administrador) { ?>
        <div class="tabla-responsive">
            <table>
                <thead><tr><th>Producto</th><th>Código</th><th>Precio</th><th>Existencias</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php while ($producto = $productos->fetch_assoc()) { ?>
                        <tr>
                            <td data-label="Producto" class="producto-tabla"><img src="<?php echo escapar(ruta_imagen_producto($producto["imagen"], "../")); ?>" alt=""><span><strong><?php echo escapar($producto["nombre"]); ?></strong><small><?php echo escapar($producto["categoria"]); ?></small></span></td>
                            <td data-label="Código"><code><?php echo escapar($producto["codigo"]); ?></code></td>
                            <td data-label="Precio">Q<?php echo number_format((float) $producto["precio"], 2); ?></td>
                            <td data-label="Existencias"><?php echo (int) $producto["cantidad"]; ?></td>
                            <td data-label="Estado"><span class="estado <?php echo $producto["estado"] === "Activo" ? "estado-activo" : "estado-inactivo"; ?>"><?php echo escapar($producto["estado"]); ?></span></td>
                            <td data-label="Acciones" class="acciones-tabla">
                                <a class="boton btn-editar boton-pequeno" href="editar.php?id=<?php echo (int) $producto["id_producto"]; ?>">Editar</a>
                                <form class="formulario-eliminar" action="eliminar.php" method="POST" data-confirmacion="¿Eliminar <?php echo escapar($producto["nombre"]); ?> del inventario? Esta acción no se puede deshacer.">
                                    <?php echo campo_csrf(); ?>
                                    <input type="hidden" name="id_producto" value="<?php echo (int) $producto["id_producto"]; ?>">
                                    <button class="boton btn-eliminar boton-pequeno" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <p class="contador-resultados"><?php echo $productos->num_rows; ?> resultado(s). Se muestran como máximo 200 registros.</p>
    <?php } elseif ($productos->num_rows > 0) { ?>
        <div class="productos-grid productos-consulta">
            <?php while ($producto = $productos->fetch_assoc()) { ?>
                <article class="tarjeta-producto">
                    <div class="producto-imagen"><img src="<?php echo escapar(ruta_imagen_producto($producto["imagen"], "../")); ?>" alt="<?php echo escapar($producto["nombre"]); ?>"><span class="insignia-categoria"><?php echo escapar($producto["categoria"]); ?></span></div>
                    <div class="producto-contenido"><small><?php echo escapar($producto["codigo"]); ?></small><h2><?php echo escapar($producto["nombre"]); ?></h2><p><?php echo escapar($producto["descripcion"]); ?></p><div class="producto-pie"><strong>Q<?php echo number_format((float) $producto["precio"], 2); ?></strong><span class="estado <?php echo (int) $producto["cantidad"] > 0 ? "estado-activo" : "estado-inactivo"; ?>"><?php echo (int) $producto["cantidad"] > 0 ? (int) $producto["cantidad"] . " disponibles" : "Agotado"; ?></span></div></div>
                </article>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="estado-vacio"><h2>No hay productos para mostrar</h2><p>Cambie los filtros o, si es administrador, registre un producto nuevo.</p></div>
    <?php } ?>
</main>

<?php
$consulta->close();
$conexion->close();
require __DIR__ . "/../includes/pie.php";
?>

