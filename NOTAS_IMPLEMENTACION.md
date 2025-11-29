# NOTAS DE IMPLEMENTACIÓN - Saldo a Favor Global

**Fecha:** 29 de Noviembre de 2025  
**Desarrollador:** Especialista en Lógica de Pagos  
**Revisión:** Requerida antes de merge  

---

## 🔍 Revisión de Código - Puntos Clave

### 1. LoteModel.php - Nuevos Métodos

**Ubicación:** `app/Models/LoteModel.php` (final del archivo)

**Métodos Agregados:**
```php
- getSaldoAFavor($loteId)
- setSaldoAFavor($loteId, $monto)
- incrementarSaldoAFavor($loteId, $monto)
- decrementarSaldoAFavor($loteId, $monto)
- getLotesConSaldoAFavor($minimoSaldo = 0.01)
```

**Notas de Revisión:**
- [ ] Todos usan `$this->db` correctamente
- [ ] Parámetros con prepared statements (?)
- [ ] Retornan tipos esperados (float, bool, array)
- [ ] Sin SQL injection posible
- [ ] Usa GREATEST() para evitar negativos

---

### 2. PagoController.php - Cambios en store()

**Ubicación:** `app/Controllers/PagoController.php` línea ~320

**Cambio:**
```php
// ANTES:
if ($resultado_distribucion['excedente'] > 0 && $opcion_excedente === 'aplicar_capital') {
    $this->aplicarAbonoCapital(...);
}

// DESPUÉS:
if ($resultado_distribucion['excedente'] > 0) {
    if ($opcion_excedente === 'aplicar_capital') {
        $this->aplicarAbonoCapital(...);
    } else {
        // NUEVO: Acumular en saldo_a_favor
        $sql_saldo = "UPDATE lotes SET saldo_a_favor = saldo_a_favor + ?, ...";
        $db->execute($sql_saldo, [$resultado_distribucion['excedente'], $lote_id]);
    }
}
```

**Notas de Revisión:**
- [ ] Dentro de transacción existente ✓
- [ ] Usa variable `$db` de transacción ✓
- [ ] SQL correctamente parametrizado ✓
- [ ] Logging agregado ✓
- [ ] Mensaje de usuario actualizado ✓

---

### 3. AmortizacionController.php - Nuevo Método reajustarPlan()

**Ubicación:** `app/Controllers/AmortizacionController.php` (línea 453 en adelante)

**Método:** `public function reajustarPlan($loteId)` (180+ líneas)

**Estructura:**
1. Validaciones (permisos, CSRF, existencia)
2. Obtener saldo_a_favor
3. Obtener cuotas pendientes
4. BEGIN TRANSACTION
5. Iterar cuotas y aplicar saldo
6. Insertar registros en tabla pagos
7. Actualizar saldo_a_favor
8. COMMIT
9. Logging y redirección

**Notas de Revisión:**
- [ ] Todos los permisos validados
- [ ] CSRF token requerido
- [ ] Transacción ACID completa
- [ ] Logging en cada paso importante
- [ ] Errores capturados y logeados
- [ ] SQL parametrizado
- [ ] Loop evita bucle infinito (break cuando saldo <= 0.01)

**Lineas Críticas:**
```php
// Línea ~480: Validación de saldo
if ($saldo_a_favor <= 0.01) {
    // Correcto - evita operaciones insignificantes
}

// Línea ~495: Loop sobre cuotas
foreach ($cuotas_pendientes as $cuota) {
    if ($saldo_aplicable <= 0.01) {
        break; // Importante: evita procesamiento innecesario
    }
    // ...
}

// Línea ~520: Cálculo de aplicación
$monto_a_aplicar = min($saldo_aplicable, $saldo_pendiente_cuota);
// Correcto - toma el mínimo para no exceder

// Línea ~545: UPDATE lotes
$sql_saldo = "UPDATE lotes SET 
              saldo_a_favor = GREATEST(0, saldo_a_favor - ?), ...";
// Correcto - GREATEST evita negativos
```

---

### 4. index.php - Nueva Ruta

**Ubicación:** `index.php` línea ~176

**Cambio:**
```php
// Agregada línea:
$router->post('/lotes/amortizacion/reajustar/{id}', 'AmortizacionController@reajustarPlan');
```

**Notas de Revisión:**
- [ ] POST (no GET) ✓
- [ ] Patrón {id} captura lote_id ✓
- [ ] Método correcto ✓
- [ ] Posición lógica en archivo ✓

---

### 5. amortizacion.php - Vista

