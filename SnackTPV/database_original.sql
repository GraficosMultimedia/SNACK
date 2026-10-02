-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 06, 2026 at 09:35 AM
-- Server version: 5.7.44-48
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `colibrip_snackliciosos`
--

-- --------------------------------------------------------

--
-- Table structure for table `catalog_product_images`
--

CREATE TABLE `catalog_product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `stored_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `catalog_product_images`
--

INSERT INTO `catalog_product_images` (`id`, `product_id`, `stored_filename`, `original_filename`, `public_path`, `mime_type`, `file_size`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, '8a64ee34b982dfe13ba49f33e05bd3ef.png', 'vaso chico.png', 'assets/productos/8a64ee34b982dfe13ba49f33e05bd3ef.png', 'image/png', 1470713, 0, '2026-08-21 08:22:38', '2026-08-21 08:28:36'),
(2, 1, '1d1c8ea10e0098d78ac23879c7f4ce78.webp', 'vaso chico frappe.webp', 'assets/productos/1d1c8ea10e0098d78ac23879c7f4ce78.webp', 'image/webp', 35650, 1, '2026-08-21 08:28:36', '2026-08-21 08:28:36'),
(3, 2, '923d1bfa0d20368c4541306d0756aecb.webp', 'vaso chico frappe.webp', 'assets/productos/923d1bfa0d20368c4541306d0756aecb.webp', 'image/webp', 35650, 0, '2026-08-21 08:29:32', '2026-08-21 08:29:46'),
(4, 2, '813aefd585aa5c0fd4d062a39d503245.png', 'vaso chico.png', 'assets/productos/813aefd585aa5c0fd4d062a39d503245.png', 'image/png', 1470713, 1, '2026-08-21 08:29:46', '2026-08-21 08:29:46');

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT '0',
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `orden`, `activa`, `created_at`) VALUES
(1, 'Fresas con crema', '', 0, 1, '2026-08-15 17:36:19'),
(2, 'Toppings extras', NULL, 0, 1, '2026-08-15 17:36:19'),
(3, 'Chicharrines caseros', NULL, 0, 1, '2026-08-15 17:36:19'),
(4, 'Más antojitos', NULL, 0, 1, '2026-08-15 17:36:19'),
(5, 'Elotes', 'Elotes con creama y chilito', 0, 0, '2026-08-16 05:23:20');

-- --------------------------------------------------------

