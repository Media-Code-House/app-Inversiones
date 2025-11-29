-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2025 at 09:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema_lotes`
--

-- --------------------------------------------------------

--
-- Table structure for table `amortizaciones`
--

CREATE TABLE `amortizaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `lote_id` int(10) UNSIGNED NOT NULL,
  `numero_cuota` int(10) UNSIGNED NOT NULL,
  `valor_cuota` decimal(15,2) NOT NULL,
  `capital` decimal(15,2) DEFAULT 0.00,
  `interes` decimal(15,2) DEFAULT 0.00,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `valor_pagado` decimal(15,2) DEFAULT 0.00,
  `saldo_pendiente` decimal(15,2) GENERATED ALWAYS AS (`valor_cuota` - `valor_pagado`) STORED,
  `fecha_vencimiento` date NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `dias_mora` int(11) DEFAULT 0,
  `estado` enum('pendiente','pagada','cancelada') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `amortizaciones`
--

INSERT INTO `amortizaciones` (`id`, `lote_id`, `numero_cuota`, `valor_cuota`, `capital`, `interes`, `saldo`, `valor_pagado`, `fecha_vencimiento`, `fecha_pago`, `dias_mora`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 1250000.00, 0.00, 0.00, 0.00, 1250000.00, '2024-03-10', '2024-03-10', 0, 'pagada', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(2, 6, 2, 1250000.00, 0.00, 0.00, 0.00, 1250000.00, '2024-04-10', '2024-04-10', 0, 'pagada', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(3, 6, 3, 1250000.00, 0.00, 0.00, 0.00, 1250000.00, '2024-05-10', '2024-05-10', 0, 'pagada', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(4, 6, 4, 1250000.00, 0.00, 0.00, 0.00, 0.00, '2024-06-10', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(5, 6, 5, 1250000.00, 0.00, 0.00, 0.00, 0.00, '2024-07-10', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(6, 6, 6, 1250000.00, 0.00, 0.00, 0.00, 0.00, '2025-12-04', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(7, 6, 7, 1250000.00, 0.00, 0.00, 0.00, 0.00, '2025-12-09', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(8, 6, 8, 1250000.00, 0.00, 0.00, 0.00, 0.00, '2026-01-08', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(9, 6, 9, 1250000.00, 0.00, 0.00, 0.00, 0.00, '2026-02-07', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(10, 6, 10, 1250000.00, 0.00, 0.00, 0.00, 0.00, '2026-03-09', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(11, 7, 1, 1500000.00, 0.00, 0.00, 0.00, 1500000.00, '2024-04-15', '2024-04-15', 0, 'pagada', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(12, 7, 2, 1500000.00, 0.00, 0.00, 0.00, 1500000.00, '2024-05-15', '2024-05-15', 0, 'pagada', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(13, 7, 3, 1500000.00, 0.00, 0.00, 0.00, 1500000.00, '2024-06-15', '2024-06-15', 0, 'pagada', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(14, 7, 4, 1500000.00, 0.00, 0.00, 0.00, 1500000.00, '2024-07-15', '2024-07-15', 0, 'pagada', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(15, 7, 5, 1500000.00, 0.00, 0.00, 0.00, 1500000.00, '2024-08-15', '2024-08-15', 0, 'pagada', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(16, 7, 6, 1500000.00, 0.00, 0.00, 0.00, 0.00, '2025-12-07', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(17, 7, 7, 1500000.00, 0.00, 0.00, 0.00, 0.00, '2026-01-06', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(18, 13, 1, 2000000.00, 0.00, 0.00, 0.00, 2000000.00, '2024-05-01', '2024-05-01', 0, 'pagada', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(19, 13, 2, 2000000.00, 0.00, 0.00, 0.00, 2000000.00, '2024-06-01', '2024-06-01', 0, 'pagada', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(20, 13, 3, 2000000.00, 0.00, 0.00, 0.00, 2000000.00, '2024-07-01', '2024-07-01', 0, 'pagada', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(21, 13, 4, 2000000.00, 0.00, 0.00, 0.00, 2000000.00, '2024-08-01', '2024-08-01', 0, 'pagada', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(22, 13, 5, 2000000.00, 0.00, 0.00, 0.00, 0.00, '2025-12-02', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(23, 13, 6, 2000000.00, 0.00, 0.00, 0.00, 0.00, '2026-01-01', NULL, 0, 'pendiente', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25');

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo_documento` enum('CC','NIT','CE','pasaporte') DEFAULT 'CC',
  `numero_documento` varchar(50) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `tipo_documento`, `numero_documento`, `nombre`, `telefono`, `email`, `direccion`, `ciudad`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 'CC', '1234567890', 'Juan Carlos Pérez Martínez', '3001234567', 'jperez@email.com', NULL, 'Montería', NULL, '2025-11-29 05:37:23', '2025-11-29 05:37:23'),
(2, 'CC', '9876543210', 'María Fernanda García López', '3109876543', 'mgarcia@email.com', NULL, 'Cereté', NULL, '2025-11-29 05:37:23', '2025-11-29 05:37:23'),
(3, 'NIT', '900123456-1', 'Inversiones S.A.S.', '3201122334', 'contacto@inversiones.com', NULL, 'Montería', NULL, '2025-11-29 05:37:23', '2025-11-29 05:37:23'),
(4, 'CC', '5555666677', 'Pedro Antonio Rojas Díaz', '3145556666', 'projas@email.com', NULL, 'Lorica', NULL, '2025-11-29 05:37:23', '2025-11-29 05:37:23'),
(5, 'CC', '1122334455', 'Ana Lucía Morales Herrera', '3187778888', 'amorales@email.com', NULL, 'Montería', NULL, '2025-11-29 05:37:23', '2025-11-29 05:37:23');

-- --------------------------------------------------------

--
-- Table structure for table `lotes`
--

CREATE TABLE `lotes` (
  `id` int(10) UNSIGNED NOT NULL,
  `proyecto_id` int(10) UNSIGNED NOT NULL,
  `codigo_lote` varchar(50) NOT NULL,
  `manzana` varchar(20) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `area_m2` decimal(10,2) DEFAULT NULL,
  `precio_lista` decimal(15,2) NOT NULL,
  `precio_venta` decimal(15,2) DEFAULT NULL,
  `cuota_inicial` decimal(15,2) DEFAULT NULL,
  `monto_financiado` decimal(15,2) DEFAULT NULL,
  `tasa_interes` decimal(5,2) DEFAULT NULL COMMENT 'Tasa anual %',
  `numero_cuotas` int(11) DEFAULT NULL,
  `fecha_inicio_amortizacion` date DEFAULT NULL,
  `fecha_venta` date DEFAULT NULL,
  `tipo_pago` enum('contado','amortizacion') DEFAULT 'contado' COMMENT 'Tipo de pago: contado al 100% o con plan de amortización',
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `vendedor_id` int(10) UNSIGNED DEFAULT NULL,
  `estado` enum('disponible','vendido','reservado','bloqueado') DEFAULT 'disponible',
  `observaciones` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lotes`
--

INSERT INTO `lotes` (`id`, `proyecto_id`, `codigo_lote`, `manzana`, `ubicacion`, `area_m2`, `precio_lista`, `precio_venta`, `cuota_inicial`, `monto_financiado`, `tasa_interes`, `numero_cuotas`, `fecha_inicio_amortizacion`, `fecha_venta`, `tipo_pago`, `cliente_id`, `vendedor_id`, `estado`, `observaciones`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 1, 'A-01', 'A', NULL, 200.00, 45000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'disponible', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(2, 1, 'A-02', NULL, NULL, NULL, 47000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'vendido', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 08:23:20'),
(3, 1, 'A-03', 'A', NULL, 205.00, 46000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'disponible', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(4, 1, 'A-04', 'A', NULL, 220.00, 50000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'disponible', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(5, 1, 'A-05', 'A', NULL, 195.00, 44000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'disponible', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(6, 1, 'B-01', 'B', NULL, 180.00, 40000000.00, 39000000.00, NULL, NULL, NULL, NULL, NULL, '2024-02-10', 'contado', 1, NULL, 'vendido', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(7, 1, 'B-02', 'B', NULL, 185.00, 41000000.00, 41000000.00, NULL, NULL, NULL, NULL, NULL, '2024-03-15', 'contado', 2, NULL, 'vendido', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(8, 1, 'B-03', 'B', NULL, 190.00, 42000000.00, 42500000.00, NULL, NULL, NULL, NULL, NULL, '2024-04-20', 'contado', 4, NULL, 'vendido', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(9, 1, 'C-01', 'C', NULL, 200.00, 45000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'reservado', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(10, 1, 'C-02', 'C', NULL, 210.00, 47000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'reservado', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(11, 2, 'L-01', NULL, NULL, 500.00, 85000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'disponible', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(12, 2, 'L-02', NULL, NULL, 550.00, 92000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'disponible', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(13, 2, 'L-03', NULL, NULL, 600.00, 100000000.00, 98000000.00, NULL, NULL, NULL, NULL, NULL, '2024-04-01', 'contado', 3, NULL, 'vendido', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(14, 2, 'L-04', NULL, NULL, 520.00, 88000000.00, 87000000.00, NULL, NULL, NULL, NULL, NULL, '2024-05-10', 'contado', 5, NULL, 'vendido', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(15, 2, 'L-05', NULL, NULL, 480.00, 82000000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'disponible', NULL, NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(16, 1, 'nn', NULL, NULL, NULL, 123.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'contado', NULL, NULL, 'disponible', NULL, NULL, '2025-11-29 07:18:21', '2025-11-29 07:18:21');

--
-- Triggers `lotes`
--
DELIMITER $$
CREATE TRIGGER `after_lote_delete` AFTER DELETE ON `lotes` FOR EACH ROW BEGIN
    UPDATE proyectos 
    SET total_lotes = (SELECT COUNT(*) FROM lotes WHERE proyecto_id = OLD.proyecto_id)
    WHERE id = OLD.proyecto_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_lote_insert` AFTER INSERT ON `lotes` FOR EACH ROW BEGIN
    UPDATE proyectos 
    SET total_lotes = (SELECT COUNT(*) FROM lotes WHERE proyecto_id = NEW.proyecto_id)
    WHERE id = NEW.proyecto_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pagos`
--

CREATE TABLE `pagos` (
  `id` int(10) UNSIGNED NOT NULL,
  `amortizacion_id` int(10) UNSIGNED NOT NULL,
  `valor_pagado` decimal(15,2) NOT NULL,
  `metodo_pago` enum('efectivo','transferencia','cheque','tarjeta','otro') DEFAULT 'efectivo',
  `fecha_pago` date NOT NULL,
  `numero_recibo` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pagos`
--

INSERT INTO `pagos` (`id`, `amortizacion_id`, `valor_pagado`, `metodo_pago`, `fecha_pago`, `numero_recibo`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 1250000.00, 'transferencia', '2024-03-10', 'REC-001', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(2, 2, 1250000.00, 'efectivo', '2024-04-08', 'REC-002', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(3, 3, 1250000.00, 'cheque', '2024-05-10', 'REC-003', NULL, '2025-11-29 05:37:24', '2025-11-29 05:37:24'),
(4, 11, 1500000.00, 'transferencia', '2024-04-15', 'REC-004', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(5, 12, 1500000.00, 'transferencia', '2024-05-14', 'REC-005', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(6, 13, 1500000.00, 'transferencia', '2024-06-15', 'REC-006', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(7, 14, 1500000.00, 'efectivo', '2024-07-13', 'REC-007', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(8, 15, 1500000.00, 'tarjeta', '2024-08-15', 'REC-008', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(9, 18, 2000000.00, 'transferencia', '2024-05-01', 'REC-009', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(10, 19, 2000000.00, 'transferencia', '2024-06-01', 'REC-010', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(11, 20, 2000000.00, 'transferencia', '2024-07-01', 'REC-011', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25'),
(12, 21, 2000000.00, 'transferencia', '2024-08-01', 'REC-012', NULL, '2025-11-29 05:37:25', '2025-11-29 05:37:25');

-- --------------------------------------------------------

--
-- Table structure for table `proyectos`
--

CREATE TABLE `proyectos` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `total_lotes` int(10) UNSIGNED DEFAULT 0,
  `estado` enum('activo','completado','pausado','cancelado') DEFAULT 'activo',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proyectos`
--

INSERT INTO `proyectos` (`id`, `codigo`, `nombre`, `ubicacion`, `descripcion`, `total_lotes`, `estado`, `fecha_inicio`, `fecha_finalizacion`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 'PRY-001', 'Urbanización El Paraíso', 'Carrera 15 #45-32, Montería', 'Proyecto residencial de 50 lotes con servicios públicos', 11, 'activo', '2024-01-15', NULL, NULL, '2025-11-29 05:37:23', '2025-11-29 07:18:21'),
(2, 'PRY-002', 'Parcelación Villa Verde', 'Vía Planeta Rica Km 12', 'Parcelación campestre con lotes desde 500m²', 5, 'activo', '2024-03-01', NULL, NULL, '2025-11-29 05:37:23', '2025-11-29 05:37:24'),
(3, 'PRY-003', 'Conjunto Cerrado Los Robles', 'Avenida Circunvalar Norte', 'Conjunto cerrado con zonas comunes y piscina', 0, 'pausado', '2023-11-20', NULL, NULL, '2025-11-29 05:37:23', '2025-11-29 05:37:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` enum('administrador','vendedor','consulta') DEFAULT 'consulta',
  `activo` tinyint(1) DEFAULT 1,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expira` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `nombre`, `rol`, `activo`, `reset_token`, `reset_token_expira`, `created_at`, `updated_at`) VALUES
(1, 'admin@inversiones.com', '$2y$12$wOs5wZG9LzTHhdAOENkWVe8d7OGgPlTK1qqUt93gE6iA.Sd5uLCiy', 'Administrador', 'administrador', 1, NULL, NULL, '2025-11-29 05:39:09', '2025-11-29 06:13:36');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vista_proyectos_resumen`
-- (See below for the actual view)
--
CREATE TABLE `vista_proyectos_resumen` (
`id` int(10) unsigned
,`codigo` varchar(50)
,`nombre` varchar(200)
,`ubicacion` varchar(255)
,`estado` enum('activo','completado','pausado','cancelado')
,`total_lotes` int(10) unsigned
,`lotes_disponibles` bigint(21)
,`lotes_vendidos` bigint(21)
,`lotes_reservados` bigint(21)
,`lotes_bloqueados` bigint(21)
,`valor_inventario` decimal(37,2)
,`valor_ventas` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Structure for view `vista_proyectos_resumen`
--
DROP TABLE IF EXISTS `vista_proyectos_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_proyectos_resumen`  AS SELECT `p`.`id` AS `id`, `p`.`codigo` AS `codigo`, `p`.`nombre` AS `nombre`, `p`.`ubicacion` AS `ubicacion`, `p`.`estado` AS `estado`, `p`.`total_lotes` AS `total_lotes`, count(distinct case when `l`.`estado` = 'disponible' then `l`.`id` end) AS `lotes_disponibles`, count(distinct case when `l`.`estado` = 'vendido' then `l`.`id` end) AS `lotes_vendidos`, count(distinct case when `l`.`estado` = 'reservado' then `l`.`id` end) AS `lotes_reservados`, count(distinct case when `l`.`estado` = 'bloqueado' then `l`.`id` end) AS `lotes_bloqueados`, sum(case when `l`.`estado` in ('disponible','reservado') then `l`.`precio_lista` else 0 end) AS `valor_inventario`, sum(case when `l`.`estado` = 'vendido' then coalesce(`l`.`precio_venta`,`l`.`precio_lista`) else 0 end) AS `valor_ventas` FROM (`proyectos` `p` left join `lotes` `l` on(`p`.`id` = `l`.`proyecto_id`)) GROUP BY `p`.`id`, `p`.`codigo`, `p`.`nombre`, `p`.`ubicacion`, `p`.`estado`, `p`.`total_lotes` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amortizaciones`
--
ALTER TABLE `amortizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lote` (`lote_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_vencimiento` (`fecha_vencimiento`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD KEY `idx_documento` (`tipo_documento`,`numero_documento`);

--
-- Indexes for table `lotes`
--
ALTER TABLE `lotes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lote_proyecto` (`proyecto_id`,`codigo_lote`),
  ADD KEY `idx_proyecto` (`proyecto_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_vendedor` (`vendedor_id`);

--
-- Indexes for table `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_amortizacion` (`amortizacion_id`),
  ADD KEY `idx_fecha_pago` (`fecha_pago`);

--
-- Indexes for table `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_codigo` (`codigo`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amortizaciones`
--
ALTER TABLE `amortizaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lotes`
--
ALTER TABLE `lotes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `amortizaciones`
--
ALTER TABLE `amortizaciones`
  ADD CONSTRAINT `amortizaciones_ibfk_1` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`);

--
-- Constraints for table `lotes`
--
ALTER TABLE `lotes`
  ADD CONSTRAINT `fk_lotes_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `lotes_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`),
  ADD CONSTRAINT `lotes_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Constraints for table `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`amortizacion_id`) REFERENCES `amortizaciones` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
