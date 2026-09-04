<?php

declare(strict_types=1);

require_once __DIR__ . "/../funciones.php";

iniciar_sesion();
requerir_sesion("../login.php");

require_once __DIR__ . "/../conexion.php";
$usuario_actual = sincronizar_usuario_sesion($conexion, "../login.php");
requerir_roles(["administrador"], "../panel.php");

$id_usuario = id_valido($_POST["id_usuario"] ?? $_GET["id"] ?? null);

if ($id_usuario === 0) {
    establecer_mensaje("error", "El usuario solicitado no es válido.");
    redireccionar("listar.php");
}

$consulta_usuario = $conexion->prepare(
    "SELECT id_usuario, nombre, usuario, tipo, estado
     FROM usuarios
     WHERE id_usuario = ?
     LIMIT 1"
);
$consulta_usuario->bind_param("i", $id_usuario);
$consulta_usuario->execute();
$datos_usuario = $consulta_usuario->get_result()->fetch_assoc();
$consulta_usuario->close();

if (!$datos_usuario) {
    establecer_mensaje("error", "El usuario ya no existe.");
    redireccionar("listar.php");
}

$nombre = $datos_usuario["nombre"];
$usuario = $datos_usuario["usuario"];
$tipo = $datos_usuario["tipo"];
$estado = $datos_usuario["estado"];
$errores = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $usuario = strtolower(trim($_POST["usuario"] ?? ""));
    $tipo = $_POST["tipo"] ?? "";
    $estado = $_POST["estado"] ?? "";
    $nuevo_password = $_POST["nuevo_password"] ?? "";
    $confirmar_password = $_POST["confirmar_password"] ?? "";

    if (!csrf_valido($_POST["csrf_token"] ?? null)) {
        $errores[] = "La solicitud no es válida. Actualice la página e inténtelo nuevamente.";
    }
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $errores[] = "El nombre debe contener entre 3 y 100 caracteres.";
    }
    if (!validar_usuario($usuario)) {
        $errores[] = "El usuario debe tener entre 4 y 30 caracteres y usar solo minúsculas, números, punto, guion o guion bajo.";
    }
    if (!in_array($tipo, ["administrador", "usuario", "visitante"], true)) {
        $errores[] = "Seleccione un tipo de usuario válido.";
    }
    if (!in_array($estado, ["Activo", "Inactivo"], true)) {
        $errores[] = "Seleccione un estado válido.";
    }
    if ($nuevo_password !== "" && !validar_password($nuevo_password)) {
        $errores[] = "La contraseña nueva debe tener al menos 8 caracteres e incluir letras y números.";
    }
    if ($nuevo_password !== $confirmar_password) {
        $errores[] = "Las contraseñas nuevas no coinciden.";
    }

    $es_cuenta_actual = $id_usuario === (int) $usuario_actual["id_usuario"];
    if ($es_cuenta_actual && ($tipo !== "administrador" || $estado !== "Activo")) {
        $errores[] = "No puede quitar el rol administrador ni desactivar la cuenta con la que inició sesión.";
    }

    $se_quita_admin_activo = $datos_usuario["tipo"] === "administrador"
        && $datos_usuario["estado"] === "Activo"
        && ($tipo !== "administrador" || $estado !== "Activo");
    if ($se_quita_admin_activo && es_ultimo_administrador_activo($conexion, $id_usuario)) {
        $errores[] = "Debe existir por lo menos un administrador activo.";
    }

    if (!$errores) {
        if ($nuevo_password !== "") {
            $password_hash = password_hash($nuevo_password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios
                    SET nombre = ?, usuario = ?, password = ?, tipo = ?, estado = ?
                    WHERE id_usuario = ?";
            $consulta = $conexion->prepare($sql);
            $consulta->bind_param("sssssi", $nombre, $usuario, $password_hash, $tipo, $estado, $id_usuario);
        } else {
            $sql = "UPDATE usuarios
                    SET nombre = ?, usuario = ?, tipo = ?, estado = ?
                    WHERE id_usuario = ?";
            $consulta = $conexion->prepare($sql);
            $consulta->bind_param("ssssi", $nombre, $usuario, $tipo, $estado, $id_usuario);
        }

        if ($consulta->execute()) {
            $consulta->close();
            if ($es_cuenta_actual && $nuevo_password !== "") {
                session_regenerate_id(true);
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
            }
            establecer_mensaje("exito", "Los datos del usuario fueron actualizados.");
            redireccionar("listar.php");
        }

        if ($consulta->errno === 1062) {
            $errores[] = "Ese nombre de usuario ya está registrado.";
        } else {
            error_log("Error al actualizar usuario: " . $consulta->error);
            $errores[] = "No fue posible actualizar el usuario.";
        }
        $consulta->close();
    }
}

