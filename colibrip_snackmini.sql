-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 01, 2026 at 06:10 PM
-- Server version: 5.7.44-48
-- PHP Version: 8.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `colibrip_snackmini`
--

-- --------------------------------------------------------

--
-- Table structure for table `caja_cortes`
--

CREATE TABLE `caja_cortes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fecha_apertura` datetime DEFAULT NULL,
  `fecha_corte` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fondo_inicial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ventas_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `efectivo_ventas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `transferencias` decimal(10,2) NOT NULL DEFAULT '0.00',
  `efectivo_esperado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `efectivo_contado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `diferencia` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cantidad_ventas` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `usuario_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `caja_cortes`
--

INSERT INTO `caja_cortes` (`id`, `fecha_apertura`, `fecha_corte`, `fondo_inicial`, `ventas_total`, `efectivo_ventas`, `transferencias`, `efectivo_esperado`, `efectivo_contado`, `diferencia`, `cantidad_ventas`, `usuario_id`) VALUES
(3, '2000-01-01 00:00:00', '2026-09-06 23:07:31', 0.00, 250.00, 300.00, 0.00, 300.00, 300.00, 0.00, 1, 1),
(4, '2026-09-06 23:07:31', '2026-09-06 23:24:30', 0.00, 599.00, 700.00, 0.00, 700.00, 750.00, 50.00, 3, 1),
(5, '2026-09-06 23:24:30', '2026-09-09 18:49:55', 0.00, 2470.00, 2740.00, 315.00, 2740.00, 0.00, -2740.00, 13, 1),
(6, '2026-09-09 18:49:55', '2026-09-11 18:30:06', 0.00, 885.00, 1630.00, 0.00, 1630.00, 0.00, -1630.00, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int(11) NOT NULL DEFAULT '0',
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `orden`, `activa`, `created_at`) VALUES
(1, 'Fresas con crema', 1, 1, '2026-09-06 17:29:31'),
(2, 'Chicharrines caseros', 2, 1, '2026-09-06 17:29:31'),
(3, 'Más antojitos', 3, 1, '2026-09-06 17:29:31');

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
(2, 'mensaje_ticket', '¡Gracias por tu compra! ✨'),
(4, 'direccion', 'Frontera Piedras Negras #55 a col. Progreso'),
(5, 'telefono', '6271104930'),
(6, 'whatsapp', '526271104930'),
(7, 'instagram', '@snackliciosos'),
(8, 'mensaje_menu', '¡Gracias por tu compra! ✨'),
(9, 'logo', '/SnackTPV/uploads/logo_20260907_114517_f9d519.png'),
(53, 'transfer_banco', ''),
(54, 'transfer_titular', ''),
(55, 'transfer_cuenta', ''),
(56, 'transfer_clabe', ''),
(57, 'transfer_instrucciones', 'Después que realices el pago es necesario nos envíes tu comprobante a nuestro whatsapp con el numero de folio del ticket generado');

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `categoria_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagen` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `permite_toppings` tinyint(1) NOT NULL DEFAULT '0',
  `max_toppings` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `toppings_gratis` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `orden` int(11) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id`, `categoria_id`, `nombre`, `descripcion`, `imagen`, `precio`, `permite_toppings`, `max_toppings`, `toppings_gratis`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Vaso chico', 'Fresas con crema en vaso chico', '', 45.00, 1, 2, 2, 1, 1, '2026-09-06 17:29:31', '2026-09-08 00:48:03'),
