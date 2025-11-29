# Funcionalidad: Saldo a Favor en Reporte de Cartera

## 📊 Cómo se ve actualmente

La página `/reportes/cartera` muestra:

```
┌────────────────────────────────────────────────────────────────────┐
│ 📋 REPORTE: Cartera Pendiente                          Volver      │
│ Control de cuotas pendientes y en mora                             │
└────────────────────────────────────────────────────────────────────┘

┌─ FILTROS ─────────────────────────────────────────────────────────┐
│ Proyecto: [Todos ▼]  | Estado Mora: [Todos ▼]  | [🔍 Buscar]     │
└───────────────────────────────────────────────────────────────────┘

┌─ RESUMEN ─────────────────────────────────────────────────────────┐
│  Total Cartera        │  En Mora         │  Vigente      │ % Mora  │
│  $45.000.000          │  $8.500.000      │  $36.5M       │ 18.9%   │
│  150 cuotas           │  25 cuotas       │  Al día       │         │
└───────────────────────────────────────────────────────────────────┘

📋 TABLA DE CUOTAS PENDIENTES:
┌──────────────┬──────────────┬───────┬──────────┬─────────────┬────────┬────────┬──────────────┐
│ Cliente      │ Proyecto     │ Lote  │ Cuota #  │ Vencimiento │ Estado │ Días   │ Saldo        │
├──────────────┼──────────────┼───────┼──────────┼─────────────┼────────┼────────┼──────────────┤
│ Juan Pérez   │ Proyecto A   │ L-001 │ #3       │ 15/10/2025  │ VENCIDA│ 45 días│ $1.977.085   │
│ María López  │ Proyecto B   │ L-015 │ #5       │ 20/10/2025  │ VENCIDA│ 40 días│ $2.100.000   │
│ Carlos Díaz  │ Proyecto A   │ L-008 │ #2       │ 28/10/2025  │ VENCIDA│ 32 días│ $1.977.085   │
└──────────────┴──────────────┴───────┴──────────┴─────────────┴────────┴────────┴──────────────┘
```

## 🎯 Nueva Funcionalidad: Saldo a Favor para Compensar Mora

Se agregarán 2 columnas nuevas a la tabla:

```
┌──────────────┬──────────────┬───────┬──────────┬─────────────┬────────┬────────┬──────────────┬────────────────┬──────────────┐
│ Cliente      │ Proyecto     │ Lote  │ Cuota #  │ Vencimiento │ Estado │ Días   │ Saldo        │ Saldo a Favor  │ Acción       │
├──────────────┼──────────────┼───────┼──────────┼─────────────┼────────┼────────┼──────────────┼────────────────┼──────────────┤
│ Juan Pérez   │ Proyecto A   │ L-001 │ #3       │ 15/10/2025  │ VENCIDA│ 45 días│ $1.977.085   │ $522.914       │ [💰 Aplicar] │
│ María López  │ Proyecto B   │ L-015 │ #5       │ 20/10/2025  │ VENCIDA│ 40 días│ $2.100.000   │ $0.00          │ -            │
│ Carlos Díaz  │ Proyecto A   │ L-008 │ #2       │ 28/10/2025  │ VENCIDA│ 32 días│ $1.977.085   │ $1.977.085     │ [💰 Aplicar] │
└──────────────┴──────────────┴───────┴──────────┴─────────────┴────────┴────────┴──────────────┴────────────────┴──────────────┘
```

## 🔧 Implementación Técnica

### 1. Modificar Query en `ReporteController::cartera()`

```php
// Agregar a la SELECT existente:
SELECT 
    ...
    COALESCE(l.saldo_a_favor, 0) as saldo_a_favor,
    CASE 
        WHEN COALESCE(l.saldo_a_favor, 0) > 0.01 THEN 'disponible'
        ELSE 'no_disponible'
    END as puede_compensar
FROM amortizaciones a
INNER JOIN lotes l ON a.lote_id = l.id
...
```

### 2. Pasar datos a la Vista

```php
// En ReporteController::cartera()
view('reportes/cartera', [
    'cuotas' => $cuotas,
    'saldosFavor' => $this->obtenerSaldosAFavor($cuotas),
    ...
]);
```

### 3. Modificar Vista `cartera.php`

Agregar columnas en la tabla:

