# Sistema de Saldo a Favor Global - Documentación Técnica

## 📋 Resumen Ejecutivo

Se ha implementado un **sistema integral de Saldo a Favor Global** que permite compensar automáticamente excedentes de pagos con cuotas futuras, evitando que un cliente que pagó de más (ej: 10 millones de excedente) entre en mora por cuotas vencidas.

**Problema Resuelto:**
- Cliente paga cuota #1 por $1.977.085,83 pero envía $12.000.000
- Excedente: $10.022.914,17
- **Antes:** Se perdía o se aplicaba solo a capital
- **Después:** Se acumula en "Saldo a Favor" y puede aplicarse para compensar mora automáticamente

---

## 🏗️ Arquitectura del Sistema

### 1. **Esquema de Base de Datos**

#### Nueva Columna: `lotes.saldo_a_favor`
```sql
ALTER TABLE `lotes` 
ADD COLUMN `saldo_a_favor` DECIMAL(15,2) DEFAULT 0.00 
COMMENT 'Saldo acumulado de pagos excedentes para aplicar a cuotas futuras'
AFTER `numero_cuotas`;

CREATE INDEX `idx_lotes_saldo_a_favor` ON `lotes` (`saldo_a_favor`, `estado`);
```

**Características:**
- Tipo: `DECIMAL(15,2)` para precisión monetaria
- Valor por defecto: 0.00
- Indexada para consultas rápidas

---

## 💻 Componentes Implementados

### 2. **Modelo: LoteModel**

Métodos agregados:

#### `getSaldoAFavor($loteId): float`
- Obtiene el saldo a favor disponible de un lote
- Retorna 0 si no hay saldo
- **Uso:** Verificar si hay compensación disponible

#### `setSaldoAFavor($loteId, $monto): bool`
- Actualiza el saldo a favor a un monto específico
- Asegura que no sea negativo (GREATEST)
- **Uso:** Reajustes manuales o inicializaciones

#### `incrementarSaldoAFavor($loteId, $monto): bool`
- Suma un monto al saldo a favor existente
- Operación atómica en BD
- **Uso:** Acumular excedentes de pagos

#### `decrementarSaldoAFavor($loteId, $monto): bool`
- Resta un monto del saldo a favor
- Asegura que no quede negativo
- **Uso:** Aplicar saldo en reajustes

#### `getLotesConSaldoAFavor($minimoSaldo = 0.01): array`
- Obtiene todos los lotes con saldo > $minimoSaldo
- Ordena por saldo descendente
- **Uso:** Reportes y auditoría

---

### 3. **Controlador: PagoController**

#### Modificación en `store()` - Manejo de Excedentes

**Lógica de Distribución:**

```
Flujo: Opción B (pagar_siguientes)
───────────────────────────────────

1. Usuario registra pago de $12M (cuando cuota es $1.97M)
   ↓
2. PagoController@store calcula distribución
   - Paga cuota #1 completamente: $1.97M
   - Saldo disponible: $12M - $1.97M = $10.03M
   ↓
3. Verifica opción_excedente:
   
   SI opcion_excedente = 'aplicar_capital'
   └─→ Recalcula todo el plan (método francés)
   
   SI opcion_excedente = 'pagar_siguientes'
   └─→ ★ NUEVO: incrementarSaldoAFavor($lote_id, $10.03M)
      └─→ saldo_a_favor = 0 + 10.03M = 10.03M
      └─→ Mensaje: "Excedente acumulado en Saldo a Favor"
```

**Código Implementado:**
```php
if ($resultado_distribucion['excedente'] > 0) {
    if ($opcion_excedente === 'aplicar_capital') {
        $this->aplicarAbonoCapital($lote_id, $resultado_distribucion['excedente'], $db);
    } else {
        // NUEVO: Acumular en saldo_a_favor
        $sql_saldo = "UPDATE lotes SET 
                      saldo_a_favor = saldo_a_favor + ?,
                      updated_at = NOW()
                      WHERE id = ?";
        $db->execute($sql_saldo, [$resultado_distribucion['excedente'], $lote_id]);
    }
}
```

---

### 4. **Controlador: AmortizacionController**

#### Nuevo Método: `reajustarPlan($loteId)`

**Ruta:** `POST /lotes/amortizacion/reajustar/{lote_id}`

**Propósito:** Aplicar saldo a favor para compensar cuotas futuras evitando mora

**Algoritmo:**

