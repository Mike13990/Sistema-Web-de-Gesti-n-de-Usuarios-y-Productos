<?php

declare(strict_types=1);

require_once __DIR__ . "/funciones.php";
require_once __DIR__ . "/conexion.php";

iniciar_sesion();

if (isset($_SESSION["id_usuario"])) {
    redireccionar("panel.php");
}

$mensaje = "";
$usuario_ingresado = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario_ingresado = strtolower(trim($_POST["usuario"] ?? ""));
    $password = $_POST["password"] ?? "";

    if (!csrf_valido($_POST["csrf_token"] ?? null)) {
        $mensaje = "La solicitud no es válida. Actualice la página e inténtelo nuevamente.";
    } elseif ($usuario_ingresado === "" || $password === "") {
        $mensaje = "Ingrese el usuario y la contraseña.";
    } else {
        $sql = "SELECT id_usuario, nombre, usuario, password, tipo, estado
                FROM usuarios
                WHERE usuario = ?
                LIMIT 1";

        $consulta = $conexion->prepare($sql);
        $consulta->bind_param("s", $usuario_ingresado);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $datos = $resultado->fetch_assoc();

        $consulta->close();

        if (
            $datos
            && $datos["estado"] === "Activo"
            && password_verify($password, $datos["password"])
        ) {
            session_regenerate_id(true);

            $_SESSION["id_usuario"] = (int) $datos["id_usuario"];
            $_SESSION["usuario"] = $datos["usuario"];
            $_SESSION["nombre"] = $datos["nombre"];
            $_SESSION["tipo"] = $datos["tipo"];
            $_SESSION["ultima_actividad"] = time();
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

            $id_usuario_autenticado = (int) $datos["id_usuario"];

            if (password_needs_rehash($datos["password"], PASSWORD_DEFAULT)) {
                $nuevo_hash = password_hash($password, PASSWORD_DEFAULT);

                $actualizar_hash = $conexion->prepare(
                    "UPDATE usuarios
                     SET password = ?
                     WHERE id_usuario = ?"
                );

                $actualizar_hash->bind_param(
                    "si",
                    $nuevo_hash,
                    $id_usuario_autenticado
                );

                $actualizar_hash->execute();
                $actualizar_hash->close();
            }

            $actualizar_acceso = $conexion->prepare(
                "UPDATE usuarios
                 SET ultimo_acceso = NOW()
                 WHERE id_usuario = ?"
            );

            $actualizar_acceso->bind_param(
                "i",
                $id_usuario_autenticado
            );

            $actualizar_acceso->execute();
            $actualizar_acceso->close();

            redireccionar("panel.php");
        }

        $mensaje = "Usuario o contraseña incorrectos, o la cuenta está inactiva.";
    }
}

$titulo_pagina = "Iniciar sesión";
$ruta_base = "";
$pagina_actual = "login";

require __DIR__ . "/includes/encabezado.php";
?>

<main id="contenido" class="contenedor-angosto">
    <section class="login">
        <div class="login-marca">
            <img
                src="assets/img/logo.png"
                alt=""
                width="72"
                height="72"
            >

            <div>
                <span class="etiqueta-superior">Acceso seguro</span>
                <h1>Iniciar sesión</h1>
            </div>
        </div>

        <p>
            Utilice la cuenta asignada por la empresa.
            Las opciones se habilitan según el rol registrado.
        </p>

        <?php mostrar_mensajes(); ?>

        <?php if ($mensaje !== "") { ?>
            <div class="mensaje error" role="alert">
                <?php echo escapar($mensaje); ?>
            </div>
        <?php } ?>

        <form method="POST" action="login.php" autocomplete="on">
            <?php echo campo_csrf(); ?>

            <div class="campo">
                <label for="usuario">Usuario</label>

                <input
                    id="usuario"
                    type="text"
                    name="usuario"
                    value="<?php echo escapar($usuario_ingresado); ?>"
                    maxlength="30"
                    autocomplete="username"
                    required
                    autofocus
                >
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    maxlength="255"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button class="boton boton-ancho" type="submit">
                Ingresar al sistema
            </button>
        </form>

        <a class="enlace-regreso" href="index.php">
            ← Regresar al sitio público
        </a>
    </section>
</main>

<?php

$conexion->close();

require __DIR__ . "/includes/pie.php";