```php
<thead class="table-light">
    <tr>
        <th>Cliente</th>
        <th>Proyecto</th>
        <th>Lote</th>
        <th class="text-center">Cuota #</th>
        <th>Fecha Vencimiento</th>
        <th class="text-center">Estado</th>
        <th class="text-center">Días Mora</th>
        <th class="text-end">Saldo Pendiente</th>
        <th class="text-end">Saldo a Favor</th>    <!-- NUEVA -->
        <th class="text-center">Acción</th>            <!-- NUEVA -->
        <th>Contacto</th>
    </tr>
</thead>

<tbody>
    <?php foreach ($cuotas as $cuota): ?>
        <tr>
            ...
            <!-- Columna: Saldo a Favor -->
            <td class="text-end">
                <?php if ($cuota['saldo_a_favor'] > 0.01): ?>
                    <span class="badge bg-success">
                        <?= formatMoney($cuota['saldo_a_favor']) ?>
                    </span>
                <?php else: ?>
                    <span class="text-muted">$0.00</span>
                <?php endif; ?>
            </td>
            
            <!-- Columna: Acción -->
            <td class="text-center">
                <?php if ($cuota['saldo_a_favor'] > 0.01 && $cuota['dias_mora'] > 0): ?>
                    <form method="POST" action="/lotes/amortizacion/reajustar-desde-cartera" style="display:inline;">
                        <input type="hidden" name="lote_id" value="<?= $cuota['lote_id'] ?>">
                        <input type="hidden" name="cuota_id" value="<?= $cuota['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
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

## 🎨 Estados Visuales del Botón

### ✅ Botón ACTIVO (Verde)
- Condición: `saldo_a_favor > 0.01` AND `dias_mora > 0`
- Texto: `💰 Aplicar`
- Color: `btn-success` (verde)
- Acción: POST a `/lotes/amortizacion/reajustar-desde-cartera`

### ❌ Botón INACTIVO (Gris)
- Condición: `saldo_a_favor <= 0.01` OR `dias_mora <= 0`
- Texto: `—`
- Color: `text-muted`
- Acción: Ninguna

## 📝 Flujo de Compensación desde Cartera

```
┌─────────────────────────────────────┐
│ Técnico ve Cartera con Mora         │
│ Cliente: Juan Pérez                 │
│ Saldo a Favor: $522.914             │
│ Mora en Cuota #3: $1.977.085        │
│ Días en Mora: 45 días               │
└──────────────┬──────────────────────┘
               │
               ↓
        [ 💰 Aplicar ]
               │
               ↓
      ┌────────────────────────┐
      │ Confirmar Aplicación   │
      │ "¿Aplicar $522.914     │
      │  a esta mora?"         │
      │ [Sí] [No]             │
      └────────┬───────────────┘
               │
               ↓
    POST /lotes/amortizacion/reajustar-desde-cartera
    {
        lote_id: 5,
        cuota_id: 23,
        csrf_token: "..."
    }
               │
               ↓
    ┌──────────────────────────────────┐
    │ Controller ejecuta reajuste:     │
    │ 1. Valida permisos               │
    │ 2. Valida saldo_a_favor > 0      │
    │ 3. Compensa Cuota #3             │
    │ 4. Crea pago automático          │
    │ 5. Decrementa saldo_a_favor      │
    │ 6. Log de auditoría              │
    └──────────┬───────────────────────┘
               │
               ↓
        ✅ Éxito - Redirecciona a Cartera
        Mensaje: "Mora compensada exitosamente"
```

## 🔌 Nueva Ruta Necesaria

Agregar a `index.php`:

```php
$router->post('/lotes/amortizacion/reajustar-desde-cartera', 'AmortizacionController@reajustarDesdeCartera');
```

## 📋 Controlador: Nuevo Método

Agregar a `app/Controllers/AmortizacionController.php`:

```php
/**
 * Reajustar plan desde tabla de cartera
 * Compensar una cuota específica en mora usando saldo a favor
 */
