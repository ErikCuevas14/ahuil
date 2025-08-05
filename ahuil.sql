-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-07-2025 a las 01:14:27
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ahuil`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE PROCEDURE `SP_ADD_COMMENT` (IN `p_id_usuario` INT, IN `p_contenido` TEXT, OUT `p_id_comentario` INT)   BEGIN
    INSERT INTO comentarios (id_usuario, contenido) VALUES (p_id_usuario, p_contenido);
    SET p_id_comentario = LAST_INSERT_ID();
END$$

CREATE PROCEDURE `SP_ADD_REPLY` (IN `p_id_usuario` INT, IN `p_contenido` TEXT, IN `p_id_comentario_padre` INT, OUT `p_id_comentario` INT)   BEGIN
    INSERT INTO comentarios (id_usuario, contenido, id_comentario_padre) VALUES (p_id_usuario, p_contenido, p_id_comentario_padre);
    SET p_id_comentario = LAST_INSERT_ID();
END$$

CREATE PROCEDURE `SP_GET_COMMENTS` ()   BEGIN
    SELECT c.id_comentario, c.contenido, c.fecha, u.apodo, c.id_comentario_padre
    FROM comentarios c
    JOIN usuarios u ON c.id_usuario = u.id_usuario
    ORDER BY c.fecha ASC;
END$$

CREATE PROCEDURE `SP_LOGIN_USER` (IN `p_email` VARCHAR(255), OUT `p_id_usuario` INT, OUT `p_apodo` VARCHAR(255), OUT `p_contrasena_hash` VARCHAR(255))   BEGIN
    SELECT id_usuario, apodo, contraseña
    INTO p_id_usuario, p_apodo, p_contrasena_hash
    FROM usuarios
    WHERE email = p_email;
END$$

CREATE PROCEDURE `SP_REGISTER_USER` (IN `p_apodo` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_contrasena_hash` VARCHAR(255), OUT `p_user_id` INT, OUT `p_success` BOOLEAN)   BEGIN
    -- Verificar si el email ya existe
    IF EXISTS (SELECT 1 FROM usuarios WHERE email = p_email) THEN
        SET p_success = FALSE;
        SET p_user_id = NULL;
    ELSE
        INSERT INTO usuarios (apodo, email, contraseña) VALUES (p_apodo, p_email, p_contrasena_hash);
        SET p_user_id = LAST_INSERT_ID();
        SET p_success = TRUE;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `id_comentario_padre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id_comentario`, `contenido`, `fecha`, `id_usuario`, `id_comentario_padre`) VALUES
(1, 'Este texto demuestra que si se puede escribir algo y esta es la letra Ñ', '2025-07-21 18:46:51', 5, NULL),
(2, 'letra ñ', '2025-07-21 18:46:58', 5, NULL),
(3, 'respuesta perrona', '2025-07-22 15:18:54', 5, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `apodo` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `apodo`, `email`, `contraseña`) VALUES
(5, 'ejemplo', 'Ejemplo1@gmail.com', '$2y$10$D0kShq7KYtojvywxT.k3XePQ5JEYnzN4J6vMWVUmOGwPD6ZnYZIk.');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_comentario_padre` (`id_comentario_padre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `apodo` (`apodo`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`id_comentario_padre`) REFERENCES `comentarios` (`id_comentario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
