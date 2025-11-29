# 🏗️ ARQUITECTURA MÓDULO 4 - CRUD de Lotes

## 📋 Resumen Ejecutivo

Implementación completa del módulo de gestión de lotes con enfoque en **integridad de datos**, **rutas dinámicas** y **lógica de negocio robusta**.

---

## 🛣️ 1. ROUTING DINÁMICO

### Rutas Implementadas

```php
// LOTES - Gestión completa
GET  /lotes                    → LoteController@index    (Listado con filtros)
GET  /lotes/create             → LoteController@create   (Formulario creación)
POST /lotes/store              → LoteController@store    (Procesar creación)
GET  /lotes/show/{id}          → LoteController@show     (Detalle completo)
GET  /lotes/edit/{id}          → LoteController@edit     (Formulario edición)
POST /lotes/update/{id}        → LoteController@update   (Procesar actualización)
```

### Características del Router

- ✅ **Parámetros dinámicos**: Soporta `{id}` en rutas
- ✅ **Métodos HTTP**: GET y POST diferenciados
- ✅ **Regex Pattern**: Conversión automática de rutas a patrones
- ✅ **Front Controller**: Punto único de entrada en `index.php`

---

## 🔗 2. INTEGRIDAD DE DATOS

### A. Relación Proyecto-Lote (1:N)

**Validaciones Implementadas:**

```php
// En LoteController@store y @update
$proyecto = $this->proyectoModel->findById($_POST['proyecto_id']);
if (!$proyecto) {
    throw new \Exception("El proyecto seleccionado no existe");
}
```

**Restricciones:**
- Todo lote **DEBE** pertenecer a un proyecto existente
- No se puede crear lote sin `proyecto_id` válido
- El formulario solo muestra proyectos activos en el selector

### B. Unicidad Compuesta (proyecto_id + codigo_lote)

**Validación en Modelo:**

```php
// LoteModel::codigoExists()
public function codigoExists($proyectoId, $codigoLote, $excludeId = null)
{
    // Verifica que codigo_lote sea único DENTRO del proyecto_id
    $sql = "SELECT COUNT(*) as count FROM lotes 
            WHERE proyecto_id = ? AND codigo_lote = ? AND id != ?";
}
```

**Resultado:**
- ✅ Códigos repetidos **permitidos** entre diferentes proyectos
- ❌ Códigos repetidos **prohibidos** dentro del mismo proyecto
- 📝 Mensaje de error contextual: incluye nombre del proyecto

### C. Relación Lote-Cliente (N:1)

**Lógica de Venta con Creación Automática:**

```php
// LoteController::handleClienteForVenta()
private function handleClienteForVenta($postData)
{
    // Opción 1: Cliente existente
    if (!empty($postData['cliente_id'])) {
        return validar_y_retornar($postData['cliente_id']);
    }
    
    // Opción 2: Crear cliente rápido automáticamente
    if (!empty($postData['nuevo_cliente'])) {
        // Verificar si existe por documento
        $existente = buscarPorDocumento();
        if ($existente) return $existente['id'];
        
        // Si no existe, crear con ClienteModel::createQuick()
        return crearClienteRapido($datosMinimos);
    }
}
```

**Características:**
1. **Verificación previa**: Busca por `tipo_documento` + `numero_documento`
2. **Evita duplicados**: Si existe, usa el ID existente
3. **Creación mínima**: Solo requiere datos esenciales
4. **Sin navegación**: Todo en un solo flujo (UX mejorada)

---

## 🎯 3. LÓGICA DE NEGOCIO

### A. Validaciones en Store/Update

| Validación | Descripción | Método Responsable |
|------------|-------------|-------------------|
| **Campos requeridos** | proyecto_id, codigo_lote, area, precio_lista | `Controller` |
| **Valores positivos** | area > 0, precio_lista > 0, precio_venta > 0 | `LoteModel::validatePositiveValues()` |
| **Proyecto existe** | Verifica FK en tabla proyectos | `ProyectoModel::findById()` |
| **Unicidad compuesta** | codigo_lote único por proyecto | `LoteModel::codigoExists()` |
| **Cliente requerido** | Si estado=vendido, cliente obligatorio | `Controller` |
| **Estado válido** | Enum: disponible, reservado, vendido, bloqueado | `Base de datos` |

