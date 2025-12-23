# 🧪 SETUP: Datos de Prueba para Sistema de Saldo a Favor Global

## 📋 Descripción

Este setup crea un escenario realista donde:

- **Cliente pagó de más en Cuota 1:** $1.000.000 (cuota era $444.927)
- **Excedente:** $555.073 se acumula en `saldo_a_favor`
- **Cuotas 2 y 3:** SIN PAGAR → En MORA
- **Objetivo:** Usar el botón "Aplicar Saldo a Favor" para compensar la mora

## 🚀 Pasos para Probar

### Paso 1: Ejecutar Setup
Accede a tu navegador:

```
https://inversiones.mch.com.co/setup_test_data.php
```

Esto creará automáticamente:
- ✅ Cliente de prueba (Cédula: 1234567890)
- ✅ Proyecto de prueba
- ✅ Lote con 60 cuotas
- ✅ Pagos registrados (con mora y saldo a favor)

**Resultado esperado:**
```
✅ Lote ID: [número]
✅ Saldo a Favor: $555.073
⚠️  Cuotas en MORA: 2 y 3
```

El script te mostrará el **LOTE ID** a usar.

---

### Paso 2: Ver el Lote en Amortización

Abre (reemplaza {ID} con el lote ID del paso anterior):

```
https://inversiones.mch.com.co/lotes/amortizacion/show/{ID}
```

**Verás:**

#### ✅ Botón en la cabecera:
```
[ Aplicar Saldo a Favor ($555.073)]  ← Este es el botón que buscabas
```

#### ⚠️ Tabla de Amortización:
```
┌────┬──────────┬──────────┬──────────┬────────┐
│ #  │Vencim.   │ Cuota    │ Pagado   │ Estado │
├────┼──────────┼──────────┼──────────┼────────┤
│ 1  │ Feb 15   │ $444.927 │ $1.000k  │ ✅ PAGADA
│ 2  │ Mar 15   │ $444.927 │ $0       │ ⚠️ MORA     ← FALTA PAGAR
│ 3  │ Apr 15   │ $444.927 │ $0       │ ⚠️ MORA     ← FALTA PAGAR
│ 4  │ May 15   │ $444.927 │ $444.927 │ ✅ PAGADA
│ 5  │ Jun 15   │ $444.927 │ $444.927 │ ✅ PAGADA
│ 6  │ Jul 15   │ $444.927 │ $0       │ ⏳ PENDIENTE
└────┴──────────┴──────────┴──────────┴────────┘

SALDO A FAVOR DISPONIBLE: $555.073
[ Aplicar Saldo a Favor ($555.073)]
```

---

### Paso 3: Hacer Click en el Botón

1. **Haz click en:** ` Aplicar Saldo a Favor ($555.073)`

2. **Aparecerá diálogo de confirmación:**
   ```
   ¿Aplicar Saldo a Favor de $555.073 para compensar 
   cuotas futuras?
   
   Esta acción reajustará el plan y evitará mora.
   
   [Cancelar]  [Aceptar]
   ```

3. **Haz click en `Aceptar`**

---

### Paso 4: Ver Resultado del Reajuste

**Después de hacer click:**

✅ **Mensaje de éxito:**
```
Reajuste completado exitosamente
Cuotas compensadas: 2
Saldo a favor restante: $110.146
```

✅ **Tabla actualizada:**
```
┌────┬──────────┬──────────┬──────────┬────────┐
│ #  │Vencim.   │ Cuota    │ Pagado   │ Estado │
├────┼──────────┼──────────┼──────────┼────────┤
│ 1  │ Feb 15   │ $444.927 │ $1.000k  │ ✅ PAGADA
│ 2  │ Mar 15   │ $444.927 │ $444.927 │ ✅ PAGADA   ← COMPENSADA
│ 3  │ Apr 15   │ $444.927 │ $110.146 │ ⏳ PENDIENTE ← PARCIAL
│ 4  │ May 15   │ $444.927 │ $444.927 │ ✅ PAGADA
│ 5  │ Jun 15   │ $444.927 │ $444.927 │ ✅ PAGADA
│ 6  │ Jul 15   │ $444.927 │ $0       │ ⏳ PENDIENTE
└────┴──────────┴──────────┴──────────┴────────┘

SALDO A FAVOR DISPONIBLE: $0.00
(Botón desapareció - saldo agotado)
```

✅ **Cliente está FUERA de MORA**

---

## 🔍 Validaciones Técnicas

### Verificar en MySQL:

```sql
-- 1. Ver el lote creado
SELECT id, codigo_lote, saldo_a_favor, numero_cuotas 
FROM lotes 
WHERE codigo_lote LIKE 'LOTE-TEST-%' 
ORDER BY id DESC LIMIT 1;

-- 2. Ver cuotas (primeras 6)
SELECT numero_cuota, fecha_vencimiento, valor_cuota, estado, valor_pagado 
FROM amortizaciones 
WHERE lote_id = [LOTE_ID] 
ORDER BY numero_cuota ASC 
LIMIT 6;

-- 3. Ver pagos registrados
SELECT amortizacion_id, monto_pagado, metodo_pago, fecha_pago, estado 
FROM pagos 
WHERE lote_id = [LOTE_ID] 
ORDER BY fecha_pago ASC;

-- 4. Ver saldo a favor actual
SELECT saldo_a_favor FROM lotes WHERE id = [LOTE_ID];
```

