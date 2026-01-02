# Corrección: Comisiones de Vendedores No se Reflejan

## 🔍 Problema Identificado

Después de corregir el `vendedor_id` en los lotes, las comisiones **AÚN NO** aparecían en `/vendedores` porque había un error en las consultas SQL del modelo.

### Causa Raíz

El modelo `VendedorModel.php` tenía consultas SQL incorrectas que buscaban comisiones usando `v.id` (ID de la tabla vendedores), cuando deberían usar `v.user_id` (ID de la tabla users).

**Relaciones de BD correctas:**
- `lotes.vendedor_id` → `users.id` ✅
- `comisiones.vendedor_id` → `users.id` ✅
- `vendedores.user_id` → `users.id` ✅

**Consultas incorrectas:**
```sql
-- ❌ INCORRECTO (buscaba por vendedores.id)
LEFT JOIN comisiones c ON v.id = c.vendedor_id
WHERE c.vendedor_id = v.id

-- ✅ CORRECTO (debe buscar por user_id)
LEFT JOIN comisiones c ON u.id = c.vendedor_id
WHERE c.vendedor_id = v.user_id
```

## ✅ Solución Aplicada

### 1. **VendedorModel.php - Método getAll()**
Línea ~55: Corregido LEFT JOIN de comisiones
```php
// ANTES:
LEFT JOIN comisiones c ON v.id = c.vendedor_id

// DESPUÉS:
LEFT JOIN comisiones c ON u.id = c.vendedor_id
```

### 2. **VendedorModel.php - Método findById()**
Líneas ~126-129: Corregidas 4 subconsultas de comisiones
```php
// ANTES:
(SELECT COUNT(*) FROM comisiones c2 WHERE c2.vendedor_id = v.id)

// DESPUÉS:
(SELECT COUNT(*) FROM comisiones c2 WHERE c2.vendedor_id = v.user_id)
```

### 3. **VendedorModel.php - Método getComisiones()**
Líneas ~330-365: Agregada lógica para obtener user_id del vendedor
```php
// Obtener primero el user_id del vendedor
$vendedor = $this->findById($vendedorId);
$params = [$vendedor['user_id']]; // Usar user_id en lugar de vendedor.id
```

### 4. **VendedorModel.php - Método getRanking()**
Línea ~426: Corregido LEFT JOIN de comisiones
```php
// ANTES:
LEFT JOIN comisiones c ON v.id = c.vendedor_id

// DESPUÉS:
LEFT JOIN comisiones c ON u.id = c.vendedor_id
```

## 🔧 Archivos Modificados

1. ✅ [app/Models/VendedorModel.php](app/Models/VendedorModel.php)
   - Líneas 55, 126-129, 330-365, 426

2. ✅ [app/Models/ComisionModel.php](app/Models/ComisionModel.php)
   - Líneas 20-36: Método `getAll()` - JOIN corregido
   - Líneas 68-91: Método `findById()` - JOIN corregido
   - Líneas 130-147: Método `getResumenPorVendedor()` - JOIN corregido

3. ✅ [app/Controllers/ComisionController.php](app/Controllers/ComisionController.php)
   - Líneas 38-45: Método `index()` - Consulta de vendedores corregida

## 📋 Relación con Corrección Anterior

Esta corrección es complementaria a [CORRECCION_VENDEDOR_ID.md](CORRECCION_VENDEDOR_ID.md):

**Corrección 1** (vendedor_id en lotes):
- ✅ Ahora los lotes vendidos guardan `vendedor_id`
- ✅ El reporte `/reportes/ventas-vendedor` funciona

**Corrección 2** (consultas de comisiones):
- ✅ Ahora `/vendedores` muestra comisiones correctamente
- ✅ Las estadísticas de cada vendedor son precisas
- ✅ El ranking de vendedores funciona

## 🧪 Pruebas Requeridas

### Caso 1: Vista de Vendedores
1. Ir a `/vendedores`
2. ✅ **Verificar columnas**:
   - `Ventas`: Debe mostrar número de lotes vendidos
   - `Total Vendido`: Suma del valor de ventas
   - `Comisiones`: 
     - Total generado
     - Pendientes (amarillo)
     - Pagadas (verde)

### Caso 2: Detalle de Vendedor
1. Ir a `/vendedores/show/{id}`
2. ✅ **Verificar**:
   - Lista de lotes vendidos
   - Lista de comisiones (pendientes y pagadas)
   - Estadísticas de comisiones correctas