**Ubicación:** `app/Views/lotes/amortizacion.php` (sección botones)

**Cambio:**
```html
<?php if (isset($saldo_a_favor) && $saldo_a_favor > 0.01 && can('registrar_pagos')): ?>
<form method="POST" action="/lotes/amortizacion/reajustar/<?= $lote['id'] ?>" style="display: inline;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <button type="submit" class="btn btn-info text-white" onclick="return confirm(...);">
        <i class="bi bi-cash-coin"></i> Aplicar Saldo a Favor (<?= formatMoney($saldo_a_favor) ?>)
    </button>
</form>
<?php endif; ?>
```

**Notas de Revisión:**
- [ ] Condición completa: isset, > 0.01, permisos ✓
- [ ] POST form con CSRF ✓
- [ ] Confirmación JavaScript ✓
- [ ] display: inline para alineación ✓
- [ ] formatMoney() para display correcto ✓

**Script agregado:**
```php
<script>
function formatCurrency(value) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}
</script>
```

**Notas:**
- [ ] Formato colombiano (es-CO) ✓
- [ ] COP currency ✓
- [ ] Sin decimales ✓

---

### 6. AmortizacionController@show() - Cambio

**Ubicación:** `app/Controllers/AmortizacionController.php` línea ~354

**Cambio:**
```php
$data = [
    'pageTitle' => 'Amortización del Lote',
    'lote' => $lote,
    'cuotas' => $cuotas,
    'resumen' => $resumen,
    'metricas' => $metricas,
    'kpis' => $kpis,
    'saldo_a_favor' => $this->loteModel->getSaldoAFavor($loteId)  // ← NUEVO
];
```

**Notas de Revisión:**
- [ ] Obtiene saldo actual de BD ✓
- [ ] Pasa correctamente a vista ✓
- [ ] Sin lógica adicional ✓

---

## 🧪 Testing - Checklist

### Pre-Deployment Testing

```
UNIT TESTS (si existen)
- [ ] LoteModel::getSaldoAFavor()
- [ ] LoteModel::incrementarSaldoAFavor()
- [ ] Transacciones en reajustarPlan()
- [ ] Validaciones de permisos

INTEGRATION TESTS
- [ ] TC-1: Acumular saldo a favor
- [ ] TC-2: Aplicar reajuste completo
- [ ] TC-3: Reajuste parcial (cuota incompleta)
- [ ] TC-4: Sin saldo disponible
- [ ] TC-5: Rollback en error

SMOKE TESTS
- [ ] Página amortizacion carga sin error
- [ ] Botón no aparece si saldo = 0
- [ ] Botón aparece si saldo > 0.01
- [ ] Botón requiere confirmación
- [ ] Mensaje de éxito después de reajuste

REGRESSION TESTS
- [ ] Pago normal sin excedente funciona
- [ ] Abono a capital aún funciona
- [ ] Otras páginas no afectadas
- [ ] Permisos existentes respetados
```

---

## 📋 SQL Execution Steps

### 1. Backup Previo
```bash
# Exportar schema actual
mysqldump -u root -p inversiones > backup_$(date +%Y%m%d).sql
```

### 2. Ejecutar Migration
```bash
mysql -u root -p inversiones < database/migration_saldo_a_favor.sql
```

### 3. Verificar
```sql
-- Verificar columna
SELECT * FROM information_schema.COLUMNS 
WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor';

-- Verificar índice
SELECT * FROM information_schema.STATISTICS 
WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor';

-- Verificar datos
SELECT id, codigo_lote, saldo_a_favor FROM lotes LIMIT 5;
-- Debe mostrar: saldo_a_favor = 0.00 para todos
```

---

## 🔄 Rollback (Si es Necesario)

```sql
-- Eliminar índice
DROP INDEX idx_lotes_saldo_a_favor ON lotes;

-- Eliminar columna
ALTER TABLE lotes DROP COLUMN saldo_a_favor;

-- Verificar
DESCRIBE lotes;
```

---

## 📝 Documentación Generada

| Archivo | Propósito | Público |
|---------|-----------|---------|
| `DOCUMENTACION_SALDO_FAVOR.md` | Técnica detallada | Sí (dev team) |
| `GUIA_PRUEBAS_SALDO_FAVOR.md` | QA/Testing | Sí (QA team) |
| `DIAGRAMA_VISUAL_SALDO_FAVOR.txt` | Flujos ASCII | Sí (stakeholders) |
| `RESUMEN_IMPLEMENTACION_SALDO_FAVOR.md` | Resumen ejecutivo | Sí (PM/stakeholders) |
| `INSTALL_SALDO_FAVOR.md` | Deploy | Sí (DevOps) |
| `NOTAS_IMPLEMENTACION.md` | Este archivo | Interna (dev) |

