# MÓDULO 4: CRUD DE LOTES - IMPLEMENTACIÓN COMPLETA
## Sistema de Inversiones - Ingeniero de Software Principal

---

## 📋 RESUMEN EJECUTIVO

**Estado:** ✅ Implementación completa del Módulo 4 con preparación para Módulo 5
**Fecha:** <?= date('Y-m-d H:i:s') ?>

**Componentes Entregados:**
1. ✅ Script de actualización de base de datos (`update.sql`)
2. ✅ Controlador mejorado con paginación (`LoteController.php`)
3. ✅ Modelo con consultas optimizadas (`LoteModel.php`)
4. ✅ Vistas actualizadas con paginación y diseño mejorado
5. ✅ Funciones helper para formateo y permisos (`helpers.php`)
6. ✅ Vistas para Módulo 5: Amortización y Registro de Pagos

---

## 🗄️ 1. BASE DE DATOS - UPDATE.SQL

### Ubicación: `database/update.sql`

### Cambios Implementados:

```sql
-- 1. Agregar campo vendedor_id para tracking de ventas
ALTER TABLE lotes 
ADD COLUMN vendedor_id INT UNSIGNED NULL AFTER cliente_id;

-- 2. Foreign Key hacia tabla users
ALTER TABLE lotes 
ADD CONSTRAINT fk_lotes_vendedor 
FOREIGN KEY (vendedor_id) REFERENCES users(id) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- 3. Índice para optimización de consultas
ALTER TABLE lotes 
ADD INDEX idx_vendedor (vendedor_id);

-- 4. Campos adicionales para descripción detallada
ALTER TABLE lotes 
ADD COLUMN ubicacion VARCHAR(255) NULL AFTER manzana,
ADD COLUMN descripcion TEXT NULL AFTER ubicacion;

-- 5. Asignar vendedor a lotes ya vendidos
UPDATE lotes 
SET vendedor_id = (SELECT id FROM users WHERE rol='admin' LIMIT 1) 
WHERE estado='vendido' AND vendedor_id IS NULL;
```

### Queries de Verificación Incluidas:

```sql
-- Verificar estructura
DESCRIBE lotes;

-- Verificar foreign keys
SHOW CREATE TABLE lotes;

-- Verificar asignación de vendedores
SELECT estado, COUNT(*) as total, 
       SUM(CASE WHEN vendedor_id IS NOT NULL THEN 1 ELSE 0 END) as con_vendedor
FROM lotes GROUP BY estado;
```

### Script de Rollback:

```sql
-- Revertir cambios si es necesario
ALTER TABLE lotes DROP FOREIGN KEY fk_lotes_vendedor;
ALTER TABLE lotes DROP INDEX idx_vendedor;
ALTER TABLE lotes DROP COLUMN vendedor_id;
ALTER TABLE lotes DROP COLUMN ubicacion;
ALTER TABLE lotes DROP COLUMN descripcion;
```

### ⚠️ INSTRUCCIONES DE EJECUCIÓN:

**Para desarrollo local:**
```powershell
# Opción 1: Desde terminal PowerShell
Get-Content "database\update.sql" | mysql -u root sistema_lotes

# Opción 2: Desde phpMyAdmin
# 1. Acceder a http://127.0.0.1/phpmyadmin
# 2. Seleccionar base de datos: sistema_lotes
# 3. Pestaña "SQL"
# 4. Copiar contenido de update.sql
# 5. Ejecutar
```

**Para producción (inversiones.mch.com.co):**
```
1. Acceder al panel de hosting
2. Ir a phpMyAdmin
3. Seleccionar base de datos: u418271893_inversiones
4. Ejecutar el contenido de update.sql
5. Verificar con las queries de comprobación
```

---

## 🎯 2. CONTROLADOR - LoteController.php

### Ubicación: `app/Controllers/LoteController.php`

### Mejoras Implementadas:

#### 2.1 Constructor Corregido
```php
public function __construct()
{
    // Se eliminó parent::__construct() que causaba HTTP 500
    $this->loteModel = new \App\Models\LoteModel();
    $this->proyectoModel = new \App\Models\ProyectoModel();
    $this->clienteModel = new \App\Models\ClienteModel();
    $this->amortizacionModel = new \App\Models\AmortizacionModel();
}
```

#### 2.2 Método index() - Listado Paginado

**Características:**
- ✅ Paginación: 15 lotes por página
- ✅ Filtros: búsqueda, proyecto_id, estado
- ✅ JOIN a proyectos, clientes, users (vendedor)
- ✅ Cálculo de precio_m2
- ✅ Badge class automático por estado

**Pseudo-código:**
```php
public function index()
{
    // 1. Autenticación requerida
    requireAuth();
    
    // 2. Obtener parámetros de paginación
    $page = $_GET['page'] ?? 1
    $perPage = 15
    
    // 3. Construir filtros
    $filters = [
        'search' => $_GET['search'] ?? '',
        'proyecto_id' => $_GET['proyecto_id'] ?? null,
        'estado' => $_GET['estado'] ?? '',
        'page' => $page,
        'per_page' => $perPage
    ]
    
    // 4. Obtener datos paginados del modelo
    $result = loteModel.getAllPaginated(filters)
    // Retorna: {data: [], total, per_page, current_page, last_page}
    
    // 5. Enriquecer cada lote con cálculos
    FOREACH lote IN result['data']:
        lote['precio_m2'] = lote['precio_lista'] / lote['area_m2']
        lote['badgeClass'] = getBadgeClass(lote['estado'])
    ENDFOREACH
    
    // 6. Preparar datos para la vista
    proyectos = proyectoModel.getAllActivos()
    estados = ['disponible', 'reservado', 'vendido', 'bloqueado']
    
    // 7. Renderizar vista
    view('lotes/index', {
        lotes: result,  // Estructura paginada completa
        proyectos: proyectos,
        estados: estados,
        filtros: filters
    })
}
```

