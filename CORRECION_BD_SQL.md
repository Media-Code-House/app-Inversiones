================================================================================
        CORRECIÓN: SQL de Prueba Actualizado para BD Real
        Fecha: 2025-11-29
================================================================================

## ✅ ARCHIVOS ACTUALIZADOS

1. database/test_datos_mora_saldo_favor.sql
   └─ CORRECCIONES APLICADAS:
      ✅ Cambiado: clientes.cedula → clientes.numero_documento
      ✅ Cambiado: clientes.nombres/apellidos → clientes.nombre
      ✅ Cambiado: clientes.estado → removido (no existe)
      ✅ Cambiado: clientes.tipo_cliente → removido (no existe)
      ✅ Cambiado: lotes.valor_lote → lotes.precio_lista
      ✅ Cambiado: lotes.valor_cuota → removido (calculado)
      ✅ Cambiado: lotes.numero_cuotas → correcto
      ✅ Cambiado: lotes.fecha_inicio → lotes.fecha_inicio_amortizacion
      ✅ Cambiado: lotes.estado = 'activo' → 'vendido'
      ✅ Cambiado: proyectos.nombre/descripcion → proyectos.codigo/nombre
      ✅ Cambiado: amortizaciones.saldo_inicial/saldo_final → saldo
      ✅ Cambiado: amortizaciones.fecha_creacion → created_at
      ✅ Cambiado: pagos.lote_id/monto_pagado → removidos
      ✅ Cambiado: pagos.amortizacion_id/valor_pagado → correcto
      ✅ Cambiado: pagos.concepto/referencia → observaciones
      ✅ Agregado: Validación IF EXISTS para saldo_a_favor

## 📊 ESTRUCTURA DE TABLAS (REAL)

### clientes
├─ id (PK)
├─ tipo_documento (enum: CC, NIT, CE, pasaporte)
├─ numero_documento (VARCHAR 50) ← ANTES: cedula
├─ nombre (VARCHAR 200) ← ANTES: nombres/apellidos
├─ telefono
├─ email
├─ direccion
├─ ciudad
├─ observaciones
├─ created_at
└─ updated_at

### proyectos
├─ id (PK)
├─ codigo (VARCHAR 20) ← REQUERIDO
├─ nombre (VARCHAR 150)
├─ ubicacion
├─ descripcion
├─ estado (enum: activo, completado, pausado, cancelado)
├─ fecha_inicio
├─ fecha_finalizacion
├─ total_lotes
├─ observaciones
├─ created_at
└─ updated_at

### lotes
├─ id (PK)
├─ proyecto_id (FK)
├─ codigo_lote
├─ manzana
├─ ubicacion
├─ area_m2
├─ precio_lista ← ANTES: valor_lote
├─ precio_venta
├─ cuota_inicial
├─ monto_financiado
├─ tasa_interes
├─ numero_cuotas
├─ fecha_inicio_amortizacion ← ANTES: fecha_inicio
├─ estado (enum: disponible, reservado, vendido, bloqueado)
├─ cliente_id (FK)
├─ vendedor_id
├─ fecha_venta
├─ observaciones
├─ descripcion
├─ saldo_a_favor (DECIMAL 15,2) ← NUEVA (migration)
├─ created_at
└─ updated_at

### amortizaciones
├─ id (PK)
├─ lote_id (FK)
├─ numero_cuota
├─ fecha_vencimiento
├─ fecha_pago
├─ estado (enum: pendiente, pagada, cancelada)
├─ valor_cuota
├─ capital
├─ interes
├─ saldo ← ANTES: saldo_inicial/saldo_final
├─ valor_pagado
├─ saldo_pendiente (GENERATED)
├─ dias_mora
├─ observaciones
├─ created_at
└─ updated_at

### pagos
├─ id (PK)
├─ amortizacion_id (FK) ← ANTES: lote_id + monto_pagado
├─ fecha_pago
├─ valor_pagado ← ANTES: monto_pagado
├─ metodo_pago (enum: efectivo, transferencia, cheque, tarjeta, otro)
├─ numero_recibo ← ANTES: referencia
├─ observaciones ← ANTES: concepto
├─ created_at
└─ updated_at

## 🔄 MAPEO DE CAMBIOS

### Script Original → Corregido

ANTES:
```sql
INSERT INTO clientes (nombres, apellidos, cedula, ...)
VALUES ('Cliente', 'Prueba Mora', '1234567890', ...);
```