(2, 1, 'Vaso mediano', 'Fresas con crema en vaso mediano', '/SnackTPV/uploads/productos/Vaso-mediano_20260907_123032_e9d2d820.webp', 60.00, 1, 2, 2, 2, 1, '2026-09-06 17:29:31', '2026-09-07 18:30:32'),
(3, 1, 'Medio litro', 'Fresas con crema, presentación de medio litro', '/SnackTPV/uploads/productos/Medio-litro_20260907_123106_8c1e1903.webp', 80.00, 1, 2, 2, 3, 1, '2026-09-06 17:29:31', '2026-09-08 00:47:04'),
(4, 1, 'Litro', 'Fresas con crema, presentación de un litro', '', 150.00, 1, 2, 2, 4, 1, '2026-09-06 17:29:31', '2026-09-08 00:49:09'),
(5, 1, 'Rebanada de pay con fresas con crema', 'Rebanada de pay acompañada con fresas y crema', '/SnackTPV/uploads/productos/Rebanada-de-pay-con-fresas-con-crema_20260907_123209_8173e370.webp', 75.00, 1, 2, 2, 5, 1, '2026-09-06 17:29:31', '2026-09-07 18:32:09'),
(6, 1, 'Fresas especiales Ferrero', 'Nutella, Ferrero Roche, almendra nuez', '/SnackTPV/uploads/productos/Fresas-especiales-Ferrero_20260907_123424_5e4a22d6.webp', 120.00, 0, 0, 0, 6, 1, '2026-09-06 17:29:31', '2026-09-07 18:34:24'),
(7, 1, 'Fresas especiales Kinder Delice', 'Nutella, Kinder Delice, almendra nuez', '/SnackTPV/uploads/productos/Fresas-especiales-Kinder-Delice_20260907_123510_04a050a2.webp', 120.00, 0, 0, 0, 7, 1, '2026-09-06 17:29:31', '2026-09-07 18:35:10'),
(8, 1, 'Fresas especiales Mazapán', 'Crema de cacahuate \r\n Mazapan. Lechera', '/SnackTPV/uploads/productos/Fresas-especiales-Mazap-n_20260907_123638_328f1cb5.webp', 120.00, 0, 0, 0, 8, 1, '2026-09-06 17:29:31', '2026-09-07 18:36:38'),
(9, 1, 'Mini hotcakes', '10 mini hotcakes. Acompañados de fruta y 1 jarabe', '/SnackTPV/uploads/productos/Mini-hotcakes_20260906_234155_e34a7e22.webp', 65.00, 0, 0, 0, 9, 0, '2026-09-06 17:29:31', '2026-09-13 00:11:53'),
(10, 1, 'Waffle grande', 'Waffle grande acompañado de fruta, jarabe y toppings', '/SnackTPV/uploads/productos/Waffle-grande_20260907_123925_d5115c34.webp', 65.00, 0, 0, 0, 10, 1, '2026-09-06 17:29:31', '2026-09-07 18:39:25'),
(11, 2, 'Plato', 'Chicharrines caseros preparados con verdura', NULL, 25.00, 0, 0, 0, 1, 1, '2026-09-06 17:29:31', '2026-09-06 17:29:31'),
(12, 2, 'Charola', 'Chicharrines caseros preparados con verdura', NULL, 55.00, 0, 0, 0, 2, 1, '2026-09-06 17:29:31', '2026-09-06 17:29:31'),
(13, 2, 'Bolsa', 'No disponible para envío a domicilio', NULL, 20.00, 0, 0, 0, 3, 1, '2026-09-06 17:29:31', '2026-09-06 17:29:31'),
(14, 2, 'Con cueritos curtidos', 'Agregado de cueritos curtidos', NULL, 10.00, 0, 0, 0, 4, 1, '2026-09-06 17:29:31', '2026-09-06 17:29:31'),
(15, 2, 'Chicharrines Bolsa salsa y crema', 'Chicharrines en bolsa con salsa y crema', '/SnackTPV/uploads/productos/Chicharrines-Bolsa-salsa-y-crema_20260906_234251_6357957c.webp', 12.00, 0, 0, 0, 5, 1, '2026-09-06 17:29:31', '2026-09-07 05:42:51'),
(16, 3, 'Tostilocos', 'Repollo, tomate, pepino, cueritos, cacahuates, salsa y crema', '/SnackTPV/uploads/productos/Tostilocos_20260906_234037_0ab5fa22.webp', 50.00, 0, 0, 0, 1, 1, '2026-09-06 17:29:31', '2026-09-07 05:40:37'),
(17, 3, 'Pepihuates chico', 'Pepino, cacahuate, rielitos, cueritos y clamato preparado', '/SnackTPV/uploads/productos/Pepihuates-chico_20260906_234533_b692a64b.webp', 45.00, 0, 0, 0, 2, 1, '2026-09-06 17:29:31', '2026-09-07 05:45:33'),
(18, 3, 'Nachos con queso', 'Nachos, Sabritas o papas caseras con queso', '/SnackTPV/uploads/productos/Nachos-con-queso_20260906_234908_bc3545a0.webp', 40.00, 0, 0, 0, 3, 1, '2026-09-06 17:29:31', '2026-09-07 05:49:08'),
(19, 3, 'Gomilocas', 'Gomitas preparadas con chamoy y chile', '/SnackTPV/uploads/productos/Gomilocas_20260906_234944_d579e9d3.webp', 35.00, 0, 0, 0, 4, 1, '2026-09-06 17:29:31', '2026-09-07 05:49:44'),
(20, 3, 'Papas caseras preparadas litro', 'Papas caseras preparadas de un litro. Pepino, cacahuates, rielitos y salsas', NULL, 50.00, 0, 0, 0, 5, 1, '2026-09-06 17:29:31', '2026-09-06 17:29:31'),
(21, 3, 'Vaso loco', 'Sabritas a elección, pepino, cacahuate, rielitos, cueritos, churritos, salsa soya y salsa Valentina', NULL, 60.00, 0, 0, 0, 6, 1, '2026-09-06 17:29:31', '2026-09-06 17:29:31'),
(22, 3, 'Pepihuates 1/2 litro', 'Pepino, cacahuates, rielitos, cuerito y clamato preparado', '/SnackTPV/uploads/productos/Pepihuates-1-2-litro_20260906_234758_bf3ee3e1.webp', 65.00, 0, 0, 0, 7, 1, '2026-09-06 17:29:31', '2026-09-07 05:47:58'),
(23, 3, 'Papas caseras charola grande', 'Pepino, cacahuates y rielitos', NULL, 110.00, 0, 0, 0, 8, 1, '2026-09-06 17:29:31', '2026-09-06 17:29:31'),
(24, 1, 'Pay extra', 'Agrega pay de queso encima de tus fresas', '', 20.00, 0, 0, 0, 11, 1, '2026-09-08 00:44:38', '2026-09-08 00:44:38'),
(25, 1, 'Kinder delice', 'Agrega kinder delice en tus fresas', '', 20.00, 0, 0, 0, 12, 1, '2026-09-08 00:45:05', '2026-09-08 00:45:05'),
(26, 1, 'Fresas especiales baileys', '', '/SnackTPV/uploads/productos/Fresas-especiales-baileys_20260907_185141_5e4a0316.webp', 120.00, 0, 0, 0, 6, 1, '2026-09-08 00:51:41', '2026-09-08 01:26:07');