**Variables Enviadas a Vista:**
```php
$lotes = [
    'data' => [
        // Array de lotes con todos los campos
        [
            'id' => 1,
            'codigo_lote' => 'A-001',
            'proyecto_nombre' => 'Villa Campestre',
            'cliente_nombre' => 'Juan Pérez',
            'vendedor_nombre' => 'María García',
            'area_m2' => 250.00,
            'precio_lista' => 50000000,
            'precio_m2' => 200000,  // Calculado
            'estado' => 'vendido',
            'badgeClass' => 'bg-primary',  // Calculado
            'tiene_amortizacion' => 1,
            // ... más campos
        ]
    ],
    'total' => 150,
    'per_page' => 15,
    'current_page' => 1,
    'last_page' => 10
];

$proyectos = [ /* Lista de proyectos activos */ ];
$estados = ['disponible', 'reservado', 'vendido', 'bloqueado'];
$filtros = [ /* Filtros aplicados */ ];
```

#### 2.3 Método show($id) - Detalle con Información Financiera

**Pseudo-código:**
```php
public function show($id)
{
    // 1. Autenticación
    requireAuth();
    
    // 2. Obtener lote
    lote = loteModel.findById(id)
    IF NOT lote:
        flash('error', 'Lote no encontrado')
        redirect('/lotes')
    ENDIF
    
    // 3. Calcular precio por metro cuadrado
    lote['precio_m2'] = lote['area_m2'] > 0 
        ? lote['precio_lista'] / lote['area_m2'] 
        : 0
    
    // 4. Obtener información financiera si tiene amortización
    IF lote['tiene_amortizacion'] > 0:
        resumenAmortizacion = amortizacionModel.getResumenByLote(id)
        cuotas = amortizacionModel.getByLote(id)
        
        // 5. Calcular variables financieras clave
        total_pagado = resumenAmortizacion['total_pagado']
        valor_total = resumenAmortizacion['valor_total_financiado']
        saldo_pendiente = resumenAmortizacion['saldo_total']
        porcentaje_pagado = (total_pagado / valor_total) * 100
        cuotas_mora = resumenAmortizacion['cuotas_vencidas']
        cuotas_pagadas = resumenAmortizacion['cuotas_pagadas']
        
        // 6. Preparar resumen del plan
        resumenPlan = {
            'total_cuotas': total_cuotas,
            'valor_total': valor_total,
            'total_pagado': total_pagado,
            'saldo_pendiente': saldo_pendiente,
            'porcentaje_pagado': porcentaje_pagado,
            'cuotas_pagadas': cuotas_pagadas,
            'cuotas_pendientes': cuotas_pendientes,
            'cuotas_mora': cuotas_mora,
            'max_dias_mora': max_dias_mora
        }
    ENDIF
    
    // 7. Obtener historial de auditoría
    historial = getHistorialSimulado(lote)
    
    // 8. Renderizar vista con todos los datos
    view('lotes/show', {
        lote: lote,
        amortizacion: resumenPlan,
        cuotas: cuotas,
        pagos: pagos,
        historial: historial,
        total_pagado: total_pagado,
        saldo_pendiente: saldo_pendiente,
        porcentaje_pagado: porcentaje_pagado,
        cuotas_mora: cuotas_mora,
        cuotas_pagadas: cuotas_pagadas,
        precio_m2: precio_m2
    })
}
```

**Variables Enviadas a Vista show.php:**
```php
$lote = [ /* Datos completos del lote */ ];
$amortizacion = [
    'total_cuotas' => 24,
    'valor_total' => 50000000,
    'total_pagado' => 25000000,
    'saldo_pendiente' => 25000000,
    'porcentaje_pagado' => 50.00,
    'cuotas_pagadas' => 12,
    'cuotas_pendientes' => 12,
    'cuotas_mora' => 2,
    'max_dias_mora' => 15
];
$cuotas = [ /* Array de cuotas */ ];
$pagos = [ /* Historial de pagos */ ];
$historial = [ /* Eventos de auditoría */ ];
$total_pagado = 25000000;
$saldo_pendiente = 25000000;
$porcentaje_pagado = 50.00;
$cuotas_mora = 2;
$cuotas_pagadas = 12;
$precio_m2 = 200000;
```

#### 2.4 Métodos Preparados para Módulo 5

##### verAmortizacion($id)
**Propósito:** Vista completa del plan de amortización
```php
public function verAmortizacion($id)
{
    lote = findById(id)
    cuotas = amortizacionModel.getByLote(id)
    resumen_plan = amortizacionModel.getResumenByLote(id)
    
    view('lotes/amortizacion', {
        lote: lote,
        cuotas: cuotas,
        resumen_plan: resumen_plan
    })
}
```

##### registrarPago($id)
**Propósito:** Formulario para registro de pagos
```php
public function registrarPago($id)
{
    lote = findById(id)
    cuotas_pendientes = amortizacionModel.getPendientesByLote(id)
    
    view('lotes/registrar_pago', {
        lote: lote,
        cuotas_pendientes: cuotas_pendientes
    })
}
```

#### 2.5 Helper getBadgeClass($estado)
```php
private function getBadgeClass($estado)
{
    SWITCH estado:
        CASE 'disponible': RETURN 'bg-success'
        CASE 'reservado': RETURN 'bg-warning text-dark'
        CASE 'vendido': RETURN 'bg-primary'
        CASE 'bloqueado': RETURN 'bg-secondary'
        DEFAULT: RETURN 'bg-secondary'
    ENDSWITCH
}
```