$titulo_pagina = "Editar usuario";
$ruta_base = "../";
$pagina_actual = "usuarios";
require __DIR__ . "/../includes/encabezado.php";
?>

<main id="contenido" class="contenedor-formulario">
    <section class="formulario">
        <div class="cabecera-formulario">
            <span class="etiqueta-superior">Cuenta #<?php echo $id_usuario; ?></span>
            <h1>Editar usuario</h1>
            <p>Actualice únicamente los campos necesarios. Deje la contraseña vacía para conservar la actual.</p>
        </div>

        <?php if ($errores) { ?>
            <div class="mensaje error" role="alert"><strong>Revise la información:</strong><ul><?php foreach ($errores as $error) { ?><li><?php echo escapar($error); ?></li><?php } ?></ul></div>
        <?php } ?>

        <form method="POST" action="editar.php">
            <?php echo campo_csrf(); ?>
            <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">

            <div class="campo"><label for="nombre">Nombre completo</label><input id="nombre" type="text" name="nombre" value="<?php echo escapar($nombre); ?>" minlength="3" maxlength="100" required></div>
            <div class="campo"><label for="usuario">Nombre de usuario</label><input id="usuario" type="text" name="usuario" value="<?php echo escapar($usuario); ?>" minlength="4" maxlength="30" pattern="[a-z0-9._-]+" required></div>

            <div class="formulario-dos-columnas">
                <div class="campo"><label for="tipo">Tipo de usuario</label><select id="tipo" name="tipo" required><option value="administrador" <?php echo $tipo === "administrador" ? "selected" : ""; ?>>Administrador</option><option value="usuario" <?php echo $tipo === "usuario" ? "selected" : ""; ?>>Usuario</option><option value="visitante" <?php echo $tipo === "visitante" ? "selected" : ""; ?>>Visitante</option></select></div>
                <div class="campo"><label for="estado">Estado</label><select id="estado" name="estado" required><option value="Activo" <?php echo $estado === "Activo" ? "selected" : ""; ?>>Activo</option><option value="Inactivo" <?php echo $estado === "Inactivo" ? "selected" : ""; ?>>Inactivo</option></select></div>
            </div>

            <hr>
            <div class="formulario-dos-columnas">
                <div class="campo"><label for="nuevo_password">Nueva contraseña <span class="opcional">(opcional)</span></label><input id="nuevo_password" type="password" name="nuevo_password" minlength="8" maxlength="255" autocomplete="new-password"><small>Mínimo 8 caracteres, con letras y números.</small></div>
                <div class="campo"><label for="confirmar_password">Confirmar nueva contraseña</label><input id="confirmar_password" type="password" name="confirmar_password" minlength="8" maxlength="255" autocomplete="new-password"></div>
            </div>

            <div class="acciones-formulario"><a class="boton boton-secundario" href="listar.php">Cancelar</a><button class="boton btn-editar" type="submit">Guardar cambios</button></div>
        </form>
    </section>
</main>

<?php
$conexion->close();
require __DIR__ . "/../includes/pie.php";
?>
