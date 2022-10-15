-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-10-2022 a las 17:30:25
-- Versión del servidor: 8.0.22
-- Versión de PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `indexing_searching`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dictionary`
--

CREATE TABLE `dictionary` (
  `id` int NOT NULL,
  `doc_id` int NOT NULL,
  `word` varchar(255) DEFAULT NULL,
  `count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `dictionary`
--

INSERT INTO `dictionary` (`id`, `doc_id`, `word`, `count`) VALUES
(1, 1, 'hola', 10),
(2, 1, 'adios', 2),
(3, 1, 'comer', 12),
(4, 1, 'jugar', 12),
(5, 1, 'el', 1),
(6, 1, 'ese', 2),
(7, 1, 'oro', 2),
(8, 1, 'plata', 1),
(10, 3, 'plata', 1),
(11, 3, 'oro', 3),
(12, 3, 'diamantes', 3),
(13, 3, 'hola', 2),
(15, 5, 'ese', 1),
(16, 5, 'jugar', 3),
(17, 5, 'comer', 1),
(18, 5, 'hola', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documents`
--

CREATE TABLE `documents` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `uri` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `documents`
--

INSERT INTO `documents` (`id`, `name`, `description`, `uri`) VALUES
(1, 'hola1', '', 'test/01'),
(3, 'hola3', '', 'test/03'),
(5, 'hola5', '', 'test/04'),
(6, 'hola', '', 'resrdasdss'),
(7, 'hola', 'descriptioasdsn', 'resrdasdssd');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postings`
--

CREATE TABLE `postings` (
  `id` int NOT NULL,
  `doc_id` int NOT NULL,
  `dic_id` int NOT NULL,
  `pos` int DEFAULT NULL,
  `example` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `postings`
--

INSERT INTO `postings` (`id`, `doc_id`, `dic_id`, `pos`, `example`) VALUES
(1, 1, 1, 20, 'texto anterior hola como esta'),
(2, 1, 2, 10, 'no se decir adios xd');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `dictionary`
--
ALTER TABLE `dictionary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_DocumentDictionary` (`doc_id`),
  ADD KEY `word` (`word`);

--
-- Indices de la tabla `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uri` (`uri`);

--
-- Indices de la tabla `postings`
--
ALTER TABLE `postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_DocumentPosting` (`doc_id`),
  ADD KEY `fk_DictionaryPosting` (`dic_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `dictionary`
--
ALTER TABLE `dictionary`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `postings`
--
ALTER TABLE `postings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `dictionary`
--
ALTER TABLE `dictionary`
  ADD CONSTRAINT `fk_DocumentDictionary` FOREIGN KEY (`doc_id`) REFERENCES `documents` (`id`);

--
-- Filtros para la tabla `postings`
--
ALTER TABLE `postings`
  ADD CONSTRAINT `fk_DictionaryPosting` FOREIGN KEY (`dic_id`) REFERENCES `dictionary` (`id`),
  ADD CONSTRAINT `fk_DocumentPosting` FOREIGN KEY (`doc_id`) REFERENCES `documents` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