---

## 📊 3. MODELO - LoteModel.php

### Ubicación: `app/Models/LoteModel.php`

### Método getAllPaginated($filters)

**Pseudo-código Completo:**
```php
public function getAllPaginated($filters)
{
    // 1. Extraer parámetros de paginación
    page = filters['page'] ?? 1
    perPage = filters['per_page'] ?? 15
    offset = (page - 1) * perPage
    
    // 2. Query base con JOINs completos
    baseSQL = "
        FROM lotes l 
        INNER JOIN proyectos p ON l.proyecto_id = p.id 
        LEFT JOIN clientes c ON l.cliente_id = c.id 
        LEFT JOIN users u ON l.vendedor_id = u.id
        WHERE 1=1
    "
    
    params = []
    whereConditions = ""
    
    // 3. Construir condiciones WHERE dinámicas
    
    // 3.1 Filtro de búsqueda (multi-campo)
    IF filters['search'] NOT EMPTY:
        whereConditions += "
            AND (l.codigo_lote LIKE ? 
                 OR l.manzana LIKE ? 
                 OR c.nombre LIKE ? 
                 OR p.nombre LIKE ?)
        "
        busqueda = "%" + filters['search'] + "%"
        params.push(busqueda, busqueda, busqueda, busqueda)
    ENDIF
    
    // 3.2 Filtro por proyecto
    IF filters['proyecto_id'] NOT EMPTY:
        whereConditions += " AND l.proyecto_id = ? "
        params.push(filters['proyecto_id'])
    ENDIF
    
    // 3.3 Filtro por estado
    IF filters['estado'] NOT EMPTY:
        whereConditions += " AND l.estado = ? "
        params.push(filters['estado'])
    ENDIF
    
    // 4. Contar total de registros (sin LIMIT)
    countSQL = "SELECT COUNT(*) as total " + baseSQL + whereConditions
    totalResult = db.fetch(countSQL, params)
    total = totalResult['total']
    
    // 5. Query completo con datos (con LIMIT y OFFSET)
    dataSQL = "
        SELECT l.*,
               p.nombre as proyecto_nombre,
               p.codigo as proyecto_codigo,
               c.nombre as cliente_nombre,
               c.documento as cliente_documento,
               u.nombre as vendedor_nombre,
               (SELECT COUNT(*) FROM amortizaciones 
                WHERE lote_id = l.id) as tiene_amortizacion
        " + baseSQL + whereConditions + "
        ORDER BY l.updated_at DESC, l.created_at DESC
        LIMIT ? OFFSET ?
    "
    
    params.push(perPage, offset)
    data = db.fetchAll(dataSQL, params)
    
    // 6. Calcular número de páginas
    lastPage = total > 0 ? CEIL(total / perPage) : 1
    
    // 7. Retornar estructura paginada
    RETURN {
        'data': data,
        'total': total,
        'per_page': perPage,
        'current_page': page,
        'last_page': lastPage
    }
}
```

**Características SQL:**
- ✅ INNER JOIN a `proyectos` (obligatorio)
- ✅ LEFT JOIN a `clientes` (puede ser NULL si no vendido)
- ✅ LEFT JOIN a `users` (vendedor, puede ser NULL)
- ✅ Subquery para contar amortizaciones activas
- ✅ ORDER BY por updated_at DESC (más recientes primero)
- ✅ LIMIT y OFFSET para paginación

---

## 🎨 4. VISTAS

### 4.1 Vista index.php - Listado con Paginación

**Ubicación:** `app/Views/lotes/index.php`

