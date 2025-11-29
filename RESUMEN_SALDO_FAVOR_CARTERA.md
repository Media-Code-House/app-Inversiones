# 🎯 Saldo a Favor en Cartera - Resumen Ejecutivo

## Situación Actual vs. Nueva Funcionalidad

### ❌ ANTES
```
/reportes/cartera muestra:
- Cliente
- Proyecto  
- Cuota
- Estado
- Días Mora
- Saldo Pendiente
- Contacto

❌ NO se ve: ¿Tiene saldo a favor el cliente?
❌ NO se puede: Aplicar saldo directo desde cartera
```

### ✅ DESPUÉS  
```
/reportes/cartera ahora muestra:
- Cliente
- Proyecto
- Cuota
- Estado
- Días Mora
- Saldo Pendiente
+ Saldo a Favor      ← NUEVA
+ Acción (Botón)     ← NUEVA

✅ SE ve: Qué clientes tienen saldo a favor
✅ SE PUEDE: Aplicar compensación en 1 click
```

---

## 👀 Cómo se VE Visualmente

### Tabla de Cartera Expandida

```
┌──────────────┬──────────────┬────────┬────────┬─────────────┬────────┬──────────────┬────────────────┬──────────────┐
│ Cliente      │ Proyecto     │ Cuota# │ Estado │ Vencimiento │ Días   │ Saldo Mora   │ Saldo a Favor  │ Acción       │
├──────────────┼──────────────┼────────┼────────┼─────────────┼────────┼──────────────┼────────────────┼──────────────┤
│ Juan Pérez   │ Proyecto A   │ #3     │VENCIDA │ 15/10/2025  │  45d   │ $1.977.085   │ $522.914 ✓     │ [💰 Aplicar] │
│ María López  │ Proyecto B   │ #5     │VENCIDA │ 20/10/2025  │  40d   │ $2.100.000   │    $0.00       │      —       │
│ Carlos Díaz  │ Proyecto A   │ #2     │VENCIDA │ 28/10/2025  │  32d   │ $1.977.085   │ $1.977.085 ✓   │ [💰 Aplicar] │
└──────────────┴──────────────┴────────┴────────┴─────────────┴────────┴──────────────┴────────────────┴──────────────┘
```

### Estados del Botón de Acción

```
ACTIVO (Verde):
┌──────────────────┐
│    💰 Aplicar    │  ← Click para compensar mora
└──────────────────┘
Condiciones:
  • Saldo a Favor > $0.01
  • Días Mora > 0


INACTIVO (Gris):
┌──────────────────┐
│        —         │  ← No se puede compensar
└──────────────────┘
Condiciones:
  • Saldo a Favor = $0
  • O sin mora
```

---

## 🔄 Flujo de Compensación

```
1. Técnico en /reportes/cartera
   ↓
2. Ve Juan Pérez:
   - Mora: $1.977.085
   - Saldo a Favor: $522.914
   ↓
3. Hace click [💰 Aplicar]
   ↓
4. Popup de confirmación:
   "¿Aplicar $522.914 a esta mora?"
   ├─ Cuota quedará: $1.454.171
   ├─ Saldo después: $0.00
   └─ Cliente sale parcialmente de mora
   ↓
5. Confirma → Sistema:
   ✓ Crea pago automático
   ✓ Decrementa saldo a favor
   ✓ Actualiza estado de cuota
   ✓ Registra en auditoría
   ↓
6. Redirecciona a cartera
   Mensaje: ✅ Mora compensada exitosamente
   
7. Juan Pérez ahora:
   - Saldo a Favor: $0.00 (botón desaparece)
   - Cuota #3: Parcialmente pagada
   - Pero aún con mora: $1.454.171 pendientes
```

---

## 📋 Cambios de Código Necesarios

### 1️⃣ ReporteController.php - Línea ~300

**ANTES:**
```php
SELECT 
    a.id,
    a.numero_cuota,
    ...
    CASE WHEN DATEDIFF... THEN 'VENCIDA'
    ... estado_mora
```

**DESPUÉS:**
```php
SELECT 
    a.id,
    a.numero_cuota,
    ...
    COALESCE(l.saldo_a_favor, 0) as saldo_a_favor,     ← AGREGAR
    CASE WHEN DATEDIFF... THEN 'VENCIDA'
    ... estado_mora
```

**Cambio mínimo:** +1 línea en SELECT

---

### 2️⃣ cartera.php - Vista (Tabla)