```
Entrada: lote_id, saldo_a_favor disponible

1. VALIDACIÓN
   ├─ Verificar permisos (registrar_pagos)
   ├─ Validar CSRF token
   ├─ Obtener lote
   ├─ Verificar saldo_a_favor > 0.01
   └─ Obtener cuotas pendientes (ORDER BY numero_cuota ASC)

2. ITERACIÓN SOBRE CUOTAS FUTURAS
   ├─ FOR EACH cuota_pendiente:
   │  ├─ Calcular: monto_a_aplicar = MIN(saldo_disponible, saldo_pendiente_cuota)
   │  ├─ Actualizar cuota:
   │  │  ├─ valor_pagado += monto_a_aplicar
   │  │  ├─ saldo_pendiente -= monto_a_aplicar
   │  │  ├─ estado = (saldo_pendiente <= 0.01 ? 'pagada' : 'pendiente')
   │  │  └─ Guardar en base de datos
   │  │
   │  ├─ Registrar en tabla 'pagos' (para auditoría)
   │  │  └─ metodo_pago = 'saldo_a_favor'
   │  │  └─ numero_recibo = 'REAJ-SAF-...'
   │  │
   │  ├─ Actualizar saldo_disponible
   │  └─ Contar cuotas_compensadas (estado = 'pagada')
   │
   └─ FIN IF (saldo_disponible <= 0.01)

3. ACTUALIZAR SALDO A FAVOR DEL LOTE
   └─ UPDATE lotes SET saldo_a_favor = GREATEST(0, saldo_a_favor - total_aplicado)

4. RESULTADO
   ├─ Transacción: COMMIT
   ├─ Mensaje: "Reajuste completado. X cuotas compensadas. Saldo restante: $Y"
   └─ Redirect: /lotes/amortizacion/show/{lote_id}
```

**Ejemplo Práctico:**

```
Lote ID: 2, Saldo a Favor: $10.022.914,17

Cuotas Pendientes:
┌──────┬──────────────┬────────────────┬──────────────────┐
│ #    │ Vencimiento  │ Valor Cuota    │ Saldo Pendiente  │
├──────┼──────────────┼────────────────┼──────────────────┤
│ 2    │ 2026-01-29   │ $1.977.085,83  │ $1.977.085,83    │
│ 3    │ 2026-03-01   │ $1.977.085,83  │ $1.977.085,83    │
│ 4    │ 2026-03-29   │ $1.977.085,83  │ $1.977.085,83    │
│ 5    │ 2026-04-29   │ $1.977.085,83  │ $1.977.085,83    │
│ 6    │ 2026-05-29   │ $1.977.085,83  │ $1.977.085,83    │
└──────┴──────────────┴────────────────┴──────────────────┘

APLICACIÓN DE SALDO:
1. Cuota 2: $1.977.085,83 → Estado: PAGADA ✓
2. Cuota 3: $1.977.085,83 → Estado: PAGADA ✓
3. Cuota 4: $1.977.085,83 → Estado: PAGADA ✓
4. Cuota 5: $1.977.085,83 → Estado: PAGADA ✓
5. Cuota 6: $1.114.570,91 (de $1.977.085,83) → Estado: PENDIENTE

RESULTADO:
✓ 4 cuotas compensadas (marcadas como PAGADAS)
✓ Saldo a Favor Restante: $0,00
✓ Cuota 6 solo necesita: $863.514,92 más
✓ Cliente NO entra en mora
```

---

### 5. **Vista: amortizacion.php**

#### Botón Condicional

**HTML Generado:**

```html
<!-- Botón solo aparece si: saldo_a_favor > 0.01 AND usuario tiene permisos -->
<?php if (isset($saldo_a_favor) && $saldo_a_favor > 0.01 && can('registrar_pagos')): ?>
<form method="POST" action="/lotes/amortizacion/reajustar/<?= $lote['id'] ?>" style="display: inline;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <button type="submit" class="btn btn-info text-white" 
            onclick="return confirm('¿Aplicar Saldo a Favor de $10.022.914,17 para compensar cuotas futuras?');">
        <i class="bi bi-cash-coin"></i> Aplicar Saldo a Favor ($10.022.914,17)
    </button>
</form>
<?php endif; ?>
```

**Características:**
- Visible solo si `saldo_a_favor > 0.01`
- Muestra el monto exacto disponible
- Requiere confirmación antes de ejecutar
- Botón tipo `btn-info` (azul) para destaque visual
- Icono de moneda para claridad semántica

---

## 🔄 Flujo Completo del Sistema

### Escenario: Cliente con Pago Excedente