**Estructura HTML:**
```html
<div class="container-fluid py-4">
    <!-- Header con botón Nuevo Lote -->
    <div class="d-flex justify-content-between">
        <h1>Gestión de Lotes</h1>
        <a href="/lotes/create" class="btn btn-primary">
            Nuevo Lote
        </a>
    </div>

    <!-- Card de Filtros -->
    <div class="card mb-4">
        <form method="GET" action="/lotes">
            <!-- Filtro por Proyecto -->
            <select name="proyecto_id">
                <option value="">Todos</option>
                <?php foreach ($proyectos as $p): ?>
                    <option value="<?= $p['id'] ?>" 
                        <?= $filtros['proyecto_id'] == $p['id'] ? 'selected' : '' ?>>
                        <?= $p['nombre'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Filtro por Estado -->
            <select name="estado">
                <option value="">Todos</option>
                <?php foreach ($estados as $e): ?>
                    <option value="<?= $e ?>"
                        <?= $filtros['estado'] == $e ? 'selected' : '' ?>>
                        <?= ucfirst($e) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Búsqueda texto -->
            <input type="text" name="search" 
                   value="<?= $filtros['search'] ?>"
                   placeholder="Buscar...">

            <button type="submit">Filtrar</button>
        </form>
    </div>

    <!-- Tabla de Lotes -->
    <div class="card">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Proyecto</th>
                    <th>Código</th>
                    <th>Manzana</th>
                    <th>Área (m²)</th>
                    <th>Precio Lista</th>
                    <th>Precio/m²</th>
                    <th>Estado</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lotes['data'] as $lote): ?>
                <tr>
                    <td>
                        <small><?= $lote['proyecto_codigo'] ?></small><br>
                        <strong><?= $lote['proyecto_nombre'] ?></strong>
                    </td>
                    <td>
                        <span class="badge bg-dark">
                            <?= $lote['codigo_lote'] ?>
                        </span>
                    </td>
                    <td><?= $lote['manzana'] ?></td>
                    <td><?= number_format($lote['area_m2'], 2) ?> m²</td>
                    <td><strong>$<?= number_format($lote['precio_lista'], 0) ?></strong></td>
                    <td class="text-muted">
                        $<?= number_format($lote['precio_m2'], 0) ?>/m²
                    </td>
                    <td>
                        <span class="badge <?= $lote['badgeClass'] ?>">
                            <?= ucfirst($lote['estado']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($lote['cliente_nombre']): ?>
                            <i class="bi bi-person-fill text-primary"></i>
                            <?= $lote['cliente_nombre'] ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($lote['vendedor_nombre']): ?>
                            <i class="bi bi-briefcase-fill text-success"></i>
                            <?= $lote['vendedor_nombre'] ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="/lotes/show/<?= $lote['id'] ?>" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            <?php if (can('editar_lotes')): ?>
                            <a href="/lotes/edit/<?= $lote['id'] ?>" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($lote['tiene_amortizacion'] > 0): ?>
                            <a href="/lotes/amortizacion/<?= $lote['id'] ?>" 
                               class="btn btn-outline-info">
                                <i class="bi bi-calendar-check"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($lotes['last_page'] > 1): ?>
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-center">
                <!-- Botón Anterior -->
                <li class="page-item <?= $lotes['current_page'] == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($filtros, ['page' => $lotes['current_page'] - 1])) ?>">
                        <i class="bi bi-chevron-left"></i> Anterior
                    </a>
                </li>

                <!-- Páginas numeradas -->
                <?php 
                $startPage = max(1, $lotes['current_page'] - 2);
                $endPage = min($lotes['last_page'], $lotes['current_page'] + 2);
                
                // Primera página
                if ($startPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($filtros, ['page' => 1])) ?>">1</a>
                    </li>
                    <?php if ($startPage > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Páginas del rango -->
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?= $i == $lotes['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($filtros, ['page' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Última página -->
                <?php if ($endPage < $lotes['last_page']): ?>
                    <?php if ($endPage < $lotes['last_page'] - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($filtros, ['page' => $lotes['last_page']])) ?>">
                            <?= $lotes['last_page'] ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Botón Siguiente -->
                <li class="page-item <?= $lotes['current_page'] == $lotes['last_page'] ? 'disabled' : '' ?>">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($filtros, ['page' => $lotes['current_page'] + 1])) ?>">
                        Siguiente <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <!-- Información de paginación -->
        <div class="mt-3 d-flex justify-content-between">
            <p class="text-muted">
                Mostrando <strong><?= count($lotes['data']) ?></strong> 
                de <strong><?= number_format($lotes['total']) ?></strong> lote(s)
            </p>
            <p class="text-muted">
                Página <strong><?= $lotes['current_page'] ?></strong> 
                de <strong><?= $lotes['last_page'] ?></strong>
            </p>
        </div>
    </div>
</div>
```

**Características de Paginación:**
- ✅ Máximo 5 páginas visibles a la vez
- ✅ Botones Anterior/Siguiente
- ✅ Puntos suspensivos (...) para páginas omitidas
- ✅ Página actual resaltada con clase `active`
- ✅ Preservación de filtros en URLs de paginación mediante `http_build_query()`
- ✅ Información de totales y página actual

### 4.2 Vista amortizacion.php - Plan de Cuotas

**Ubicación:** `app/Views/lotes/amortizacion.php`

**Secciones:**

1. **Header con Información del Lote**
2. **Cards de Resumen Financiero:**
   - Valor Total
   - Total Pagado
   - Saldo Pendiente
   - Progreso (%)
3. **Métricas de Cuotas:**
   - Total Cuotas
   - Cuotas Pagadas
   - Cuotas Pendientes
   - Cuotas en Mora
4. **Tabla de Cuotas con:**
   - Número de cuota
   - Fecha vencimiento
   - Valor cuota
   - Valor pagado
   - Saldo pendiente
   - Días de mora
   - Estado (badge con color)
   - Fecha de pago
   - Botón "Registrar Pago" si pendiente

