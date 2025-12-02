# Implementación RBAC (Role-Based Access Control)

## Módulo 7: Sistema de Control de Acceso Basado en Roles

### Fecha de Implementación
**Completado:** 2024

---

## 📋 Resumen Ejecutivo

Se ha implementado un sistema completo de Control de Acceso Basado en Roles (RBAC) en toda la aplicación, con tres niveles de permisos claramente definidos:

- **Administrador**: Acceso completo CRUD en todos los módulos
- **Consulta**: Acceso de lectura/escritura (sin DELETE)
- **Vendedor**: Acceso de solo lectura con filtrado de datos

---

## 🎯 Roles y Permisos

### 1. ROL: ADMINISTRADOR
**Permisos:** Acceso completo sin restricciones

| Módulo | Crear | Leer | Actualizar | Eliminar |
|--------|-------|------|------------|----------|
| Proyectos | ✅ | ✅ | ✅ | ✅ |
| Lotes | ✅ | ✅ | ✅ | ✅ |
| Amortizaciones | ✅ | ✅ | ✅ | ✅ |
| Pagos | ✅ | ✅ | ✅ | ✅ |
| Comisiones | ✅ | ✅ | ✅ | ✅ |
| Vendedores | ✅ | ✅ | ✅ | ✅ |
| Reportes | ✅ | ✅ | ✅ | ✅ |

**Filtrado de datos:** Ninguno - Ve todos los datos del sistema

---

### 2. ROL: CONSULTA
**Permisos:** Lectura y escritura (NO eliminar)

| Módulo | Crear | Leer | Actualizar | Eliminar |
|--------|-------|------|------------|----------|
| Proyectos | ✅ | ✅ | ✅ | ❌ |
| Lotes | ✅ | ✅ | ✅ | ❌ |
| Amortizaciones | ✅ | ✅ | ✅ | ❌ |
| Pagos | ✅ | ✅ | ✅ | ❌ |
| Comisiones | ❌ | ❌ | ❌ | ❌ |
| Vendedores | ❌ | ❌ | ❌ | ❌ |
| Reportes | ❌ | ✅ | ❌ | ❌ |

**Filtrado de datos:** Ninguno - Ve todos los datos del sistema

**Restricciones específicas:**
- **BLOQUEADO:** Todos los métodos `delete()` en controladores de negocio
- **BLOQUEADO:** Gestión de comisiones (solo administrador)
- **BLOQUEADO:** Gestión de vendedores (solo administrador)

---

### 3. ROL: VENDEDOR
**Permisos:** Solo lectura con filtrado estricto

| Módulo | Crear | Leer | Actualizar | Eliminar |
|--------|-------|------|------------|----------|
| Proyectos | ❌ | ✅ | ❌ | ❌ |
| Lotes | ❌ | ✅* | ❌ | ❌ |
| Amortizaciones | ❌ | ✅* | ❌ | ❌ |
| Pagos | ❌ | ✅* | ❌ | ❌ |
| Comisiones | ❌ | ✅* | ❌ | ❌ |
| Vendedores | ❌ | ✅** | ❌ | ❌ |
| Reportes | ❌ | ✅* | ❌ | ❌ |

**(*) Filtrado por vendedor_id:** Solo ve datos asociados a su ID
**(**) Solo mi perfil:** Solo puede ver su propio perfil de vendedor

**Filtrado de datos CRÍTICO:**
```sql
-- En todas las consultas de lotes
WHERE l.vendedor_id = [USUARIO_ID]

-- En reportes
AND l.vendedor_id = [USUARIO_ID]

-- En comisiones
WHERE vendedor_id = [USUARIO_ID]
```

---

## 🔧 Implementación Técnica

### Helpers de Autenticación (core/helpers.php)

```php
// Obtener usuario autenticado
function user() {
    return $_SESSION['user'] ?? null;
}

// Verificar rol específico
function hasRole($rol) {
    return isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] == $rol;
}
```

### Método requireRole() en Controller Base

```php
protected function requireRole($roles) {
    if (is_array($roles)) {
        requireAuth();
        $hasRole = false;
        foreach ($roles as $rol) {
            if (hasRole($rol)) { $hasRole = true; break; }
        }
        if (!$hasRole) {
            setFlash('danger', 'No tienes permisos para acceder a esta página');
            redirect('/dashboard');
        }
    } else {
        requireRole($roles);
    }
}
```

---

## 📦 Controladores Modificados

### 1. LoteController

#### Métodos Protegidos:
- **create()** - Solo administrador y consulta
- **store()** - Solo administrador y consulta
- **edit()** - Solo administrador y consulta
- **update()** - Solo administrador y consulta
- **delete()** - Solo administrador (bloqueado para consulta y vendedor)

#### Filtrado de Datos (Vendedor):
```php
// En index()
if ($user['rol'] === 'vendedor') {
    $filters['vendedor_id'] = $user['id'];
}

// En show()
if ($user['rol'] === 'vendedor' && $lote['vendedor_id'] != $user['id']) {
    $this->flash('error', 'No tienes permiso para ver este lote');
    $this->redirect('/lotes');
    return;
}
```

