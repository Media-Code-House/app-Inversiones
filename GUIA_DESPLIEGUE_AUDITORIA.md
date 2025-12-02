# GUÍA DE DESPLIEGUE Y AUDITORÍA
## Corrección de Abonos Extraordinarios a Capital

**Fecha:** 2 de diciembre de 2025  
**Versión:** 1.0  
**Prioridad:** Alta  

---

## 🚀 PARTE 1: DESPLIEGUE A PRODUCCIÓN

### Paso 1: Backup de Seguridad
```bash
# Backup de archivos PHP
cp app/Controllers/PagoController.php app/Controllers/PagoController.php.backup.20251202
cp app/Controllers/AmortizacionController.php app/Controllers/AmortizacionController.php.backup.20251202

# Backup de base de datos
mysqldump -u usuario -p nombre_bd > backup_bd_20251202.sql
```

### Paso 2: Validar Cambios en Desarrollo
```bash
# Ejecutar script de validación
php validar_correccion_abonos.php

# Resultado esperado:
# ✅ VALIDACIÓN EXITOSA
# Nueva Cuota: $158.145,69
# Diferencia: $0.00 (dentro de tolerancia)
```

### Paso 3: Verificar Sintaxis
```bash
# Validar sintaxis PHP
php -l app/Controllers/PagoController.php
php -l app/Controllers/AmortizacionController.php

# Resultado esperado:
# No syntax errors detected
```

### Paso 4: Copiar Archivos Corregidos
```bash
# Copiar a producción (ajustar rutas según servidor)
cp app/Controllers/PagoController.php /ruta/produccion/app/Controllers/
cp app/Controllers/AmortizacionController.php /ruta/produccion/app/Controllers/
```

### Paso 5: Verificar en Producción
1. Acceder al sistema en producción
2. Ir a un lote con amortización activa
3. Intentar registrar un pago con excedente
4. Verificar que aparezcan los nuevos logs en el archivo de logs

---

## 🔍 PARTE 2: AUDITORÍA DE DATOS HISTÓRICOS

### Consulta 1: Identificar Lotes con Abonos Extraordinarios

```sql
-- Lotes que potencialmente tuvieron abonos extraordinarios
SELECT 
    l.id AS lote_id,
    l.codigo_lote,
    l.estado,
    COUNT(DISTINCT a.id) AS total_cuotas,
    COUNT(DISTINCT p.id) AS total_pagos,
    SUM(CASE WHEN p.valor_pagado > a.valor_cuota * 1.1 THEN 1 ELSE 0 END) AS pagos_excedentes,
    SUM(p.valor_pagado) AS total_pagado,
    SUM(a.valor_cuota) AS total_contratado
FROM lotes l
INNER JOIN amortizaciones a ON l.id = a.lote_id
LEFT JOIN pagos p ON a.id = p.amortizacion_id
WHERE l.estado IN ('vendido', 'cancelado')
GROUP BY l.id
HAVING pagos_excedentes > 0
ORDER BY pagos_excedentes DESC, l.id;
```

**Interpretar Resultados:**
- Si `pagos_excedentes > 0`: El lote tuvo pagos mayores a la cuota regular
- Estos lotes **podrían** haber sido afectados por la lógica antigua

---

### Consulta 2: Detalles de Abonos Extraordinarios

```sql
-- Detalle de pagos extraordinarios por lote
SELECT 
    l.id AS lote_id,
    l.codigo_lote,
    a.numero_cuota,
    a.valor_cuota AS cuota_regular,
    p.valor_pagado AS pago_realizado,
    (p.valor_pagado - a.valor_cuota) AS excedente,
    p.fecha_pago,
    p.observaciones,
    a.estado AS estado_cuota_actual
FROM lotes l
INNER JOIN amortizaciones a ON l.id = a.lote_id
INNER JOIN pagos p ON a.id = p.amortizacion_id
WHERE p.valor_pagado > a.valor_cuota * 1.05  -- Pagos 5% mayores a la cuota
ORDER BY l.id, p.fecha_pago;
```

**Interpretar Resultados:**
- `excedente > 0`: Monto del abono extraordinario
- `fecha_pago`: Fecha en que se aplicó (importante para determinar si fue con lógica antigua)
- `estado_cuota_actual`: Si la cuota aún está pendiente, se puede recalcular

---

### Consulta 3: Verificar Recálculos Previos