**Código Resumen Financiero:**
```html
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body">
                <p class="text-muted mb-1">Valor Total</p>
                <h4><?= formatMoney($resumen_plan['valor_total']) ?></h4>
                <i class="bi bi-currency-dollar text-primary fs-1"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <p class="text-muted mb-1">Total Pagado</p>
                <h4 class="text-success"><?= formatMoney($resumen_plan['total_pagado']) ?></h4>
                <i class="bi bi-check-circle-fill text-success fs-1"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <p class="text-muted mb-1">Saldo Pendiente</p>
                <h4 class="text-warning"><?= formatMoney($resumen_plan['saldo_pendiente']) ?></h4>
                <i class="bi bi-hourglass-split text-warning fs-1"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body">
                <p class="text-muted mb-1">Progreso</p>
                <h4 class="text-info"><?= number_format($resumen_plan['porcentaje_pagado'], 1) ?>%</h4>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-info" 
                         style="width: <?= $resumen_plan['porcentaje_pagado'] ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 4.3 Vista registrar_pago.php - Formulario de Pagos

**Ubicación:** `app/Views/lotes/registrar_pago.php`

**Características:**

1. **Selección de Cuota:**
   - Dropdown con cuotas pendientes
   - Muestra: N° cuota, fecha vencimiento, saldo, días mora
   - Al seleccionar, carga información automáticamente

2. **Información Dinámica:**
   - Valor de la cuota
   - Monto ya pagado
   - Saldo pendiente

3. **Campo Monto con Botones Rápidos:**
   - Input numérico
   - Botón "Pagar Saldo Total"
   - Botón "Pagar 50%"

4. **Campos Adicionales:**
   - Fecha del pago (default: hoy)
   - Método de pago (efectivo, transferencia, cheque, tarjeta)
   - Referencia/Comprobante
   - Observaciones

5. **JavaScript para Auto-cálculos:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const selectCuota = document.getElementById('amortizacion_id');
    const inputMonto = document.getElementById('monto');
    let saldoActual = 0;

    // Cuando se selecciona una cuota
    selectCuota.addEventListener('change', function() {
        const option = this.selectedOptions[0];
        
        if (option.value) {
            const valorCuota = parseFloat(option.dataset.valor);
            const valorPagado = parseFloat(option.dataset.pagado);
            const saldoPendiente = parseFloat(option.dataset.saldo);
            
            saldoActual = saldoPendiente;
            
            // Actualizar display de información
            document.getElementById('valorCuota').textContent = formatMoney(valorCuota);
            document.getElementById('yaPagado').textContent = formatMoney(valorPagado);
            document.getElementById('saldoPendiente').textContent = formatMoney(saldoPendiente);
            
            // Mostrar card de información
            document.getElementById('infoCuota').classList.remove('d-none');
            
            // Prellenar monto con saldo pendiente
            inputMonto.max = saldoPendiente;
            inputMonto.value = saldoPendiente;
        }
    });

    // Botón pagar saldo total
    document.getElementById('btnPagarSaldo').addEventListener('click', function() {
        if (saldoActual > 0) {
            inputMonto.value = saldoActual.toFixed(2);
        }
    });

    // Botón pagar 50%
    document.getElementById('btnPagar50').addEventListener('click', function() {
        if (saldoActual > 0) {
            inputMonto.value = (saldoActual / 2).toFixed(2);
        }
    });

    // Validación del formulario
    document.getElementById('formPago').addEventListener('submit', function(e) {
        const monto = parseFloat(inputMonto.value);
        
        if (monto <= 0) {
            e.preventDefault();
            alert('El monto debe ser mayor a cero');
            return false;
        }
        
        if (monto > saldoActual) {
            e.preventDefault();
            alert('El monto no puede ser mayor al saldo pendiente');
            return false;
        }
        
        if (!confirm('¿Confirma el registro de este pago por ' + formatMoney(monto) + '?')) {
            e.preventDefault();
            return false;
        }
    });
});

function formatMoney(amount) {
    return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
```

**Validaciones JavaScript:**
- ✅ Monto mayor a cero
- ✅ Monto no excede saldo pendiente
- ✅ Confirmación antes de enviar
- ✅ Formateo automático de moneda

---

## 🛠️ 5. FUNCIONES HELPER - helpers.php

### Ubicación: `core/helpers.php`

### Nuevas Funciones Agregadas:

#### 5.1 can($permission) - Sistema de Permisos

```php
/**
 * Verifica si el usuario tiene un permiso específico
 * (Placeholder para sistema de permisos - Módulo 6)
 */
function can($permission)
{
    // 1. Verificar autenticación
    if (!isAuthenticated()) {
        return false;
    }
    
    // 2. Administradores tienen todos los permisos
    if (hasRole('admin')) {
        return true;
    }
    
    // 3. Mapeo básico de permisos por rol
    $rolePermissions = [
        'vendedor' => [
            'ver_lotes',
            'crear_lotes',
            'editar_lotes',
            'ver_clientes',
            'crear_clientes',
            'registrar_pagos'
        ],
        'usuario' => [
            'ver_lotes',
            'ver_clientes'
        ]
    ];
    
    $userRole = $_SESSION['user']['rol'] ?? 'usuario';
    $permissions = $rolePermissions[$userRole] ?? [];
    
    return in_array($permission, $permissions);
}
```

**Uso en Vistas:**
```php
<?php if (can('editar_lotes')): ?>
    <a href="/lotes/edit/<?= $lote['id'] ?>" class="btn btn-secondary">
        <i class="bi bi-pencil"></i> Editar
    </a>
<?php endif; ?>

<?php if (can('registrar_pagos')): ?>
    <a href="/lotes/registrar-pago/<?= $lote['id'] ?>" class="btn btn-success">
        <i class="bi bi-cash-coin"></i> Registrar Pago
    </a>
<?php endif; ?>
```

#### 5.2 csrfField() - Campo CSRF para Formularios

```php
/**
 * Genera campo CSRF oculto para formularios
 */
function csrfField()
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}
```

**Uso en Formularios:**
```php
<form method="POST" action="/lotes/store">
    <?= csrfField() ?>
    <!-- Resto del formulario -->
</form>
```

#### 5.3 statusClass($estado) - Clase CSS para Estados

```php
/**
 * Obtiene la clase CSS para badges de estado de lote
 */
function statusClass($estado)
{
    $classes = [
        'disponible' => 'bg-success',
        'reservado' => 'bg-warning text-dark',
        'vendido' => 'bg-primary',
        'bloqueado' => 'bg-secondary'
    ];
    
    return $classes[$estado] ?? 'bg-secondary';
}
```

**Uso:**
```php
<span class="badge <?= statusClass($lote['estado']) ?>">
    <?= ucfirst($lote['estado']) ?>
</span>
```

#### 5.4 formatMoney($amount, $currency = '$') - Formateo de Moneda

```php
/**
 * Formatea un número como moneda
 */
function formatMoney($amount, $currency = '$')
{
    return $currency . ' ' . number_format($amount, 2, '.', ',');
}
```

**Uso:**
```php
<p>Precio: <?= formatMoney($lote['precio_lista']) ?></p>
<!-- Output: Precio: $ 50,000,000.00 -->
```

#### 5.5 formatDate($date, $format = 'd/m/Y') - Formateo de Fechas

```php
/**
 * Formatea una fecha
 */
function formatDate($date, $format = 'd/m/Y')
{
    return date($format, strtotime($date));
}
```

