
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
(2, 2, 2, '2026-01-29', '2025-11-29', 'pagada', 1977085.83, 1572656.69, 404429.14, 38870257.47, 1977085.83, 0, '', '2025-11-29 10:15:03', '2025-11-29 15:46:37'),
(3, 2, 3, '2026-03-01', '2025-11-29', 'pagada', 1977085.83, 1588383.26, 388702.57, 37281874.22, 1977085.83, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:10:57'),
(4, 2, 4, '2026-03-29', '2025-11-29', 'pagada', 2147527.42, 1742568.53, 404958.88, 38753319.73, 2147527.42, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(5, 2, 5, '2026-04-29', NULL, 'pendiente', 2332878.13, 1911897.37, 420980.76, 40186178.45, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(6, 2, 6, '2026-05-29', NULL, 'pendiente', 2332878.13, 1931016.34, 401861.78, 38255162.11, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(7, 2, 7, '2026-06-29', NULL, 'pendiente', 2332878.13, 1950326.51, 382551.62, 36304835.60, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(8, 2, 8, '2026-07-29', NULL, 'pendiente', 2332878.13, 1969829.77, 363048.36, 34335005.83, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(9, 2, 9, '2026-08-29', NULL, 'pendiente', 2332878.13, 1989528.07, 343350.06, 32345477.76, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(10, 2, 10, '2026-09-29', NULL, 'pendiente', 2332878.13, 2009423.35, 323454.78, 30336054.41, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(11, 2, 11, '2026-10-29', NULL, 'pendiente', 2332878.13, 2029517.58, 303360.54, 28306536.82, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(12, 2, 12, '2026-11-29', NULL, 'pendiente', 2332878.13, 2049812.76, 283065.37, 26256724.07, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(13, 2, 13, '2026-12-29', NULL, 'pendiente', 2332878.13, 2070310.89, 262567.24, 24186413.18, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(14, 2, 14, '2027-01-29', NULL, 'pendiente', 2332878.13, 2091014.00, 241864.13, 22095399.18, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(15, 2, 15, '2027-03-01', NULL, 'pendiente', 2332878.13, 2111924.14, 220953.99, 19983475.05, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(16, 2, 16, '2027-03-29', NULL, 'pendiente', 2332878.13, 2133043.38, 199834.75, 17850431.67, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(17, 2, 17, '2027-04-29', NULL, 'pendiente', 2332878.13, 2154373.81, 178504.32, 15696057.86, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(18, 2, 18, '2027-05-29', NULL, 'pendiente', 2332878.13, 2175917.55, 156960.58, 13520140.31, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(19, 2, 19, '2027-06-29', NULL, 'pendiente', 2332878.13, 2197676.72, 135201.40, 11322463.58, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(20, 2, 20, '2027-07-29', NULL, 'pendiente', 2332878.13, 2219653.49, 113224.64, 9102810.09, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(21, 2, 21, '2027-08-29', NULL, 'pendiente', 2332878.13, 2241850.03, 91028.10, 6860960.06, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(22, 2, 22, '2027-09-29', NULL, 'pendiente', 2332878.13, 2264268.53, 68609.60, 4596691.54, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(23, 2, 23, '2027-10-29', NULL, 'pendiente', 2332878.13, 2286911.21, 45966.92, 2309780.32, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(24, 2, 24, '2027-11-29', NULL, 'pendiente', 2332878.13, 2309780.32, 23097.80, 0.00, 0.00, 0, '', '2025-11-29 10:15:03', '2025-11-29 16:18:10'),
(25, 4, 1, '2025-02-15', '2025-02-10', 'pagada', 1977085.83, 1644418.16, 332667.67, 18355581.84, 2500000.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:32:37'),
(26, 4, 2, '2025-03-15', '2025-11-29', 'pagada', 1977085.83, 1659611.10, 317474.73, 16695970.74, 1977085.83, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(27, 4, 3, '2025-04-15', '2025-12-02', 'pagada', 2041465.91, 1656507.03, 384958.88, 36839381.23, 2041465.91, 0, NULL, '2025-11-29 16:32:37', '2025-12-02 14:05:16'),
(28, 4, 4, '2025-05-15', '2025-05-10', 'pagada', 1977085.83, 1691101.41, 285984.42, 13329699.22, 1977085.83, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:32:37'),
(29, 4, 5, '2025-06-15', '2025-12-02', 'pagada', 2041465.91, 1673072.10, 368393.81, 35166309.13, 2041465.91, 0, NULL, '2025-11-29 16:32:37', '2025-12-02 14:05:16'),
(30, 4, 6, '2025-07-15', '2025-12-02', 'pagada', 2041465.91, 1689802.82, 351663.09, 33476506.31, 2041465.91, 0, NULL, '2025-11-29 16:32:37', '2025-12-02 14:05:16'),
(31, 4, 7, '2025-08-15', '2025-12-02', 'pagada', 2041465.91, 1706700.85, 334765.06, 31769805.46, 2041465.91, 0, NULL, '2025-11-29 16:32:37', '2025-12-02 14:05:16'),
(32, 4, 8, '2025-09-15', '2025-12-02', 'pagada', 2041465.91, 1723767.86, 317698.05, 30046037.61, 2041465.91, 0, NULL, '2025-11-29 16:32:37', '2025-12-02 14:05:16'),
(33, 4, 9, '2025-10-15', '2025-12-02', 'pagada', 2041465.91, 1741005.54, 300460.38, 28305032.07, 2041465.91, 0, NULL, '2025-11-29 16:32:37', '2025-12-02 14:05:16'),
(34, 4, 10, '2025-11-15', '2025-12-02', 'pagada', 2041465.91, 1758415.59, 283050.32, 26546616.48, 2041465.91, 0, NULL, '2025-11-29 16:32:37', '2025-12-02 14:05:16'),
(35, 4, 11, '2025-12-15', NULL, 'pendiente', 2041465.91, 1775999.75, 265466.16, 24770616.74, 232652.80, 0, NULL, '2025-11-29 16:32:37', '2025-12-02 14:05:16'),
(36, 4, 12, '2026-01-15', NULL, 'pendiente', 2041465.91, 1793759.74, 247706.17, 22976856.99, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(37, 4, 13, '2026-02-15', NULL, 'pendiente', 2041465.91, 1811697.34, 229768.57, 21165159.65, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(38, 4, 14, '2026-03-15', NULL, 'pendiente', 2041465.91, 1829814.31, 211651.60, 19335345.34, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(39, 4, 15, '2026-04-15', NULL, 'pendiente', 2041465.91, 1848112.46, 193353.45, 17487232.88, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(40, 4, 16, '2026-05-15', NULL, 'pendiente', 2041465.91, 1866593.58, 174872.33, 15620639.29, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(41, 4, 17, '2026-06-15', NULL, 'pendiente', 2041465.91, 1885259.52, 156206.39, 13735379.78, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(42, 4, 18, '2026-07-15', NULL, 'pendiente', 2041465.91, 1904112.11, 137353.80, 11831267.66, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(43, 4, 19, '2026-08-15', NULL, 'pendiente', 2041465.91, 1923153.23, 118312.68, 9908114.43, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(44, 4, 20, '2026-09-15', NULL, 'pendiente', 2041465.91, 1942384.77, 99081.14, 7965729.66, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(45, 4, 21, '2026-10-15', NULL, 'pendiente', 2041465.91, 1961808.61, 79657.30, 6003921.05, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(46, 4, 22, '2026-11-15', NULL, 'pendiente', 2041465.91, 1981426.70, 60039.21, 4022494.35, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(47, 4, 23, '2026-12-15', NULL, 'pendiente', 2041465.91, 2001240.97, 40224.94, 2021253.38, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(48, 4, 24, '2027-01-15', NULL, 'pendiente', 2041465.91, 2021253.38, 20212.53, 0.00, 0.00, 0, NULL, '2025-11-29 16:32:37', '2025-11-29 16:40:43'),
(49, 5, 1, '2025-11-15', '2025-11-29', 'pagada', 1000000.00, 900000.00, 100000.00, 11100000.00, 1000000.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(50, 5, 2, '2025-12-15', NULL, 'pendiente', 675178.53, 605178.53, 70000.00, 6394821.47, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(51, 5, 3, '2026-01-15', NULL, 'pendiente', 675178.53, 611230.32, 63948.21, 5783591.15, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(52, 5, 4, '2026-02-15', NULL, 'pendiente', 675178.53, 617342.62, 57835.91, 5166248.54, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(53, 5, 5, '2026-03-15', NULL, 'pendiente', 675178.53, 623516.04, 51662.49, 4542732.49, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(54, 5, 6, '2026-04-15', NULL, 'pendiente', 675178.53, 629751.21, 45427.32, 3912981.29, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(55, 5, 7, '2026-05-15', NULL, 'pendiente', 675178.53, 636048.72, 39129.81, 3276932.57, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(56, 5, 8, '2026-06-15', NULL, 'pendiente', 675178.53, 642409.20, 32769.33, 2634523.37, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(57, 5, 9, '2026-07-15', NULL, 'pendiente', 675178.53, 648833.30, 26345.23, 1985690.07, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(58, 5, 10, '2026-08-15', NULL, 'pendiente', 675178.53, 655321.63, 19856.90, 1330368.44, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(59, 5, 11, '2026-09-15', NULL, 'pendiente', 675178.53, 661874.85, 13303.68, 668493.59, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(60, 5, 12, '2026-10-15', NULL, 'pendiente', 675178.53, 668493.59, 6684.94, 0.00, 0.00, 0, NULL, '2025-11-29 16:47:49', '2025-11-29 16:49:31'),
(61, 6, 1, '2025-09-03', '2025-12-02', 'pagada', 2500000.00, 2000000.00, 500000.00, 2500000.00, 2500000.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:28:51'),
(62, 6, 2, '2025-10-03', NULL, 'pendiente', 2000000.00, 2000000.00, 0.00, 8000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:28:51'),
(63, 6, 3, '2025-11-02', NULL, 'pendiente', 2000000.00, 2000000.00, 0.00, 6000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:28:51'),
(64, 6, 4, '2026-01-02', NULL, 'pendiente', 2000000.00, 2000000.00, 0.00, 4000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:28:51'),
(65, 6, 5, '2026-02-02', NULL, 'pendiente', 2000000.00, 2000000.00, 0.00, 2000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:28:51'),
(66, 6, 6, '2026-03-02', NULL, 'pendiente', 2000000.00, 2000000.00, 0.00, 0.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:28:51'),
(67, 7, 1, '2025-10-03', '2025-12-02', 'pagada', 3000000.00, 2400000.00, 600000.00, 3000000.00, 3000000.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:24:06'),
(68, 7, 2, '2025-11-02', '2025-12-02', 'pagada', 3000000.00, 2460000.00, 540000.00, 3000000.00, 3000000.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:25:49'),
(69, 7, 3, '2026-01-02', '2025-12-02', 'pagada', 2166666.67, 2166666.67, 0.00, 4333333.33, 2166666.67, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:26:10'),
(70, 7, 4, '2026-02-02', NULL, 'pendiente', 1250000.01, 1250000.01, 0.00, 1250000.01, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:26:10'),
(71, 7, 5, '2026-03-02', NULL, 'pendiente', 1250000.01, 1250000.01, 0.00, 0.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:26:10'),
(72, 8, 1, '2025-11-02', NULL, 'pendiente', 3750000.00, 3000000.00, 750000.00, 3750000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(73, 8, 2, '2026-01-02', NULL, 'pendiente', 3750000.00, 3075000.00, 675000.00, 3750000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(74, 8, 3, '2026-02-02', NULL, 'pendiente', 3750000.00, 3150000.00, 600000.00, 3750000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(75, 8, 4, '2026-03-02', NULL, 'pendiente', 3750000.00, 3225000.00, 525000.00, 3750000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(76, 9, 1, '2025-08-04', NULL, 'pendiente', 5000000.00, 4000000.00, 1000000.00, 5000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(77, 9, 2, '2025-09-03', NULL, 'pendiente', 5000000.00, 4100000.00, 900000.00, 5000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(78, 9, 3, '2025-10-03', NULL, 'pendiente', 5000000.00, 4200000.00, 800000.00, 5000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(79, 9, 4, '2025-11-02', NULL, 'pendiente', 5000000.00, 4300000.00, 700000.00, 5000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(80, 9, 5, '2026-01-02', NULL, 'pendiente', 5000000.00, 4400000.00, 600000.00, 5000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(81, 9, 6, '2026-02-02', NULL, 'pendiente', 5000000.00, 4500000.00, 500000.00, 5000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(82, 10, 1, '2025-11-17', NULL, 'pendiente', 2000000.00, 1600000.00, 400000.00, 2000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(83, 10, 2, '2025-12-17', NULL, 'pendiente', 2000000.00, 1640000.00, 360000.00, 2000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(84, 10, 3, '2026-01-16', NULL, 'pendiente', 2000000.00, 1680000.00, 320000.00, 2000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(85, 10, 4, '2026-02-15', NULL, 'pendiente', 2000000.00, 1720000.00, 280000.00, 2000000.00, 0.00, 0, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52');

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
(2, 'CC', '76767', 'sfsef', '424', 'trujillosantiago3445@gmail.com', 'sfe', 'sdfsf', '', '2025-11-29 08:47:48', '2025-11-29 09:08:50'),
(3, 'CC', '1234567890', 'Cliente Prueba Mora', '3001234567', 'prueba@test.com', NULL, 'Medellín', NULL, '2025-11-29 16:32:37', '2025-11-29 16:32:37'),
(4, 'CC', '1111111111', 'Cliente Mora Simple', '3001111111', 'cliente@test.com', NULL, 'Medellín', NULL, '2025-11-29 16:47:49', '2025-11-29 16:47:49'),
(5, 'CC', '1234567801', 'Carlos Moroso López', '3101234567', 'carlos.moroso@email.com', 'Calle 50 #20-30', 'Bogotá', NULL, '2025-12-02 13:57:39', '2025-12-02 13:57:39'),
(6, 'CC', '1234567802', 'María Atrasada Gómez', '3102234567', 'maria.atrasada@email.com', 'Carrera 15 #40-50', 'Medellín', NULL, '2025-12-02 13:57:39', '2025-12-02 13:57:39'),
(7, 'CC', '1234567803', 'Juan Vencido Martínez', '3103234567', 'juan.vencido@email.com', 'Avenida 30 #60-70', 'Cali', NULL, '2025-12-02 13:57:39', '2025-12-02 13:57:39'),
(8, 'NIT', '900123456', 'Empresa Morosa S.A.S.', '3104234567', 'empresa.morosa@email.com', 'Calle 80 #90-100', 'Barranquilla', NULL, '2025-12-02 13:57:39', '2025-12-02 13:57:39'),
(9, 'CC', '1234567805', 'Ana Impago Rodríguez', '3105234567', 'ana.impago@email.com', 'Carrera 70 #80-90', 'Cartagena', NULL, '2025-12-02 13:57:39', '2025-12-02 13:57:39');

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
  `saldo_a_favor` decimal(15,2) DEFAULT 0.00 COMMENT 'Saldo acumulado de pagos excedentes disponible para reajustar mora y compensar cuotas futuras',
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

INSERT INTO `lotes` (`id`, `proyecto_id`, `codigo_lote`, `manzana`, `ubicacion`, `area_m2`, `precio_lista`, `precio_venta`, `cuota_inicial`, `monto_financiado`, `tasa_interes`, `numero_cuotas`, `saldo_a_favor`, `fecha_inicio_amortizacion`, `estado`, `cliente_id`, `vendedor_id`, `fecha_venta`, `observaciones`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 1, '666', NULL, NULL, 666.00, 5000000.00, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, 'disponible', NULL, NULL, NULL, NULL, NULL, '2025-11-29 07:25:02', '2025-11-29 07:25:02'),
(2, 3, '444', NULL, NULL, 300.00, 60000000.00, NULL, 18000000.00, 42000000.00, 12.00, 24, 0.00, '2025-11-29', 'vendido', NULL, NULL, NULL, NULL, NULL, '2025-11-29 07:26:16', '2025-11-29 10:15:03'),
(3, 2, '42424', NULL, NULL, 0.00, 45000000.00, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, 'vendido', NULL, NULL, NULL, NULL, NULL, '2025-11-29 07:27:27', '2025-11-29 09:13:17'),
(4, 13, 'LOTE-TEST-001', 'M-01', NULL, 150.00, 20000000.00, NULL, NULL, 20000000.00, 12.00, 24, 0.00, '2025-02-15', 'vendido', 3, NULL, NULL, NULL, NULL, '2025-11-29 16:32:37', '2025-11-29 16:41:34'),
(5, 14, 'LOTE-SIMPLE', 'M-01', NULL, 100.00, 12000000.00, NULL, NULL, 12000000.00, 12.00, 12, 0.00, '2025-10-15', 'vendido', 4, NULL, NULL, NULL, NULL, '2025-11-29 16:47:49', '2025-11-29 16:47:49'),
(6, 1, 'MORA-001', NULL, NULL, 100.00, 50000000.00, 50000000.00, NULL, NULL, NULL, NULL, 0.00, NULL, 'vendido', 1, NULL, '2025-02-02', NULL, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(7, 1, 'MORA-002', NULL, NULL, 120.00, 60000000.00, 60000000.00, NULL, NULL, NULL, NULL, 0.00, NULL, 'vendido', 2, NULL, '2025-04-02', NULL, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(8, 1, 'MORA-003', NULL, NULL, 150.00, 75000000.00, 75000000.00, NULL, NULL, NULL, NULL, 0.00, NULL, 'vendido', 3, NULL, '2025-06-02', NULL, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(9, 2, 'MORA-004', NULL, NULL, 200.00, 100000000.00, 100000000.00, NULL, NULL, NULL, NULL, 0.00, NULL, 'vendido', 4, NULL, '2024-12-02', NULL, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52'),
(10, 2, 'MORA-005', NULL, NULL, 80.00, 40000000.00, 40000000.00, NULL, NULL, NULL, NULL, 0.00, NULL, 'vendido', 5, NULL, '2025-08-02', NULL, NULL, '2025-12-02 14:03:52', '2025-12-02 14:03:52');

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
(8, 1, '2025-11-29', 1977085.83, 'transferencia', 'nn', '', '2025-11-29 13:53:29', '2025-11-29 13:53:29'),
(9, 2, '2025-11-29', 1888888.00, 'transferencia', '', '', '2025-11-29 15:14:48', '2025-11-29 15:14:48'),
(10, 2, '2025-11-29', 88197.83, 'transferencia', '', '', '2025-11-29 15:46:37', '2025-11-29 15:46:37'),
(11, 3, '2025-11-29', 1977085.83, 'transferencia', '', '', '2025-11-29 16:10:57', '2025-11-29 16:10:57'),
(12, 4, '2025-11-29', 2147527.42, 'transferencia', '', '', '2025-11-29 16:18:10', '2025-11-29 16:18:10'),
(13, 25, '2025-02-10', 2500000.00, 'transferencia', 'TRF-2025-02-001', 'Pago Cuota 1 - Exceso de $522.914', '2025-11-29 16:32:37', '2025-11-29 16:32:37'),
(14, 28, '2025-05-10', 1977085.83, 'transferencia', 'TRF-2025-05-001', 'Pago Normal Cuota 4', '2025-11-29 16:32:37', '2025-11-29 16:32:37'),
(15, 26, '2025-11-29', 1977085.83, 'efectivo', '', '', '2025-11-29 16:40:43', '2025-11-29 16:40:43'),
(16, 27, '2025-11-29', 522914.17, '', 'REAJ-SAF-20251129104134-27', 'Aplicación automática de Saldo a Favor - Reajuste de Mora', '2025-11-29 16:41:34', '2025-11-29 16:41:34'),
(17, 49, '2025-11-29', 1000000.00, 'efectivo', '', '', '2025-11-29 16:49:31', '2025-11-29 16:49:31'),
(18, 27, '2025-12-02', 1518551.74, 'transferencia', '', '', '2025-12-02 14:05:16', '2025-12-02 14:05:16'),
(19, 29, '2025-12-02', 2041465.91, 'transferencia', '', '', '2025-12-02 14:05:16', '2025-12-02 14:05:16'),
(20, 30, '2025-12-02', 2041465.91, 'transferencia', '', '', '2025-12-02 14:05:16', '2025-12-02 14:05:16'),
(21, 31, '2025-12-02', 2041465.91, 'transferencia', '', '', '2025-12-02 14:05:16', '2025-12-02 14:05:16'),
(22, 32, '2025-12-02', 2041465.91, 'transferencia', '', '', '2025-12-02 14:05:16', '2025-12-02 14:05:16'),
(23, 33, '2025-12-02', 2041465.91, 'transferencia', '', '', '2025-12-02 14:05:16', '2025-12-02 14:05:16'),
(24, 34, '2025-12-02', 2041465.91, 'transferencia', '', '', '2025-12-02 14:05:16', '2025-12-02 14:05:16'),
(25, 35, '2025-12-02', 232652.80, 'transferencia', '', '', '2025-12-02 14:05:16', '2025-12-02 14:05:16'),
(26, 67, '2025-12-02', 3000000.00, 'transferencia', '', '', '2025-12-02 14:24:06', '2025-12-02 14:24:06'),
(27, 68, '2025-12-02', 500000.00, 'transferencia', '', '', '2025-12-02 14:24:18', '2025-12-02 14:24:18'),
(28, 68, '2025-12-02', 2500000.00, 'transferencia', '', '', '2025-12-02 14:25:49', '2025-12-02 14:25:49'),
(29, 69, '2025-12-02', 2166666.67, 'transferencia', '', '', '2025-12-02 14:26:10', '2025-12-02 14:26:10'),
(30, 61, '2025-12-02', 2500000.00, 'transferencia', '', '', '2025-12-02 14:28:51', '2025-12-02 14:28:51');

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
(1, 'PRY-001', 'Urbanización El Paraíso', 'Carrera 15 #45-32, Montería  gseg', NULL, 'activo', '2024-01-15', NULL, 4, 'gsergseg', '2025-11-29 05:35:02', '2025-12-02 14:03:52'),
(2, 'PRY-002', 'Parcelación Villa Verde', 'Vía Planeta Rica Km 12', 'Parcelación campestre con lotes desde 500m²', 'activo', '2024-03-01', NULL, 3, NULL, '2025-11-29 05:35:02', '2025-12-02 14:03:52'),
(3, 'PRY-003', 'Conjunto Cerrado Los Robles', 'Avenida Circunvalar Norte', NULL, 'activo', '2023-11-20', NULL, 1, '', '2025-11-29 05:35:02', '2025-11-29 07:41:48'),
(13, 'PRY-TEST', 'Proyecto Prueba Saldo a Favor', 'Medellín', 'Proyecto de prueba para validar sistema de Saldo a Favor Global', 'activo', '2025-01-01', NULL, 1, NULL, '2025-11-29 16:32:37', '2025-11-29 16:32:37'),
(14, 'PRY-SIMPLE', 'Proyecto Simple Mora', 'Medellín', 'Proyecto simple para prueba de saldo a favor', 'activo', '2025-01-01', NULL, 1, NULL, '2025-11-29 16:47:49', '2025-11-29 16:47:49');

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
(1, 'admin@sistema.com', '$2y$12$wOs5wZG9LzTHhdAOENkWVe8d7OGgPlTK1qqUt93gE6iA.Sd5uLCiy', 'Administrador', 'administrador', NULL, NULL, 1, '2025-11-29 05:15:26', '2025-12-02 14:29:18'),
(3, 'consulta@sistema.com', '$2y$12$rb8bbi/fZhxPC/8G7g9nBuTjLEVX9GDUOwijr1cQrlZfT6pjf2/f6', 'Usuario Consulta', 'consulta', NULL, NULL, 1, '2025-12-02 12:58:58', '2025-12-02 12:59:19'),
(4, 'vendedor@sistema.com', '$2y$12$rb8bbi/fZhxPC/8G7g9nBuTjLEVX9GDUOwijr1cQrlZfT6pjf2/f6', 'María Vendedor', 'vendedor', NULL, NULL, 1, '2025-12-02 12:58:58', '2025-12-02 12:59:57');

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
(1, 1, 'VEND-0001', 'CC', 'DOC-1', 'Administrador', 'Administrador', '3', '3', 'admin@sistema.com', '3', '3', '2025-11-29', NULL, 'indefinido', 3.00, NULL, NULL, NULL, 'activo', NULL, NULL, '2025-11-29 14:06:39', '2025-12-02 14:34:29'),
(2, 4, 'VEND-0002', 'CC', '987654321', 'María', 'Vendedora González', '6012345678', '+57 300 123 4567', 'vendedor@sistema.com', 'Calle 100 #20-30, Apto 501', 'Bogotá', '2024-01-15', NULL, 'indefinido', 5.00, 'Bancolombia', 'ahorros', '12345678901234', 'activo', 'Vendedora con excelente desempeño comercial. Especializada en lotes residenciales.', NULL, '2025-12-02 14:32:40', '2025-12-02 14:32:40');

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
  ADD KEY `idx_vendedor` (`vendedor_id`),
  ADD KEY `idx_lotes_saldo_a_favor` (`saldo_a_favor`,`estado`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `metas_vendedores`
--
ALTER TABLE `metas_vendedores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `pagos_comisiones`
--
ALTER TABLE `pagos_comisiones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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

CREATE ALGORITHM=UNDEFINED DEFINER=`u418271893_santi`@`127.0.0.1` SQL SECURITY DEFINER VIEW `vista_vendedores_resumen`  AS SELECT `v`.`id` AS `id`, `v`.`codigo_vendedor` AS `codigo_vendedor`, `v`.`user_id` AS `user_id`, concat(`v`.`nombres`,' ',`v`.`apellidos`) AS `nombre_completo`, `v`.`email` AS `email`, `v`.`telefono` AS `telefono`, `v`.`celular` AS `celular`, `v`.`fecha_ingreso` AS `fecha_ingreso`, `v`.`porcentaje_comision_default` AS `porcentaje_comision_default`, `v`.`estado` AS `estado`, `u`.`rol` AS `rol`, count(distinct `l`.`id`) AS `total_lotes_vendidos`, coalesce(sum(case when `l`.`estado` = 'vendido' then coalesce(`l`.`precio_venta`,`l`.`precio_lista`) end),0) AS `valor_total_vendido`, count(distinct `c`.`id`) AS `total_comisiones`, coalesce(sum(`c`.`valor_comision`),0) AS `total_comisiones_generadas`, coalesce(sum(case when `c`.`estado` = 'pendiente' then `c`.`valor_comision` else 0 end),0) AS `comisiones_pendientes`, coalesce(sum(case when `c`.`estado` = 'pagada' then `c`.`valor_comision` else 0 end),0) AS `comisiones_pagadas`, count(distinct `pc`.`id`) AS `total_pagos_recibidos`, coalesce(sum(`pc`.`valor_pagado`),0) AS `total_dinero_recibido`, max(`l`.`fecha_venta`) AS `fecha_ultima_venta`, max(`c`.`fecha_venta`) AS `fecha_ultima_comision` FROM ((((`vendedores` `v` join `users` `u` on(`v`.`user_id` = `u`.`id`)) left join `lotes` `l` on(`v`.`user_id` = `l`.`vendedor_id` and `l`.`estado` = 'vendido')) left join `comisiones` `c` on(`v`.`id` = `c`.`vendedor_id`)) left join `pagos_comisiones` `pc` on(`v`.`id` = `pc`.`vendedor_id`)) WHERE `v`.`estado` = 'activo' GROUP BY `v`.`id`, `v`.`codigo_vendedor`, `v`.`user_id`, `v`.`nombres`, `v`.`apellidos`, `v`.`email`, `v`.`telefono`, `v`.`celular`, `v`.`fecha_ingreso`, `v`.`porcentaje_comision_default`, `v`.`estado`, `u`.`rol` ;

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