### B. Reglas de Negocio (Business Rules)

**Cambio de Estado:**

```php
// LoteModel::canChangeEstado()
if ($lote['estado'] === 'vendido' && $lote['amortizacion_activa'] > 0) {
    if ($nuevoEstado !== 'vendido') {
        return ['valid' => false, 
                'message' => 'No se puede cambiar el estado de un lote vendido con amortización activa'];
    }
}
```

**Edición Restringida:**

```php
// LoteController@edit
if ($lote['estado'] === 'vendido' && $lote['amortizacion_activa'] > 0) {
    $puedeEditar = false;
    $mensajeBloqueo = 'Este lote tiene amortización activa. Solo campos descriptivos.';
}
```

---

## 📊 4. CONSULTAS CON JOINs

### Listado de Lotes (LoteModel::getAll)

```sql
SELECT 
    l.*,
    p.nombre as proyecto_nombre,
    p.codigo as proyecto_codigo,
    c.nombre as cliente_nombre,
    (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id) as tiene_amortizacion
FROM lotes l
INNER JOIN proyectos p ON l.proyecto_id = p.id
LEFT JOIN clientes c ON l.cliente_id = c.id
WHERE 1=1
    AND (l.proyecto_id = ? OR ? IS NULL)
    AND (l.estado = ? OR ? IS NULL)
    AND (l.codigo_lote LIKE ? OR l.ubicacion LIKE ? OR ? IS NULL)
ORDER BY l.created_at DESC
```

**Resultados:**
- ✅ Nombre del proyecto visible en cada fila
- ✅ Nombre del cliente (si vendido)
- ✅ Indicador de amortización activa
- ✅ Filtrado dinámico por proyecto/estado/búsqueda

### Detalle de Lote (LoteModel::findById)

```sql
SELECT 
    l.*, 
    p.nombre as proyecto_nombre, 
    p.codigo as proyecto_codigo,
    p.ubicacion as proyecto_ubicacion,
    c.nombre as cliente_nombre,
    c.documento as cliente_documento,
    c.telefono as cliente_telefono,
    c.email as cliente_email,
    (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id) as tiene_amortizacion,
    (SELECT COUNT(*) FROM amortizaciones WHERE lote_id = l.id AND estado = 'activa') as amortizacion_activa
FROM lotes l 
INNER JOIN proyectos p ON l.proyecto_id = p.id 
LEFT JOIN clientes c ON l.cliente_id = c.id 
WHERE l.id = ?
```

**Datos Integrados:**
- Información completa del proyecto
- Datos del cliente (si vendido)
- Contadores de amortización
- Datos financieros del lote

---

## 🎨 5. VISTAS (UX/UI)

### A. lotes/index.php

**Características:**
- 🔍 Filtros: Proyecto, Estado, Búsqueda de texto
- 📋 Tabla con columnas: Proyecto, Código, Área, Precio, Estado, Cliente, Amortización, Acciones
- 🎨 Badges de color por estado:
  - Verde: Disponible
  - Amarillo: Reservado
  - Azul: Vendido
  - Gris: Bloqueado
- 🔔 Tooltips en botones de acción
- 📊 Contador de resultados

### B. lotes/create.php

**Innovaciones:**

1. **Validación de Proyectos:**
```php
if (empty($proyectos)) {
    redirect_to('/proyectos/create');
    mensaje: 'Debes crear al menos un proyecto antes de poder agregar lotes';
}
```

2. **Selector Dual de Cliente:**
   - **Opción A**: Seleccionar cliente existente (dropdown)
   - **Opción B**: Crear cliente rápido (formulario inline)
     - Tipo de documento (select)
     - Número de documento (input)
     - Nombre completo (input)
     - Teléfono (opcional)

3. **Campos Condicionales:**
   - Datos de venta solo aparecen si `estado = vendido`
   - Cliente es requerido solo en modo vendido

4. **JavaScript Validation:**
   - Cambio dinámico entre cliente existente/nuevo
   - Validación antes de submit
   - Campos required condicionales

### C. lotes/edit.php

**Características de Seguridad:**

