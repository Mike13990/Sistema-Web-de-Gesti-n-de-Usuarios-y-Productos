<?php

declare(strict_types=1);

require_once __DIR__ . "/../funciones.php";

iniciar_sesion();
requerir_sesion("../login.php");

require_once __DIR__ . "/../conexion.php";
$usuario_actual = sincronizar_usuario_sesion($conexion, "../login.php");
requerir_roles(["administrador", "usuario"], "../panel.php");

$busqueda = trim($_GET["buscar"] ?? "");
$filtro_tipo = $_GET["tipo"] ?? "";
$tipos_validos = ["administrador", "usuario", "visitante"];

if (!in_array($filtro_tipo, $tipos_validos, true)) {
    $filtro_tipo = "";
}

$termino = "%" . $busqueda . "%";
$sql = "SELECT id_usuario, nombre, usuario, tipo, estado, ultimo_acceso, creado_en
        FROM usuarios
        WHERE (? = '' OR tipo = ?)
          AND (? = '' OR nombre LIKE ? OR usuario LIKE ?)
        ORDER BY
            CASE tipo
                WHEN 'administrador' THEN 1
                WHEN 'usuario' THEN 2
                ELSE 3
            END,
            nombre
        LIMIT 200";
$consulta = $conexion->prepare($sql);
$consulta->bind_param(
    "sssss",
    $filtro_tipo,
    $filtro_tipo,
    $busqueda,
    $termino,
    $termino
);
$consulta->execute();
$usuarios = $consulta->get_result();

$es_administrador = $usuario_actual["tipo"] === "administrador";
$titulo_pagina = "Usuarios";
$ruta_base = "../";
$pagina_actual = "usuarios";
require __DIR__ . "/../includes/encabezado.php";
?>

<main id="contenido" class="contenedor-panel">
    <section class="cabecera-modulo">
        <div>
            <span class="etiqueta-superior">Directorio protegido</span>
            <h1>Usuarios del sistema</h1>
            <p>
                <?php if ($es_administrador) { ?>Administre cuentas, roles y estados.<?php } else { ?>Consulte el directorio sin acceso a contraseñas y registre únicamente visitantes.<?php } ?>
            </p>
        </div>
        <a class="boton btn-agregar" href="agregar.php">
            <?php echo $es_administrador ? "Agregar usuario" : "Agregar visitante"; ?>
        </a>
    </section>

    <?php mostrar_mensajes(); ?>

    <form class="filtros" method="GET" action="listar.php">
        <div class="campo">
            <label for="buscar">Buscar</label>
            <input id="buscar" type="search" name="buscar" value="<?php echo escapar($busqueda); ?>" placeholder="Nombre o usuario">
        </div>
        <div class="campo">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo">
                <option value="">Todos</option>
                <?php foreach ($tipos_validos as $tipo) { ?>
                    <option value="<?php echo escapar($tipo); ?>" <?php echo $filtro_tipo === $tipo ? "selected" : ""; ?>><?php echo escapar(nombre_rol($tipo)); ?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit">Aplicar filtros</button>
        <a href="listar.php">Limpiar</a>
    </form>

    <div class="nota-seguridad">
        <strong>Privacidad:</strong> la consulta selecciona campos específicos y nunca solicita la columna <code>password</code>.
    </div>

    <?php if ($usuarios->num_rows > 0) { ?>
        <div class="tabla-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último acceso</th>
                        <?php if ($es_administrador) { ?><th>Acciones</th><?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($usuario = $usuarios->fetch_assoc()) { ?>
                        <tr>
                            <td data-label="Nombre"><?php echo escapar($usuario["nombre"]); ?></td>
                            <td data-label="Usuario"><code><?php echo escapar($usuario["usuario"]); ?></code></td>
                            <td data-label="Rol"><span class="rol rol-<?php echo escapar($usuario["tipo"]); ?>"><?php echo escapar(nombre_rol($usuario["tipo"])); ?></span></td>
                            <td data-label="Estado"><span class="estado <?php echo $usuario["estado"] === "Activo" ? "estado-activo" : "estado-inactivo"; ?>"><?php echo escapar($usuario["estado"]); ?></span></td>
                            <td data-label="Último acceso"><?php echo $usuario["ultimo_acceso"] ? escapar(date("d/m/Y H:i", strtotime($usuario["ultimo_acceso"]))) : "Sin ingreso"; ?></td>
                            <?php if ($es_administrador) { ?>
                                <td data-label="Acciones" class="acciones-tabla">
                                    <a class="boton btn-editar boton-pequeno" href="editar.php?id=<?php echo (int) $usuario["id_usuario"]; ?>">Editar</a>
                                    <?php if ((int) $usuario["id_usuario"] !== (int) $usuario_actual["id_usuario"]) { ?>
                                        <form class="formulario-eliminar" action="eliminar.php" method="POST" data-confirmacion="¿Eliminar la cuenta de <?php echo escapar($usuario["nombre"]); ?>? Esta acción no se puede deshacer.">
                                            <?php echo campo_csrf(); ?>
                                            <input type="hidden" name="id_usuario" value="<?php echo (int) $usuario["id_usuario"]; ?>">
                                            <button class="boton btn-eliminar boton-pequeno" type="submit">Eliminar</button>
                                        </form>
                                    <?php } else { ?>
                                        <span class="texto-suave">Sesión actual</span>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <p class="contador-resultados"><?php echo $usuarios->num_rows; ?> resultado(s). Se muestran como máximo 200 registros.</p>
    <?php } else { ?>
        <div class="estado-vacio"><h2>No hay resultados</h2><p>Cambie los filtros o registre un usuario nuevo.</p></div>
    <?php } ?>
</main>

<?php
$consulta->close();
$conexion->close();
require __DIR__ . "/../includes/pie.php";
?>