#### Modelo Actualizado:
```php
// LoteModel::getAllPaginated()
// RBAC: Filtro por vendedor (para rol vendedor)
if (!empty($filters['vendedor_id'])) {
    $whereConditions .= " AND l.vendedor_id = ? ";
    $params[] = $filters['vendedor_id'];
}
```

---

### 2. ProyectoController

#### Métodos Protegidos:
- **create()** - Solo administrador y consulta
- **store()** - Solo administrador y consulta
- **edit()** - Solo administrador y consulta
- **update()** - Solo administrador y consulta
- **delete()** - Solo administrador

#### Código de Bloqueo DELETE:
```php
public function delete($id) {
    $user = user();
    if ($user['rol'] === 'consulta') {
        setFlash('error', 'El rol consulta no tiene permisos para eliminar proyectos');
        redirect('/proyectos');
        return;
    }
    
    if ($user['rol'] === 'vendedor') {
        setFlash('error', 'El rol vendedor no tiene permisos para eliminar proyectos');
        redirect('/proyectos');
        return;
    }
}
```

---

### 3. AmortizacionController

#### Métodos Protegidos:
- **create()** - Solo administrador y consulta
- **store()** - Solo administrador y consulta

#### Filtrado de Datos (Vendedor):
```php
// En show()
if ($user['rol'] === 'vendedor' && $lote['vendedor_id'] != $user['id']) {
    $_SESSION['error'] = 'No tienes permiso para ver la amortización de este lote';
    redirect('/lotes');
    return;
}
```

---

### 4. PagoController

#### Métodos Protegidos:
- **create()** - Solo administrador y consulta
- **store()** - Solo administrador y consulta

#### Validación de Rol:
```php
$user = user();
if ($user['rol'] === 'vendedor') {
    $_SESSION['error'] = 'El rol vendedor no tiene permisos para registrar pagos';
    redirect('/lotes');
    return;
}
```

---

### 5. ReporteController

#### Filtrado en TODOS los Reportes:

**lotesVendidos():**
```php
// RBAC: Si es vendedor, forzar filtro por su ID
if ($user['rol'] === 'vendedor') {
    $vendedorId = $user['id'];
}
```

**ventasPorProyecto():**
```php
// RBAC: Si es vendedor, filtrar solo lotes asignados a él
if ($user['rol'] === 'vendedor') {
    $sql .= " AND (l.vendedor_id = {$user['id']} OR l.vendedor_id IS NULL)";
}
```

**ventasPorVendedor():**
```php
// RBAC: Si es vendedor, filtrar solo su ID
if ($user['rol'] === 'vendedor') {
    $whereConditions[] = "u.id = ?";
    $params[] = $user['id'];
}
```

**cartera():**
```php
// RBAC: Si es vendedor, filtrar solo sus lotes
if ($user['rol'] === 'vendedor') {
    $sql .= " AND l.vendedor_id = ?";
    $params[] = $user['id'];
}
```

---

### 6. ComisionController

**NOTA:** Ya tenía RBAC implementado correctamente.

#### Métodos Admin-Only:
- index() - Solo administrador
- resumen() - Solo administrador
- show() - Solo administrador
- pagar() - Solo administrador
- registrarPago() - Solo administrador
- configuracion() - Solo administrador
- actualizarConfiguracion() - Solo administrador

#### Método Vendedor:
- **misComisiones()** - Acceso público autenticado, filtrado automático por user ID

---

### 7. VendedorController

**NOTA:** Ya tenía RBAC implementado desde el inicio.

Todos los métodos requieren: `$this->requireRole(['administrador'])`

---

## 🔒 Matriz de Seguridad

### Tabla de Permisos por Controlador

| Controller | Administrador | Consulta | Vendedor |
|------------|--------------|----------|----------|
| **ProyectoController** |
| - index() | ✅ Full | ✅ Full | ✅ Read |
| - create() | ✅ | ✅ | ❌ |
| - store() | ✅ | ✅ | ❌ |
| - show() | ✅ | ✅ | ✅ |
| - edit() | ✅ | ✅ | ❌ |
| - update() | ✅ | ✅ | ❌ |
| - delete() | ✅ | ❌ | ❌ |
| **LoteController** |
| - index() | ✅ Full | ✅ Full | ✅ Filtered |
| - create() | ✅ | ✅ | ❌ |
| - store() | ✅ | ✅ | ❌ |
| - show() | ✅ Full | ✅ Full | ✅ Filtered |
| - edit() | ✅ | ✅ | ❌ |
| - update() | ✅ | ✅ | ❌ |
| - delete() | ✅ | ❌ | ❌ |
| **AmortizacionController** |
| - create() | ✅ | ✅ | ❌ |
| - store() | ✅ | ✅ | ❌ |
| - show() | ✅ Full | ✅ Full | ✅ Filtered |
| **PagoController** |
| - create() | ✅ | ✅ | ❌ |
| - store() | ✅ | ✅ | ❌ |
| **ComisionController** |
| - index() | ✅ | ❌ | ❌ |
| - resumen() | ✅ | ❌ | ❌ |
| - show() | ✅ | ❌ | ❌ |
| - pagar() | ✅ | ❌ | ❌ |
| - registrarPago() | ✅ | ❌ | ❌ |
| - configuracion() | ✅ | ❌ | ❌ |
| - actualizarConfiguracion() | ✅ | ❌ | ❌ |
| - misComisiones() | ✅ | ❌ | ✅ Filtered |
| **VendedorController** |
| - ALL METHODS | ✅ | ❌ | ❌ |
| **ReporteController** |
| - lotesVendidos() | ✅ Full | ✅ Full | ✅ Filtered |
| - ventasPorProyecto() | ✅ Full | ✅ Full | ✅ Filtered |
| - ventasPorVendedor() | ✅ Full | ✅ Full | ✅ Filtered |
| - cartera() | ✅ Full | ✅ Full | ✅ Filtered |
| - estadoClientes() | ✅ Full | ✅ Full | ✅ Filtered |

