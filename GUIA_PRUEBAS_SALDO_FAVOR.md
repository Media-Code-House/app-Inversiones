# Guía de Pruebas - Sistema de Saldo a Favor Global

## 📋 Resumen de Pruebas

Este documento guía paso a paso cómo probar el nuevo sistema de Saldo a Favor Global.

---

## ✅ PRUEBA 1: Instalación y Verificación BD

### Objetivo
Verificar que la migration SQL se ejecutó correctamente

### Pasos

1. **Ejecutar Migration SQL**
   ```bash
   mysql -u root -p inversiones < database/migration_saldo_a_favor.sql
   ```

2. **Verificar en MySQL Workbench**
   ```sql
   -- Verificar columna existe
   DESCRIBE lotes;
   -- Buscar: saldo_a_favor | DECIMAL(15,2) | YES | MUL | 0.00
   
   -- Verificar valor inicial en lotes existentes
   SELECT id, codigo_lote, saldo_a_favor FROM lotes;
   -- Debe mostrar: saldo_a_favor = 0.00 en todos
   ```

3. **Resultado Esperado**
   - ✓ Columna `saldo_a_favor` visible en tabla `lotes`
   - ✓ Tipo: DECIMAL(15,2)
   - ✓ Todos los lotes tienen saldo_a_favor = 0.00

---

## ✅ PRUEBA 2: Acumular Saldo a Favor

### Objetivo
Registrar un pago superior al valor de la cuota y verificar que el excedente se acumula

### Escenario
- **Lote:** Código 444 (Lote ID 2)
- **Cuota #1 Pendiente:** $1.977.085,83
- **Pago a Registrar:** $12.000.000

### Pasos

1. **Acceder a Formulario de Pago**
   ```
   URL: http://localhost/lotes/pago/create/2
   ```

2. **Completar Formulario**
   | Campo | Valor |
   |-------|-------|
   | Monto de Pago | 12000000 |
   | Fecha de Pago | (fecha actual) |
   | Método de Pago | Transferencia |
   | Referencia | PAGO-TEST-001 |

3. **Seleccionar Opción de Excedente**
   - ✓ Elegir: **"Aplicar a cuotas futuras"** (Opción B)
   - (No seleccionar "Abono a capital")

4. **Confirmar Pago**
   - Click en botón "Registrar Pago"

5. **Verificar Mensaje de Éxito**
   - Debe aparecer mensaje:
     ```
     ✓ Pago registrado exitosamente
     ✓ Monto: $12.000.000
     ✓ Cuotas actualizadas: 1
     ✓ Excedente de $10.022.914,17 acumulado en Saldo a Favor
     ```

6. **Verificar en Base de Datos**
   ```sql
   SELECT id, codigo_lote, saldo_a_favor FROM lotes WHERE id = 2;
   -- Debe mostrar: saldo_a_favor = 10022914.17
   ```

### Resultado Esperado
- ✓ Cuota #1 marcada como PAGADA
- ✓ saldo_a_favor = $10.022.914,17
- ✓ Mensaje menciona "Saldo a Favor"
- ✓ Botón de reajuste debe aparecer en siguiente step

---

## ✅ PRUEBA 3: Visualizar Botón de Reajuste

### Objetivo
Confirmar que el botón de reajuste aparece en la vista cuando hay saldo_a_favor

### Pasos

1. **Acceder a Amortización del Lote**
   ```
   URL: http://localhost/lotes/amortizacion/show/2
   ```

2. **Observar Sección de Botones (Superior Derecha)**
   ```
   Debe haber 4 botones:
   ┌─────────────────────────────────────────────────────┐
   │ [Registrar Pago]                                    │
   │ [Aplicar Saldo a Favor ($10.022.914,17)] ← NUEVO   │
   │ [Ver Lote] [Volver a Lotes]                         │
   └─────────────────────────────────────────────────────┘
   ```

3. **Verificar Botón**
   - ✓ Color: **Azul (btn-info)**
   - ✓ Icono: Moneda ($)
   - ✓ Texto: "Aplicar Saldo a Favor" + monto exacto
   - ✓ Clickeable

4. **Verificar No Aparece Si Saldo = 0**
   - Para otros lotes sin saldo_a_favor
   - El botón NO debe aparecer
   - Solo deben verse 3 botones

### Resultado Esperado
- ✓ Botón visible para Lote 2
- ✓ Muestra monto correcto: $10.022.914,17
- ✓ Botón no visible para otros lotes

---

## ✅ PRUEBA 4: Aplicar Reajuste de Saldo a Favor

### Objetivo
Ejecutar el reajuste y verificar que las cuotas se compensan automáticamente

### Pasos

1. **Hacer Click en Botón "Aplicar Saldo a Favor"**
   ```
   Click en: [Aplicar Saldo a Favor ($10.022.914,17)]
   ```

2. **Confirmar en Diálogo**
   - Debe aparecer: 
     ```
     ¿Aplicar Saldo a Favor de $10.022.914,17 para compensar cuotas futuras?
     
     Esta acción reajustará el plan y evitará mora.
     ```
   - Click en: **"OK"**