--
-- Table structure for table `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(10) UNSIGNED NOT NULL,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`) VALUES
(1, 'nombre_negocio', 'Snackliciosos'),
(2, 'direccion', 'Frontera Piedras Negras #55 a col. Progreso'),
(3, 'telefono', '6271104930'),
(4, 'instagram', '@snackliciosos'),
(5, 'whatsapp', '6271104930'),
(6, 'mensaje', '¡Gracias por tu compra! ✨'),
(19, 'logo', 'assets/img/logo/logo_45b2cb7ad70975cb.png');

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `categoria_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `orden` int(11) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Vaso chico', 'Fresas con crema en vaso chico', 45.00, 0, 1, '2026-08-15 17:36:19', '2026-08-16 20:50:41'),
(2, 1, 'Vaso mediano', 'Fresas con crema en vaso mediano', 60.00, 0, 1, '2026-08-15 17:36:19', '2026-08-16 17:35:58'),
(3, 1, 'Medio litro', 'Fresas con crema, presentación de medio litro', 80.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 23:04:03'),
(4, 1, 'Litro', 'Fresas con crema, presentación de un litro', 150.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 17:36:19'),
(5, 1, 'Rebanada de pay con fresas con crema', 'Rebanada de pay acompañada con fresas y crema', 75.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 23:04:27'),
(8, 2, 'Agrega 2 toppings', 'Oreo, nuez, bombones, chispas, Lunetas, almendra, Nutella . Granola. Lechera  granillo de chocolate o granillo de colores', 0.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 23:06:08'),
(9, 3, 'Plato', 'Chicharrines caseros preparados con verdura', 25.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 23:06:38'),
(10, 3, 'Charola', 'Chicharrines caseros preparados con verdura', 55.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 23:07:12'),
(11, 3, 'Bolsa', 'No disponible para envío a domicilio', 20.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 23:08:12'),
(12, 3, 'Con cueritos curtidos', 'Agregado de cueritos curtidos', 10.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 17:36:19'),
(13, 4, 'Tostilocos', 'Repollo . Tomate.  Pepino . Cueritos . Cacahuates . Salsa . Crema', 50.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 23:10:00'),
(14, 4, 'Pepihuates chico', 'Pepino.cacahuate. rielitos . Cueritos . Clamato preparado', 45.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 23:10:57'),
(15, 4, 'Nachos con queso', 'Nachos . Sabritas . O papas caseras con queso', 40.00, 0, 1, '2026-08-15 17:36:19', '2026-08-15 23:11:29'),
(16, 4, 'Gomilocas', 'Gomitas preparadas con chamoy y chile', 35.00, 0, 1, '2026-08-15 17:36:20', '2026-08-15 23:13:08'),
(17, 4, 'Papas caseras preparadas litro', 'Papas caseras preparadas, presentación de un litro\r\nPepino . Cacahuates.  Rielitos . Salsas', 50.00, 0, 1, '2026-08-15 17:36:20', '2026-08-15 23:15:15'),
(18, 4, 'Vaso loco', 'Sabritas a elección pepino cacahuate rielitos cueritos churritos salsa soya y salsa valentina', 60.00, 0, 1, '2026-08-15 17:36:20', '2026-08-15 23:14:42'),
(23, 3, 'Chicharrines Bolsa salsa y crema', 'Chicharrines en bolsa con salsa y crema', 12.00, 0, 1, '2026-08-15 22:50:07', '2026-08-15 23:09:17'),
(24, 4, 'Pepihuates 1/2 litro', 'Pepino . Cacahuates. Rielitos . Cuerito y clamato preparado', 65.00, 0, 1, '2026-08-15 23:12:19', '2026-08-15 23:12:19'),
(25, 4, 'Papas caseras charola grande', 'Pepino . Cacahuates . Rielitos .', 110.00, 0, 1, '2026-08-15 23:18:12', '2026-08-15 23:18:12'),
(26, 1, 'Fresas especiales Ferrero', 'Nutella.  Ferrero Roche.  Almendra.  (Nuez en temporada )', 120.00, 0, 1, '2026-08-15 23:18:55', '2026-08-15 23:18:55'),
(27, 1, 'Fresas especiales kinder delice', 'Nutella . Kinder delice.  Almendra', 120.00, 0, 1, '2026-08-15 23:19:19', '2026-08-15 23:19:19'),
(28, 1, 'Fresas especiales mazapán', '', 120.00, 0, 1, '2026-08-15 23:19:47', '2026-08-15 23:19:47'),
(29, 2, 'Kinder delice . Pay de queso', 'Agrega kinder  o pay de queso por $20', 20.00, 0, 1, '2026-08-15 23:20:22', '2026-08-15 23:20:22'),
(30, 1, 'Mini hotcakes', '10 mini hotcakes . Acompañados de fruta y 1 jarabe', 65.00, 0, 1, '2026-08-15 23:23:04', '2026-08-15 23:23:04'),
(31, 1, 'Waffle grande', 'Waffle grande acompañado de fruta . Jarabe y toppings', 65.00, 0, 1, '2026-08-15 23:23:38', '2026-08-15 23:23:38');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `activo`, `created_at`) VALUES
(1, 'admin', '$2y$10$LzHgqWPp4Gin0CFSE4uKk.d2opJ/ssU9EuALIBOz8oQEl4VMPi0u6', 1, '2026-08-15 16:25:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `catalog_product_images`
--
ALTER TABLE `catalog_product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_catalog_product_images_product` (`product_id`),
  ADD KEY `idx_catalog_product_images_active` (`product_id`,`is_active`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_clave` (`clave`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_productos_categoria` (`categoria_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `catalog_product_images`
--
ALTER TABLE `catalog_product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
