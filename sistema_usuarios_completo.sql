-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: sistema_usuarios
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.24.04.4

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `sistema_usuarios`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sistema_usuarios` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `sistema_usuarios`;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id_producto` int unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(600) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) unsigned NOT NULL,
  `cantidad` int unsigned NOT NULL DEFAULT '0',
  `imagen` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('Activo','Inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  `creado_por` int unsigned DEFAULT NULL,
  `actualizado_por` int unsigned DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `uq_productos_codigo` (`codigo`),
  KEY `idx_productos_estado_nombre` (`estado`,`nombre`),
  KEY `idx_productos_categoria` (`categoria`),
  KEY `fk_productos_creado_por` (`creado_por`),
  KEY `fk_productos_actualizado_por` (`actualizado_por`),
  CONSTRAINT `fk_productos_actualizado_por` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_productos_cantidad` CHECK ((`cantidad` >= 0)),
  CONSTRAINT `chk_productos_precio` CHECK ((`precio` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'NA-TEC-075','Teclado Nube K75','Escritorio','Teclado compacto de perfil bajo con distribución en español, conexión dual y teclas silenciosas para jornadas de estudio o trabajo.',549.00,18,'assets/img/productos/teclado-nube.jpg','Activo',NULL,NULL,'2026-09-08 05:13:41','2026-09-08 05:13:41'),(2,'NA-CON-007','Hub Enlace 7','Conectividad','Concentrador USB-C de siete puertos con salidas de video, lector de tarjetas y carga de paso para equipos portátiles.',389.50,24,'assets/img/productos/hub-enlace.jpg','Activo',NULL,NULL,'2026-09-08 05:13:41','2026-09-08 05:13:41'),(3,'NA-ILU-014','Lámpara Lumen Flex','Iluminación','Lámpara regulable con brazo articulado, tres temperaturas de color y control táctil para escritorios de espacio reducido.',279.00,11,'assets/img/productos/lampara-lumen.jpg','Activo',NULL,NULL,'2026-09-08 05:13:41','2026-09-08 05:13:41'),(4,'NA-ERG-018','Base Cumbre Air','Ergonomía','Soporte de aluminio ventilado con seis niveles de altura para mejorar la postura y el flujo de aire de la computadora portátil.',329.00,16,'assets/img/productos/base-cumbre.jpg','Activo',NULL,NULL,'2026-09-08 05:13:41','2026-09-08 05:13:41'),(5,'NA-MOV-018','Mochila Ruta 18','Movilidad','Mochila de dieciocho litros con compartimento acolchado, bolsillo de acceso rápido y organización para accesorios tecnológicos.',459.00,9,'assets/img/productos/mochila-ruta.jpg','Activo',NULL,NULL,'2026-09-08 05:13:41','2026-09-08 05:13:41'),(6,'NA-VID-108','Webcam Mirador FHD','Video','Cámara Full HD con tapa física de privacidad, enfoque automático y micrófonos dobles para clases y reuniones virtuales.',499.00,13,'assets/img/productos/webcam-mirador.jpg','Activo',NULL,NULL,'2026-09-08 05:13:41','2026-09-08 05:13:41'),(7,'NA-ERG-021','Mouse Pulso Silent','Ergonomía','Mouse inalámbrico silencioso con forma ergonómica, sensibilidad ajustable y autonomía extendida para trabajo diario.',219.75,31,'assets/img/productos/mouse-pulso.jpg','Activo',NULL,NULL,'2026-09-08 05:13:41','2026-09-08 05:13:41'),(8,'NA-AUD-030','Audífonos Brisa ANC','Audio','Audífonos circumaurales con cancelación activa de ruido, micrófono desmontable y conexión inalámbrica multipunto.',689.00,7,'assets/img/productos/audifonos-brisa.jpg','Activo',NULL,NULL,'2026-09-08 05:13:41','2026-09-08 05:13:41');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('administrador','usuario','visitante') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'visitante',
  `estado` enum('Activo','Inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  `creado_por` int unsigned DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_usuarios_usuario` (`usuario`),
  KEY `idx_usuarios_tipo_estado` (`tipo`,`estado`),
  KEY `fk_usuarios_creado_por` (`creado_por`),
  CONSTRAINT `fk_usuarios_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (7,'juan perez','admin502','$2y$10$TRuI.L3zY30csjHUonXubej5KNspoPPUpuPPs48B5bwZNTwH8eBO6','administrador','Activo',NULL,'2026-09-08 05:41:30','2026-09-08 05:11:10','2026-09-08 05:41:30'),(8,'ximena Aguilar','adminximena','$2y$12$y2Wuzf9EA1Uw7LLWrdOI7.4JFcJ4ittdtFV19Xeh72BE2a6Mo8Cd.','administrador','Activo',NULL,NULL,'2026-09-08 05:11:10','2026-09-08 05:11:10'),(9,'Mateo Herrera','operador502','$2y$10$4boBX59lWhE5BkJRtoKKJeBYKWCfhb1nBcHL1dpcBEMTPUREaVWD.','usuario','Activo',NULL,'2026-09-08 05:33:37','2026-09-08 05:11:10','2026-09-08 05:33:37'),(10,'ruben lopez','ruben45','$2y$12$7Y6SjCohMCwC0fAdrWhe2udapKphvXOAsNvOSEmUoLmBxlZ2xaUXC','usuario','Activo',NULL,NULL,'2026-09-08 05:11:10','2026-09-08 05:11:10'),(11,'Lucía Méndez','visita502','$2y$10$U1MJtCyx7VpxL0wjMChAi.jSqWkXDMi0oCyahej9845AxLZcE0BFa','visitante','Activo',NULL,'2026-09-08 05:31:35','2026-09-08 05:11:10','2026-09-08 05:31:35'),(12,'paco web','paco87','$2y$12$FRVjnGJEAW7qFOEwYwPG6e55QTksKVd82Dn6MG8eGY6F2Me2dYFjC','visitante','Activo',NULL,NULL,'2026-09-08 05:11:10','2026-09-08 05:11:10');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-08  5:52:03