```sql
-- Identificar lotes donde se recalculó el plan
SELECT 
    l.id AS lote_id,
    l.codigo_lote,
    MIN(a.valor_cuota) AS cuota_minima,
    MAX(a.valor_cuota) AS cuota_maxima,
    AVG(a.valor_cuota) AS cuota_promedio,
    COUNT(DISTINCT a.valor_cuota) AS valores_cuota_distintos,
    CASE 
        WHEN COUNT(DISTINCT a.valor_cuota) > 2 THEN 'Probable Recálculo'
        ELSE 'Cuota Uniforme'
    END AS analisis
FROM lotes l
INNER JOIN amortizaciones a ON l.id = a.lote_id
WHERE l.estado = 'vendido'
GROUP BY l.id
HAVING valores_cuota_distintos > 2
ORDER BY valores_cuota_distintos DESC;
```

**Interpretar Resultados:**
- `valores_cuota_distintos > 2`: El plan fue recalculado al menos una vez
- `cuota_maxima > cuota_minima * 1.2`: Posible recálculo con lógica incorrecta

---

### Consulta 4: Cuotas Futuras Afectadas

```sql
-- Cuotas pendientes que podrían requerir ajuste
SELECT 
    l.id AS lote_id,
    l.codigo_lote,
    c.nombre AS cliente,
    a.numero_cuota,
    a.fecha_vencimiento,
    a.valor_cuota AS cuota_actual,
    a.capital,
    a.interes,
    a.saldo AS saldo_capital_pendiente,
    CASE 
        WHEN a.valor_cuota > (SELECT AVG(valor_cuota) FROM amortizaciones WHERE lote_id = l.id) * 1.15 
        THEN 'Revisar - Cuota Elevada'
        ELSE 'Normal'
    END AS alerta
FROM lotes l
INNER JOIN clientes c ON l.cliente_id = c.id
INNER JOIN amortizaciones a ON l.id = a.lote_id
WHERE l.estado = 'vendido'
  AND a.estado = 'pendiente'
  AND a.valor_cuota > (SELECT AVG(valor_cuota) FROM amortizaciones WHERE lote_id = l.id AND estado = 'pendiente') * 1.1
ORDER BY l.id, a.numero_cuota;
```

---

## 🛠️ PARTE 3: ACCIONES CORRECTIVAS (OPCIONAL)

### Opción A: Recalcular Planes Afectados (Recomendado)

Si se identifican lotes afectados por la lógica antigua:

```sql
-- Script para recalcular un lote específico
-- EJECUTAR MANUALMENTE POR CADA LOTE IDENTIFICADO

SET @lote_id = 123;  -- Reemplazar con ID del lote

-- 1. Calcular saldo de capital real
SELECT 
    @saldo_capital_real := SUM(capital),
    @numero_cuotas := COUNT(*),
    @tasa_mensual := (l.tasa_interes / 100 / 12)
FROM amortizaciones a
INNER JOIN lotes l ON a.lote_id = l.id
WHERE a.lote_id = @lote_id 
  AND a.estado = 'pendiente'
GROUP BY l.id;

-- 2. Ver valores calculados
SELECT 
    @saldo_capital_real AS saldo_capital,
    @numero_cuotas AS cuotas_pendientes,
    @tasa_mensual AS tasa_mensual;

-- 3. Calcular nueva cuota (manual - ejecutar en PHP o calculadora)
-- Nueva Cuota = saldo_capital * [r(1+r)^n] / [(1+r)^n - 1]
-- Donde r = tasa_mensual, n = numero_cuotas
```

### Opción B: Aplicar Crédito a Favor del Cliente

Si el recálculo es muy complejo:

```sql
-- Calcular diferencia entre cuota incorrecta y cuota correcta
-- Aplicar como crédito en saldo_a_favor del lote

UPDATE lotes
SET saldo_a_favor = saldo_a_favor + @monto_credito
WHERE id = @lote_id;

-- Registrar en logs
INSERT INTO logs (tipo, mensaje, fecha)
VALUES ('ajuste_abono', 'Crédito aplicado por corrección de abonos extraordinarios - Lote ID: ' + @lote_id, NOW());
```

---

## 📊 PARTE 4: MONITOREO POST-DESPLIEGUE

### Verificaciones Diarias (Primeros 7 días)