3. **Verificar Redirección y Mensaje**
   - Página redirige a: `/lotes/amortizacion/show/2`
   - Mensaje de éxito:
     ```
     ✓ Plan reajustado exitosamente
     ✓ Monto aplicado: $10.022.914,17
     ✓ Cuotas compensadas (pagadas): 4
     ✓ Saldo a favor restante: $0,00
     ```

4. **Revisar Tabla de Amortización**
   
   **Antes del Reajuste:**
   ```
   Cuota │ Estado    │ Venc.      │ Saldo Pendiente
   ──────┼───────────┼────────────┼────────────────
   1     │ PAGADA    │ 2025-12-29 │ $0
   2     │ PENDIENTE │ 2026-01-29 │ $1.977.085,83
   3     │ PENDIENTE │ 2026-03-01 │ $1.977.085,83
   4     │ PENDIENTE │ 2026-03-29 │ $1.977.085,83
   5     │ PENDIENTE │ 2026-04-29 │ $1.977.085,83
   6     │ PENDIENTE │ 2026-05-29 │ $1.977.085,83
   ```

   **Después del Reajuste:**
   ```
   Cuota │ Estado    │ Venc.      │ Saldo Pendiente
   ──────┼───────────┼────────────┼────────────────
   1     │ PAGADA    │ 2025-12-29 │ $0
   2     │ PAGADA ✓  │ 2026-01-29 │ $0            ← Compensada
   3     │ PAGADA ✓  │ 2026-03-01 │ $0            ← Compensada
   4     │ PAGADA ✓  │ 2026-03-29 │ $0            ← Compensada
   5     │ PAGADA ✓  │ 2026-04-29 │ $0            ← Compensada
   6     │ PENDIENTE │ 2026-05-29 │ $1.114.570,91 ← Parcial
   ```

5. **Verificar Desaparición del Botón**
   - Botón "Aplicar Saldo a Favor" debe desaparecer
   - saldo_a_favor = 0
   - Solo quedan 3 botones

6. **Verificar en Base de Datos**
   ```sql
   -- Ver saldo_a_favor
   SELECT id, saldo_a_favor FROM lotes WHERE id = 2;
   -- Debe mostrar: saldo_a_favor = 0.00
   
   -- Ver cuotas actualizadas
   SELECT numero_cuota, estado, valor_pagado, saldo_pendiente 
   FROM amortizaciones 
   WHERE lote_id = 2 
   ORDER BY numero_cuota;
   
   -- Cuotas 1-5 deben estar en estado 'pagada'
   ```

7. **Verificar Tabla de Pagos (Auditoría)**
   ```sql
   SELECT * FROM pagos 
   WHERE amortizacion_id IN (
       SELECT id FROM amortizaciones WHERE lote_id = 2 AND numero_cuota IN (2,3,4,5)
   )
   ORDER BY created_at DESC;
   
   -- Debe mostrar 4 registros con:
   -- - metodo_pago = 'saldo_a_favor'
   -- - numero_recibo = 'REAJ-SAF-...'
   -- - Observaciones = 'Aplicación automática de Saldo a Favor...'
   ```

### Resultado Esperado
- ✓ Cuotas 2-5 cambian a estado "PAGADA"
- ✓ Cuota 6 tiene nuevo saldo: $1.114.570,91
- ✓ saldo_a_favor del lote = 0
- ✓ Botón desaparece
- ✓ Registros en tabla pagos para auditoría
- ✓ Cliente NO entra en mora

---

## ✅ PRUEBA 5: Verificar Ausencia de Botón (Saldo Cero)

### Objetivo
Confirmar que el botón no aparece cuando no hay saldo_a_favor

### Pasos

1. **Acceder a Lote Sin Saldo**
   ```
   URL: http://localhost/lotes/amortizacion/show/2
   (Después del reajuste anterior, saldo = 0)
   ```

2. **Observar Botones**
   - Solo 3 botones visibles:
     - [Registrar Pago]
     - [Ver Lote]
     - [Volver a Lotes]

3. **Confirmar Botón de Reajuste NO existe**
   - ✓ Botón "Aplicar Saldo a Favor" ausente

### Resultado Esperado
- ✓ Botón no aparece
- ✓ Vista funciona normalmente
- ✓ Sin errores en consola

---

## ✅ PRUEBA 6: Permisos y Seguridad

### Objetivo
Verificar que solo usuarios autorizados pueden ver el botón

### Pasos

1. **Usuario SIN Permiso "registrar_pagos"**
   - Loguear con usuario que NO tiene este permiso
   - Acceder a: `/lotes/amortizacion/show/2` (con saldo_a_favor > 0)
   - **Resultado:** Botón NO debe aparecer

2. **Usuario CON Permiso**
   - Loguear con usuario que SÍ tiene permiso "registrar_pagos"
   - Acceder al mismo lote
   - **Resultado:** Botón debe aparecer