**Uso:**
```php
<p>Vencimiento: <?= formatDate($cuota['fecha_vencimiento']) ?></p>
<!-- Output: Vencimiento: 15/12/2024 -->
```

---

## 📝 6. RESUMEN DE VARIABLES CLAVE

### 6.1 Variables del Controlador a Vista index.php

```php
// En LoteController::index()
$this->view('lotes/index', [
    'title' => 'Gestión de Lotes',
    
    // Datos paginados
    'lotes' => [
        'data' => [...],          // Array de lotes
        'total' => 150,           // Total de registros
        'per_page' => 15,         // Lotes por página
        'current_page' => 1,      // Página actual
        'last_page' => 10         // Última página
    ],
    
    // Datos para filtros
    'proyectos' => [...],         // Array de proyectos activos
    'estados' => [
        'disponible',
        'reservado',
        'vendido',
        'bloqueado'
    ],
    
    // Filtros aplicados (para persistencia)
    'filtros' => [
        'search' => 'Villa',
        'proyecto_id' => 1,
        'estado' => 'vendido'
    ]
]);
```

### 6.2 Variables del Controlador a Vista show.php

```php
// En LoteController::show($id)
$this->view('lotes/show', [
    'title' => 'Detalle del Lote: A-001',
    
    // Datos del lote
    'lote' => [
        'id' => 1,
        'codigo_lote' => 'A-001',
        'proyecto_nombre' => 'Villa Campestre',
        'cliente_nombre' => 'Juan Pérez',
        'vendedor_nombre' => 'María García',
        'area_m2' => 250.00,
        'precio_lista' => 50000000,
        'precio_m2' => 200000,     // CALCULADO
        'estado' => 'vendido',
        // ... más campos
    ],
    
    // Resumen financiero
    'amortizacion' => [
        'total_cuotas' => 24,
        'valor_total' => 50000000,
        'total_pagado' => 25000000,
        'saldo_pendiente' => 25000000,
        'porcentaje_pagado' => 50.00,
        'cuotas_pagadas' => 12,
        'cuotas_pendientes' => 12,
        'cuotas_mora' => 2,
        'max_dias_mora' => 15
    ],
    
    // Arrays de datos financieros
    'cuotas' => [...],           // Array de cuotas
    'pagos' => [...],            // Historial de pagos
    'historial' => [...],        // Eventos de auditoría
    
    // Variables individuales para acceso directo
    'total_pagado' => 25000000,
    'saldo_pendiente' => 25000000,
    'porcentaje_pagado' => 50.00,
    'cuotas_mora' => 2,
    'cuotas_pagadas' => 12,
    'precio_m2' => 200000
]);
```

### 6.3 Variables del Controlador a Vista amortizacion.php

```php
// En LoteController::verAmortizacion($id)
$this->view('lotes/amortizacion', [
    'title' => 'Plan de Amortización - A-001',
    
    'lote' => [...],              // Datos completos del lote
    
    'cuotas' => [
        [
            'id' => 1,
            'numero_cuota' => 1,
            'valor_cuota' => 2083333.33,
            'valor_pagado' => 2083333.33,
            'saldo_pendiente' => 0,
            'fecha_vencimiento' => '2024-01-15',
            'fecha_pago' => '2024-01-10',
            'estado' => 'pagada',
            'dias_mora' => 0,
            'observaciones' => null
        ],
        // ... más cuotas
    ],
    
    'resumen_plan' => [
        'total_cuotas' => 24,
        'valor_total' => 50000000,
        'total_pagado' => 25000000,
        'saldo_pendiente' => 25000000,
        'porcentaje_pagado' => 50.00,
        'cuotas_pagadas' => 12,
        'cuotas_pendientes' => 12,
        'cuotas_mora' => 2,
        'max_dias_mora' => 15
    ]
]);
```

### 6.4 Variables del Controlador a Vista registrar_pago.php

```php
// En LoteController::registrarPago($id)
$this->view('lotes/registrar_pago', [
    'title' => 'Registrar Pago - A-001',
    
    'lote' => [...],              // Datos completos del lote
    
    'cuotas_pendientes' => [
        [
            'id' => 13,
            'numero_cuota' => 13,
            'valor_cuota' => 2083333.33,
            'valor_pagado' => 0,
            'saldo_pendiente' => 2083333.33,
            'fecha_vencimiento' => '2025-01-15',
            'dias_mora' => 5,
            'estado' => 'pendiente'
        ],
        // ... más cuotas pendientes
    ]
]);
```

---

## ⚙️ 7. CONFIGURACIÓN Y PRUEBAS

### 7.1 Checklist de Implementación

#### Base de Datos
- [ ] Ejecutar `database/update.sql` en desarrollo local
- [ ] Ejecutar `database/update.sql` en producción
- [ ] Verificar creación de columna `vendedor_id`
- [ ] Verificar foreign key `fk_lotes_vendedor`
- [ ] Verificar índice `idx_vendedor`
- [ ] Ejecutar queries de verificación

#### Archivos Modificados
- [x] `app/Controllers/LoteController.php` - Métodos actualizados
- [x] `app/Models/LoteModel.php` - Método `getAllPaginated()` agregado
- [x] `core/helpers.php` - Funciones `can()`, `csrfField()`, `statusClass()` agregadas
- [x] `app/Views/lotes/index.php` - Actualizada con paginación

#### Archivos Creados
- [x] `database/update.sql` - Script de actualización
- [x] `app/Views/lotes/amortizacion.php` - Vista plan de amortización
- [x] `app/Views/lotes/registrar_pago.php` - Vista formulario de pagos
- [x] `DOCUMENTACION_MODULO_4.md` - Este documento

