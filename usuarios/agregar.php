<?php

declare(strict_types=1);

require_once __DIR__ . "/../funciones.php";

iniciar_sesion();
requerir_sesion("../login.php");

require_once __DIR__ . "/../conexion.php";
$usuario_actual = sincronizar_usuario_sesion($conexion, "../login.php");
requerir_roles(["administrador", "usuario"], "../panel.php");

$es_administrador = $usuario_actual["tipo"] === "administrador";
$nombre = "";
$usuario = "";
$tipo = $es_administrador ? "usuario" : "visitante";
$estado = "Activo";
$errores = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $usuario = strtolower(trim($_POST["usuario"] ?? ""));
    $password = $_POST["password"] ?? "";
    $confirmar_password = $_POST["confirmar_password"] ?? "";
    $tipo = $es_administrador ? ($_POST["tipo"] ?? "") : "visitante";
    $estado = $es_administrador ? ($_POST["estado"] ?? "") : "Activo";

    if (!csrf_valido($_POST["csrf_token"] ?? null)) {
        $errores[] = "La solicitud no es válida. Actualice la página e inténtelo nuevamente.";
    }
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $errores[] = "El nombre debe contener entre 3 y 100 caracteres.";
    }
    if (!validar_usuario($usuario)) {
        $errores[] = "El usuario debe tener entre 4 y 30 caracteres y usar solo minúsculas, números, punto, guion o guion bajo.";
    }
    if (!validar_password($password)) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres e incluir letras y números.";
    }
    if ($password !== $confirmar_password) {
        $errores[] = "Las contraseñas no coinciden.";
    }
    if (!in_array($tipo, ["administrador", "usuario", "visitante"], true)) {
        $errores[] = "Seleccione un tipo de usuario válido.";
    }
    if (!in_array($estado, ["Activo", "Inactivo"], true)) {
        $errores[] = "Seleccione un estado válido.";
    }

    if (!$errores) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $creado_por = (int) $usuario_actual["id_usuario"];
        $sql = "INSERT INTO usuarios (nombre, usuario, password, tipo, estado, creado_por)
                VALUES (?, ?, ?, ?, ?, ?)";
        $consulta = $conexion->prepare($sql);
        $consulta->bind_param(
            "sssssi",
            $nombre,
            $usuario,
            $password_hash,
            $tipo,
            $estado,
            $creado_por
        );

        if ($consulta->execute()) {
            $consulta->close();
            establecer_mensaje(
                "exito",
                $es_administrador
                    ? "El usuario fue registrado correctamente."
                    : "El visitante fue registrado correctamente."
            );
            redireccionar("listar.php");
        }

        if ($consulta->errno === 1062) {
            $errores[] = "Ese nombre de usuario ya está registrado.";
        } else {
            error_log("Error al registrar usuario: " . $consulta->error);
            $errores[] = "No fue posible guardar el usuario.";
        }
        $consulta->close();
    }
}

$titulo_pagina = $es_administrador ? "Agregar usuario" : "Agregar visitante";
$ruta_base = "../";
$pagina_actual = "usuarios";
require __DIR__ . "/../includes/encabezado.php";
?>

<main id="contenido" class="contenedor-formulario">
    <section class="formulario">
        <div class="cabecera-formulario">
            <span class="etiqueta-superior"><?php echo $es_administrador ? "Administración de cuentas" : "Permiso limitado"; ?></span>
            <h1><?php echo $es_administrador ? "Agregar usuario" : "Agregar visitante"; ?></h1>
            <p><?php echo $es_administrador ? "Complete los datos y asigne el rol correspondiente." : "Su cuenta solo puede crear usuarios con rol Visitante y estado Activo."; ?></p>
        </div>

        <?php if ($errores) { ?>
            <div class="mensaje error" role="alert"><strong>Revise la información:</strong><ul><?php foreach ($errores as $error) { ?><li><?php echo escapar($error); ?></li><?php } ?></ul></div>
        <?php } ?>

        <form method="POST" action="agregar.php">
            <?php echo campo_csrf(); ?>

            <div class="campo">
                <label for="nombre">Nombre completo</label>
                <input id="nombre" type="text" name="nombre" value="<?php echo escapar($nombre); ?>" minlength="3" maxlength="100" autocomplete="name" required>
            </div>

            <div class="campo">
                <label for="usuario">Nombre de usuario</label>
                <input id="usuario" type="text" name="usuario" value="<?php echo escapar($usuario); ?>" minlength="4" maxlength="30" pattern="[a-z0-9._-]+" autocomplete="username" required>
                <small>Use minúsculas, números, punto, guion o guion bajo.</small>
            </div>

            <div class="formulario-dos-columnas">
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input id="password" type="password" name="password" minlength="8" maxlength="255" autocomplete="new-password" required>
                    <small>Mínimo 8 caracteres, con letras y números.</small>
                </div>
                <div class="campo">
                    <label for="confirmar_password">Confirmar contraseña</label>
                    <input id="confirmar_password" type="password" name="confirmar_password" minlength="8" maxlength="255" autocomplete="new-password" required>
                </div>
            </div>

            <?php if ($es_administrador) { ?>
                <div class="formulario-dos-columnas">
                    <div class="campo">
                        <label for="tipo">Tipo de usuario</label>
                        <select id="tipo" name="tipo" required>
                            <option value="administrador" <?php echo $tipo === "administrador" ? "selected" : ""; ?>>Administrador</option>
                            <option value="usuario" <?php echo $tipo === "usuario" ? "selected" : ""; ?>>Usuario</option>
                            <option value="visitante" <?php echo $tipo === "visitante" ? "selected" : ""; ?>>Visitante</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" required>
                            <option value="Activo" <?php echo $estado === "Activo" ? "selected" : ""; ?>>Activo</option>
                            <option value="Inactivo" <?php echo $estado === "Inactivo" ? "selected" : ""; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            <?php } else { ?>
                <div class="resumen-permiso"><span>Rol asignado</span><strong>Visitante</strong><span>Estado</span><strong>Activo</strong></div>
            <?php } ?>

            <div class="acciones-formulario">
                <a class="boton boton-secundario" href="listar.php">Cancelar</a>
                <button class="boton btn-agregar" type="submit">Guardar usuario</button>
            </div>
        </form>
    </section>
</main>

<?php
$conexion->close();
require __DIR__ . "/../includes/pie.php";
?>

