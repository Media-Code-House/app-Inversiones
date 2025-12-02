# Guía Rápida RBAC - Control de Acceso por Roles

## 🎯 Roles del Sistema

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **administrador** | Acceso completo | CRUD completo en todos los módulos |
| **consulta** | Lectura y escritura | Solo lectura/escritura (NO eliminar) |
| **vendedor** | Solo lectura | Acceso de solo lectura con filtrado de datos |

---

## 🛠️ Uso en Controladores

### 1. Verificar Autenticación

```php
public function metodo() {
    $this->requireAuth(); // Siempre primero
}
```

### 2. Verificar Rol (Uno o Múltiples)

```php
// Un rol específico
$this->requireRole(['administrador']);

// Múltiples roles permitidos
$this->requireRole(['administrador', 'consulta']);
```

### 3. Obtener Usuario Autenticado

```php
$user = user();
$userId = $user['id'];
$userRol = $user['rol'];
$userName = $user['nombre'];
```

### 4. Verificar Rol Manualmente

```php
if ($user['rol'] === 'vendedor') {
    // Código específico para vendedor
}

if ($user['rol'] === 'consulta') {
    // Código específico para consulta
}
```

---

## 📦 Patrón de Implementación por Método

### CREATE (create, store)

```php
public function create() {
    $this->requireAuth();
    
    // RBAC: Solo administrador y consulta pueden crear
    $this->requireRole(['administrador', 'consulta']);
    
    // ... resto del código
}
```

### READ (index, show)

```php
public function index() {
    $this->requireAuth();
    
    // RBAC: Obtener usuario autenticado
    $user = user();
    
    $filters = [];
    
    // RBAC: Si es vendedor, filtrar por su ID
    if ($user['rol'] === 'vendedor') {
        $filters['vendedor_id'] = $user['id'];
    }
    
    $data = $this->model->getAll($filters);
    // ... resto del código
}

public function show($id) {
    $this->requireAuth();
    
    $record = $this->model->findById($id);
    
    // RBAC: Vendedor solo puede ver sus propios registros
    $user = user();
    if ($user['rol'] === 'vendedor' && $record['vendedor_id'] != $user['id']) {
        $this->flash('error', 'No tienes permiso para ver este registro');
        $this->redirect('/ruta');
        return;
    }
    
    // ... resto del código
}
```

### UPDATE (edit, update)

```php
public function edit($id) {
    $this->requireAuth();
    
    // RBAC: Solo administrador y consulta pueden editar
    $this->requireRole(['administrador', 'consulta']);
    
    // ... resto del código
}
```

### DELETE

```php
public function delete($id) {
    $this->requireAuth();
    
    // RBAC: Solo administrador puede eliminar
    $user = user();
    if ($user['rol'] === 'consulta') {
        setFlash('error', 'El rol consulta no tiene permisos para eliminar');
        redirect('/ruta');
        return;
    }
    
    if ($user['rol'] === 'vendedor') {
        setFlash('error', 'El rol vendedor no tiene permisos para eliminar');
        redirect('/ruta');
        return;
    }
    
    // ... resto del código de eliminación
}
```

---

## 🗃️ Patrón en Modelos

### Agregar Filtro de Vendedor

```php
public function getAll($filters = []) {
    $sql = "SELECT * FROM tabla WHERE 1=1";
    $params = [];
    
    // Otros filtros...
    
    // RBAC: Filtro por vendedor (para rol vendedor)
    if (!empty($filters['vendedor_id'])) {
        $sql .= " AND vendedor_id = ?";
        $params[] = $filters['vendedor_id'];
    }
    
    return $this->db->fetchAll($sql, $params);
}
```

---

## 📊 Reportes - Filtrado Especial

Los reportes SIEMPRE deben filtrar para vendedor:

```php
public function reporteVentas() {
    $this->requireAuth();
    
    $user = user();
    
    $sql = "SELECT ... FROM lotes l WHERE ...";
    $params = [];
    
    // RBAC: Si es vendedor, filtrar solo sus lotes
    if ($user['rol'] === 'vendedor') {
        $sql .= " AND l.vendedor_id = ?";
        $params[] = $user['id'];
    }
    
    // ... resto del código
}
```

---

## ✅ Checklist de Implementación

Al agregar un nuevo método a un controlador:

- [ ] ¿Agregué `$this->requireAuth()`?
- [ ] ¿Es un método de escritura? → Agregar `requireRole(['administrador', 'consulta'])`
- [ ] ¿Es un método DELETE? → Solo permitir administrador
- [ ] ¿Retorna datos? → Filtrar por `vendedor_id` si el rol es vendedor
- [ ] ¿Los mensajes de error son claros?
- [ ] ¿Probé con los 3 roles?

---

## 🚫 Anti-Patrones (NO HACER)

### ❌ NO confiar solo en la UI

```php
// MAL - Solo ocultar botón en la vista
<?php if ($user['rol'] === 'administrador'): ?>
    <button>Eliminar</button>
<?php endif; ?>

// El usuario aún podría acceder directamente a la URL
```

### ❌ NO omitir validación en el backend

```php
// MAL - Asumir que nadie accederá
public function delete($id) {
    // Sin validación de rol
    $this->model->delete($id);
}
```

### ❌ NO usar solo JavaScript

```javascript
// MAL - Validación solo en JS
if (userRole === 'admin') {
    deleteRecord();
}
```

---

## ✅ Mejores Prácticas

### ✅ Validar SIEMPRE en servidor

```php
public function delete($id) {
    $this->requireAuth();
    
    $user = user();
    if ($user['rol'] !== 'administrador') {
        // Bloquear acceso
    }
}
```

### ✅ Filtrar datos en SQL

```php
// Filtro directamente en la query
if ($user['rol'] === 'vendedor') {
    $sql .= " AND l.vendedor_id = ?";
    $params[] = $user['id'];
}
```

### ✅ Mensajes descriptivos

```php
setFlash('error', 'El rol consulta no tiene permisos para eliminar proyectos');
```

---

## 🔍 Testing Rápido

### Script de Prueba Manual:

1. **Login como Administrador:**
   - ✅ Navegar a /proyectos/create
   - ✅ Crear proyecto
   - ✅ Navegar a /proyectos/delete/1
   - ✅ Eliminar proyecto

2. **Login como Consulta:**
   - ✅ Navegar a /proyectos/create
   - ✅ Crear proyecto
   - ❌ Navegar a /proyectos/delete/1 → Debe bloquear

3. **Login como Vendedor:**
   - ❌ Navegar a /proyectos/create → Debe bloquear
   - ✅ Navegar a /lotes → Solo ve sus lotes
   - ✅ Navegar a /reportes/lotes-vendidos → Solo ve sus ventas

---

## 📞 Soporte

**Preguntas frecuentes:**

**P: ¿Dónde está definido el rol del usuario?**
R: En `$_SESSION['user']['rol']`

**P: ¿Cómo verifico si un usuario es administrador?**
R: `$user = user(); if ($user['rol'] === 'administrador') { ... }`

**P: ¿Cómo bloqueo un método para vendedor?**
R: `$this->requireRole(['administrador', 'consulta']);`

**P: ¿Cómo filtro datos para vendedor?**
R: Agregar condición `WHERE vendedor_id = ?` en queries SQL

---

**Última Actualización:** 2024
**Versión:** 1.0
