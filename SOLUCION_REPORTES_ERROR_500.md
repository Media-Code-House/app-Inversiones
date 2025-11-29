# 🔧 SOLUCIÓN REPORTES - ERROR 500

## Cambios Realizados:

### 1. ✅ Corregido uso de `fetchAll()` en lugar de `query()`
- **Antes:** `$this->db->query($sql)` devolvía PDOStatement
- **Ahora:** `$this->db->fetchAll($sql)` devuelve array de resultados

### 2. ✅ Agregado COALESCE para manejar NULL en consultas SQL
```sql
-- Antes:
l.precio_venta

-- Ahora:
COALESCE(l.precio_venta, l.precio_lista) as precio_venta
```

### 3. ✅ Cambiado INNER JOIN a LEFT JOIN para clientes
```sql
-- Antes:
INNER JOIN clientes c ON l.cliente_id = c.id

-- Ahora:
LEFT JOIN clientes c ON l.cliente_id = c.id
```

### 4. ✅ Agregado valores por defecto para campos NULL
```sql
COALESCE(c.nombre, 'Sin cliente asignado') as cliente_nombre
COALESCE(u.nombre, 'Sin vendedor asignado') as vendedor_nombre
```

### 5. ✅ Manejo seguro de arrays vacíos
```php
// Filtrar valores NULL antes de sumar
$preciosVenta = array_filter(array_column($lotes, 'precio_venta'), fn($v) => $v !== null);
$totalVentas = array_sum($preciosVenta);
```

### 6. ✅ Agregado permiso 'ver_reportes' al rol vendedor
En `core/helpers.php` línea 258

---

## 🧪 Pasos para Verificar en Producción:

### Opción 1: Archivo de Debug
Acceder a: `https://inversiones.mch.com.co/debug-reportes.php`

Este archivo ejecutará el ReporteController y mostrará el error exacto.

### Opción 2: Activar debug en URL
Acceder a: `https://inversiones.mch.com.co/reportes/lotes-vendidos?debug=1`

Esto mostrará los errores PHP directamente.

---

## 🚨 Posibles Causas Restantes:

Si después de los cambios sigue dando error 500:

### 1. **Límites de memoria PHP**
```php
// Verificar en php.ini:
memory_limit = 256M
```

### 2. **Timeout de ejecución**
```php
// Aumentar en php.ini:
max_execution_time = 300
```

### 3. **mod_security bloqueando**
Revisar logs del servidor:
```bash
tail -f /var/log/apache2/error.log
```

### 4. **Permisos de archivos**
```bash
chmod 755 app/Controllers/ReporteController.php
chmod 755 app/Views/reportes/*.php
```

### 5. **Cache de OPcache**
Limpiar cache:
```bash
php -r "opcache_reset();"
# O reiniciar Apache/PHP-FPM
```

---

## 📊 Consultas SQL Corregidas:

### Lotes Vendidos:
```sql
SELECT 
    l.id,
    l.codigo_lote,
    l.fecha_venta,
    COALESCE(l.precio_venta, l.precio_lista) as precio_venta,
    p.nombre as proyecto_nombre,
    p.codigo as proyecto_codigo,
    COALESCE(c.nombre, 'Sin cliente asignado') as cliente_nombre,
    COALESCE(c.numero_documento, '') as cliente_documento,
    COALESCE(u.nombre, 'Sin vendedor asignado') as vendedor_nombre,
    (COALESCE(l.precio_venta, l.precio_lista) * 0.03) as comision_vendedor
FROM lotes l
INNER JOIN proyectos p ON l.proyecto_id = p.id
LEFT JOIN clientes c ON l.cliente_id = c.id
LEFT JOIN users u ON l.vendedor_id = u.id
WHERE l.estado = 'vendido'
```

### Ventas por Proyecto:
```sql
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    p.ubicacion,
    COUNT(l.id) as total_lotes,
    SUM(CASE WHEN l.estado = 'disponible' THEN 1 ELSE 0 END) as lotes_disponibles,
    SUM(CASE WHEN l.estado = 'vendido' THEN 1 ELSE 0 END) as lotes_vendidos,
    SUM(CASE WHEN l.estado = 'vendido' THEN COALESCE(l.precio_venta, l.precio_lista) ELSE 0 END) as valor_ventas,
    ROUND(SUM(CASE WHEN l.estado = 'vendido' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(l.id), 0), 1) as porcentaje_vendido
FROM proyectos p
LEFT JOIN lotes l ON p.id = l.proyecto_id
GROUP BY p.id
ORDER BY valor_ventas DESC
```

### Cartera Pendiente:
```sql
SELECT 
    a.id,
    a.numero_cuota,
    a.fecha_vencimiento,
    a.valor_cuota,
    a.saldo,
    a.estado,
    l.codigo_lote,
    COALESCE(l.precio_venta, l.precio_lista) as valor_lote,
    p.nombre as proyecto_nombre,
    COALESCE(c.nombre, 'Sin cliente') as cliente_nombre,
    COALESCE(c.telefono, '') as cliente_telefono,
    COALESCE(c.email, '') as cliente_email,
    DATEDIFF(CURDATE(), a.fecha_vencimiento) as dias_mora,
    CASE 
        WHEN DATEDIFF(CURDATE(), a.fecha_vencimiento) > 0 THEN 'VENCIDA'
        WHEN DATEDIFF(CURDATE(), a.fecha_vencimiento) BETWEEN -7 AND 0 THEN 'POR VENCER'
        ELSE 'VIGENTE'
    END as estado_mora
FROM amortizaciones a
INNER JOIN lotes l ON a.lote_id = l.id
INNER JOIN proyectos p ON l.proyecto_id = p.id
LEFT JOIN clientes c ON l.cliente_id = c.id
WHERE a.estado = 'pendiente' AND a.saldo > 0
```

---

## ✅ Estado del Módulo:

- [x] ReporteController con todas las queries corregidas
- [x] Manejo de NULL en SQL con COALESCE
- [x] Manejo de arrays vacíos en PHP
- [x] LEFT JOIN en lugar de INNER JOIN
- [x] Permisos configurados correctamente
- [x] Arrow functions compatibles con PHP 8.2
- [x] Archivo debug-reportes.php creado

---

## 🎯 Próximo Paso:

**Subir los archivos corregidos a producción y acceder a:**
- https://inversiones.mch.com.co/debug-reportes.php

Si el debug muestra que todo funciona, el problema es de caché o configuración del servidor.