```php
<?php if (!$puedeEditar): ?>
    <div class="alert alert-warning">
        ⚠ Este lote vendido tiene una amortización activa. 
        Solo se pueden modificar campos descriptivos.
    </div>
<?php endif; ?>

<!-- Campos bloqueados -->
<input ... <?= !$puedeEditar ? 'readonly' : '' ?>>
<select ... <?= !$puedeEditar ? 'disabled' : '' ?>>
```

- Campos críticos **deshabilitados** si hay amortización activa
- Mensaje visual de advertencia
- Hidden inputs para mantener valores originales

### D. lotes/show.php

**Layout de 4 Cards:**

1. **Información del Lote** (Card azul)
   - Código, Estado, Área, Precio de lista
   - Ubicación, Descripción
   - Timestamps (creado, actualizado)

2. **Información del Proyecto** (Card cyan)
   - Nombre del proyecto
   - Código del proyecto
   - Ubicación del proyecto
   - Botón: "Ver Proyecto Completo"

3. **Información del Cliente** (Card verde - si vendido)
   - Nombre, Documento, Teléfono, Email
   - Precio de venta, Fecha de venta
   - Botón: "Ver Cliente Completo"

4. **Resumen de Amortización** (Card amarilla - si existe)
   - Total de cuotas
   - Valor total financiado
   - Cuotas pagadas/pendientes
   - Total pagado/saldo pendiente
   - Alerta de cuotas vencidas
   - Botón: "Ver Plan de Amortización"

---

## 🔐 6. AUTENTICACIÓN

Todos los métodos del LoteController incluyen:

```php
public function index()
{
    $this->requireAuth(); // Verifica sesión activa
    // ... lógica del método
}
```

- ✅ Protección en todos los endpoints
- ✅ Redirección automática a login si no autenticado
- ✅ Validación de sesión en cada request

---

## 🔄 7. FLUJO DE TRABAJO COMPLETO

### Caso de Uso: Vender un Lote

```
1. Usuario accede a /lotes
   ↓
2. Filtra por proyecto específico
   ↓
3. Click en "Editar" de un lote disponible
   ↓
4. Cambia estado a "vendido"
   ↓
5. Sistema muestra opciones de cliente:
   • Opción A: Selecciona cliente existente
   • Opción B: Crea cliente nuevo
   ↓
6. Si Opción B:
   - Ingresa: CC, 1234567890, Juan Pérez, 3001234567
   - Sistema verifica si documento existe
   - Si no existe, crea automáticamente
   - Retorna cliente_id
   ↓
7. Completa precio_venta y fecha_venta
   ↓
8. Submit → LoteController@update
   ↓
9. Validaciones:
   ✓ Proyecto existe
   ✓ Código único en proyecto
   ✓ Valores positivos
   ✓ Cliente procesado correctamente
   ↓
10. UPDATE lotes SET estado='vendido', cliente_id=X, ...
    ↓
11. Flash message: "Lote actualizado exitosamente en proyecto X"
    ↓
12. Redirect a /lotes/show/{id}
    ↓
13. Vista muestra:
    - Lote con badge azul "Vendido"
    - Card de cliente con datos completos
    - Opción de crear plan de amortización
```

---

## 📈 8. MÉTRICAS Y PERFORMANCE

### Consultas Optimizadas

- **JOINs eficientes**: INNER JOIN para proyectos, LEFT JOIN para clientes
- **Subqueries limitadas**: Solo para contadores de amortización
- **Índices utilizados**: 
  - PRIMARY KEY (id)
  - UNIQUE INDEX (proyecto_id, codigo_lote)
  - FOREIGN KEY INDEX (proyecto_id)
  - FOREIGN KEY INDEX (cliente_id)

### Validaciones por Capa

| Capa | Validaciones | Ejemplo |
|------|-------------|---------|
| **Cliente (JavaScript)** | Campos requeridos, formatos | `required`, `pattern` |
| **Controlador (PHP)** | Lógica de negocio | `codigoExists()`, `validatePositiveValues()` |
| **Modelo (SQL)** | Integridad referencial | `FOREIGN KEY`, `UNIQUE` |
| **Base de Datos** | Constraints | `NOT NULL`, `CHECK` |

---

## 🧪 9. CASOS DE PRUEBA

### CP-01: Crear Lote con Cliente Nuevo

