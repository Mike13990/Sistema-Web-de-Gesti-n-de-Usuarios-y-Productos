<?php

declare(strict_types=1);

require_once __DIR__ . "/funciones.php";

iniciar_sesion();

requerir_sesion("login.php");
require_once __DIR__ . "/conexion.php";
$usuario_actual = sincronizar_usuario_sesion($conexion,"login.php");
$tipo = $usuario_actual["tipo"];

if ($tipo == "administrador")
    {
        $metricas = $conexion -> query("Select
        (SELECT COUNT (*) from usuarios) as total_usuarios, 
        (select count (*) from productos where estado = 'activo') as productos_activos, 
        (select coalesce(sum(cantidad),0) from productos) as valor_inventario")->fetch_assoc();
    }
    elseif($tipo == "usuario")
        {
            $metricas = $conexion -> query("select
            (select count (*) from productos where estado = 'activo') as productos_activos,
            (select coalesce(sum(cantidad),0) FROM productos where estado = 'activo') as unidades,
            (select count (*) from usuarios where estado = 'activo') as usuarios_activos")->fetch_assoc(); 
        }
            else
                {
                    $metricas = $conexion -> query("select count (*) as productos_activos, coalesce(sum(cantidad),0) as unidades from productos where estado = 'activo'")->fetch_assoc();

                }
                $titulo_pagina = "mip panel";
                $ruta_base = "";
                $pagina_actual = "panel";
                require_once __DIR__ . "/includes/encabezado.php";
?>
<main id="contenido class="contenedor-panel">
<?php
    mostrar_mensajes();
?>
<section class="bienvenida-panel">
    <div>
        <span class="etiqueta-superior">area de
            <?php 
                echo escapar(strtolwer(nombre_rol($tipo)));
            ?>
        </span>
        <h1>hola,
            <?php 
                echo escapar($usuario_actual["nombre"]);
            ?>
        </h1>
        <p>Estas son las herramientas habilitadas para su cuenta. Los permisos se verifican nuevamente en cada operacion</p>
    </div>
    <span class="rol-grande">
        <?php 
            echo escapar(nombre_rol($tipo));
        ?>
    </span>
</section>
<?php
    if ($tipo == "administrador")
        {
            ?>
            <section class="metricas-grid" arial-label="resumen administrativo">
                <article><span>usuarios</span><strong>
                    <?php
                        echo (int )$metricas["total_usuarios"];
                    ?>
                    </strong><small>registros totales</small></article>
                    <article><span>Catalogos</span><strong>
                    <?php
                        echo (int) $metricas["productos_activos"];
                    ?>
                    </strong><small>productos consultables</small></article>
                    <article><span>existencias</span>
                    <strong>
                    <?php
                        echo (int) $metricas["unidades"];
                    ?>
                    </strong>
                        <small>unidades activas</small></article>
                        <article><span>Directorio</span><strong>
                    <?php
                        echo (int) $metricas["usuarios_activos"];
                    ?>
                    </strong><small>usuarios activos</small></article>
                </section>
           <section class="seccion-panel">
            <div class="opciones-grid opciones-tres">
                <article class="tarjeta-opcion"><span class="icono-opcion">PR</span><h3>Consultar productos</h3><p>Puede revisar los productos activos, precios y existencias. No puede modificarlos ni eliminarlos.</p><a class="boton" href="productos/listar.php">Consultar</a></article>
                <article class="tarjeta-opcion"><span class="icono-opcion">DI</span><h3>Ver directorio</h3><p>Puede ver los usuarios registrados. La contraseña nunca se consulta ni se muestra.</p><a class="boton" href="usuarios/listar.php">Ver usuarios</a></article>
                <article class="tarjeta-opcion"><span class="icono-opcion">+V</span><h3>Agregar visitante</h3><p>Puede registrar cuentas nuevas únicamente con el rol de visitante y estado activo.</p><a class="boton" href="usuarios/agregar.php">Registrar visitante</a></article>
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