public function reajustarDesdeCartera()
{
    // Validaciones
    $loteId = $_POST['lote_id'] ?? null;
    $cuotaId = $_POST['cuota_id'] ?? null;
    
    if (!$loteId || !$cuotaId) {
        json_response(['success' => false, 'error' => 'Parámetros inválidos']);
        return;
    }
    
    if (!can('registrar_pagos')) {
        json_response(['success' => false, 'error' => 'Permisos insuficientes']);
        return;
    }
    
    if (!$this->validateCsrf($_POST['csrf_token'] ?? '')) {
        json_response(['success' => false, 'error' => 'Token CSRF inválido']);
        return;
    }
    
    // Obtener saldo a favor
    $saldoAFavor = $this->loteModel->getSaldoAFavor($loteId);
    
    if ($saldoAFavor < 0.01) {
        json_response(['success' => false, 'error' => 'No hay saldo a favor disponible']);
        return;
    }
    
    try {
        $this->db->beginTransaction();
        
        // Obtener cuota específica
        $cuota = $this->db->fetch(
            "SELECT * FROM amortizaciones WHERE id = ? AND lote_id = ?",
            [$cuotaId, $loteId]
        );
        
        if (!$cuota) {
            throw new Exception('Cuota no encontrada');
        }
        
        // Crear pago con saldo a favor
        $montoAplicado = min($saldoAFavor, $cuota['saldo']);
        
        $this->db->execute(
            "INSERT INTO pagos (amortizacion_id, fecha_pago, valor_pagado, metodo_pago, observaciones, created_at)
             VALUES (?, NOW(), ?, 'saldo_a_favor', ?, NOW())",
            [$cuota['id'], $montoAplicado, 'Compensación de mora con saldo a favor']
        );
        
        // Actualizar cuota
        $nuevoSaldo = max(0, $cuota['saldo'] - $montoAplicado);
        $estado = ($nuevoSaldo < 0.01) ? 'pagada' : 'pendiente';
        
        $this->db->execute(
            "UPDATE amortizaciones SET estado = ?, saldo = ?, valor_pagado = valor_pagado + ?
             WHERE id = ?",
            [$estado, $nuevoSaldo, $montoAplicado, $cuota['id']]
        );
        
        // Actualizar saldo a favor
        $nuevoSaldoAFavor = $saldoAFavor - $montoAplicado;
        $this->loteModel->setSaldoAFavor($loteId, $nuevoSaldoAFavor);
        
        // Log de auditoría
        \Logger::log('saldo_a_favor', "Mora compensada: Lote $loteId, Cuota $cuotaId, Monto: $montoAplicado");
        
        $this->db->commit();
        
        $_SESSION['success'] = "Mora compensada exitosamente. Monto aplicado: " . formatMoney($montoAplicado);
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        $_SESSION['error'] = "Error al compensar mora: " . $e->getMessage();
        \Logger::error('saldo_a_favor', $e->getMessage());
    }
    
    redirect('/reportes/cartera');
}
```

## 🎨 Estilos CSS Opcionales

Agregar a `assets/css/theme.css`:

```css
/* Saldo a Favor Badge */
.badge.bg-success {
    font-weight: 600;
    font-size: 0.95rem;
}

/* Botón de Acción en Cartera */
.btn-compensar {
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-compensar:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);
}

/* Row resaltada cuando tiene saldo a favor */
tr.tiene-saldo-favor {
    background-color: rgba(25, 135, 84, 0.05);
}
```

## 📊 Resumen de Cambios

| Archivo | Cambio | Líneas |
|---------|--------|--------|
| `app/Controllers/ReporteController.php` | Agregar columnas `saldo_a_favor` y `puede_compensar` a query | +3 |
| `app/Controllers/AmortizacionController.php` | Nuevo método `reajustarDesdeCartera()` | +80 |
| `app/Views/reportes/cartera.php` | Agregar 2 columnas a tabla + botones de acción | +30 |
| `index.php` | Nueva ruta POST | +1 |
| `assets/css/theme.css` | Estilos opcionales | +20 |

## 🚀 Orden de Implementación

1. ✅ Modificar ReporteController (agregar columnas a query)
2. ✅ Modificar vista cartera.php (mostrar saldo y botón)
3. ✅ Agregar ruta nueva en index.php
4. ✅ Implementar método reajustarDesdeCartera() en AmortizacionController
5. ✅ Probar flujo completo

## 💡 Alternativa: Modal de Confirmación

Si prefieres una UX más sofisticada, usar Bootstrap Modal:

```php
<!-- Modal de Confirmación -->
<div class="modal fade" id="modalCompensarMora" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compensar Mora con Saldo a Favor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/lotes/amortizacion/reajustar-desde-cartera">
                <div class="modal-body">
                    <p>Cliente: <strong id="modalCliente"></strong></p>
                    <p>Saldo a Favor: <strong id="modalSaldo" class="text-success"></strong></p>
                    <p>Mora Pendiente: <strong id="modalMora" class="text-danger"></strong></p>
                    <p>Se aplicarán: <strong id="modalAplicado" class="text-primary"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">✓ Aplicar Saldo</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

**Estado:** Listo para implementar  
**Complejidad:** Media  
**Tiempo estimado:** 2-3 horas  
**Testing requerido:** ✅ Flujo completo de compensación