### 7.2 Pruebas Funcionales

#### Test 1: Paginación
```
1. Ir a http://127.0.0.1:8004/lotes
2. Verificar que muestra máximo 15 lotes por página
3. Verificar que muestra información "Página X de Y"
4. Hacer clic en "Siguiente"
5. Verificar que la URL incluye ?page=2
6. Verificar que carga la página 2 correctamente
```

#### Test 2: Filtros
```
1. En /lotes, seleccionar un proyecto del dropdown
2. Hacer clic en "Filtrar"
3. Verificar que solo muestra lotes de ese proyecto
4. Agregar filtro de estado "vendido"
5. Verificar que muestra solo lotes vendidos del proyecto
6. Escribir texto en búsqueda
7. Verificar que filtra por código, manzana, cliente, proyecto
```

#### Test 3: Persistencia de Filtros en Paginación
```
1. Aplicar filtros: proyecto_id=1, estado=vendido, search=villa
2. Ir a página 2 usando botón "Siguiente"
3. Verificar que la URL es: /lotes?proyecto_id=1&estado=vendido&search=villa&page=2
4. Verificar que los filtros siguen aplicados
5. Verificar que los dropdowns mantienen su valor seleccionado
```

#### Test 4: Cálculo de precio_m2
```
1. Abrir /lotes
2. Para cada lote en la tabla, verificar columna "Precio/m²"
3. Calcular manualmente: precio_lista / area_m2
4. Verificar que el valor mostrado coincide
```

#### Test 5: Vista de Detalle
```
1. Hacer clic en el botón "Ver" (ojo) de un lote vendido
2. Verificar que abre /lotes/show/{id}
3. Verificar que muestra:
   - Datos del lote completos
   - Precio por m² calculado
   - Resumen financiero (si tiene amortización)
   - Cuotas pagadas, pendientes, en mora
   - Porcentaje de pago
```

#### Test 6: Vista de Amortización
```
1. En /lotes, hacer clic en icono de calendario de un lote con amortización
2. Verificar que abre /lotes/amortizacion/{id}
3. Verificar cards de resumen:
   - Valor Total
   - Total Pagado
   - Saldo Pendiente
   - Progreso (%)
4. Verificar tabla de cuotas con todos los campos
5. Verificar que cuotas en mora tienen badge rojo
6. Verificar que cuotas pagadas tienen badge verde
```

#### Test 7: Formulario de Registro de Pago
```
1. En vista de amortización, hacer clic en "Registrar Pago"
2. Verificar que abre /lotes/registrar-pago/{id}
3. Seleccionar una cuota del dropdown
4. Verificar que se muestra info de la cuota (valor, pagado, saldo)
5. Verificar que el campo monto se prellenó con el saldo
6. Hacer clic en "Pagar 50%"
7. Verificar que el monto cambia a 50% del saldo
8. Hacer clic en "Pagar Saldo Total"
9. Verificar que el monto vuelve al saldo completo
```

#### Test 8: Permisos can()
```
1. Iniciar sesión como usuario con rol 'usuario'
2. Ir a /lotes
3. Verificar que NO aparece botón "Editar"
4. Verificar que NO aparece botón "Registrar Pago"
5. Cerrar sesión e iniciar como 'admin'
6. Verificar que SÍ aparecen todos los botones
```

### 7.3 Queries de Verificación

#### Verificar estructura de tabla lotes
```sql
DESCRIBE lotes;

-- Debería mostrar:
-- vendedor_id | int unsigned | YES | MUL | NULL
-- ubicacion   | varchar(255) | YES |     | NULL
-- descripcion | text         | YES |     | NULL
```

#### Verificar foreign key
```sql
SHOW CREATE TABLE lotes;

-- Debería incluir:
-- CONSTRAINT `fk_lotes_vendedor` FOREIGN KEY (`vendedor_id`) 
-- REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
```

#### Verificar asignación de vendedores
```sql
SELECT 
    estado,
    COUNT(*) as total_lotes,
    SUM(CASE WHEN vendedor_id IS NOT NULL THEN 1 ELSE 0 END) as con_vendedor,
    SUM(CASE WHEN vendedor_id IS NULL THEN 1 ELSE 0 END) as sin_vendedor
FROM lotes
GROUP BY estado;

-- Todos los lotes vendidos deberían tener vendedor_id
```

#### Probar query de getAllPaginated
```sql
-- Simular el query del método (página 1, 15 registros)
SELECT l.*,
       p.nombre as proyecto_nombre,
       p.codigo as proyecto_codigo,
       c.nombre as cliente_nombre,
       c.documento as cliente_documento,
       u.nombre as vendedor_nombre,
       (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id) as tiene_amortizacion
FROM lotes l 
INNER JOIN proyectos p ON l.proyecto_id = p.id 
LEFT JOIN clientes c ON l.cliente_id = c.id 
LEFT JOIN users u ON l.vendedor_id = u.id
WHERE 1=1
ORDER BY l.updated_at DESC, l.created_at DESC
LIMIT 15 OFFSET 0;

-- Debería retornar máximo 15 lotes con todos los campos
```

---

## 🚀 8. PRÓXIMOS PASOS - MÓDULO 5

### Funcionalidades Preparadas (Pendientes de Implementación Completa):

#### 8.1 PagoController
**Archivo a crear:** `app/Controllers/PagoController.php`

**Método: store()**
```php
public function store()
{
    // 1. Validar CSRF
    // 2. Validar datos del formulario
    // 3. Obtener cuota
    // 4. Validar que monto no exceda saldo
    // 5. Registrar pago en tabla pagos
    // 6. Actualizar amortización (valor_pagado, saldo_pendiente)
    // 7. Si saldo = 0, cambiar estado a 'pagada'
    // 8. Registrar en historial de auditoría
    // 9. Generar recibo PDF
    // 10. Redireccionar con mensaje de éxito
}
```