**ANTES:**
```php
<thead>
    <th>Cliente</th>
    <th>Proyecto</th>
    ...
    <th>Saldo Pendiente</th>
    <th>Contacto</th>
</thead>
```

**DESPUÉS:**
```php
<thead>
    <th>Cliente</th>
    <th>Proyecto</th>
    ...
    <th>Saldo Pendiente</th>
    <th class="text-end">Saldo a Favor</th>           ← NUEVA
    <th class="text-center">Acción</th>                 ← NUEVA
    <th>Contacto</th>
</thead>

<tbody>
    <?php foreach ($cuotas as $cuota): ?>
        <tr>
            ...
            <!-- Nueva columna: Saldo a Favor -->
            <td class="text-end">
                <?php if ($cuota['saldo_a_favor'] > 0.01): ?>
                    <span class="badge bg-success">
                        <?= formatMoney($cuota['saldo_a_favor']) ?>
                    </span>
                <?php else: ?>
                    <span class="text-muted">$0.00</span>
                <?php endif; ?>
            </td>
            
            <!-- Nueva columna: Acción -->
            <td class="text-center">
                <?php if ($cuota['saldo_a_favor'] > 0.01 && $cuota['dias_mora'] > 0): ?>
                    <form method="POST" 
                          action="/lotes/amortizacion/reajustar-desde-cartera"
                          style="display:inline;">
                        <input type="hidden" name="lote_id" value="<?= $cuota['lote_id'] ?>">
                        <input type="hidden" name="cuota_id" value="<?= $cuota['id'] ?>">
                        <input type="hidden" name="csrf_token" 
                               value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-sm btn-success"
                                onclick="return confirm('¿Aplicar $<?= number_format($cuota['saldo_a_favor'], 0) ?> a esta mora?')">
                            <i class="bi bi-cash-coin"></i> Aplicar
                        </button>
                    </form>
                <?php else: ?>
                    <span class="text-muted small">—</span>
                <?php endif; ?>
            </td>
            ...
        </tr>
    <?php endforeach; ?>
</tbody>
```

**Cambio:** +30-40 líneas en la vista

---

### 3️⃣ index.php - Nueva Ruta

**AGREGAR:**
```php
$router->post('/lotes/amortizacion/reajustar-desde-cartera', 
              'AmortizacionController@reajustarDesdeCartera');
```

**Cambio:** +1 línea

---

### 4️⃣ AmortizacionController.php - Nuevo Método

**AGREGAR MÉTODO (aprox. 80 líneas):**
```php
/**
 * Reajustar plan y compensar mora desde cartera
 * POST /lotes/amortizacion/reajustar-desde-cartera
 */
public function reajustarDesdeCartera()
{
    // 1. Obtener parámetros
    $loteId = $_POST['lote_id'] ?? null;
    $cuotaId = $_POST['cuota_id'] ?? null;
    
    // 2. Validaciones básicas
    if (!$loteId || !$cuotaId || !$this->validateCsrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Parámetros inválidos';
        redirect('/reportes/cartera');
        return;
    }
    
    // 3. Validar permisos
    if (!can('registrar_pagos')) {
        $_SESSION['error'] = 'No tienes permisos';
        redirect('/reportes/cartera');
        return;
    }
    
    // 4. Obtener saldo a favor
    $saldoAFavor = $this->loteModel->getSaldoAFavor($loteId);
    
    if ($saldoAFavor < 0.01) {
        $_SESSION['error'] = 'No hay saldo a favor disponible';
        redirect('/reportes/cartera');
        return;
    }
    
    try {
        $this->db->beginTransaction();
        
        // 5. Obtener cuota
        $cuota = $this->db->fetch(
            "SELECT * FROM amortizaciones WHERE id = ? AND lote_id = ?",
            [$cuotaId, $loteId]
        );
        
        if (!$cuota || $cuota['dias_mora'] <= 0) {
            throw new Exception('Cuota no válida para compensación');
        }
        
        // 6. Calcular monto a aplicar
        $montoAplicado = min($saldoAFavor, $cuota['saldo']);
        
        // 7. Crear pago automático
        $this->db->execute(
            "INSERT INTO pagos (amortizacion_id, fecha_pago, valor_pagado, metodo_pago, observaciones, created_at)
             VALUES (?, NOW(), ?, 'saldo_a_favor', ?, NOW())",
            [$cuota['id'], $montoAplicado, 'Compensación de mora desde cartera']
        );
        
        // 8. Actualizar cuota
        $nuevoSaldo = max(0, $cuota['saldo'] - $montoAplicado);
        $estado = ($nuevoSaldo < 0.01) ? 'pagada' : 'pendiente';
        
        $this->db->execute(
            "UPDATE amortizaciones 
             SET estado = ?, saldo = ?, valor_pagado = valor_pagado + ?
             WHERE id = ?",
            [$estado, $nuevoSaldo, $montoAplicado, $cuota['id']]
        );
        
        // 9. Actualizar saldo a favor
        $nuevoSaldoAFavor = $saldoAFavor - $montoAplicado;
        $this->loteModel->setSaldoAFavor($loteId, $nuevoSaldoAFavor);
        
        // 10. Log
        \Logger::log('saldo_a_favor', 
                     "Mora compensada desde cartera: Lote $loteId, " .
                     "Cuota $cuotaId, Monto: " . formatMoney($montoAplicado));
        
        $this->db->commit();
        
        $_SESSION['success'] = "✅ Mora compensada: " . formatMoney($montoAplicado);
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        $_SESSION['error'] = "❌ Error: " . $e->getMessage();
        \Logger::error('saldo_a_favor', $e->getMessage());
    }
    
    redirect('/reportes/cartera');
}
```

