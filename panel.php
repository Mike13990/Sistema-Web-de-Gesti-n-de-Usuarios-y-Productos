<?php

declare(strict_types=1);

require_once __DIR__ . "/funciones.php";

iniciar_sesion();
requerir_sesion("login.php");

require_once __DIR__ . "/conexion.php";
$usuario_actual = sincronizar_usuario_sesion($conexion, "login.php");
$tipo = $usuario_actual["tipo"];

if ($tipo === "administrador") {
    $metricas = $conexion->query(
        "SELECT
            (SELECT COUNT(*) FROM usuarios) AS total_usuarios,
            (SELECT COUNT(*) FROM productos WHERE estado = 'Activo') AS productos_activos,
            (SELECT COALESCE(SUM(cantidad), 0) FROM productos) AS unidades,
            (SELECT COALESCE(SUM(precio * cantidad), 0) FROM productos) AS valor_inventario"
    )->fetch_assoc();
} elseif ($tipo === "usuario") {
    $metricas = $conexion->query(
        "SELECT
            (SELECT COUNT(*) FROM productos WHERE estado = 'Activo') AS productos_activos,
            (SELECT COALESCE(SUM(cantidad), 0) FROM productos WHERE estado = 'Activo') AS unidades,
            (SELECT COUNT(*) FROM usuarios WHERE estado = 'Activo') AS usuarios_activos"
    )->fetch_assoc();
} else {
    $metricas = $conexion->query(
        "SELECT COUNT(*) AS productos_activos,
                COALESCE(SUM(cantidad), 0) AS unidades
         FROM productos
         WHERE estado = 'Activo'"
    )->fetch_assoc();
}

$titulo_pagina = "Mi panel";
$ruta_base = "";
$pagina_actual = "panel";
require __DIR__ . "/includes/encabezado.php";
?>

<main id="contenido" class="contenedor-panel">
    <?php mostrar_mensajes(); ?>

    <section class="bienvenida-panel">
        <div>
            <span class="etiqueta-superior">Área de <?php echo escapar(strtolower(nombre_rol($tipo))); ?></span>
            <h1>Hola, <?php echo escapar($usuario_actual["nombre"]); ?></h1>
            <p>Estas son las herramientas habilitadas para su cuenta. Los permisos se verifican nuevamente en cada operación.</p>
        </div>
        <span class="rol-grande"><?php echo escapar(nombre_rol($tipo)); ?></span>
    </section>

    <?php if ($tipo === "administrador") { ?>
        <section class="metricas-grid" aria-label="Resumen administrativo">
            <article><span>Usuarios</span><strong><?php echo (int) $metricas["total_usuarios"]; ?></strong><small>registros totales</small></article>
            <article><span>Catálogo</span><strong><?php echo (int) $metricas["productos_activos"]; ?></strong><small>productos activos</small></article>
            <article><span>Existencias</span><strong><?php echo (int) $metricas["unidades"]; ?></strong><small>unidades registradas</small></article>
            <article><span>Inventario</span><strong>Q<?php echo number_format((float) $metricas["valor_inventario"], 2); ?></strong><small>valor estimado</small></article>
        </section>

        <section class="seccion-panel">
            <div class="cabecera-seccion">
                <div><span class="etiqueta-superior">Control completo</span><h2>Administración</h2></div>
            </div>
            <div class="opciones-grid">
                <article class="tarjeta-opcion">
                    <span class="icono-opcion">US</span>
                    <h3>Gestionar usuarios</h3>
                    <p>Listar, agregar, editar y eliminar cuentas con controles para proteger al administrador principal.</p>
                    <div class="grupo-botones">
                        <a class="boton" href="usuarios/listar.php">Ver usuarios</a>
                        <a class="boton boton-secundario" href="usuarios/agregar.php">Agregar</a>
                    </div>
                </article>
                <article class="tarjeta-opcion">
                    <span class="icono-opcion">PR</span>
                    <h3>Gestionar productos</h3>
                    <p>Administrar información, precios, existencias, imágenes y visibilidad del catálogo público.</p>
                    <div class="grupo-botones"><a class="boton" href="productos/listar.php">Ver productos</a><a class="boton boton-secundario" href="productos/agregar.php">Agregar</a></div>
                </article>
            </div>
        </section>
    <?php } elseif ($tipo === "usuario") { ?>
        <section class="metricas-grid metricas-tres" aria-label="Resumen de usuario">
            <article><span>Catálogo</span><strong><?php echo (int) $metricas["productos_activos"]; ?></strong><small>productos consultables</small></article>
            <article><span>Existencias</span><strong><?php echo (int) $metricas["unidades"]; ?></strong><small>unidades activas</small></article>
            <article><span>Directorio</span><strong><?php echo (int) $metricas["usuarios_activos"]; ?></strong><small>usuarios activos</small></article>
        </section>

        <section class="seccion-panel">
            <div class="opciones-grid opciones-tres">
                <article class="tarjeta-opcion">
                    <span class="icono-opcion">PR</span>
                    <h3>Consultar productos</h3><p>Puede revisar los productos activos, precios y existencias. No puede modificarlos ni eliminarlos.</p>
                    <a class="boton" href="productos/listar.php">Consultar</a></article>
                <article class="tarjeta-opcion">
                    <span class="icono-opcion">DI</span>
                    <h3>Ver directorio</h3><p>Puede ver los usuarios registrados. La contraseña nunca se consulta ni se muestra.</p>
                    <a class="boton" href="usuarios/listar.php">Ver usuarios</a></article>
                <article class="tarjeta-opcion">
                    <span class="icono-opcion">+V</span>
                    <h3>Agregar visitante</h3><p>Puede registrar cuentas nuevas únicamente con el rol de visitante y estado activo.</p>
                    <a class="boton" href="usuarios/agregar.php">Registrar visitante</a></article>
            </div>
        </section>
    <?php } else { ?>
        <section class="metricas-grid metricas-dos" aria-label="Resumen de visitante">
            <article><span>Catálogo</span><strong><?php echo (int) $metricas["productos_activos"]; ?></strong><small>productos activos</small></article>
            <article><span>Disponibilidad</span><strong><?php echo (int) $metricas["unidades"]; ?></strong><small>unidades publicadas</small></article>
        </section>

        <section class="seccion-panel panel-limitado">
            <span class="icono-opcion">VI</span>
            <h2>Acceso de consulta</h2>
            <p>Su rol permite consultar la página pública y el catálogo. No puede entrar a los módulos de usuarios ni de administración de productos.</p>
            <a class="boton" href="index.php#productos">Ver catálogo público</a>
        </section>
    <?php } ?>
</main>

<?php
$conexion->close();
require __DIR__ . "/includes/pie.php";
?>