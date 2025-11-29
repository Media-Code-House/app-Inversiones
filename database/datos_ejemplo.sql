-- ================================================
-- DATOS DE EJEMPLO PARA TESTING - MÓDULO 3
-- Sistema de Gestión de Lotes e Inversiones
-- ================================================
-- Ejecutar DESPUÉS de schema.sql

USE u418271893_inversiones;

-- ================================================
-- PROYECTOS DE EJEMPLO
-- ================================================

INSERT INTO proyectos (codigo, nombre, ubicacion, descripcion, estado, fecha_inicio) VALUES
('PRY-001', 'Urbanización El Paraíso', 'Carrera 15 #45-32, Montería', 'Proyecto residencial de 50 lotes con servicios públicos', 'activo', '2024-01-15'),
('PRY-002', 'Parcelación Villa Verde', 'Vía Planeta Rica Km 12', 'Parcelación campestre con lotes desde 500m²', 'activo', '2024-03-01'),
('PRY-003', 'Conjunto Cerrado Los Robles', 'Avenida Circunvalar Norte', 'Conjunto cerrado con zonas comunes y piscina', 'pausado', '2023-11-20');

-- ================================================
-- CLIENTES DE EJEMPLO
-- ================================================

INSERT INTO clientes (tipo_documento, numero_documento, nombre, telefono, email, ciudad) VALUES
('CC', '1234567890', 'Juan Carlos Pérez Martínez', '3001234567', 'jperez@email.com', 'Montería'),
('CC', '9876543210', 'María Fernanda García López', '3109876543', 'mgarcia@email.com', 'Cereté'),
('NIT', '900123456-1', 'Inversiones S.A.S.', '3201122334', 'contacto@inversiones.com', 'Montería'),
('CC', '5555666677', 'Pedro Antonio Rojas Díaz', '3145556666', 'projas@email.com', 'Lorica'),
('CC', '1122334455', 'Ana Lucía Morales Herrera', '3187778888', 'amorales@email.com', 'Montería');

-- ================================================
-- LOTES DE EJEMPLO - Proyecto 1 (El Paraíso)
-- ================================================

INSERT INTO lotes (proyecto_id, codigo_lote, manzana, area_m2, precio_lista, estado) VALUES
-- Manzana A - Disponibles
(1, 'A-01', 'A', 200.00, 45000000, 'disponible'),
(1, 'A-02', 'A', 210.00, 47000000, 'disponible'),
(1, 'A-03', 'A', 205.00, 46000000, 'disponible'),
(1, 'A-04', 'A', 220.00, 50000000, 'disponible'),
(1, 'A-05', 'A', 195.00, 44000000, 'disponible'),

-- Manzana B - Vendidos
(1, 'B-01', 'B', 180.00, 40000000, 'vendido'),
(1, 'B-02', 'B', 185.00, 41000000, 'vendido'),
(1, 'B-03', 'B', 190.00, 42000000, 'vendido'),

-- Manzana C - Reservados
(1, 'C-01', 'C', 200.00, 45000000, 'reservado'),
(1, 'C-02', 'C', 210.00, 47000000, 'reservado');

-- Actualizar clientes en lotes vendidos
UPDATE lotes SET cliente_id = 1, precio_venta = 39000000, fecha_venta = '2024-02-10' WHERE codigo_lote = 'B-01' AND proyecto_id = 1;
UPDATE lotes SET cliente_id = 2, precio_venta = 41000000, fecha_venta = '2024-03-15' WHERE codigo_lote = 'B-02' AND proyecto_id = 1;
UPDATE lotes SET cliente_id = 4, precio_venta = 42500000, fecha_venta = '2024-04-20' WHERE codigo_lote = 'B-03' AND proyecto_id = 1;

-- ================================================
-- LOTES DE EJEMPLO - Proyecto 2 (Villa Verde)
-- ================================================

INSERT INTO lotes (proyecto_id, codigo_lote, area_m2, precio_lista, estado) VALUES
(2, 'L-01', 500.00, 85000000, 'disponible'),
(2, 'L-02', 550.00, 92000000, 'disponible'),
(2, 'L-03', 600.00, 100000000, 'vendido'),
(2, 'L-04', 520.00, 88000000, 'vendido'),
(2, 'L-05', 480.00, 82000000, 'disponible');