DESPUÉS:
```sql
INSERT INTO clientes (tipo_documento, numero_documento, nombre, ...)
VALUES ('CC', '1234567890', 'Cliente Prueba Mora', ...);
```

ANTES:
```sql
INSERT INTO lotes (valor_lote, valor_cuota, fecha_inicio, estado, ...)
VALUES (20000000, 444927, '2025-01-15', 'activo', ...);
```

DESPUÉS:
```sql
INSERT INTO lotes (precio_lista, monto_financiado, 
                   fecha_inicio_amortizacion, estado, ...)
VALUES (20000000, 20000000, '2025-02-15', 'vendido', ...);
```

ANTES:
```sql
INSERT INTO amortizaciones (saldo_inicial, saldo_final, fecha_creacion, ...)
VALUES (20000000, 19000000, NOW(), ...);
```

DESPUÉS:
```sql
INSERT INTO amortizaciones (saldo, created_at, ...)
VALUES (19000000, NOW(), ...);
```

ANTES:
```sql
INSERT INTO pagos (lote_id, monto_pagado, concepto, referencia, ...)
VALUES (@lote_id, 1000000, 'Pago Cuota 1', 'TRF-001', ...);
```

DESPUÉS:
```sql
INSERT INTO pagos (amortizacion_id, valor_pagado, observaciones, ...)
VALUES (@cuota_1_id, 2500000, 'Pago Cuota 1', ...);
```

## 📝 VALIDACIONES APLICADAS

✅ IF EXISTS para verificar saldo_a_favor antes de actualizar
✅ DELETE FROM amortizaciones limpia cuotas anteriores
✅ DELIMITER $$ para permitir bucles WHILE
✅ Queries comentadas para debugging
✅ LIMIT 1 en todos los SELECT para UNICIDad
✅ NOW() para timestamps

## 🔍 PRUEBAS DE VERIFICACIÓN

Ejecuta después del setup:

```sql
-- 1. Verificar cliente creado
SELECT id, tipo_documento, numero_documento, nombre 
FROM clientes WHERE numero_documento = '1234567890';

-- 2. Verificar proyecto creado
SELECT id, codigo, nombre 
FROM proyectos WHERE codigo = 'PRY-TEST';

-- 3. Verificar lote creado
SELECT id, codigo_lote, saldo_a_favor, numero_cuotas 
FROM lotes WHERE codigo_lote = 'LOTE-TEST-001';

-- 4. Verificar 5 primeras cuotas
SELECT numero_cuota, fecha_vencimiento, valor_cuota, estado, valor_pagado 
FROM amortizaciones 
WHERE lote_id IN (SELECT id FROM lotes WHERE codigo_lote = 'LOTE-TEST-001')
ORDER BY numero_cuota LIMIT 5;

-- 5. Verificar pagos
SELECT p.*, a.numero_cuota 
FROM pagos p
JOIN amortizaciones a ON p.amortizacion_id = a.id
WHERE a.lote_id IN (SELECT id FROM lotes WHERE codigo_lote = 'LOTE-TEST-001')
ORDER BY p.fecha_pago;
```

## 📋 ESTADO FINAL ESPERADO

Cuota 1 (Feb 15):
  - Estado: PAGADA
  - Valor Pagado: $2.500.000
  - Saldo Pendiente: $0
  - Pago en BD: SÍ (referencia TRF-2025-02-001)

Cuota 2 (Mar 15):
  - Estado: PENDIENTE
  - Valor Pagado: $0
  - Saldo Pendiente: $1.977.085
  - Pago en BD: NO

Cuota 3 (Apr 15):
  - Estado: PENDIENTE
  - Valor Pagado: $0
  - Saldo Pendiente: $1.977.085
  - Pago en BD: NO

Cuota 4 (May 15):
  - Estado: PAGADA
  - Valor Pagado: $1.977.085
  - Saldo Pendiente: $0
  - Pago en BD: SÍ (referencia TRF-2025-05-001)

Lote:
  - Saldo a Favor: $522.914
  - Botón debe aparecer: SÍ
  - Cliente: EN MORA (Cuotas 2 y 3 sin pagar)

## ✅ PRÓXIMO PASO

1. Ejecutar migration SQL
2. Ejecutar este SQL corregido
3. Verificar datos con queries anteriores
4. Abrir lote en navegador
5. Hacer click en botón "Aplicar Saldo a Favor"
6. ¡Listo!

================================================================================
Archivo: database/test_datos_mora_saldo_favor.sql ← ACTUALIZADO ✅
================================================================================