---

## 🚨 Consideraciones Importantes

### 1. Backwards Compatibility
✓ **Sí** - La nueva columna tiene DEFAULT 0.00
✓ Lotes existentes sin cambios
✓ Funcionalidad existente no afectada
✗ Requiere migration SQL

### 2. Performance
✓ **Indexado** - idx_lotes_saldo_a_favor
✓ Queries O(1) o O(n) según operación
✓ Transacciones cortas

### 3. Data Integrity
✓ **ACID** - BEGIN/COMMIT/ROLLBACK
✓ **Constraints** - GREATEST() evita negativos
✓ **Audit Trail** - Tabla pagos registra todo

### 4. Security
✓ **CSRF** - Token requerido
✓ **Permisos** - can('registrar_pagos')
✓ **SQL Injection** - Prepared statements
✓ **Input Validation** - Múltiples capas

---

## 📌 Dependencias Externas

| Componente | Versión | Nota |
|------------|---------|------|
| PHP | 7.2+ | Existente |
| MySQL/MariaDB | 5.7+ | Existente |
| Bootstrap | 5.x | Existente |
| jQuery | Cualquiera | No requerido (vanilla JS) |

---

## 🔗 Referencias de Código

### Métodos relacionados (no modificar)
- `PagoController::create()` - Muestra formulario
- `PagoController::distribuirPago()` - Distribuye monto
- `PagoController::aplicarAbonoCapital()` - Recalcula plan
- `AmortizacionController::calcularPlanAmortizacionFrances()` - Calcula cuotas
- `AmortizacionModel::getPendientesByLote()` - Obtiene pendientes

### Métodos nuevos que DEBEN existir
- `LoteModel::getSaldoAFavor()` ← **VERIFICAR**
- `LoteModel::incrementarSaldoAFavor()` ← **VERIFICAR**
- `AmortizacionController::reajustarPlan()` ← **VERIFICAR**

---

## 💬 Cambios de Comportamiento

### ANTES
- Pago excedente: Se pierde o se aplica a capital (completo recalc)
- Sin opción de compensar mora después

### DESPUÉS
- Pago excedente: Se acumula en saldo_a_favor
- Usuario puede reajustar cuando quiera
- Mora se evita automáticamente

**Impacto en Usuario:**
- Mayor flexibilidad
- Mejor control de deudas
- Evita mora innecesaria

**Impacto en Sistema:**
- Nueva transacción en reajuste
- Nueva entrada en tabla pagos
- Índice adicional

---

## 🎯 Sign-Off Checklist

Antes de merge a main:

```
REVISIÓN DE CÓDIGO
- [ ] SQL review (migration)
- [ ] PHP code review (controladores, modelos)
- [ ] HTML/JS review (vista)
- [ ] Seguridad review (CSRF, permisos)

TESTING
- [ ] Unit tests si existen
- [ ] Integration tests pasado
- [ ] Smoke tests completado
- [ ] Regression tests OK
- [ ] Manual QA OK

DOCUMENTACIÓN
- [ ] Documentación técnica completa
- [ ] Guía de pruebas lista
- [ ] Notas de deploy listas
- [ ] README actualizado

DEPLOYMENT
- [ ] Migration SQL probada en dev
- [ ] Backup script preparado
- [ ] Rollback script preparado
- [ ] Comunicación a stakeholders

FINALIZACIÓN
- [ ] Code merged a main
- [ ] Tag versión creado
- [ ] Release notes generadas
- [ ] Notificación a team
```

---

## 📞 Contacto y Soporte

**Implementación:** Especialista en Lógica de Pagos  
**Preguntas:** Revisar documentación primero  
**Issues:** Crear ticket con:
- Pasos a reproducir
- Error logs
- Base de datos state

---

## 📅 Timeline

| Fase | Fecha | Estado |
|------|-------|--------|
| Especificación | 29-11-2025 | ✅ Completada |
| Implementación | 29-11-2025 | ✅ Completada |
| Documentación | 29-11-2025 | ✅ Completada |
| Code Review | TBD | ⏳ Pendiente |
| QA/Testing | TBD | ⏳ Pendiente |
| Deployment | TBD | ⏳ Pendiente |
| Production | TBD | ⏳ Pendiente |

---

**Documento creado:** 29 de Noviembre de 2025  
**Versión:** 1.0  
**Estado:** Listo para Revisión