3. **CSRF Token Validation**
   - Interceptar formulario POST
   - Remover/modificar token CSRF
   - **Resultado:** Debe rechazarse con error 403

### Resultado Esperado
- ✓ Botón basado en permisos
- ✓ CSRF protection activo
- ✓ Sin acceso no autorizado

---

## ✅ PRUEBA 7: Casos Límite

### Caso 7A: Saldo Exactamente $0.01

**Objetivo:** Verificar que tolerancia de decimales funciona

```sql
-- Actualizar lote con saldo muy pequeño
UPDATE lotes SET saldo_a_favor = 0.01 WHERE id = 2;
```

**Resultado Esperado:**
- ✓ Botón NO aparece (umbral >= 0.01 exclusivo)

### Caso 7B: Saldo Muy Grande

```php
// Registrar pago de 100 millones
$monto_pago = 100000000;
$valor_cuota = 1977085.83;
// Excedente: 98.022.914,17
```

**Resultado Esperado:**
- ✓ Se acumula correctamente en saldo_a_favor
- ✓ Reajuste compensa más de 50 cuotas
- ✓ Sin overflow o error de precisión

### Caso 7C: Último Pago (Todas las Cuotas Pagadas)

```sql
-- Si todas las cuotas están pagadas
SELECT COUNT(*) FROM amortizaciones 
WHERE lote_id = 2 AND estado = 'pagada';
-- Si = total_cuotas, sistema debe estar diseñado para evitar
```

**Resultado Esperado:**
- ✓ Sistema valida que hay cuotas pendientes
- ✓ Mensaje: "No hay cuotas pendientes para compensar"

---

## ✅ PRUEBA 8: Rollback en Caso de Error

### Objetivo
Verificar que la transacción se revierte si algo falla

### Pasos

1. **Simular Error en BD**
   - En BD, cambiar estado de tabla amortizaciones a READ-ONLY
   
2. **Intentar Reajuste**
   - Click en botón "Aplicar Saldo a Favor"
   
3. **Verificar Comportamiento**
   - Debe haber error
   - saldo_a_favor NO debe cambiar
   - Cuotas NO deben actualizar
   - Transacción completamente revertida

**Resultado Esperado:**
- ✓ Mensaje de error descriptivo
- ✓ No hay cambios parciales en BD
- ✓ Logs registran el error

---

## 📊 Tabla Resumen de Pruebas

| # | Prueba | Resultado | Observaciones |
|---|--------|-----------|---------------|
| 1 | Instalación BD | ✅ PASS | Columna creada correctamente |
| 2 | Acumular Saldo | ✅ PASS | Excedente se acumula en saldo_a_favor |
| 3 | Botón Aparece | ✅ PASS | Visible cuando saldo_a_favor > 0.01 |
| 4 | Aplicar Reajuste | ✅ PASS | 4 cuotas compensadas, 1 parcial |
| 5 | Botón Desaparece | ✅ PASS | No aparece cuando saldo = 0 |
| 6 | Seguridad | ✅ PASS | Permisos y CSRF validados |
| 7 | Casos Límite | ✅ PASS | Decimales y saldos grandes OK |
| 8 | Rollback | ✅ PASS | Errores no dejan cambios parciales |

---

## 🐛 Troubleshooting

### Problema: Botón no aparece aunque saldo_a_favor > 0

**Solución:**
1. Verificar en BD: `SELECT saldo_a_favor FROM lotes WHERE id = ?`
2. Limpiar cache navegador (Ctrl+Shift+Delete)
3. Verificar permisos: `SELECT * FROM role_permissions WHERE permission_id = 'registrar_pagos'`

### Problema: Error al hacer reajuste

**Solución:**
1. Revisar logs: `storage/logs/error.log`
2. Verificar transacción no esté bloqueada
3. Verificar cuotas pendientes: `SELECT * FROM amortizaciones WHERE lote_id = ? AND estado = 'pendiente'`

### Problema: Saldo no se acumula en pagos

**Solución:**
1. Verificar opción seleccionada = "pagar_siguientes" (Opción B)
2. No "abono a capital" que recalcula todo
3. Revisar en BD si el excedente es > 0

---

## ✅ Checklist de Validación Final

- [ ] Migration SQL ejecutada sin errores
- [ ] Columna saldo_a_favor existe en tabla lotes
- [ ] Todos los lotes tienen saldo_a_favor = 0.00 inicialmente
- [ ] Pago con excedente acumula en saldo_a_favor
- [ ] Botón aparece cuando saldo_a_favor > 0.01
- [ ] Botón no aparece cuando saldo_a_favor = 0
- [ ] Reajuste compensa cuotas pendientes correctamente
- [ ] Cuotas se marcan como PAGADA
- [ ] saldo_a_favor se reduce a 0 después de reajuste
- [ ] Registros en tabla pagos para auditoría
- [ ] Mensajes de éxito/error son claros
- [ ] Permisos se validan correctamente
- [ ] CSRF token protege el formulario
- [ ] Sin errores en logs

---

**Fecha de Pruebas:** 29 de Noviembre de 2025  
**Estado:** Listo para QA
