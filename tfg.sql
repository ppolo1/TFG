-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-11-2025 a las 07:53:18
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
-- Base de datos: `tfg`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(255) NOT NULL,
  `genero` varchar(255) NOT NULL,
  `sinopsis` varchar(255) NOT NULL,
  `ejemplares` int(11) NOT NULL,
  `img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id`, `titulo`, `autor`, `genero`, `sinopsis`, `ejemplares`, `img`) VALUES
(1, 'Robin Hood', 'Howard Pyle', 'Aventuras', 'Leyenda inglesa sobre un forajido y héroe que robaba a los ricos para dárselo a los pobres, y luchaba contra el injusto Sheriff de Nottingham y el Príncipe Juan', 60, 'Robin-Hood.jpg'),
(2, 'Alas de Sangre', 'Rebecca Yarros', 'Fantasía, Novela Rosa', 'Violet Sorrengail creía que se uniría al Cuadrante de los Escribas para vivir una vida tranquila, sin embargo, por órdenes de su madre, debe unirse a los miles de candidatos ', 10, 'Fourth-Wing.jpg'),
(3, '1984', 'George Orwell', 'Distopía', 'Novela distópica sobre un estado totalitario que vigila y controla toda la sociedad.', 15, '1984.jpg'),
(4, 'El principito', 'Antoine de Saint-Exupéry', 'Infantil, Filosofía', 'Historia poética y filosófica de un pequeño príncipe que viaja por planetas.', 20, 'El-Principito.jpg'),
(5, 'Cien años de soledad', 'Gabriel García Márquez', 'Realismo mágico', 'Saga familiar que mezcla realidad y elementos mágicos en Macondo.', 8, 'Cien-Anos.jpg'),
(6, 'Sapiens', 'Yuval Noah Harari', 'Divulgación', 'Breve historia de la humanidad desde la aparición del Homo sapiens.', 12, 'Sapiens.jpg'),
(7, 'La sombra del viento', 'Carlos Ruiz Zafón', 'Misterio, Novela', 'Un joven descubre un libro que cambiará su vida en la Barcelona de posguerra.', 9, 'Sombra-Del-Viento.jpg'),
(8, 'Dune', 'Frank Herbert', 'Ciencia ficción', 'Épica de política, ecología y poder en el planeta desértico Arrakis.', 7, 'Dune.jpg'),
(9, 'Orgullo y prejuicio', 'Jane Austen', 'Clásico, Romance', 'Comedia de costumbres sobre el matrimonio y la sociedad inglesa del s. XIX.', 11, 'Orgullo-Prejuicio.jpg'),
(10, 'El ruido y la furia', 'William Faulkner', 'Clásico', 'Novela experimental que explora la decadencia de una familia sureña.', 5, 'Ruido-Furia.jpg'),
(11, 'Meditaciones', 'Marco Aurelio', 'Filosofía', 'Reflexiones personales del emperador romano sobre la ética y la vida.', 14, 'Meditaciones.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `passwd` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `passwd`) VALUES
(1, 'Pablo', 'Polo', 'pppcuenca@gmail.com', '123456'),
(2, 'Sarah', 'Gonzalez', 'gogkergm@ninc', '1234');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NULL,
  `book_id` int(11) NOT NULL,
  `purchased_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_purchases_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchases_book` FOREIGN KEY (`book_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `usuarios` ADD `is_admin` TINYINT(1) DEFAULT 0 AFTER `passwd`;

-- Hacer admin al usuario con id 1 (ejemplo)
UPDATE `usuarios` SET `is_admin` = 1 WHERE `id` = 1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