-- --------------------------------------------------------

--
-- Table structure for table `producto_imagenes`
--

CREATE TABLE `producto_imagenes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `imagen` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int(11) NOT NULL DEFAULT '0',
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `producto_imagenes`
--

INSERT INTO `producto_imagenes` (`id`, `producto_id`, `imagen`, `orden`, `activa`, `created_at`) VALUES
(1, 2, '/SnackTPV/uploads/productos/Vaso-mediano_20260907_123032_e9d2d820.webp', 0, 1, '2026-09-09 05:03:32'),
(3, 9, '/SnackTPV/uploads/productos/Mini-hotcakes_20260906_234155_e34a7e22.webp', 0, 1, '2026-09-13 00:11:53');

-- --------------------------------------------------------

--
-- Table structure for table `producto_toppings`
--

CREATE TABLE `producto_toppings` (
  `producto_id` int(10) UNSIGNED NOT NULL,
  `topping_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `producto_toppings`
--

INSERT INTO `producto_toppings` (`producto_id`, `topping_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(1, 3),
(2, 3),
(3, 3),
(4, 3),
(5, 3),
(1, 4),
(2, 4),
(3, 4),
(4, 4),
(5, 4),
(1, 5),
(2, 5),
(3, 5),
(4, 5),
(5, 5),
(1, 6),
(2, 6),
(3, 6),
(4, 6),
(5, 6),
(1, 7),
(2, 7),
(3, 7),
(4, 7),
(5, 7),
(1, 8),
(2, 8),
(3, 8),
(4, 8),
(5, 8),
(1, 9),
(2, 9),
(3, 9),
(4, 9),
(5, 9),
(1, 10),
(2, 10),
(3, 10),
(4, 10),
(5, 10),
(1, 11),
(2, 11),
(3, 11),
(4, 11),
(5, 11);

-- --------------------------------------------------------

--
-- Table structure for table `toppings`
--

CREATE TABLE `toppings` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `orden` int(11) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `toppings`
--

INSERT INTO `toppings` (`id`, `nombre`, `precio`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Oreo', 5.00, 1, 1, '2026-09-06 17:29:31', '2026-09-09 04:25:39'),
(2, 'Nuez', 5.00, 2, 1, '2026-09-06 17:29:31', '2026-09-09 04:25:51'),
(3, 'Bombones', 5.00, 3, 1, '2026-09-06 17:29:31', '2026-09-09 04:26:05'),
(4, 'Chispas', 5.00, 4, 1, '2026-09-06 17:29:31', '2026-09-09 04:26:24'),
(5, 'Lunetas', 5.00, 5, 1, '2026-09-06 17:29:31', '2026-09-09 04:26:37'),
(6, 'Almendra', 5.00, 6, 1, '2026-09-06 17:29:31', '2026-09-09 04:26:51'),
(7, 'Nutella', 5.00, 7, 1, '2026-09-06 17:29:31', '2026-09-09 04:27:07'),
(8, 'Granola', 5.00, 8, 1, '2026-09-06 17:29:31', '2026-09-09 04:27:20'),
(9, 'Lechera', 5.00, 9, 1, '2026-09-06 17:29:31', '2026-09-09 04:27:33'),
(10, 'Granillo de chocolate', 5.00, 10, 1, '2026-09-06 17:29:31', '2026-09-09 04:27:48'),
(11, 'Granillo de colores', 5.00, 11, 1, '2026-09-06 17:29:31', '2026-09-09 04:28:01'),
(12, 'Kinder delice', 20.00, 0, 0, '2026-09-08 00:36:11', '2026-09-08 00:43:42'),
(13, 'Pay de queso', 20.00, 0, 0, '2026-09-08 00:38:39', '2026-09-08 00:43:49');

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
(1, 'admin', '$2y$10$LzHgqWPp4Gin0CFSE4uKk.d2opJ/ssU9EuALIBOz8oQEl4VMPi0u6', 1, '2026-09-06 17:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `ventas`
--

CREATE TABLE `ventas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `folio` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `efectivo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cambio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ventas`
--

INSERT INTO `ventas` (`id`, `folio`, `fecha`, `total`, `efectivo`, `cambio`, `metodo_pago`) VALUES
(6, 'V-20260906-230646-251', '2026-09-06 23:06:46', 250.00, 300.00, 50.00, 'efectivo'),
(7, 'V-20260906-230853-195', '2026-09-06 23:08:53', 174.00, 200.00, 26.00, 'efectivo'),
(8, 'V-20260906-231425-765', '2026-09-06 23:14:25', 305.00, 350.00, 45.00, 'efectivo'),
(9, 'V-20260906-232311-153', '2026-09-06 23:23:11', 120.00, 150.00, 30.00, 'efectivo'),
(10, 'V-20260907-184613-424', '2026-09-07 18:46:13', 80.00, 80.00, 0.00, 'efectivo'),
(11, 'V-20260907-192104-601', '2026-09-07 19:21:04', 415.00, 500.00, 85.00, 'efectivo'),
(12, 'V-20260907-192240-293', '2026-09-07 19:22:40', 420.00, 500.00, 80.00, 'efectivo'),
(13, 'V-20260907-192311-126', '2026-09-07 19:23:11', 80.00, 80.00, 0.00, 'efectivo'),
(14, 'V-20260907-192324-415', '2026-09-07 19:23:24', 80.00, 80.00, 0.00, 'efectivo'),
(15, 'V-20260907-192916-834', '2026-09-07 19:29:16', 240.00, 500.00, 260.00, 'efectivo'),
(18, 'V-20260908-213855-966', '2026-09-08 21:38:55', 120.00, 200.00, 80.00, 'efectivo'),
(19, 'V-20260908-214006-738', '2026-09-08 21:40:06', 120.00, 200.00, 80.00, 'efectivo'),
(20, 'V-20260908-214020-928', '2026-09-08 21:40:20', 130.00, 0.00, 0.00, 'transferencia'),
(21, 'V-20260909-182024-391', '2026-09-09 18:20:24', 185.00, 0.00, 0.00, 'transferencia'),
(22, 'V-20260909-183055-183', '2026-09-09 18:30:55', 160.00, 160.00, 0.00, 'efectivo'),
(23, 'V-20260909-183124-374', '2026-09-09 18:31:24', 240.00, 240.00, 0.00, 'efectivo'),
(24, 'V-20260909-183150-928', '2026-09-09 18:31:50', 200.00, 200.00, 0.00, 'efectivo'),
(25, 'V-20260909-185053-328', '2026-09-09 18:50:53', 270.00, 320.00, 50.00, 'efectivo'),
(26, 'V-20260909-185139-917', '2026-09-09 18:51:39', 100.00, 100.00, 0.00, 'efectivo'),
(27, 'V-20260909-185227-550', '2026-09-09 18:52:27', 40.00, 50.00, 10.00, 'efectivo'),
(28, 'V-20260909-195154-199', '2026-09-09 19:51:54', 180.00, 500.00, 320.00, 'efectivo'),
(29, 'V-20260909-200448-491', '2026-09-09 20:04:48', 135.00, 500.00, 365.00, 'efectivo'),
(30, 'V-20260909-213753-661', '2026-09-09 21:37:53', 160.00, 160.00, 0.00, 'efectivo'),
(31, 'V-20260911-183038-248', '2026-09-11 18:30:38', 80.00, 80.00, 0.00, 'efectivo'),
(32, 'V-20260911-183117-197', '2026-09-11 18:31:17', 200.00, 200.00, 0.00, 'efectivo'),
(33, 'V-20260911-183234-477', '2026-09-11 18:32:34', 200.00, 200.00, 0.00, 'efectivo'),
(34, 'V-20260917-135652-522', '2026-09-17 13:56:52', 195.00, 200.00, 5.00, 'efectivo'),
(35, 'V-20260917-135806-484', '2026-09-17 13:58:06', 45.00, 50.00, 5.00, 'efectivo'),
(36, 'V-20260918-171705-339', '2026-09-18 17:17:05', 45.00, 50.00, 5.00, 'efectivo'),
(37, 'V-20260918-174453-502', '2026-09-18 17:44:53', 45.00, 50.00, 5.00, 'efectivo'),
(38, 'V-20260918-180000-521', '2026-09-18 18:00:00', 45.00, 50.00, 5.00, 'efectivo'),
(39, 'V-20260918-180843-543', '2026-09-18 18:08:43', 45.00, 50.00, 5.00, 'efectivo'),
(40, 'V-20260918-191111-841', '2026-09-18 19:11:11', 45.00, 50.00, 5.00, 'efectivo'),
(41, 'V-20260918-191529-958', '2026-09-18 19:15:29', 45.00, 50.00, 5.00, 'efectivo'),
(42, 'V-20260918-191918-412', '2026-09-18 19:19:18', 45.00, 50.00, 5.00, 'efectivo'),
(43, 'V-20260918-200959-983', '2026-09-18 20:09:59', 45.00, 50.00, 5.00, 'efectivo'),
(44, 'V-20260919-192559-643', '2026-09-19 19:25:59', 45.00, 50.00, 5.00, 'efectivo'),
(45, 'V-20260919-192901-405', '2026-09-19 19:29:01', 45.00, 50.00, 5.00, 'efectivo'),
(46, 'V-20260919-193044-650', '2026-09-19 19:30:44', 45.00, 50.00, 5.00, 'efectivo'),
(47, 'V-20260919-193940-976', '2026-09-19 19:39:40', 45.00, 50.00, 5.00, 'efectivo'),
(48, 'V-20260919-194108-423', '2026-09-19 19:41:08', 45.00, 50.00, 5.00, 'efectivo'),
(49, 'V-20260919-194347-410', '2026-09-19 19:43:47', 45.00, 50.00, 5.00, 'efectivo'),
(50, 'V-20260919-200137-394', '2026-09-19 20:01:37', 45.00, 50.00, 5.00, 'efectivo');

-- --------------------------------------------------------

--
-- Table structure for table `venta_detalle`
--

CREATE TABLE `venta_detalle` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `venta_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `venta_detalle`
--

INSERT INTO `venta_detalle` (`id`, `venta_id`, `producto_id`, `nombre`, `precio`, `cantidad`, `subtotal`) VALUES
(4, 6, 7, 'Fresas especiales Kinder Delice', 120.00, 1, 120.00),
(5, 6, 9, 'Mini hotcakes', 65.00, 1, 65.00),
(6, 6, 10, 'Waffle grande', 65.00, 1, 65.00),
(7, 7, 7, 'Fresas especiales Kinder Delice', 120.00, 1, 120.00),
(8, 7, 1, 'Vaso chico', 44.00, 1, 54.00),
(9, 8, 1, 'Vaso chico', 45.00, 1, 55.00),
(10, 8, 2, 'Vaso mediano', 60.00, 1, 60.00),
(11, 8, 1, 'Vaso chico', 45.00, 1, 60.00),
(12, 8, 10, 'Waffle grande', 65.00, 1, 65.00),
(13, 8, 9, 'Mini hotcakes', 65.00, 1, 65.00),
(14, 9, 7, 'Fresas especiales Kinder Delice', 120.00, 1, 120.00),
(15, 10, 2, 'Vaso mediano', 60.00, 1, 60.00),
(16, 10, 25, 'Kinder delice', 20.00, 1, 20.00),
(17, 11, 3, 'Medio litro', 80.00, 1, 80.00),
(18, 11, 11, 'Plato', 25.00, 1, 25.00),
(19, 11, 12, 'Charola', 55.00, 1, 55.00),
(20, 11, 14, 'Con cueritos curtidos', 10.00, 1, 10.00),
(21, 11, 14, 'Con cueritos curtidos', 10.00, 1, 10.00),
(22, 11, 16, 'Tostilocos', 50.00, 1, 50.00),
(23, 11, 22, 'Pepihuates 1/2 litro', 65.00, 1, 65.00),
(24, 11, 26, 'Fresas especiales baileys', 120.00, 1, 120.00),
(25, 12, 3, 'Medio litro', 80.00, 1, 80.00),
(26, 12, 3, 'Medio litro', 80.00, 1, 80.00),
(27, 12, 3, 'Medio litro', 80.00, 1, 80.00),
(28, 12, 3, 'Medio litro', 80.00, 1, 80.00),
(29, 12, 3, 'Medio litro', 80.00, 1, 80.00),
(30, 12, 24, 'Pay extra', 20.00, 1, 20.00),
(31, 13, 2, 'Vaso mediano', 60.00, 1, 60.00),
(32, 13, 24, 'Pay extra', 20.00, 1, 20.00),
(33, 14, 3, 'Medio litro', 80.00, 1, 80.00),
(34, 15, 6, 'Fresas especiales Ferrero', 120.00, 1, 120.00),
(35, 15, 6, 'Fresas especiales Ferrero', 120.00, 1, 120.00),
(38, 18, 6, 'Fresas especiales Ferrero', 120.00, 1, 120.00),
(39, 19, 6, 'Fresas especiales Ferrero', 120.00, 1, 120.00),
(40, 20, 10, 'Waffle grande', 65.00, 1, 65.00),
(41, 20, 10, 'Waffle grande', 65.00, 1, 65.00),
(42, 21, 7, 'Fresas especiales Kinder Delice', 120.00, 1, 120.00),
(43, 21, 14, 'Con cueritos curtidos', 10.00, 1, 10.00),
(44, 21, 12, 'Charola', 55.00, 1, 55.00),
(45, 22, 2, 'Vaso mediano', 60.00, 1, 60.00),
(46, 22, 16, 'Tostilocos', 50.00, 1, 50.00),
(47, 22, 16, 'Tostilocos', 50.00, 1, 50.00),
(48, 23, 6, 'Fresas especiales Ferrero', 120.00, 1, 120.00),
(49, 23, 8, 'Fresas especiales Mazapán', 120.00, 1, 120.00),
(50, 24, 6, 'Fresas especiales Ferrero', 120.00, 1, 120.00),
(51, 24, 3, 'Medio litro', 80.00, 1, 80.00),
(52, 25, 1, 'Vaso chico', 45.00, 1, 45.00),
(53, 25, 1, 'Vaso chico', 45.00, 1, 45.00),
(54, 25, 1, 'Vaso chico', 45.00, 1, 45.00),
(55, 25, 1, 'Vaso chico', 45.00, 1, 45.00),
(56, 25, 1, 'Vaso chico', 45.00, 1, 45.00),
(57, 25, 1, 'Vaso chico', 45.00, 1, 45.00),
(58, 26, 2, 'Vaso mediano', 60.00, 1, 60.00),
(59, 26, 18, 'Nachos con queso', 40.00, 1, 40.00),
(60, 27, 18, 'Nachos con queso', 40.00, 1, 40.00),
(61, 28, 3, 'Medio litro', 80.00, 1, 80.00),
(62, 28, 3, 'Medio litro', 80.00, 1, 80.00),
(63, 28, 24, 'Pay extra', 20.00, 1, 20.00),
(64, 29, 3, 'Medio litro', 80.00, 1, 80.00),
(65, 29, 12, 'Charola', 55.00, 1, 55.00),
(66, 30, 2, 'Vaso mediano', 60.00, 1, 60.00),
(67, 30, 2, 'Vaso mediano', 60.00, 1, 60.00),
(68, 30, 24, 'Pay extra', 20.00, 1, 20.00),
(69, 30, 24, 'Pay extra', 20.00, 1, 20.00),
(70, 31, 3, 'Medio litro', 80.00, 1, 80.00),
(71, 32, 6, 'Fresas especiales Ferrero', 120.00, 1, 120.00),
(72, 32, 18, 'Nachos con queso', 40.00, 1, 40.00),
(73, 32, 18, 'Nachos con queso', 40.00, 1, 40.00),
(74, 33, 21, 'Vaso loco', 60.00, 1, 60.00),
(75, 33, 16, 'Tostilocos', 50.00, 1, 50.00),
(76, 33, 16, 'Tostilocos', 50.00, 1, 50.00),
(77, 33, 18, 'Nachos con queso', 40.00, 1, 40.00),
(78, 34, 1, 'Vaso chico', 45.00, 1, 45.00),
(79, 34, 4, 'Litro', 150.00, 1, 150.00),
(80, 35, 1, 'Vaso chico', 45.00, 1, 45.00),
(81, 36, 1, 'Vaso chico', 45.00, 1, 45.00),
(82, 37, 1, 'Vaso chico', 45.00, 1, 45.00),
(83, 38, 1, 'Vaso chico', 45.00, 1, 45.00),
(84, 39, 1, 'Vaso chico', 45.00, 1, 45.00),
(85, 40, 1, 'Vaso chico', 45.00, 1, 45.00),
(86, 41, 1, 'Vaso chico', 45.00, 1, 45.00),
(87, 42, 1, 'Vaso chico', 45.00, 1, 45.00),
(88, 43, 1, 'Vaso chico', 45.00, 1, 45.00),
(89, 44, 1, 'Vaso chico', 45.00, 1, 45.00),
(90, 45, 1, 'Vaso chico', 45.00, 1, 45.00),
(91, 46, 1, 'Vaso chico', 45.00, 1, 45.00),
(92, 47, 1, 'Vaso chico', 45.00, 1, 45.00),
(93, 48, 1, 'Vaso chico', 45.00, 1, 45.00),
(94, 49, 1, 'Vaso chico', 45.00, 1, 45.00),
(95, 50, 1, 'Vaso chico', 45.00, 1, 45.00);

-- --------------------------------------------------------

--
-- Table structure for table `venta_toppings`
--

CREATE TABLE `venta_toppings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `venta_detalle_id` bigint(20) UNSIGNED NOT NULL,
  `topping_id` int(10) UNSIGNED DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL,
  `es_gratis` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `venta_toppings`
--

INSERT INTO `venta_toppings` (`id`, `venta_detalle_id`, `topping_id`, `nombre`, `precio`, `cantidad`, `subtotal`, `es_gratis`) VALUES
(5, 8, 1, 'Oreo', 10.00, 1, 0.00, 1),
(6, 8, 2, 'Nuez', 10.00, 1, 0.00, 1),
(7, 8, 3, 'Bombones', 10.00, 1, 10.00, 0),
(8, 9, 1, 'Oreo', 10.00, 1, 10.00, 0),
(9, 11, 3, 'Bombones', 10.00, 1, 0.00, 1),
(10, 11, 2, 'Nuez', 10.00, 1, 0.00, 1),
(11, 11, 7, 'Nutella', 15.00, 1, 15.00, 0),
(12, 17, 2, 'Nuez', 0.00, 1, 0.00, 1),
(13, 17, 5, 'Lunetas', 0.00, 1, 0.00, 1),
(14, 25, 6, 'Almendra', 0.00, 1, 0.00, 0),
(15, 26, 3, 'Bombones', 0.00, 1, 0.00, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `caja_cortes`
--
ALTER TABLE `caja_cortes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_corte_fecha` (`fecha_corte`);

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
  ADD UNIQUE KEY `uk_configuracion_clave` (`clave`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_productos_categoria` (`categoria_id`),
  ADD KEY `idx_productos_activo` (`activo`);

--
-- Indexes for table `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto_imagenes_producto` (`producto_id`),
  ADD KEY `idx_producto_imagenes_orden` (`producto_id`,`orden`);

--
-- Indexes for table `producto_toppings`
--
ALTER TABLE `producto_toppings`
  ADD PRIMARY KEY (`producto_id`,`topping_id`),
  ADD KEY `idx_pt_topping` (`topping_id`);

--
-- Indexes for table `toppings`
--
ALTER TABLE `toppings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_toppings_activo` (`activo`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_usuarios_usuario` (`usuario`);

--
-- Indexes for table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ventas_folio` (`folio`),
  ADD KEY `idx_ventas_fecha` (`fecha`);

--
-- Indexes for table `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detalle_venta` (`venta_id`),
  ADD KEY `idx_detalle_producto` (`producto_id`);

--
-- Indexes for table `venta_toppings`
--
ALTER TABLE `venta_toppings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_venta_toppings_detalle` (`venta_detalle_id`),
  ADD KEY `idx_venta_toppings_topping` (`topping_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `caja_cortes`
--
ALTER TABLE `caja_cortes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `toppings`
--
ALTER TABLE `toppings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `venta_detalle`
--
ALTER TABLE `venta_detalle`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `venta_toppings`
--
ALTER TABLE `venta_toppings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD CONSTRAINT `fk_producto_imagenes_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `producto_toppings`
--
ALTER TABLE `producto_toppings`
  ADD CONSTRAINT `fk_pt_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pt_topping` FOREIGN KEY (`topping_id`) REFERENCES `toppings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `venta_toppings`
--
ALTER TABLE `venta_toppings`
  ADD CONSTRAINT `fk_venta_toppings_detalle` FOREIGN KEY (`venta_detalle_id`) REFERENCES `venta_detalle` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_venta_toppings_topping` FOREIGN KEY (`topping_id`) REFERENCES `toppings` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
