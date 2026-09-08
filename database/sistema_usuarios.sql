CREATE DATABASE IF NOT EXISTS sistema_usuarios
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sistema_usuarios;

SET NAMES utf8mb4;

-- USUARIOS
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,

    tipo ENUM(
        'administrador',
        'usuario',
        'visitante'
    ) NOT NULL DEFAULT 'visitante',

    estado ENUM(
        'Activo',
        'Inactivo'
    ) NOT NULL DEFAULT 'Activo',

    creado_por INT UNSIGNED NULL,
    ultimo_acceso DATETIME NULL,

    creado_en TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    actualizado_en TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_usuarios_usuario
        UNIQUE (usuario),

    INDEX idx_usuarios_tipo_estado (tipo, estado),

    CONSTRAINT fk_usuarios_creado_por
        FOREIGN KEY (creado_por)
        REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- PRODUCTOS
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(60) NOT NULL,
    descripcion VARCHAR(600) NOT NULL,
    precio DECIMAL(10,2) UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 0,
    imagen VARCHAR(500) NULL,

    estado ENUM(
        'Activo',
        'Inactivo'
    ) NOT NULL DEFAULT 'Activo',

    creado_por INT UNSIGNED NULL,
    actualizado_por INT UNSIGNED NULL,

    creado_en TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    actualizado_en TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_productos_codigo
        UNIQUE (codigo),

    INDEX idx_productos_estado_nombre (estado, nombre),
    INDEX idx_productos_categoria (categoria),

    CONSTRAINT chk_productos_precio
        CHECK (precio > 0),

    CONSTRAINT chk_productos_cantidad
        CHECK (cantidad >= 0),

    CONSTRAINT fk_productos_creado_por
        FOREIGN KEY (creado_por)
        REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_productos_actualizado_por
        FOREIGN KEY (actualizado_por)
        REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;


INSERT INTO sistema_usuarios.usuarios (nombre, usuario, password, tipo, estado)
 VALUES ('juan perez', 'admin502', '$2y$12$ng8sqLfVPMbTkhstMhZXUuCwxml5BNTYCq6WHnU6G/rVXIb8hoeGq', 'administrador', 'Activo');

INSERT INTO sistema_usuarios.usuarios (nombre, usuario, password, tipo, estado) 
VALUES ('ximena Aguilar', 'adminximena', '$2y$12$y2Wuzf9EA1Uw7LLWrdOI7.4JFcJ4ittdtFV19Xeh72BE2a6Mo8Cd.', 'administrador', 'Activo');

INSERT INTO sistema_usuarios.usuarios (nombre, usuario, password, tipo, estado)
 VALUES ('Mateo Herrera', 'operador502', '$2y$12$pA2GE6gPjAeSIHTUtD4Iu.fiRTQGpVvRHbv4snseFhDcYcjP53SA6', 'usuario', 'Activo');

INSERT INTO sistema_usuarios.usuarios (nombre, usuario, password, tipo, estado) 
VALUES ('ruben lopez', 'ruben45', '$2y$12$7Y6SjCohMCwC0fAdrWhe2udapKphvXOAsNvOSEmUoLmBxlZ2xaUXC', 'usuario', 'Activo');

INSERT INTO sistema_usuarios.usuarios (nombre, usuario, password, tipo, estado)
 VALUES ('Lucía Méndez', 'visita502', '$2y$12$meCLzWR5AZy1PsTZIkKqXelt.St.9gyv5Zs7fUkiavm0OVycloRmS', 'visitante', 'Activo');

INSERT INTO sistema_usuarios.usuarios (nombre, usuario, password, tipo, estado) 
VALUES ('paco web', 'paco87', '$2y$12$FRVjnGJEAW7qFOEwYwPG6e55QTksKVd82Dn6MG8eGY6F2Me2dYFjC', 'visitante', 'Activo');

INSERT INTO sistema_usuarios.productos (codigo, nombre, categoria, descripcion, precio, cantidad, imagen, estado) VALUES ('NA-TEC-075', 'Teclado Nube K75', 'Escritorio', 'Teclado compacto de perfil bajo con distribución en español, conexión dual y teclas silenciosas para jornadas de estudio o trabajo.', 549.00, 18, 'assets/img/productos/teclado-nube.jpg', 'Activo');

INSERT INTO sistema_usuarios.productos (codigo, nombre, categoria, descripcion, precio, cantidad, imagen, estado) VALUES ('NA-CON-007', 'Hub Enlace 7', 'Conectividad', 'Concentrador USB-C de siete puertos con salidas de video, lector de tarjetas y carga de paso para equipos portátiles.', 389.50, 24, 'assets/img/productos/hub-enlace.jpg', 'Activo');

INSERT INTO sistema_usuarios.productos (codigo, nombre, categoria, descripcion, precio, cantidad, imagen, estado) VALUES ('NA-ILU-014', 'Lámpara Lumen Flex', 'Iluminación', 'Lámpara regulable con brazo articulado, tres temperaturas de color y control táctil para escritorios de espacio reducido.', 279.00, 11, 'assets/img/productos/lampara-lumen.jpg', 'Activo');

INSERT INTO sistema_usuarios.productos (codigo, nombre, categoria, descripcion, precio, cantidad, imagen, estado) VALUES ('NA-ERG-018', 'Base Cumbre Air', 'Ergonomía', 'Soporte de aluminio ventilado con seis niveles de altura para mejorar la postura y el flujo de aire de la computadora portátil.', 329.00, 16, 'assets/img/productos/base-cumbre.jpg', 'Activo');

INSERT INTO sistema_usuarios.productos (codigo, nombre, categoria, descripcion, precio, cantidad, imagen, estado) VALUES ('NA-MOV-018', 'Mochila Ruta 18', 'Movilidad', 'Mochila de dieciocho litros con compartimento acolchado, bolsillo de acceso rápido y organización para accesorios tecnológicos.', 459.00, 9, 'assets/img/productos/mochila-ruta.jpg', 'Activo');

INSERT INTO sistema_usuarios.productos (codigo, nombre, categoria, descripcion, precio, cantidad, imagen, estado) VALUES ('NA-VID-108', 'Webcam Mirador FHD', 'Video', 'Cámara Full HD con tapa física de privacidad, enfoque automático y micrófonos dobles para clases y reuniones virtuales.', 499.00, 13, 'assets/img/productos/webcam-mirador.jpg', 'Activo');

INSERT INTO sistema_usuarios.productos (codigo, nombre, categoria, descripcion, precio, cantidad, imagen, estado) VALUES ('NA-ERG-021', 'Mouse Pulso Silent', 'Ergonomía', 'Mouse inalámbrico silencioso con forma ergonómica, sensibilidad ajustable y autonomía extendida para trabajo diario.', 219.75, 31, 'assets/img/productos/mouse-pulso.jpg', 'Activo');

INSERT INTO sistema_usuarios.productos (codigo, nombre, categoria, descripcion, precio, cantidad, imagen, estado) VALUES ('NA-AUD-030', 'Audífonos Brisa ANC', 'Audio', 'Audífonos circumaurales con cancelación activa de ruido, micrófono desmontable y conexión inalámbrica multipunto.', 689.00, 7, 'assets/img/productos/audifonos-brisa.jpg', 'Activo');