-- Actualizar clientes en lotes vendidos
UPDATE lotes SET cliente_id = 3, precio_venta = 98000000, fecha_venta = '2024-04-01' WHERE codigo_lote = 'L-03' AND proyecto_id = 2;
UPDATE lotes SET cliente_id = 5, precio_venta = 87000000, fecha_venta = '2024-05-10' WHERE codigo_lote = 'L-04' AND proyecto_id = 2;

-- ================================================
-- PLAN DE AMORTIZACIÓN - Lote B-01 (Juan Carlos)
-- ================================================

-- Lote vendido: $39,000,000
-- Inicial: $9,000,000 (pagado)
-- Saldo financiado: $30,000,000
-- Plan: 24 cuotas mensuales de $1,250,000

INSERT INTO amortizaciones (lote_id, numero_cuota, valor_cuota, fecha_vencimiento, estado) VALUES
-- Cuotas pagadas (Feb-Abr 2024)
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 1, 1250000, '2024-03-10', 'pagada'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 2, 1250000, '2024-04-10', 'pagada'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 3, 1250000, '2024-05-10', 'pagada'),

-- Cuotas en mora (Mayo-Jun vencidas, hoy es ~Dic 2024)
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 4, 1250000, '2024-06-10', 'pendiente'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 5, 1250000, '2024-07-10', 'pendiente'),

-- Cuotas próximas a vencer
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 6, 1250000, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'pendiente'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 7, 1250000, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'pendiente'),

-- Cuotas futuras
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 8, 1250000, DATE_ADD(CURDATE(), INTERVAL 40 DAY), 'pendiente'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 9, 1250000, DATE_ADD(CURDATE(), INTERVAL 70 DAY), 'pendiente'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1), 10, 1250000, DATE_ADD(CURDATE(), INTERVAL 100 DAY), 'pendiente');

-- Marcar cuotas 1-3 como pagadas
UPDATE amortizaciones 
SET valor_pagado = 1250000, saldo_pendiente = 0, fecha_pago = fecha_vencimiento
WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1)
AND numero_cuota IN (1, 2, 3);

-- ================================================
-- PLAN DE AMORTIZACIÓN - Lote B-02 (María Fernanda)
-- ================================================

-- Lote vendido: $41,000,000
-- Inicial: $11,000,000 (pagado)
-- Saldo financiado: $30,000,000
-- Plan: 20 cuotas mensuales de $1,500,000

INSERT INTO amortizaciones (lote_id, numero_cuota, valor_cuota, fecha_vencimiento, estado) VALUES
-- Cuotas pagadas
((SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1), 1, 1500000, '2024-04-15', 'pagada'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1), 2, 1500000, '2024-05-15', 'pagada'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1), 3, 1500000, '2024-06-15', 'pagada'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1), 4, 1500000, '2024-07-15', 'pagada'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1), 5, 1500000, '2024-08-15', 'pagada'),

-- Cuotas próximas
((SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1), 6, 1500000, DATE_ADD(CURDATE(), INTERVAL 8 DAY), 'pendiente'),
((SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1), 7, 1500000, DATE_ADD(CURDATE(), INTERVAL 38 DAY), 'pendiente');

-- Marcar cuotas 1-5 como pagadas
UPDATE amortizaciones 
SET valor_pagado = 1500000, saldo_pendiente = 0, fecha_pago = fecha_vencimiento
WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1)
AND numero_cuota IN (1, 2, 3, 4, 5);

-- ================================================
-- REGISTRO DE PAGOS - Lote B-01
-- ================================================

INSERT INTO pagos (amortizacion_id, valor_pagado, metodo_pago, fecha_pago, numero_recibo) VALUES
-- Cuota 1
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1) AND numero_cuota = 1), 1250000, 'transferencia', '2024-03-10', 'REC-001'),
-- Cuota 2
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1) AND numero_cuota = 2), 1250000, 'efectivo', '2024-04-08', 'REC-002'),
-- Cuota 3
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-01' AND proyecto_id = 1) AND numero_cuota = 3), 1250000, 'cheque', '2024-05-10', 'REC-003');

-- ================================================
-- REGISTRO DE PAGOS - Lote B-02
-- ================================================