**Cambio:** +1 nuevo método (~80 líneas)

---

## 🎨 Estilos (Opcional - Si quieres mejorar UX)

```css
/* En assets/css/theme.css */

/* Badge de saldo a favor */
.badge.bg-success {
    font-weight: 600;
    font-size: 0.95rem;
}

/* Botón de compensación */
.btn-compensar {
    transition: all 0.2s ease;
}

.btn-compensar:hover {
    transform: scale(1.05);
}

/* Row con saldo a favor se resalta levemente */
tr[data-tiene-saldo="true"] {
    background-color: rgba(25, 135, 84, 0.05);
}
```

---

## 📊 Resumen de Cambios

| Archivo | Tipo de Cambio | Líneas |
|---------|---|---|
| ReporteController.php | Query: agregar columna | +1 |
| cartera.php | UI: 2 nuevas columnas + botones | +40 |
| index.php | Ruta nueva | +1 |
| AmortizacionController.php | Método nuevo | +80 |
| theme.css | Estilos (opcional) | +15 |
| **TOTAL** | | **~137** |

---

## 🚀 Orden de Implementación

```
1️⃣  ReporteController - Agregar columna saldo_a_favor a query (1 línea)
2️⃣  index.php - Agregar nueva ruta (1 línea)  
3️⃣  AmortizacionController - Implementar reajustarDesdeCartera() (80 líneas)
4️⃣  cartera.php - Agregar 2 columnas en tabla (40 líneas)
5️⃣  Pruebas: Flujo completo de compensación
```

**Tiempo estimado:** 2-3 horas  
**Complejidad:** Media  
**Testing:** ✅ Crítico

---

## 📝 Casos de Uso

### ✅ Caso 1: Cliente con saldo a favor suficiente
```
Cliente: Juan Pérez
Lote: L-001
Cuota #3: $1.977.085 (45 días mora)
Saldo a Favor: $522.914

Acción: Click [💰 Aplicar]

Resultado:
- Cuota #3: $1.454.171 pendientes (parcial)
- Saldo a Favor: $0.00
- Estado: Parcialmente compensada
```

### ✅ Caso 2: Cliente sin saldo a favor
```
Cliente: María López
Lote: L-015
Cuota #5: $2.100.000 (40 días mora)
Saldo a Favor: $0.00

Botón: INACTIVO (gris, —)
Acción: No disponible
```

### ✅ Caso 3: Cliente con saldo pero sin mora
```
Cliente: Pedro González
Lote: L-008
Cuota #1: (Vigente, sin vencer)
Saldo a Favor: $300.000

Botón: INACTIVO (gris, —)
Acción: No se puede compensar cuota vigente
```

---

## 🎁 Beneficios

- ✅ **Visibilidad:** Técnico ve saldo a favor en tabla de cartera
- ✅ **Eficiencia:** Compensación en 1 click, sin dejar la pantalla
- ✅ **Automatización:** Sistema crea pagos automáticamente
- ✅ **Auditoria:** Todo queda registrado en logs
- ✅ **Seguridad:** CSRF tokens + permisos validados
- ✅ **Datos:** Mantenidos en ACID transactions

---

**Estado:** Listo para implementar  
**Archivo de referencia:** `FUNCIONALIDAD_SALDO_FAVOR_CARTERA.md`
