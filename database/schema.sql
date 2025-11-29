
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
(1, 2, 1, '2025-12-29', NULL, 'pendiente', 1977085.83, 1557085.83, 420000.00, 40442914.17, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 10:15:03'),
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
    IF NEW.estado_pago = 'mora' THEN
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
-- Indexes for table `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_amortizacion` (`amortizacion_id`),
  ADD KEY `idx_fecha_pago` (`fecha_pago`),
  ADD KEY `idx_recibo` (`numero_recibo`);

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
-- AUTO_INCREMENT for table `lotes`
--
ALTER TABLE `lotes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
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

-- --------------------------------------------------------

--
-- Structure for view `vista_proyectos_resumen`
--
DROP TABLE IF EXISTS `vista_proyectos_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u418271893_santi`@`localhost` SQL SECURITY DEFINER VIEW `vista_proyectos_resumen`  AS SELECT `p`.`id` AS `id`, `p`.`codigo` AS `codigo`, `p`.`nombre` AS `nombre`, `p`.`ubicacion` AS `ubicacion`, `p`.`estado` AS `estado`, `p`.`total_lotes` AS `total_lotes`, count(distinct case when `l`.`estado` = 'disponible' then `l`.`id` end) AS `lotes_disponibles`, count(distinct case when `l`.`estado` = 'vendido' then `l`.`id` end) AS `lotes_vendidos`, count(distinct case when `l`.`estado` = 'reservado' then `l`.`id` end) AS `lotes_reservados`, count(distinct case when `l`.`estado` = 'bloqueado' then `l`.`id` end) AS `lotes_bloqueados`, sum(case when `l`.`estado` in ('disponible','reservado') then `l`.`precio_lista` else 0 end) AS `valor_inventario`, sum(case when `l`.`estado` = 'vendido' then coalesce(`l`.`precio_venta`,`l`.`precio_lista`) else 0 end) AS `valor_ventas` FROM (`proyectos` `p` left join `lotes` `l` on(`p`.`id` = `l`.`proyecto_id`)) GROUP BY `p`.`id`, `p`.`codigo`, `p`.`nombre`, `p`.`ubicacion`, `p`.`estado`, `p`.`total_lotes` ;

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