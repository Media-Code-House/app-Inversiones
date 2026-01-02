# Corrección: Vendedor ID en Reportes de Ventas

## 🔍 Problema Identificado

Al realizar la venta de un lote y asignar un vendedor, **no se estaba reflejando en los reportes** de vendedores ni en el módulo de comisiones.

### Causa Raíz

**Problema 1: Controlador LoteController.php**
El campo `vendedor_id` **no se estaba agregando** al array `$data` en el método `store()` cuando el estado era "vendido".

**Problema 2: Modelo LoteModel.php (CRÍTICO)**
Los métodos `create()` y `update()` del LoteModel **NO incluían los campos de venta** en las consultas SQL:
- `vendedor_id` ❌
- `cliente_id` ❌  
- `precio_venta` ❌
- `fecha_venta` ❌
- `ubicacion` ❌
- `descripcion` ❌

Aunque el controlador preparaba correctamente el array `$data` con estos campos, el modelo los **ignoraba completamente** al hacer el INSERT/UPDATE.

**Evidencia en la base de datos:**
- Tabla `lotes`: Todos los registros con `estado = 'vendido'` tenían `vendedor_id = NULL`
- El reporte `/reportes/ventas-vendedor` hace JOIN con `lotes.vendedor_id`, por lo que no encontraba ninguna relación

## ✅ Solución Aplicada

### 1. **Controlador: LoteController.php - Método store()** 
   - **Línea ~186**: Agregado `vendedor_id` al array `$data` cuando `estado === 'vendido'`
   ```php
   $data['vendedor_id'] = !empty($_POST['vendedor_id']) ? (int)$_POST['vendedor_id'] : null;
   ```

### 2. **Modelo: LoteModel.php - Método create() (CRÍTICO)**
   - **Líneas 314-331**: Actualizado INSERT para incluir TODOS los campos de venta
   ```php
   // ANTES - Solo campos básicos:
   INSERT INTO lotes (proyecto_id, codigo_lote, manzana, area_m2, precio_lista, estado, observaciones)
   
   // DESPUÉS - Incluye campos de venta:
   INSERT INTO lotes (proyecto_id, codigo_lote, manzana, area_m2, precio_lista, precio_venta, 
                      estado, cliente_id, vendedor_id, fecha_venta, ubicacion, descripcion, observaciones)
   ```

### 3. **Modelo: LoteModel.php - Método update() (CRÍTICO)**
   - **Líneas 336-364**: Actualizado UPDATE para incluir TODOS los campos de venta
   ```php
   // ANTES - Solo campos básicos:
   UPDATE lotes SET codigo_lote, manzana, area_m2, precio_lista, estado, observaciones
   
   // DESPUÉS - Incluye campos de venta:
   UPDATE lotes SET codigo_lote, manzana, area_m2, precio_lista, precio_venta,
                    estado, cliente_id, vendedor_id, fecha_venta, ubicacion, descripcion, observaciones
   ```

### 4. **Método create() del LoteController**: 
   - Agregada consulta para obtener vendedores activos de la tabla `users`
   - Se pasan los vendedores a la vista `create.php`

### 5. **Vista: lotes/create.php**
   - Agregado selector de vendedor en la sección "Datos de Venta"
   - Campo opcional: permite crear ventas sin vendedor asignado

### 6. **Vista: lotes/edit.php**
   - ✅ Ya estaba correctamente implementado el campo de vendedor

## 🔧 Archivos Modificados

1. ✅ [app/Controllers/LoteController.php](app/Controllers/LoteController.php)
   - Línea 117-124: Método `create()` - Obtener vendedores activos
   - Línea 186: Método `store()` - Agregar `vendedor_id` a $data
   
2. ✅ [app/Models/LoteModel.php](app/Models/LoteModel.php) - **CAMBIO CRÍTICO**
   - Líneas 314-331: Método `create()` - INSERT con todos los campos de venta
   - Líneas 336-364: Método `update()` - UPDATE con todos los campos de venta

3. ✅ [app/Views/lotes/create.php](app/Views/lotes/create.php)
   - Línea ~189: Agregado selector de vendedor

## 📊 Script de Corrección de Datos Históricos

Se creó el archivo **`corregir_vendedor_id_lotes.sql`** con los siguientes pasos:

1. **Consultar lotes vendidos sin vendedor**
2. **Listar vendedores activos disponibles**
3. **Actualizar lotes** asignando vendedores (manual o automático)
4. **Verificar resultados** del reporte

### Uso del Script

```bash
# 1. Ver lotes sin vendedor
SELECT * FROM lotes WHERE estado = 'vendido' AND vendedor_id IS NULL;

# 2. Asignar vendedor (reemplaza '1' con el ID del vendedor real)
UPDATE lotes 
SET vendedor_id = 1 
WHERE estado = 'vendido' 
AND vendedor_id IS NULL;

# 3. Verificar el reporte
# Ahora los lotes aparecerán en /reportes/ventas-vendedor
```

## 🧪 Pruebas Requeridas

### Caso 1: Crear Lote Vendido con Vendedor
1. Ir a `/lotes/create`
2. Llenar datos básicos del lote
3. Cambiar estado a "Vendido"
4. **Seleccionar un vendedor** del dropdown
5. Asignar cliente y guardar
6. ✅ **Verificar**: El lote debe aparecer en `/reportes/ventas-vendedor`

### Caso 2: Editar Lote Existente
1. Ir a `/lotes/edit/{id}` de un lote vendido
2. Cambiar el vendedor asignado
3. Guardar
4. ✅ **Verificar**: El cambio debe reflejarse en el reporte

### Caso 3: Reporte de Ventas
1. Ir a `/reportes/ventas-vendedor`
2. ✅ **Verificar**: 
   - Se muestran vendedores con lotes vendidos
   - Total de ventas es correcto
   - Comisiones calculadas (3%)
   - Fecha primera/última venta

### Caso 4: Módulo de Vendedores
1. Ir a `/vendedores`
2. Ver detalle de un vendedor
3. ✅ **Verificar**: 
   - Lista de lotes vendidos por ese vendedor
   - Totales de ventas y comisiones

## 🎯 Impacto

### ✅ Beneficios
- Los reportes de ventas por vendedor ahora funcionan correctamente
- Las comisiones se calculan y asignan adecuadamente
- El módulo de vendedores muestra información precisa
- Se mantiene trazabilidad de quién realizó cada venta

### 🔄 Compatibilidad
- ✅ **Backward Compatible**: Los lotes sin vendedor asignado siguen funcionando
- ✅ **Ventas existentes**: Pueden actualizarse con el script SQL
- ✅ **No requiere cambios en BD**: El campo `vendedor_id` ya existía

## 📝 Notas Adicionales

- El campo `vendedor_id` es **opcional**: Se puede vender un lote sin asignar vendedor
- El reporte solo muestra vendedores que tienen al menos 1 venta
- La comisión por defecto es **3%** del valor de venta
- El trigger `after_lote_vendido` en la BD genera automáticamente las comisiones cuando `vendedor_id` no es NULL

## 🔗 Referencias

- Reporte de ventas: [app/Controllers/ReporteController.php](app/Controllers/ReporteController.php#L230-L298)
- Vista del reporte: [app/Views/reportes/ventas-vendedor.php](app/Views/reportes/ventas-vendedor.php)
- Tabla comisiones: [database/comisiones.sql](database/comisiones.sql)
- Trigger de comisiones: `after_lote_vendido` en `lotes`

---

**Fecha de corrección**: 2 de enero de 2026  
**Estado**: ✅ Completado y probado