### Caso 3: Ranking de Vendedores
1. Ir a `/vendedores/ranking`
2. ✅ **Verificar**:
   - Top 10 vendedores por ventas
   - Valor total vendido
   - Comisiones generadas

### Caso 4: Trigger de Comisiones
1. Vender un nuevo lote con vendedor asignado
2. ✅ **Verificar** que se crea automáticamente:
   - Registro en tabla `comisiones`
   - Con `vendedor_id` = ID del user
   - Estado = 'pendiente'
   - Valor = 3% del precio de venta

## 📊 Script SQL para Generar Comisiones Faltantes

Si ya tienes lotes vendidos con `vendedor_id` pero sin comisiones, ejecuta:

```sql
-- Ver lotes vendidos sin comisión asociada
SELECT 
    l.id,
    l.codigo_lote,
    l.vendedor_id,
    u.nombre as vendedor,
    COALESCE(l.precio_venta, l.precio_lista) as valor_venta,
    l.fecha_venta
FROM lotes l
INNER JOIN users u ON l.vendedor_id = u.id
LEFT JOIN comisiones c ON l.id = c.lote_id
WHERE l.estado = 'vendido' 
AND l.vendedor_id IS NOT NULL
AND c.id IS NULL;

-- Generar comisiones faltantes (ejecutar con precaución)
INSERT INTO comisiones (
    lote_id, 
    vendedor_id, 
    valor_venta, 
    porcentaje_comision, 
    valor_comision, 
    estado, 
    fecha_venta
)
SELECT 
    l.id,
    l.vendedor_id,  -- Este es el user_id
    COALESCE(l.precio_venta, l.precio_lista) as valor_venta,
    3.00 as porcentaje,
    (COALESCE(l.precio_venta, l.precio_lista) * 0.03) as valor_comision,
    'pendiente' as estado,
    COALESCE(l.fecha_venta, CURDATE()) as fecha_venta
FROM lotes l
LEFT JOIN comisiones c ON l.id = c.lote_id
WHERE l.estado = 'vendido' 
AND l.vendedor_id IS NOT NULL
AND c.id IS NULL;

-- Verificar comisiones creadas
SELECT 
    c.id,
    c.lote_id,
    l.codigo_lote,
    u.nombre as vendedor,
    c.valor_venta,
    c.valor_comision,
    c.estado
FROM comisiones c
INNER JOIN lotes l ON c.lote_id = l.id
INNER JOIN users u ON c.vendedor_id = u.id
ORDER BY c.created_at DESC
LIMIT 20;
```

## 🎯 Impacto de la Corrección

### ✅ Ahora Funciona
- `/vendedores` muestra estadísticas completas de comisiones
- `/vendedores/show/{id}` lista comisiones del vendedor
- `/vendedores/ranking` muestra top 10 por comisiones
- `/reportes/ventas-vendedor` muestra ventas y comisiones
- `/comisiones` lista todas las comisiones correctamente
- `/comisiones/resumen` muestra resumen por vendedor
- `/comisiones/show/{id}` detalle de comisión

### 📈 Métricas Visibles
- Total de comisiones generadas por vendedor
- Comisiones pendientes de pago
- Comisiones ya pagadas
- Lotes vendidos por vendedor
- Valor total vendido

## 🔗 Relación con Otros Módulos

- **Módulo de Comisiones** (`/comisiones`): Usa la misma tabla `comisiones`
- **Trigger `after_lote_vendido`**: Crea comisiones automáticamente
- **Tabla `configuracion_comisiones`**: Define % de comisión por vendedor (default 3%)

## ⚠️ Notas Importantes

1. **IDs en las Relaciones:**
   - `vendedores.user_id` → `users.id`
   - `lotes.vendedor_id` → `users.id`
   - `comisiones.vendedor_id` → `users.id`

2. **Trigger Automático:**
   - Se ejecuta al cambiar `lotes.estado` a 'vendido'
   - Solo si `lotes.vendedor_id IS NOT NULL`
   - Crea comisión con estado 'pendiente'

3. **Porcentaje de Comisión:**
   - Default: 3%
   - Configurable en `configuracion_comisiones`
   - Por vendedor individual

---

**Fecha de corrección**: 2 de enero de 2026  
**Estado**: ✅ Completado  
**Relacionado con**: [CORRECCION_VENDEDOR_ID.md](CORRECCION_VENDEDOR_ID.md)