---

## ⚙️ Detalles del Escenario

### Cliente de Prueba:
```
Nombre: Cliente Prueba Mora
Cédula: 1234567890
Email: prueba@test.com
Estado: activo
```

### Proyecto de Prueba:
```
Nombre: Proyecto Prueba Saldo a Favor
Ubicación: Medellín
Estado: activo
```

### Configuración del Lote:
```
Monto: $20.000.000
Plazo: 60 meses (5 años)
Tasa: 12% anual (1% mensual)
Cuota fija: $444.927
Método: Francés (amortización con cuota fija)
```

### Pagos Registrados:
```
1. Cuota 1 (Feb 15): $1.000.000 ✅ PAGADA
   └─ Excedente: $555.073 → saldo_a_favor
   
2. Cuota 2 (Mar 15): $0 ⚠️ NO PAGADA (MORA)

3. Cuota 3 (Apr 15): $0 ⚠️ NO PAGADA (MORA)

4. Cuota 4 (May 15): $444.927 ✅ PAGADA

5. Cuota 5 (Jun 15): $444.927 ✅ PAGADA

6-60. PENDIENTES
```

---

## 📝 Flujo Completo de la Prueba

```
┌──────────────────────────────────────────────────────────┐
│ SITUACIÓN INICIAL                                        │
├──────────────────────────────────────────────────────────┤
│ • Saldo a Favor: $555.073                               │
│ • Cuota 2: MORA ($444.927 sin pagar)                    │
│ • Cuota 3: MORA ($444.927 sin pagar)                    │
│ • Cliente: EN RIESGO DE EMBARGO                         │
└──────────────────────────────────────────────────────────┘
                          ↓
                    [Click Botón]
                          ↓
┌──────────────────────────────────────────────────────────┐
│ PROCESAMIENTO (Backend)                                  │
├──────────────────────────────────────────────────────────┤
│ 1. Validar permisos ✅                                   │
│ 2. Validar CSRF token ✅                                │
│ 3. Iniciar transacción ACID                            │
│ 4. Iterar cuotas:                                       │
│    - Cuota 2: Compensar $444.927 → PAGADA             │
│    - Cuota 3: Compensar $110.146 → PENDIENTE          │
│ 5. Registrar en auditoría (tabla pagos)                │
│ 6. Actualizar saldo_a_favor = 0                        │
│ 7. COMMIT transacción                                  │
│ 8. Loguear todo                                        │
└──────────────────────────────────────────────────────────┘
                          ↓
┌──────────────────────────────────────────────────────────┐
│ SITUACIÓN FINAL                                          │
├──────────────────────────────────────────────────────────┤
│ • Saldo a Favor: $0.00 ✅                              │
│ • Cuota 2: PAGADA ✅ (compensada)                      │
│ • Cuota 3: PENDIENTE (parcial) ⏳                      │
│ • Cliente: FUERA DE MORA ✅                            │
│ • Botón: DESAPARECE (saldo agotado)                    │
└──────────────────────────────────────────────────────────┘
```

---

## 🐛 Troubleshooting

### Problema: El setup no crea datos

**Solución:**
1. Verifica que la migration SQL se ejecutó:
   ```bash
   mysql -u root -p inversiones < database/migration_saldo_a_favor.sql
   ```

2. Verifica que la columna existe:
   ```sql
   SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
   WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor';
   ```

### Problema: El botón no aparece

**Solución:**
1. Verifica que `saldo_a_favor > 0.01`:
   ```sql
   SELECT saldo_a_favor FROM lotes WHERE id = [LOTE_ID];
   ```

2. Verifica que tengas permisos `registrar_pagos`:
   - Abre sesión con usuario admin o con permiso

3. Verifica que la vista tenga la variable:
   - Abre DevTools → Console → Verifica que `$saldo_a_favor` existe

### Problema: El reajuste no funciona

**Solución:**
1. Verifica logs: `storage/logs/`
2. Verifica que hay cuotas PENDIENTE después de la pagada
3. Verifica transacciones en MySQL

---

## 📊 Monitores Post-Prueba

### En la tabla de amortización:
- ✅ Cuota 2 debe mostrar estado PAGADA
- ✅ Cuota 3 debe mostrar saldo parcial
- ✅ Botón debe desaparecer

### En base de datos:
```sql
-- Ver cuotas compensadas
SELECT * FROM amortizaciones 
WHERE lote_id = [LOTE_ID] 
AND numero_cuota IN (2,3);

-- Ver auditoría (tabla pagos)
SELECT * FROM pagos 
WHERE lote_id = [LOTE_ID] 
AND metodo_pago = 'saldo_a_favor';

-- Ver saldo final
SELECT saldo_a_favor FROM lotes WHERE id = [LOTE_ID];
```

---

## ✅ Éxito

Si todo funciona correctamente, verás:

1. ✅ Botón azul aparece
2. ✅ Confirmación funciona
3. ✅ Cuotas se marcan como PAGADA
4. ✅ Saldo se reduce a 0
5. ✅ Botón desaparece
6. ✅ Cliente sale de mora

**¡Sistema funcionando 100%!** 🎉

---

## 📞 Soporte

Si hay problemas:
1. Ejecuta `/test_saldo_a_favor.php` para diagnóstico
2. Revisa los logs en `storage/logs/`
3. Verifica que la BD tiene saldo_a_favor
4. Verifica permisos del usuario