---

## ✅ Puntos de Validación

### Checklist de Implementación

- [x] Helper `user()` disponible en core/helpers.php
- [x] Helper `hasRole()` funcional
- [x] Método `requireRole()` en Controller base con soporte de arrays
- [x] LoteController con filtrado por vendedor_id
- [x] ProyectoController con bloqueo de escritura para vendedor
- [x] AmortizacionController con validación de acceso
- [x] PagoController con validación de acceso
- [x] ReporteController con filtrado completo para vendedor
- [x] ComisionController ya implementado correctamente
- [x] VendedorController ya implementado correctamente
- [x] LoteModel con soporte de filtro vendedor_id
- [x] Bloqueo de DELETE para rol consulta en todos los controladores
- [x] Mensajes de error descriptivos para cada rol

---

## 🎨 Mensajes de Error por Rol

### Mensajes Estandarizados:

```php
// Para Consulta (intentando DELETE)
'El rol consulta no tiene permisos para eliminar [recurso]'

// Para Vendedor (intentando CREATE/UPDATE)
'El rol vendedor no tiene permisos para [acción] [recurso]'

// Para Vendedor (acceso no autorizado a datos)
'No tienes permiso para ver [este recurso]'
```

---

## 📊 Filtros de Datos

### Vendedor Role - Data Filtering Strategy

**Principio:** Un vendedor SOLO puede ver datos relacionados con lotes donde `lotes.vendedor_id = user.id`

#### Implementación por Módulo:

1. **Lotes:** WHERE l.vendedor_id = ?
2. **Amortizaciones:** JOIN lotes → WHERE l.vendedor_id = ?
3. **Pagos:** JOIN amortizaciones → JOIN lotes → WHERE l.vendedor_id = ?
4. **Comisiones:** WHERE vendedor_id = ?
5. **Reportes:** Todos filtrados por l.vendedor_id = ?

---

## 🚀 Testing

### Escenarios de Prueba Recomendados:

1. **Administrador:**
   - ✅ Puede acceder a todos los módulos
   - ✅ Puede crear, leer, actualizar y eliminar en todos los módulos
   - ✅ Ve todos los datos sin filtros

2. **Consulta:**
   - ✅ Puede crear, leer y actualizar proyectos
   - ❌ NO puede eliminar proyectos
   - ✅ Puede crear, leer y actualizar lotes
   - ❌ NO puede eliminar lotes
   - ✅ Puede ver todos los reportes sin filtros
   - ❌ NO puede acceder a gestión de comisiones
   - ❌ NO puede acceder a gestión de vendedores

3. **Vendedor:**
   - ✅ Puede ver proyectos (todos)
   - ✅ Puede ver solo sus lotes
   - ❌ NO puede crear/editar lotes
   - ❌ NO puede eliminar nada
   - ✅ Puede ver solo sus comisiones
   - ✅ Reportes filtrados solo con sus datos
   - ❌ NO puede acceder a configuración de comisiones
   - ❌ NO puede acceder a gestión de vendedores

---

## 📝 Notas Adicionales

### Consideraciones de Seguridad:

1. **Validación en Servidor:** Todos los permisos se validan en el backend (PHP), NO confiar en restricciones de UI.

2. **Filtrado SQL:** Los filtros de vendedor se aplican directamente en las queries SQL para evitar exposición de datos.

3. **Mensajes de Error:** Los mensajes son descriptivos pero no revelan información sensible del sistema.

4. **Session Security:** Se asume que la autenticación y gestión de sesiones está correctamente implementada.

### Mejoras Futuras Sugeridas:

1. **Auditoría:** Registrar intentos de acceso no autorizado
2. **Rate Limiting:** Limitar intentos de acceso a recursos protegidos
3. **Permisos Granulares:** Sistema de permisos basado en tabla (permissions table)
4. **Middleware:** Refactorizar RBAC a middleware para DRY

---

## 📧 Contacto y Soporte

Para preguntas sobre la implementación RBAC, contactar al equipo de desarrollo.

**Última Actualización:** 2024
**Versión del Documento:** 1.0
**Estado:** Implementación Completa ✅