INSERT INTO pagos (amortizacion_id, valor_pagado, metodo_pago, fecha_pago, numero_recibo) VALUES
-- Cuotas 1-5
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1) AND numero_cuota = 1), 1500000, 'transferencia', '2024-04-15', 'REC-004'),
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1) AND numero_cuota = 2), 1500000, 'transferencia', '2024-05-14', 'REC-005'),
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1) AND numero_cuota = 3), 1500000, 'transferencia', '2024-06-15', 'REC-006'),
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1) AND numero_cuota = 4), 1500000, 'efectivo', '2024-07-13', 'REC-007'),
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'B-02' AND proyecto_id = 1) AND numero_cuota = 5), 1500000, 'tarjeta', '2024-08-15', 'REC-008');

-- ================================================
-- PLAN DE AMORTIZACIÓN - Lote L-03 (Inversiones S.A.S.)
-- ================================================

-- Lote vendido: $98,000,000
-- Inicial: $38,000,000 (pagado)
-- Saldo financiado: $60,000,000
-- Plan: 30 cuotas mensuales de $2,000,000

INSERT INTO amortizaciones (lote_id, numero_cuota, valor_cuota, fecha_vencimiento, estado) VALUES
-- Cuotas pagadas
((SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2), 1, 2000000, '2024-05-01', 'pagada'),
((SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2), 2, 2000000, '2024-06-01', 'pagada'),
((SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2), 3, 2000000, '2024-07-01', 'pagada'),
((SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2), 4, 2000000, '2024-08-01', 'pagada'),

-- Cuotas próximas
((SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2), 5, 2000000, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'pendiente'),
((SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2), 6, 2000000, DATE_ADD(CURDATE(), INTERVAL 33 DAY), 'pendiente');

-- Marcar cuotas 1-4 como pagadas
UPDATE amortizaciones 
SET valor_pagado = 2000000, saldo_pendiente = 0, fecha_pago = fecha_vencimiento
WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2)
AND numero_cuota IN (1, 2, 3, 4);

-- ================================================
-- REGISTRO DE PAGOS - Lote L-03
-- ================================================

INSERT INTO pagos (amortizacion_id, valor_pagado, metodo_pago, fecha_pago, numero_recibo) VALUES
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2) AND numero_cuota = 1), 2000000, 'transferencia', '2024-05-01', 'REC-009'),
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2) AND numero_cuota = 2), 2000000, 'transferencia', '2024-06-01', 'REC-010'),
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2) AND numero_cuota = 3), 2000000, 'transferencia', '2024-07-01', 'REC-011'),
((SELECT id FROM amortizaciones WHERE lote_id = (SELECT id FROM lotes WHERE codigo_lote = 'L-03' AND proyecto_id = 2) AND numero_cuota = 4), 2000000, 'transferencia', '2024-08-01', 'REC-012');

-- ================================================
-- RESUMEN DE DATOS INSERTADOS
-- ================================================

-- Proyectos: 3 (2 activos, 1 pausado)
-- Clientes: 5
-- Lotes: 15 (8 disponibles, 5 vendidos, 2 reservados)
-- Amortizaciones: 26 cuotas
-- Pagos: 12 transacciones

-- ================================================
-- VERIFICACIÓN FINAL
-- ================================================

SELECT 'Proyectos:' as Entidad, COUNT(*) as Total FROM proyectos
UNION ALL
SELECT 'Clientes:', COUNT(*) FROM clientes
UNION ALL
SELECT 'Lotes:', COUNT(*) FROM lotes
UNION ALL
SELECT 'Amortizaciones:', COUNT(*) FROM amortizaciones
UNION ALL
SELECT 'Pagos:', COUNT(*) FROM pagos;

-- Ver resumen de proyectos
SELECT * FROM vista_proyectos_resumen;

-- Ver cartera pendiente
SELECT 
    CONCAT('Cartera Pendiente: ', FORMAT(SUM(saldo_pendiente), 0)) as Resumen
FROM amortizaciones 
WHERE estado = 'pendiente';

-- Ver cuotas en mora
SELECT 
    CONCAT('Cuotas en Mora: ', COUNT(*), ' - Total: ', FORMAT(SUM(saldo_pendiente), 0)) as Resumen
FROM amortizaciones 
WHERE estado = 'pendiente' AND fecha_vencimiento < CURDATE();

-- ¡LISTO! El sistema tiene datos de prueba para testing
