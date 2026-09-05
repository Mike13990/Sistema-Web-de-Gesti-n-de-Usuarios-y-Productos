<?php

$titulo_pagina = $titulo_pagina ?? APP_NOMBRE;
$ruta_base = $ruta_base ?? "";
$pagina_actual = $pagina_actual ?? "";
$sesion_activa = isset($_SESSION["id_usuario"]);
$tipo_actual = $_SESSION["tipo"] ?? "";

function clase_enlace_activo(string $pagina, string $pagina_actual): string
{
    return $pagina === $pagina_actual ? " enlace-activo" : "";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Catálogo y sistema de gestión de Nodo Altura 502.">
    <meta name="theme-color" content="#102a43">
    <title><?php echo escapar($titulo_pagina); ?> | <?php echo escapar(APP_NOMBRE); ?></title>
    <link rel="icon" href="<?php echo escapar($ruta_base); ?>assets/img/logo.png" type="image/png">
    <link rel="stylesheet" href="<?php echo escapar($ruta_base); ?>css/style.css">
    <script src="<?php echo escapar($ruta_base); ?>assets/js/app.js" defer></script>
</head>
<body>
    <a class="saltar-contenido" href="#contenido">Saltar al contenido</a>

    <header class="encabezado-sitio">
        <div class="barra-navegacion">
            <a class="marca" href="<?php echo escapar($ruta_base); ?>index.php" aria-label="Ir al inicio de Nodo Altura 502">
                <img src="<?php echo escapar($ruta_base); ?>assets/img/logo.png" alt="" width="48" height="48">
                <span>
                    <strong><?php echo escapar(APP_NOMBRE); ?></strong>
                    <small><?php echo escapar(APP_ESLOGAN); ?></small>
                </span>
            </a>

            <button class="boton-menu" type="button" aria-expanded="false" aria-controls="menu-principal">
                <span></span>
                <span></span>
                <span></span>
                <span class="solo-lectura">Abrir menú</span>
            </button>

            <nav id="menu-principal" class="menu-principal" aria-label="Navegación principal">
                <a class="<?php echo clase_enlace_activo("inicio", $pagina_actual); ?>" href="<?php echo escapar($ruta_base); ?>index.php">Inicio</a>
                <a href="<?php echo escapar($ruta_base); ?>index.php#productos">Catálogo</a>

                <?php if ($sesion_activa) { ?>
                    <a class="<?php echo clase_enlace_activo("panel", $pagina_actual); ?>" href="<?php echo escapar($ruta_base); ?>panel.php">Mi panel</a>

                    <?php if (in_array($tipo_actual, ["administrador", "usuario"], true)) { ?>
                        <a class="<?php echo clase_enlace_activo("usuarios", $pagina_actual); ?>" href="<?php echo escapar($ruta_base); ?>usuarios/listar.php">Usuarios</a>
                        <a class="<?php echo clase_enlace_activo("productos", $pagina_actual); ?>" href="<?php echo escapar($ruta_base); ?>productos/listar.php">Productos</a>
                    <?php } ?>

                    <span class="usuario-conectado">
                        <?php echo escapar($_SESSION["nombre"] ?? $_SESSION["usuario"] ?? "Usuario"); ?>
                        · <?php echo escapar(nombre_rol($tipo_actual)); ?>
                    </span>
                    <form class="formulario-salir" action="<?php echo escapar($ruta_base); ?>cerrar.php" method="POST">
                        <?php echo campo_csrf(); ?>
                        <button class="enlace-salir" type="submit">Cerrar sesión</button>
                    </form>
                <?php } else { ?>
                    <a class="boton boton-claro boton-menu-login<?php echo clase_enlace_activo("login", $pagina_actual); ?>" href="<?php echo escapar($ruta_base); ?>login.php">Iniciar sesión</a>
                <?php } ?>
            </nav>
        </div>
    </header>
</head>
</html>