```
PASO 1: REGISTRO DE PAGO CON EXCEDENTE
─────────────────────────────────────────
Usuario en: /lotes/pago/create/2
│
├─ Lote: Código 444
├─ Monto a Pagar: $12.000.000
├─ Cuota #1 Pendiente: $1.977.085,83
├─ Opción: "Pagar Siguientes" (Opción B)
│
└─→ POST /lotes/pago/store

    PagoController@store()
    ├─ Distribuye $12M
    ├─ Cuota 1: PAGADA ($1.977.085,83)
    ├─ Excedente: $10.022.914,17
    │
    └─→ incrementarSaldoAFavor(2, 10022914.17)
        UPDATE lotes SET saldo_a_favor = 0 + 10022914.17
        Result: ✓ Saldo a Favor = $10.022.914,17


PASO 2: VISUALIZACIÓN - BOTÓN APARECE
──────────────────────────────────────
Usuario en: /lotes/amortizacion/show/2

AmortizacionController@show()
├─ saldo_a_favor = getSaldoAFavor(2) = $10.022.914,17
├─ Pasa a vista: ['saldo_a_favor' => 10022914.17]
│
└─→ En amortizacion.php:
    IF (saldo_a_favor > 0.01 AND can('registrar_pagos'))
        Mostrar: [Aplicar Saldo a Favor ($10.022.914,17)] ← BOTÓN


PASO 3: REAJUSTE - COMPENSA MORA
────────────────────────────────
Usuario hace click en botón

POST /lotes/amortizacion/reajustar/2

AmortizacionController@reajustarPlan(2)
├─ saldo_disponible = 10022914.17
├─ Cuotas pendientes:
│  ├─ Cuota 2 (pendiente):     -1977085.83 → PAGADA
│  ├─ Cuota 3 (pendiente):     -1977085.83 → PAGADA
│  ├─ Cuota 4 (pendiente):     -1977085.83 → PAGADA
│  ├─ Cuota 5 (pendiente):     -1977085.83 → PAGADA
│  ├─ Cuota 6 (pendiente):     -1114570.91 → PENDIENTE
│  └─ Saldo Restante: $0
│
├─ Actualiza en BD:
│  └─ UPDATE amortizaciones SET estado='pagada' WHERE id IN (2,3,4,5)
│  └─ UPDATE amortizaciones SET valor_pagado=... WHERE id=6
│  └─ INSERT INTO pagos (metodo_pago='saldo_a_favor', ...)
│
├─ Actualiza lote:
│  └─ UPDATE lotes SET saldo_a_favor = 0 WHERE id=2
│
└─→ Redirect a /lotes/amortizacion/show/2 con mensaje de éxito


PASO 4: RESULTADO
─────────────────
✓ Cuotas 2-5: Estado PAGADA
✓ Cuota 6: Aún pendiente (pero sin mora si se paga a tiempo)
✓ Saldo a Favor: $0 (agotado)
✓ Botón desaparece (saldo_a_favor = 0)
✓ Cliente NO entra en mora por cuota 2
```

---

## 📊 Flujo de Datos

```
┌─────────────────────────────────────────────────────────────┐
│                    REGISTRAR PAGO                          │
│              PagoController@store()                         │
└────────────────────┬────────────────────────────────────────┘
                     │
         ┌───────────┴────────────┐
         │                        │
    Opción A              Opción B (NUEVO)
    CAPITAL               PAGAR SIGUIENTES
         │                        │
         ▼                        ▼
    Recalcula         incrementarSaldoAFavor()
    todo plan              LoteModel
         │                        │
         └────────────┬───────────┘
                      │
            ┌─────────▼──────────┐
            │  lotes.saldo_a_favor│
            │   (acumulado)       │
            └──────────┬──────────┘
                       │
           ┌───────────┴──────────┐
           │                      │
      VISUALIZACIÓN         REAJUSTE AUTOMÁTICO
      (ver en amortizacion │  (si usuario lo solicita)
       si saldo > 0)       │  POST /reajustar/{id}
           │               │
           ▼               ▼
      BOTÓN APARECE → reajustarPlan()
                    ├─ Itera cuotas pendientes
                    ├─ Aplica saldo a cada una
                    ├─ Marca como PAGADA si se cubre
                    └─ decrementarSaldoAFavor()
                       
                       RESULTADO:
                       ✓ Cuotas compensadas
                       ✓ Mora evitada
                       ✓ Botón desaparece
```

---

## 🔐 Seguridad

### Validaciones Implementadas

1. **Permisos:**
   - `registrar_pagos`: Requerido para ver botón y ejecutar reajuste
   - Validación en ambos controladores (PagoController, AmortizacionController)

2. **CSRF Protection:**
   - Token requerido en formulario POST
   - Validación con `$this->validateCsrf()`

3. **Transacciones de BD:**
   - Todas las operaciones en transacción (BEGIN, COMMIT, ROLLBACK)
   - Rollback automático si hay error