```
GIVEN: Usuario autenticado en /lotes/create
WHEN: Selecciona proyecto, ingresa código "L-001", estado "vendido"
  AND: Elige "Crear Cliente Rápido"
  AND: Ingresa CC, 123456, "Juan Pérez", 3001234
THEN: 
  - Sistema verifica que código no exista en proyecto
  - Sistema busca cliente por documento
  - Si no existe, crea cliente automáticamente
  - Crea lote asociado a proyecto y cliente
  - Redirige a vista de detalle
```

### CP-02: Validar Unicidad Compuesta

```
GIVEN: Existe lote "L-001" en Proyecto A
WHEN: Usuario intenta crear otro lote "L-001" en Proyecto A
THEN: 
  - Sistema rechaza con error: "Ya existe un lote con el código 'L-001' en el proyecto 'Proyecto A'"

WHEN: Usuario crea lote "L-001" en Proyecto B (diferente)
THEN:
  - Sistema permite la creación (unicidad por proyecto)
```

### CP-03: Bloquear Edición con Amortización

```
GIVEN: Lote vendido con amortización activa
WHEN: Usuario accede a /lotes/edit/{id}
THEN:
  - Vista muestra warning
  - Campos críticos deshabilitados (proyecto, código, área, precio, estado, cliente)
  - Solo permite editar: ubicación, descripción
```

---

## 🚀 10. PRÓXIMOS PASOS

### Mejoras Sugeridas

1. **Paginación**: Implementar en `LoteController@index` cuando hay >50 lotes
2. **Exportación**: Añadir botón "Exportar a Excel" en listado
3. **Búsqueda Avanzada**: Filtro por rango de precios y áreas
4. **Historial**: Registro de cambios de estado del lote
5. **Dashboard de Lotes**: Gráficos por proyecto y estado
6. **API REST**: Endpoints JSON para integración móvil

### Módulos Dependientes

- ✅ **Módulo 1**: Autenticación (usuarios)
- ✅ **Módulo 2**: Diseño (Bootstrap + theme.css)
- ✅ **Módulo 3**: Base de Datos (proyectos, clientes, lotes)
- ✅ **Módulo 4**: CRUD Lotes (este documento)
- 🔜 **Módulo 5**: Amortizaciones (planes de pago)
- 🔜 **Módulo 6**: Pagos (registro de transacciones)
- 🔜 **Módulo 7**: Reportes (análisis y estadísticas)

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### Rutas Dinámicas
- [x] Configuradas en `index.php`
- [x] Parámetros dinámicos `{id}` funcionando
- [x] Métodos GET/POST diferenciados

### Integridad de Datos
- [x] Validación de existencia de proyecto
- [x] Unicidad compuesta (proyecto_id + codigo_lote)
- [x] Creación automática de cliente
- [x] Verificación previa por documento

### Controlador
- [x] `index()` con filtros
- [x] `create()` con validación de proyectos
- [x] `store()` con lógica completa
- [x] `edit()` con restricciones
- [x] `update()` con validaciones
- [x] `show()` con JOINs
- [x] `handleClienteForVenta()` helper privado

### Modelo
- [x] `getAll($filters)` con JOINs
- [x] `findById()` con datos completos
- [x] `codigoExists()` con unicidad compuesta
- [x] `validatePositiveValues()`
- [x] `canChangeEstado()`

### Vistas
- [x] `index.php` con filtros y tabla
- [x] `create.php` con selector dual de cliente
- [x] `edit.php` con campos condicionales
- [x] `show.php` con 4 cards informativas

### Seguridad
- [x] `requireAuth()` en todos los métodos
- [x] Validación de sesión
- [x] Protección XSS con `htmlspecialchars()`
- [x] Prepared statements en SQL

---

## 📝 CONCLUSIÓN

El módulo de Lotes implementa una **arquitectura sólida** que garantiza:

1. ✅ **Integridad Referencial**: Todas las relaciones FK validadas
2. ✅ **Lógica de Negocio**: Reglas claras y aplicadas consistentemente
3. ✅ **Experiencia de Usuario**: Flujos simplificados (cliente rápido)
4. ✅ **Seguridad**: Autenticación + validaciones multi-capa
5. ✅ **Mantenibilidad**: Código limpio y documentado

**Estado**: ✅ **COMPLETADO Y OPERACIONAL**

---

*Documento generado: 2025-11-29*  
*Arquitecto: Sistema de Gestión de Lotes e Inversiones*