#### 8.2 Tabla pagos
```sql
CREATE TABLE pagos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amortizacion_id INT UNSIGNED NOT NULL,
    lote_id INT UNSIGNED NOT NULL,
    monto DECIMAL(15,2) NOT NULL,
    fecha_pago DATE NOT NULL,
    metodo_pago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta') NOT NULL,
    referencia VARCHAR(100) NULL,
    observaciones TEXT NULL,
    usuario_registro_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (amortizacion_id) REFERENCES amortizaciones(id),
    FOREIGN KEY (lote_id) REFERENCES lotes(id),
    FOREIGN KEY (usuario_registro_id) REFERENCES users(id),
    
    INDEX idx_amortizacion (amortizacion_id),
    INDEX idx_lote (lote_id),
    INDEX idx_fecha (fecha_pago)
) ENGINE=InnoDB;
```

#### 8.3 Tabla auditoria
```sql
CREATE TABLE auditoria (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tabla VARCHAR(50) NOT NULL,
    registro_id INT UNSIGNED NOT NULL,
    accion ENUM('create', 'update', 'delete', 'payment') NOT NULL,
    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,
    usuario_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES users(id),
    
    INDEX idx_tabla_registro (tabla, registro_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB;
```

#### 8.4 Generación de Recibos PDF
**Librería sugerida:** TCPDF o Dompdf

**Método en PagoController:**
```php
public function generarReciboPDF($pagoId)
{
    // 1. Obtener datos del pago
    // 2. Obtener datos del lote y cliente
    // 3. Generar PDF con:
    //    - Logo de la empresa
    //    - Información del cliente
    //    - Detalle del lote
    //    - Información del pago
    //    - Resumen de saldo
    // 4. Descargar o enviar por email
}
```

#### 8.5 Dashboard Financiero
**Vista:** `app/Views/dashboard/financiero.php`

**Cards de Resumen:**
- Total Cartera (todos los saldos pendientes)
- Cartera al Día
- Cartera Vencida
- Pagos del Mes
- Proyección de Ingresos

**Gráficos:**
- Evolución de pagos (últimos 12 meses)
- Cartera por proyecto
- Distribución de estados de pago

---

## 📞 9. SOPORTE Y CONTACTO

### Archivos de Referencia

1. **update.sql** - Script de actualización de base de datos
2. **LoteController.php** - Controlador con toda la lógica
3. **LoteModel.php** - Modelo con consultas SQL
4. **helpers.php** - Funciones auxiliares
5. **lotes/index.php** - Vista con paginación
6. **lotes/amortizacion.php** - Vista de plan de cuotas
7. **lotes/registrar_pago.php** - Formulario de pagos

### Estado de Implementación

| Componente | Estado | Archivo |
|------------|--------|---------|
| Script SQL | ✅ Completo | `database/update.sql` |
| LoteController | ✅ Completo | `app/Controllers/LoteController.php` |
| LoteModel | ✅ Completo | `app/Models/LoteModel.php` |
| AmortizacionModel | ✅ Existía y funciona | `app/Models/AmortizacionModel.php` |
| Helpers | ✅ Completo | `core/helpers.php` |
| Vista index | ✅ Completo | `app/Views/lotes/index.php` |
| Vista amortizacion | ✅ Completo | `app/Views/lotes/amortizacion.php` |
| Vista registrar_pago | ✅ Completo | `app/Views/lotes/registrar_pago.php` |
| PagoController | ⏳ Por implementar | - |
| Tabla pagos | ⏳ Por crear | - |
| Tabla auditoria | ⏳ Por crear | - |

### Errores Conocidos Resueltos

1. ~~HTTP 500 en /lotes~~ - ✅ Resuelto: Eliminado `parent::__construct()`
2. ~~Método getAllPaginated() no existe~~ - ✅ Resuelto: Implementado en LoteModel
3. ~~Función can() no definida~~ - ✅ Resuelto: Agregada a helpers.php

---

## 🎓 CONCLUSIÓN

**Módulo 4: CRUD de Lotes** ha sido implementado completamente con las siguientes mejoras:

✅ **Base de Datos:**
- Campo `vendedor_id` para tracking de ventas
- Foreign keys y constraints adecuados
- Campos adicionales para descripción (ubicacion, descripcion)

✅ **Backend:**
- Paginación completa (15 registros por página)
- Filtros con persistencia (búsqueda, proyecto, estado)
- Cálculos financieros automáticos
- JOINs optimizados a 4 tablas (lotes, proyectos, clientes, users)

✅ **Frontend:**
- Listado con paginación visual
- Filtros con persistencia en URL
- Badges de colores por estado
- Iconos de Bootstrap
- Responsive design con Bootstrap 5

✅ **Preparación Módulo 5:**
- Vista de plan de amortización completa
- Formulario de registro de pagos con validaciones JavaScript
- Métodos del controlador listos para integración

✅ **Funciones Helper:**
- `can()` - Sistema básico de permisos
- `csrfField()` - Protección CSRF
- `statusClass()` - Clases CSS dinámicas
- `formatMoney()` - Formateo de moneda
- `formatDate()` - Formateo de fechas

**Próximos Pasos:** Ejecutar `update.sql` en ambos entornos (local y producción) y comenzar con las pruebas funcionales.

---

**Elaborado por:** Ingeniero de Software Principal y Arquitecto de Datos
**Fecha:** <?= date('d/m/Y H:i:s') ?>
**Versión:** 1.0
**Sistema:** App Inversiones - Gestión de Lotes y Proyectos