4. **Validaciones de Negocio:**
   - Saldo > 0.01 (evita operaciones con decimales insignificantes)
   - Solo cuotas con estado 'pendiente' se procesan
   - Cuotas del lote correcto (validar lote_id)

5. **Auditoría:**
   - Todos los pagos registrados en tabla `pagos`
   - Método: 'saldo_a_favor'
   - Recibo: 'REAJ-SAF-TIMESTAMP-CUOTA_ID'
   - Observaciones: Anotadas automáticamente

---

## 📝 Logging

Todos los eventos se registran con `\Logger`:

```php
\Logger::info("=== INICIO reajustarPlan() ===", ['lote_id' => $loteId]);
\Logger::info("Cuota compensada exitosamente", [...]);
\Logger::info("=== REAJUSTE COMPLETADO EXITOSAMENTE ===", [...]);
\Logger::error("=== ERROR EN REAJUSTE DE PLAN ===");
```

Niveles:
- `info`: Eventos normales del flujo
- `debug`: Detalles de cálculos
- `warning`: Situaciones inesperadas pero no críticas
- `error`: Errores que requieren atención

---

## 🧪 Casos de Prueba

### TC-1: Acumular Saldo a Favor
```
Dado: Lote 2 con cuota #1 = $1.977.085,83
Cuando: Usuario paga $12.000.000 (Opción B)
Entonces: 
  ✓ Cuota 1 = PAGADA
  ✓ saldo_a_favor = $10.022.914,17
  ✓ Botón aparece en amortizacion.php
```

### TC-2: Aplicar Reajuste
```
Dado: Lote 2 con saldo_a_favor = $10.022.914,17
Cuando: Usuario hace click en "Aplicar Saldo a Favor"
Entonces:
  ✓ Cuotas 2-5 = PAGADA
  ✓ Cuota 6 = PENDIENTE (parcial)
  ✓ saldo_a_favor = $0
  ✓ Botón desaparece
```

### TC-3: Sin Saldo
```
Dado: Lote 2 con saldo_a_favor = $0
Cuando: Usuario accede a /lotes/amortizacion/show/2
Entonces:
  ✓ Botón NO aparece
  ✓ Vista se muestra normalmente
```

---

## 🚀 Deployment

### SQL a Ejecutar

```bash
# Conectarse a la BD
mysql -u usuario -p -h servidor u418271893_inversiones < database/migration_saldo_a_favor.sql
```

### Archivos Modificados

1. ✅ `database/migration_saldo_a_favor.sql` - **Nuevo**
2. ✅ `app/Models/LoteModel.php` - Métodos agregados
3. ✅ `app/Controllers/PagoController.php` - Lógica de excedentes
4. ✅ `app/Controllers/AmortizacionController.php` - Método reajustarPlan()
5. ✅ `app/Views/lotes/amortizacion.php` - Botón condicional
6. ✅ `index.php` - Ruta POST /lotes/amortizacion/reajustar/{id}

### Verificación Post-Deployment

```sql
-- Verificar columna existe
SELECT * FROM information_schema.COLUMNS 
WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor';

-- Verificar índice
SELECT * FROM information_schema.STATISTICS 
WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor';

-- Verificar datos
SELECT id, codigo_lote, saldo_a_favor FROM lotes WHERE saldo_a_favor > 0;
```

---

## 📞 Soporte y Troubleshooting

### ¿Por qué no aparece el botón?

1. ✓ Verificar `saldo_a_favor > 0.01` en tabla lotes
2. ✓ Verificar usuario tiene permiso `registrar_pagos`
3. ✓ Limpiar cache del navegador

### ¿Por qué falla el reajuste?

1. ✓ Verificar que hay cuotas pendientes
2. ✓ Revisar logs en `storage/logs/`
3. ✓ Verificar transacción no fue rollback

### Resetear saldo_a_favor de un lote

```sql
UPDATE lotes SET saldo_a_favor = 0 WHERE id = {lote_id};
```

---

## 📈 Mejoras Futuras

1. **Aplicación Selectiva:** Permitir elegir qué cuotas compensar
2. **Reporte de Saldos:** Dashboard de lotes con saldo_a_favor
3. **Autorización Manual:** Admin debe aprobar reajustes grandes
4. **Notificaciones:** Email cuando se aplica saldo_a_favor
5. **Reversión:** Botón para deshacer reajuste (refund)

---

## 📄 Referencias

- Documentación del Método Francés: `MODULO_5_COMPLETADO.md`
- Schema Base de Datos: `database/schema.sql`
- Documentación General: `README.md`

---

**Versión:** 1.0  
**Fecha:** 29 de Noviembre de 2025  
**Autor:** Especialista en Lógica de Pagos  
**Estado:** ✅ Implementado y Documentado
