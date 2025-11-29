-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 29, 2025 at 02:36 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u418271893_inversiones`
--

-- --------------------------------------------------------

--
-- Table structure for table `amortizaciones`
--

CREATE TABLE `amortizaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `lote_id` int(10) UNSIGNED NOT NULL,
  `numero_cuota` smallint(5) UNSIGNED NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `estado` enum('pendiente','pagada','cancelada') DEFAULT 'pendiente',
  `valor_cuota` decimal(15,2) NOT NULL,
  `capital` decimal(15,2) NOT NULL DEFAULT 0.00,
  `interes` decimal(15,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(15,2) NOT NULL DEFAULT 0.00,
  `valor_pagado` decimal(15,2) DEFAULT 0.00,
  `saldo_pendiente` decimal(15,2) GENERATED ALWAYS AS (`valor_cuota` - `valor_pagado`) STORED,
  `dias_mora` int(10) UNSIGNED DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `amortizaciones`
--

INSERT INTO `amortizaciones` (`id`, `lote_id`, `numero_cuota`, `fecha_vencimiento`, `fecha_pago`, `estado`, `valor_cuota`, `capital`, `interes`, `saldo`, `valor_pagado`, `dias_mora`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2025-12-29', '2025-11-29', 'pagada', 1977085.83, 1557085.83, 420000.00, 40442914.17, 1977085.83, 0, '', '2025-11-29 10:15:03', '2025-11-29 13:53:29'),
(2, 2, 2, '2026-01-29', NULL, 'pendiente', 1977085.83, 1572656.69, 404429.14, 38870257.47, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(3, 2, 3, '2026-03-01', NULL, 'pendiente', 1977085.83, 1588383.26, 388702.57, 37281874.22, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(4, 2, 4, '2026-03-29', NULL, 'pendiente', 1977085.83, 1604267.09, 372818.74, 35677607.13, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(5, 2, 5, '2026-04-29', NULL, 'pendiente', 1977085.83, 1620309.76, 356776.07, 34057297.36, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(6, 2, 6, '2026-05-29', NULL, 'pendiente', 1977085.83, 1636512.86, 340572.97, 32420784.50, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(7, 2, 7, '2026-06-29', NULL, 'pendiente', 1977085.83, 1652877.99, 324207.85, 30767906.51, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(8, 2, 8, '2026-07-29', NULL, 'pendiente', 1977085.83, 1669406.77, 307679.07, 29098499.75, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(9, 2, 9, '2026-08-29', NULL, 'pendiente', 1977085.83, 1686100.84, 290985.00, 27412398.91, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(10, 2, 10, '2026-09-29', NULL, 'pendiente', 1977085.83, 1702961.84, 274123.99, 25709437.07, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(11, 2, 11, '2026-10-29', NULL, 'pendiente', 1977085.83, 1719991.46, 257094.37, 23989445.60, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(12, 2, 12, '2026-11-29', NULL, 'pendiente', 1977085.83, 1737191.38, 239894.46, 22252254.23, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(13, 2, 13, '2026-12-29', NULL, 'pendiente', 1977085.83, 1754563.29, 222522.54, 20497690.94, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(14, 2, 14, '2027-01-29', NULL, 'pendiente', 1977085.83, 1772108.92, 204976.91, 18725582.01, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(15, 2, 15, '2027-03-01', NULL, 'pendiente', 1977085.83, 1789830.01, 187255.82, 16935752.00, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(16, 2, 16, '2027-03-29', NULL, 'pendiente', 1977085.83, 1807728.31, 169357.52, 15128023.68, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(17, 2, 17, '2027-04-29', NULL, 'pendiente', 1977085.83, 1825805.60, 151280.24, 13302218.09, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(18, 2, 18, '2027-05-29', NULL, 'pendiente', 1977085.83, 1844063.65, 133022.18, 11458154.44, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(19, 2, 19, '2027-06-29', NULL, 'pendiente', 1977085.83, 1862504.29, 114581.54, 9595650.15, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(20, 2, 20, '2027-07-29', NULL, 'pendiente', 1977085.83, 1881129.33, 95956.50, 7714520.81, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(21, 2, 21, '2027-08-29', NULL, 'pendiente', 1977085.83, 1899940.63, 77145.21, 5814580.19, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(22, 2, 22, '2027-09-29', NULL, 'pendiente', 1977085.83, 1918940.03, 58145.80, 3895640.16, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(23, 2, 23, '2027-10-29', NULL, 'pendiente', 1977085.83, 1938129.43, 38956.40, 1957510.73, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
(24, 2, 24, '2027-11-29', NULL, 'pendiente', 1977085.83, 1957510.73, 19575.11, 0.00, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03');

--
-- Triggers `amortizaciones`
--
DELIMITER $$
CREATE TRIGGER `before_amortizacion_update` BEFORE UPDATE ON `amortizaciones` FOR EACH ROW BEGIN
    -- Actualizar días de mora cuando el estado cambia a mora
    IF NEW.estado = 'mora' THEN
        SET NEW.dias_mora = DATEDIFF(CURDATE(), NEW.fecha_vencimiento);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo_documento` enum('CC','NIT','CE','pasaporte') DEFAULT 'CC',
  `numero_documento` varchar(50) NOT NULL COMMENT 'Documento de identidad',
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `tipo_documento`, `numero_documento`, `nombre`, `telefono`, `email`, `direccion`, `ciudad`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 'CC', '123456', 'juan', '3167495848', '', '2', '2', '2', '2025-11-29 08:42:08', '2025-11-29 08:42:08'),
(2, 'CC', '76767', 'sfsef', '424', 'trujillosantiago3445@gmail.com', 'sfe', 'sdfsf', '', '2025-11-29 08:47:48', '2025-11-29 09:08:50');

-- --------------------------------------------------------

--
-- Table structure for table `comisiones`
--

CREATE TABLE `comisiones` (
  `id` int(10) UNSIGNED NOT NULL,
  `lote_id` int(10) UNSIGNED NOT NULL COMMENT 'Lote vendido',
  `vendedor_id` int(10) UNSIGNED NOT NULL COMMENT 'Vendedor que realizó la venta',
  `valor_venta` decimal(15,2) NOT NULL COMMENT 'Valor total de la venta del lote',
  `porcentaje_comision` decimal(5,2) NOT NULL COMMENT 'Porcentaje aplicado en esta venta',
  `valor_comision` decimal(15,2) NOT NULL COMMENT 'Valor calculado de la comisión',
  `estado` enum('pendiente','pagada','pagada_parcial','cancelada') DEFAULT 'pendiente',
  `fecha_venta` date NOT NULL COMMENT 'Fecha de la venta del lote',
  `fecha_calculo` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de cálculo de la comisión',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial_comisiones`
--

CREATE TABLE `historial_comisiones` (
  `id` int(10) UNSIGNED NOT NULL,
  `vendedor_id` int(10) UNSIGNED NOT NULL,
  `porcentaje_anterior` decimal(5,2) NOT NULL,
  `porcentaje_nuevo` decimal(5,2) NOT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_modifico_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lotes`
--

CREATE TABLE `lotes` (
  `id` int(10) UNSIGNED NOT NULL,
  `proyecto_id` int(10) UNSIGNED NOT NULL,
  `codigo_lote` varchar(20) NOT NULL COMMENT 'Código del lote dentro del proyecto',
  `manzana` varchar(10) DEFAULT NULL COMMENT 'Manzana o sector',
  `ubicacion` varchar(255) DEFAULT NULL,
  `area_m2` decimal(10,2) NOT NULL COMMENT 'Área en metros cuadrados',
  `precio_lista` decimal(15,2) NOT NULL COMMENT 'Precio de lista del lote',
  `precio_venta` decimal(15,2) DEFAULT NULL COMMENT 'Precio real de venta (si difiere)',
  `cuota_inicial` decimal(15,2) DEFAULT NULL,
  `monto_financiado` decimal(15,2) DEFAULT NULL,
  `tasa_interes` decimal(5,2) DEFAULT NULL,
  `numero_cuotas` int(11) DEFAULT NULL,
  `fecha_inicio_amortizacion` date DEFAULT NULL,
  `estado` enum('disponible','reservado','vendido','bloqueado') NOT NULL DEFAULT 'disponible',
  `cliente_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Cliente dueño (si está vendido)',
  `vendedor_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha_venta` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lotes`
--

INSERT INTO `lotes` (`id`, `proyecto_id`, `codigo_lote`, `manzana`, `ubicacion`, `area_m2`, `precio_lista`, `precio_venta`, `cuota_inicial`, `monto_financiado`, `tasa_interes`, `numero_cuotas`, `fecha_inicio_amortizacion`, `estado`, `cliente_id`, `vendedor_id`, `fecha_venta`, `observaciones`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 1, '666', NULL, NULL, 666.00, 5000000.00, NULL, NULL, NULL, NULL, NULL, NULL, 'disponible', NULL, NULL, NULL, NULL, NULL, '2025-11-29 07:25:02', '2025-11-29 07:25:02'),
(2, 3, '444', NULL, NULL, 300.00, 60000000.00, NULL, 18000000.00, 42000000.00, 12.00, 24, '2025-11-29', 'vendido', NULL, NULL, NULL, NULL, NULL, '2025-11-29 07:26:16', '2025-11-29 10:15:03'),
(3, 2, '42424', NULL, NULL, 0.00, 45000000.00, NULL, NULL, NULL, NULL, NULL, NULL, 'vendido', NULL, NULL, NULL, NULL, NULL, '2025-11-29 07:27:27', '2025-11-29 09:13:17');

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
DELIMITER $$
CREATE TRIGGER `after_lote_vendido` AFTER UPDATE ON `lotes` FOR EACH ROW BEGIN
    DECLARE v_vendedor_id INT;
    DECLARE v_porcentaje DECIMAL(5,2);
    DECLARE v_valor_venta DECIMAL(15,2);
    DECLARE v_valor_comision DECIMAL(15,2);
    
    -- Si el lote cambió a vendido Y tiene vendedor asignado (user_id)
    IF NEW.estado = 'vendido' AND OLD.estado != 'vendido' AND NEW.vendedor_id IS NOT NULL THEN
        
        -- Obtener el ID del vendedor desde la tabla vendedores usando el user_id
        SELECT id, porcentaje_comision_default 
        INTO v_vendedor_id, v_porcentaje
        FROM vendedores 
        WHERE user_id = NEW.vendedor_id 
        AND estado = 'activo'
        LIMIT 1;
        
        -- Solo proceder si encontramos un vendedor activo
        IF v_vendedor_id IS NOT NULL THEN
            
            -- Calcular valor de la venta (usar precio_venta si existe, sino precio_lista)
            SET v_valor_venta = COALESCE(NEW.precio_venta, NEW.precio_lista);
            
            -- Calcular comisión
            SET v_valor_comision = (v_valor_venta * v_porcentaje / 100);
            
            -- Insertar registro de comisión
            INSERT INTO comisiones (
                lote_id, 
                vendedor_id, 
                valor_venta, 
                porcentaje_comision, 
                valor_comision, 
                estado, 
                fecha_venta
            ) VALUES (
                NEW.id,
                v_vendedor_id,
                v_valor_venta,
                v_porcentaje,
                v_valor_comision,
                'pendiente',
                COALESCE(NEW.fecha_venta, CURDATE())
            );
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `metas_vendedores`
--

CREATE TABLE `metas_vendedores` (
  `id` int(10) UNSIGNED NOT NULL,
  `vendedor_id` int(10) UNSIGNED NOT NULL,
  `periodo_tipo` enum('mensual','trimestral','semestral','anual') NOT NULL DEFAULT 'mensual',
  `periodo_inicio` date NOT NULL COMMENT 'Fecha inicio del periodo',
  `periodo_fin` date NOT NULL COMMENT 'Fecha fin del periodo',
  `meta_ventas` int(11) NOT NULL DEFAULT 0 COMMENT 'Número de lotes a vender',
  `meta_valor` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor en pesos a vender',
  `ventas_realizadas` int(11) DEFAULT 0 COMMENT 'Lotes vendidos en el periodo',
  `valor_vendido` decimal(15,2) DEFAULT 0.00 COMMENT 'Valor vendido en el periodo',
  `porcentaje_cumplimiento` decimal(5,2) GENERATED ALWAYS AS (case when `meta_ventas` > 0 then `ventas_realizadas` / `meta_ventas` * 100 else 0 end) STORED COMMENT 'Cumplimiento calculado automáticamente',
  `estado` enum('activa','completada','vencida','cancelada') DEFAULT 'activa',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pagos`
--

CREATE TABLE `pagos` (
  `id` int(10) UNSIGNED NOT NULL,
  `amortizacion_id` int(10) UNSIGNED NOT NULL,
  `fecha_pago` date NOT NULL,
  `valor_pagado` decimal(15,2) NOT NULL,
  `metodo_pago` enum('efectivo','transferencia','cheque','tarjeta','otro') NOT NULL DEFAULT 'efectivo',
  `numero_recibo` varchar(50) DEFAULT NULL COMMENT 'Número de recibo o comprobante',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pagos`
--

INSERT INTO `pagos` (`id`, `amortizacion_id`, `fecha_pago`, `valor_pagado`, `metodo_pago`, `numero_recibo`, `observaciones`, `created_at`, `updated_at`) VALUES
(8, 1, '2025-11-29', 1977085.83, 'transferencia', 'nn', '', '2025-11-29 13:53:29', '2025-11-29 13:53:29');

-- --------------------------------------------------------

--
-- Table structure for table `pagos_comisiones`
--

CREATE TABLE `pagos_comisiones` (
  `id` int(10) UNSIGNED NOT NULL,
  `comision_id` int(10) UNSIGNED NOT NULL COMMENT 'Comisión que se está pagando',
  `vendedor_id` int(10) UNSIGNED NOT NULL COMMENT 'Vendedor receptor del pago',
  `valor_pagado` decimal(15,2) NOT NULL COMMENT 'Valor del pago realizado',
  `fecha_pago` date NOT NULL COMMENT 'Fecha del pago',
  `metodo_pago` enum('efectivo','transferencia','cheque','consignacion','otro') NOT NULL DEFAULT 'transferencia',
  `numero_comprobante` varchar(100) DEFAULT NULL COMMENT 'Número de comprobante/transacción',
  `banco` varchar(100) DEFAULT NULL COMMENT 'Banco utilizado para el pago',
  `referencia` varchar(255) DEFAULT NULL COMMENT 'Referencia adicional del pago',
  `observaciones` text DEFAULT NULL,
  `usuario_registro_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Usuario que registró el pago',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `pagos_comisiones`
--
DELIMITER $$
CREATE TRIGGER `after_pago_comision_insert` AFTER INSERT ON `pagos_comisiones` FOR EACH ROW BEGIN
    DECLARE v_total_pagado DECIMAL(15,2);
    DECLARE v_valor_comision DECIMAL(15,2);
    
    -- Obtener el valor de la comisión
    SELECT valor_comision INTO v_valor_comision
    FROM comisiones
    WHERE id = NEW.comision_id;
    
    -- Calcular total pagado para esta comisión
    SELECT COALESCE(SUM(valor_pagado), 0) INTO v_total_pagado
    FROM pagos_comisiones
    WHERE comision_id = NEW.comision_id;
    
    -- Actualizar estado de la comisión según lo pagado
    IF v_total_pagado >= v_valor_comision THEN
        UPDATE comisiones 
        SET estado = 'pagada' 
        WHERE id = NEW.comision_id;
    ELSEIF v_total_pagado > 0 THEN
        UPDATE comisiones 
        SET estado = 'pagada_parcial' 
        WHERE id = NEW.comision_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `proyectos`
--

CREATE TABLE `proyectos` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(20) NOT NULL COMMENT 'Código único del proyecto',
  `nombre` varchar(150) NOT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','completado','pausado','cancelado') DEFAULT 'activo',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `total_lotes` int(10) UNSIGNED DEFAULT 0 COMMENT 'Total de lotes en el proyecto',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proyectos`
--

INSERT INTO `proyectos` (`id`, `codigo`, `nombre`, `ubicacion`, `descripcion`, `estado`, `fecha_inicio`, `fecha_finalizacion`, `total_lotes`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 'PRY-001', 'Urbanización El Paraíso', 'Carrera 15 #45-32, Montería  gseg', NULL, 'activo', '2024-01-15', NULL, 1, 'gsergseg', '2025-11-29 05:35:02', '2025-11-29 07:36:38'),
(2, 'PRY-002', 'Parcelación Villa Verde', 'Vía Planeta Rica Km 12', 'Parcelación campestre con lotes desde 500m²', 'activo', '2024-03-01', NULL, 1, NULL, '2025-11-29 05:35:02', '2025-11-29 07:27:27'),
(3, 'PRY-003', 'Conjunto Cerrado Los Robles', 'Avenida Circunvalar Norte', NULL, 'activo', '2023-11-20', NULL, 1, '', '2025-11-29 05:35:02', '2025-11-29 07:41:48');

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
  `reset_token` varchar(64) DEFAULT NULL COMMENT 'Token para recuperación de contraseña',
  `reset_token_expira` datetime DEFAULT NULL COMMENT 'Fecha de expiración del token',
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Estado del usuario',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `nombre`, `rol`, `reset_token`, `reset_token_expira`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'admin@sistema.com', '$2y$12$wOs5wZG9LzTHhdAOENkWVe8d7OGgPlTK1qqUt93gE6iA.Sd5uLCiy', 'Administrador', 'administrador', NULL, NULL, 1, '2025-11-29 05:15:26', '2025-11-29 10:03:21');

-- --------------------------------------------------------

--
-- Table structure for table `vendedores`
--

CREATE TABLE `vendedores` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'Relación con tabla users',
  `codigo_vendedor` varchar(20) NOT NULL COMMENT 'Código único del vendedor',
  `tipo_documento` enum('CC','NIT','CE','pasaporte') DEFAULT 'CC',
  `numero_documento` varchar(50) NOT NULL COMMENT 'Documento de identidad',
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL COMMENT 'Fecha de ingreso a la empresa',
  `fecha_salida` date DEFAULT NULL COMMENT 'Fecha de retiro (si aplica)',
  `tipo_contrato` enum('indefinido','fijo','prestacion_servicios','freelance') DEFAULT 'indefinido',
  `porcentaje_comision_default` decimal(5,2) NOT NULL DEFAULT 3.00 COMMENT 'Porcentaje por defecto',
  `banco` varchar(100) DEFAULT NULL COMMENT 'Banco para pagos',
  `tipo_cuenta` enum('ahorros','corriente') DEFAULT NULL,
  `numero_cuenta` varchar(50) DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido') DEFAULT 'activo',
  `observaciones` text DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL COMMENT 'Ruta de la foto del vendedor',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendedores`
--

INSERT INTO `vendedores` (`id`, `user_id`, `codigo_vendedor`, `tipo_documento`, `numero_documento`, `nombres`, `apellidos`, `telefono`, `celular`, `email`, `direccion`, `ciudad`, `fecha_ingreso`, `fecha_salida`, `tipo_contrato`, `porcentaje_comision_default`, `banco`, `tipo_cuenta`, `numero_cuenta`, `estado`, `observaciones`, `foto_perfil`, `created_at`, `updated_at`) VALUES
(1, 1, 'VEND-0001', 'CC', 'DOC-1', 'Administrador', 'Administrador', NULL, NULL, 'admin@sistema.com', NULL, NULL, '2025-11-29', NULL, 'indefinido', 3.00, NULL, NULL, NULL, 'activo', NULL, NULL, '2025-11-29 14:06:39', '2025-11-29 14:06:39');

--
-- Triggers `vendedores`
--
DELIMITER $$
CREATE TRIGGER `before_vendedor_update_comision` BEFORE UPDATE ON `vendedores` FOR EACH ROW BEGIN
    -- Si cambió el porcentaje de comisión, guardar en historial
    IF OLD.porcentaje_comision_default != NEW.porcentaje_comision_default THEN
        INSERT INTO historial_comisiones (
            vendedor_id,
            porcentaje_anterior,
            porcentaje_nuevo,
            fecha_cambio
        ) VALUES (
            NEW.id,
            OLD.porcentaje_comision_default,
            NEW.porcentaje_comision_default,
            NOW()
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vista_proyectos_resumen`
-- (See below for the actual view)
--
CREATE TABLE `vista_proyectos_resumen` (
`id` int(10) unsigned
,`codigo` varchar(20)
,`nombre` varchar(150)
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
-- Stand-in structure for view `vista_vendedores_resumen`
-- (See below for the actual view)
--
CREATE TABLE `vista_vendedores_resumen` (
`id` int(10) unsigned
,`codigo_vendedor` varchar(20)
,`user_id` int(10) unsigned
,`nombre_completo` varchar(201)
,`email` varchar(150)
,`telefono` varchar(20)
,`celular` varchar(20)
,`fecha_ingreso` date
,`porcentaje_comision_default` decimal(5,2)
,`estado` enum('activo','inactivo','suspendido')
,`rol` enum('administrador','vendedor','consulta')
,`total_lotes_vendidos` bigint(21)
,`valor_total_vendido` decimal(37,2)
,`total_comisiones` bigint(21)
,`total_comisiones_generadas` decimal(37,2)
,`comisiones_pendientes` decimal(37,2)
,`comisiones_pagadas` decimal(37,2)
,`total_pagos_recibidos` bigint(21)
,`total_dinero_recibido` decimal(37,2)
,`fecha_ultima_venta` date
,`fecha_ultima_comision` date
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amortizaciones`
--
ALTER TABLE `amortizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lote` (`lote_id`),
  ADD KEY `idx_fecha_vencimiento` (`fecha_vencimiento`),
  ADD KEY `idx_mora` (`dias_mora`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD UNIQUE KEY `uk_documento` (`numero_documento`),
  ADD KEY `idx_nombre` (`nombre`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `comisiones`
--
ALTER TABLE `comisiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lote` (`lote_id`),
  ADD KEY `idx_vendedor` (`vendedor_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_venta` (`fecha_venta`),
  ADD KEY `idx_vendedor_estado` (`vendedor_id`,`estado`);

--
-- Indexes for table `historial_comisiones`
--
ALTER TABLE `historial_comisiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendedor` (`vendedor_id`),
  ADD KEY `idx_fecha` (`fecha_cambio`),
  ADD KEY `historial_comisiones_ibfk_2` (`usuario_modifico_id`);

--
-- Indexes for table `lotes`
--
ALTER TABLE `lotes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_proyecto_codigo` (`proyecto_id`,`codigo_lote`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_proyecto` (`proyecto_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_fecha_venta` (`fecha_venta`),
  ADD KEY `idx_vendedor` (`vendedor_id`);

--
-- Indexes for table `metas_vendedores`
--
ALTER TABLE `metas_vendedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendedor` (`vendedor_id`),
  ADD KEY `idx_periodo` (`periodo_inicio`,`periodo_fin`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indexes for table `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_amortizacion` (`amortizacion_id`),
  ADD KEY `idx_fecha_pago` (`fecha_pago`),
  ADD KEY `idx_recibo` (`numero_recibo`);

--
-- Indexes for table `pagos_comisiones`
--
ALTER TABLE `pagos_comisiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comision` (`comision_id`),
  ADD KEY `idx_vendedor` (`vendedor_id`),
  ADD KEY `idx_fecha_pago` (`fecha_pago`),
  ADD KEY `idx_usuario_registro` (`usuario_registro_id`),
  ADD KEY `idx_fecha_metodo` (`fecha_pago`,`metodo_pago`);

--
-- Indexes for table `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `uk_codigo` (`codigo`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_inicio` (`fecha_inicio`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indexes for table `vendedores`
--
ALTER TABLE `vendedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user` (`user_id`),
  ADD UNIQUE KEY `uk_codigo` (`codigo_vendedor`),
  ADD UNIQUE KEY `uk_documento` (`numero_documento`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_estado_fecha` (`estado`,`fecha_ingreso`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amortizaciones`
--
ALTER TABLE `amortizaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `comisiones`
--
ALTER TABLE `comisiones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_comisiones`
--
ALTER TABLE `historial_comisiones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lotes`
--
ALTER TABLE `lotes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `metas_vendedores`
--
ALTER TABLE `metas_vendedores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pagos_comisiones`
--
ALTER TABLE `pagos_comisiones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Structure for view `vista_proyectos_resumen`
--
DROP TABLE IF EXISTS `vista_proyectos_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u418271893_santi`@`localhost` SQL SECURITY DEFINER VIEW `vista_proyectos_resumen`  AS SELECT `p`.`id` AS `id`, `p`.`codigo` AS `codigo`, `p`.`nombre` AS `nombre`, `p`.`ubicacion` AS `ubicacion`, `p`.`estado` AS `estado`, `p`.`total_lotes` AS `total_lotes`, count(distinct case when `l`.`estado` = 'disponible' then `l`.`id` end) AS `lotes_disponibles`, count(distinct case when `l`.`estado` = 'vendido' then `l`.`id` end) AS `lotes_vendidos`, count(distinct case when `l`.`estado` = 'reservado' then `l`.`id` end) AS `lotes_reservados`, count(distinct case when `l`.`estado` = 'bloqueado' then `l`.`id` end) AS `lotes_bloqueados`, sum(case when `l`.`estado` in ('disponible','reservado') then `l`.`precio_lista` else 0 end) AS `valor_inventario`, sum(case when `l`.`estado` = 'vendido' then coalesce(`l`.`precio_venta`,`l`.`precio_lista`) else 0 end) AS `valor_ventas` FROM (`proyectos` `p` left join `lotes` `l` on(`p`.`id` = `l`.`proyecto_id`)) GROUP BY `p`.`id`, `p`.`codigo`, `p`.`nombre`, `p`.`ubicacion`, `p`.`estado`, `p`.`total_lotes` ;

-- --------------------------------------------------------

--
-- Structure for view `vista_vendedores_resumen`
--
DROP TABLE IF EXISTS `vista_vendedores_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u418271893_santi`@`127.0.0.1` SQL SECURITY DEFINER VIEW `vista_vendedores_resumen`  AS SELECT `v`.`id` AS `id`, `v`.`codigo_vendedor` AS `codigo_vendedor`, `v`.`user_id` AS `user_id`, concat(`v`.`nombres`,' ',`v`.`apellidos`) AS `nombre_completo`, `v`.`email` AS `email`, `v`.`telefono` AS `telefono`, `v`.`celular` AS `celular`, `v`.`fecha_ingreso` AS `fecha_ingreso`, `v`.`porcentaje_comision_default` AS `porcentaje_comision_default`, `v`.`estado` AS `estado`, `u`.`rol` AS `rol`, count(distinct `l`.`id`) AS `total_lotes_vendidos`, coalesce(sum(`l`.`precio_venta`),0) AS `valor_total_vendido`, count(distinct `c`.`id`) AS `total_comisiones`, coalesce(sum(`c`.`valor_comision`),0) AS `total_comisiones_generadas`, coalesce(sum(case when `c`.`estado` = 'pendiente' then `c`.`valor_comision` else 0 end),0) AS `comisiones_pendientes`, coalesce(sum(case when `c`.`estado` = 'pagada' then `c`.`valor_comision` else 0 end),0) AS `comisiones_pagadas`, count(distinct `pc`.`id`) AS `total_pagos_recibidos`, coalesce(sum(`pc`.`valor_pagado`),0) AS `total_dinero_recibido`, max(`l`.`fecha_venta`) AS `fecha_ultima_venta`, max(`c`.`fecha_venta`) AS `fecha_ultima_comision` FROM ((((`vendedores` `v` join `users` `u` on(`v`.`user_id` = `u`.`id`)) left join `lotes` `l` on(`v`.`id` = `l`.`vendedor_id` and `l`.`estado` = 'vendido')) left join `comisiones` `c` on(`v`.`id` = `c`.`vendedor_id`)) left join `pagos_comisiones` `pc` on(`v`.`id` = `pc`.`vendedor_id`)) WHERE `v`.`estado` = 'activo' GROUP BY `v`.`id`, `v`.`codigo_vendedor`, `v`.`user_id`, `v`.`nombres`, `v`.`apellidos`, `v`.`email`, `v`.`telefono`, `v`.`celular`, `v`.`fecha_ingreso`, `v`.`porcentaje_comision_default`, `v`.`estado`, `u`.`rol` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `amortizaciones`
--
ALTER TABLE `amortizaciones`
  ADD CONSTRAINT `amortizaciones_ibfk_1` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`);

--
-- Constraints for table `comisiones`
--
ALTER TABLE `comisiones`
  ADD CONSTRAINT `comisiones_ibfk_1` FOREIGN KEY (`lote_id`) REFERENCES `lotes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comisiones_ibfk_2` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historial_comisiones`
--
ALTER TABLE `historial_comisiones`
  ADD CONSTRAINT `historial_comisiones_ibfk_1` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historial_comisiones_ibfk_2` FOREIGN KEY (`usuario_modifico_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lotes`
--
ALTER TABLE `lotes`
  ADD CONSTRAINT `fk_lotes_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `lotes_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`),
  ADD CONSTRAINT `lotes_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Constraints for table `metas_vendedores`
--
ALTER TABLE `metas_vendedores`
  ADD CONSTRAINT `metas_vendedores_ibfk_1` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`amortizacion_id`) REFERENCES `amortizaciones` (`id`);

--
-- Constraints for table `pagos_comisiones`
--
ALTER TABLE `pagos_comisiones`
  ADD CONSTRAINT `pagos_comisiones_ibfk_1` FOREIGN KEY (`comision_id`) REFERENCES `comisiones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagos_comisiones_ibfk_2` FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagos_comisiones_ibfk_3` FOREIGN KEY (`usuario_registro_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vendedores`
--
ALTER TABLE `vendedores`
  ADD CONSTRAINT `vendedores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