```sql
-- Monitorear pagos con excedentes aplicados HOY
SELECT 
    l.codigo_lote,
    c.nombre AS cliente,
    p.fecha_pago,
    p.valor_pagado,
    a.valor_cuota,
    (p.valor_pagado - a.valor_cuota) AS excedente,
    a_despues.valor_cuota AS nueva_cuota,
    (a.valor_cuota - a_despues.valor_cuota) AS reduccion_cuota
FROM pagos p
INNER JOIN amortizaciones a ON p.amortizacion_id = a.id
INNER JOIN lotes l ON a.lote_id = l.id
INNER JOIN clientes c ON l.cliente_id = c.id
LEFT JOIN amortizaciones a_despues ON a_despues.lote_id = l.id 
    AND a_despues.numero_cuota = a.numero_cuota + 1
WHERE DATE(p.fecha_pago) = CURDATE()
  AND p.valor_pagado > a.valor_cuota * 1.05
ORDER BY p.fecha_pago DESC;
```

**Validar:**
- ✅ `reduccion_cuota > 0`: La nueva cuota es menor (CORRECTO)
- ❌ `reduccion_cuota < 0`: La nueva cuota es mayor (ERROR - revisar logs)

---

### Revisar Logs de Sistema

```bash
# Ver logs de abonos extraordinarios
tail -f storage/logs/app.log | grep "aplicarAbonoCapital"

# Buscar errores
tail -f storage/logs/app.log | grep "ERROR"
```

**Logs Esperados:**
```
[INFO] === INICIO aplicarAbonoCapital() ===
[INFO] Saldo de Capital Real calculado: $3.235.000
[INFO] Nuevo Capital después del abono: $3.235.000
[INFO] Nueva cuota fija calculada: $158.145,69
[INFO] Reducción: $37.397,92 (19.13%)
[INFO] === FIN aplicarAbonoCapital() - Plan recalculado exitosamente ===
```

---

## ✅ CHECKLIST DE DESPLIEGUE

### Pre-Despliegue
- [ ] Backup de archivos PHP realizado
- [ ] Backup de base de datos realizado
- [ ] Script de validación ejecutado exitosamente
- [ ] Sintaxis PHP verificada sin errores
- [ ] Documentación revisada

### Despliegue
- [ ] Archivos copiados a producción
- [ ] Permisos de archivos verificados (644)
- [ ] Caché de PHP limpiado (si aplica)
- [ ] Sistema accesible después del despliegue

### Post-Despliegue
- [ ] Registrar pago de prueba con excedente
- [ ] Verificar que cuota nueva sea menor
- [ ] Revisar logs sin errores
- [ ] Ejecutar auditoría de datos históricos
- [ ] Documentar casos identificados

### Auditoría (Primeros 7 días)
- [ ] Día 1: Monitorear pagos con excedentes
- [ ] Día 2: Revisar logs de errores
- [ ] Día 3: Validar reducción de cuotas
- [ ] Día 7: Reporte de casos procesados

---

## 📞 CONTACTO EN CASO DE PROBLEMAS

### Síntoma 1: Cuota aumenta después de abono
**Causa:** La corrección no se aplicó correctamente  
**Acción:** 
1. Verificar que archivos en producción tengan el código corregido
2. Revisar logs para confirmar que se usa `array_sum(capital)`
3. Contactar a soporte técnico

### Síntoma 2: Error al registrar pago
**Causa:** Posible error de sintaxis o base de datos  
**Acción:**
1. Revisar logs: `tail -f storage/logs/app.log`
2. Verificar conexión a base de datos
3. Rollback a versión anterior si es crítico

### Síntoma 3: Resultados diferentes a lo esperado
**Causa:** Parámetros incorrectos o datos inconsistentes  
**Acción:**
1. Ejecutar `validar_correccion_abonos.php`
2. Comparar con ejemplo documentado ($158.145,69)
3. Revisar tasas de interés y plazos en base de datos

---

## 📋 REPORTE FINAL

Después de completar el despliegue y auditoría, documentar:

1. **Fecha de Despliegue:** _______________
2. **Responsable:** _______________
3. **Lotes Identificados con Abonos Históricos:** _______________
4. **Lotes Recalculados:** _______________
5. **Créditos Aplicados:** _______________
6. **Problemas Encontrados:** _______________
7. **Estado Final:** ⬜ Exitoso  ⬜ Con observaciones  ⬜ Requiere revisión

---

**Preparado por:** GitHub Copilot (Claude Sonnet 4.5)  
**Fecha:** 2 de diciembre de 2025  
**Versión:** 1.0
