# Documentación: Vista de Perfil Robustecida con Integridad de Datos

## 📋 Resumen de la Implementación

Se ha robustecido exitosamente la vista de perfil (`/perfil`) para usuarios con rol 'vendedor', vinculando la información de la tabla `users` con los datos de la tabla `vendedores` mediante consultas JOIN condicionales, garantizando la **integridad de datos** y **seguridad**.

---

## 🎯 Objetivo Cumplido

Implementar la vista `/perfil` con **tres secciones claramente definidas**, asegurando la consistencia de los datos y la seguridad para usuarios con rol 'vendedor' o 'administrador'.

---

## 🏗️ Arquitectura Implementada

### 1. **Rutas y Lógica del Controlador** ✅

#### Archivo: `app/Controllers/PerfilController.php`

**Método `index()` - Consulta Condicional:**

```php
public function index()
{
    // Obtener usuario autenticado
    $userData = $this->userModel->findById($user['id']);
    
    // Consulta condicional: JOIN a vendedores si el rol es 'vendedor' o 'administrador'
    $perfil_vendedor = null;
    
    if ($userData['rol'] === 'vendedor' || $userData['rol'] === 'administrador') {
        $db = \Database::getInstance();
        
        // Consulta con JOIN para obtener datos de vendedor asociado al user_id
        $sql = "SELECT 
                    v.*,
                    u.email as user_email,
                    u.nombre as user_nombre
                FROM vendedores v
                INNER JOIN users u ON v.user_id = u.id
                WHERE v.user_id = ?
                LIMIT 1";
                
        $perfil_vendedor = $db->fetch($sql, [$userData['id']]);
    }
    
    // Variables para la vista
    $this->view('perfil/index', [
        'title' => 'Mi Perfil de Usuario',
        'user' => $userData,                    // Datos de users
        'perfil_vendedor' => $perfil_vendedor   // Datos de vendedores (null si no es vendedor)
    ]);
}
```

**Variables de la Vista:**
- ✅ `user`: Datos de la tabla `users`
- ✅ `perfil_vendedor`: Datos de la tabla `vendedores` (o `null` si el usuario no es vendedor)
- ✅ `title`: "Mi Perfil de Usuario"

---

### 2. **VISTA: `app/Views/perfil/index.php`** ✅

#### Estructura de Tres Secciones (Tarjetas/Pestañas)

---

#### **TARJETA 1: Datos Personales** 🟦 (Siempre Visible)

**Contenido:**
- ✅ Nombre de Usuario (`nombre_usuario` de la tabla `users`)
- ✅ Correo Electrónico (`email` de la tabla `users`)

**Funcionalidad:**
- ✅ Formulario para **Actualizar Datos Personales**
- ✅ Validación con `post()` y `old()` para re-población de campos
- ✅ Utiliza `csrfField()` para protección CSRF

**Ruta de Actualización:**
```
POST /perfil/update → PerfilController@updateData
```

**Características:**
- Validación estricta de email (formato válido)
- Verificación de email único (no duplicado por otro usuario)
- Actualización de la sesión con los nuevos datos

---

#### **TARJETA 2: Roles y Seguridad** 🟨 (Siempre Visible)

**Contenido:**

**a) Rol Asignado:**
- ✅ Muestra el texto del rol: `administrador`, `consulta`, `vendedor`
- ✅ Badge que utiliza la función `statusClass()` para determinar el color
- ✅ Rol mostrado en modo solo lectura con mensaje informativo

**b) Cambio de Contraseña:**
- ✅ Formulario de seguridad separado con tres campos:
  - `contrasena_actual`
  - `nueva_contrasena`
  - `confirmar_contrasena`

**Lógica de Seguridad (Validación Estricta):**

```php
public function updatePassword()
{
    // Obtener usuario completo con contraseña de la BD
    $userData = $db->fetch(
        "SELECT id, email, nombre, password FROM users WHERE id = ?",
        [$userId]
    );
    
    // VALIDACIÓN ESTRICTA: Verificar contraseña actual ANTES de aplicar hash
    if (!password_verify($_POST['contrasena_actual'], $userData['password'])) {
        \Logger::warning("Intento fallido de cambio de contraseña");
        throw new \Exception('La contraseña actual es incorrecta');
    }
    
    // Generar hash seguro de la nueva contraseña
    $newPasswordHash = password_hash($_POST['nueva_contrasena'], PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Actualizar contraseña
    $this->userModel->updatePassword($userId, $newPasswordHash);
}
```

**Ruta de Actualización:**
```
POST /perfil/update-password → PerfilController@updatePassword
```

**Validaciones Implementadas:**
- ✅ Contraseña actual obligatoria
- ✅ Verificación estricta de la contraseña actual con `password_verify()`
- ✅ Nueva contraseña mínimo 6 caracteres
- ✅ Confirmación de nueva contraseña debe coincidir
- ✅ Nueva contraseña debe ser diferente a la actual
- ✅ Hash seguro con `PASSWORD_BCRYPT` (cost: 12)
- ✅ Logging de intentos fallidos y exitosos

---

#### **TARJETA 3: Datos de Vendedor** 🟩 (Condicional)

**Visibilidad:**
```php
<?php if (($user['rol'] === 'vendedor' || $user['rol'] === 'administrador') && $perfil_vendedor): ?>
    <!-- Contenido de la tarjeta -->
<?php endif; ?>
```

✅ Solo visible si:
- `user->rol` es `'vendedor'` O `'administrador'`
- **Y** existe un registro en la tabla `vendedores` asociado al `user_id`

**Contenido Mostrado:**

**a) Información Principal del Vendedor:**
- ✅ **ID del Vendedor** (de la tabla `vendedores`)
- ✅ **Código de Vendedor** (`codigo_vendedor`)
- ✅ **Estado** con badge usando `statusClass()` (activo, inactivo, suspendido)
- ✅ **Porcentaje de Comisión Default**

**b) Información Personal:**
- ✅ Nombres y Apellidos
- ✅ Tipo de Documento y Número de Documento

**c) Fechas y Contrato:**
- ✅ **Fecha de Ingreso** (usando helper `formatDateTime()`)
- ✅ Tipo de Contrato (indefinido, fijo, prestación de servicios, freelance)
- ✅ Información Bancaria (si está registrada)

**d) Formulario de Actualización de Datos de Contacto:**

Campos editables:
- ✅ Teléfono
- ✅ Celular Corporativo (obligatorio)
- ✅ Ciudad
- ✅ Dirección Completa

**Ruta de Actualización:**
```
POST /perfil/update-vendedor → PerfilController@updateVendedor
```

**Funcionalidad del Método `updateVendedor()`:**

```php
public function updateVendedor()
{
    // Verificar que el usuario sea vendedor o administrador
    if ($user['rol'] !== 'vendedor' && $user['rol'] !== 'administrador') {
        throw new \Exception('No tienes permisos para actualizar datos de vendedor');
    }
    
    // Verificar que exista un registro de vendedor asociado
    $vendedor = $db->fetch(
        "SELECT id, user_id FROM vendedores WHERE user_id = ?",
        [$userId]
    );
    
    if (!$vendedor) {
        throw new \Exception('No se encontró un perfil de vendedor asociado');
    }
    
    // Actualizar datos de contacto
    $sql = "UPDATE vendedores 
            SET telefono = ?, celular = ?, direccion = ?, ciudad = ?, updated_at = NOW()
            WHERE id = ?";
}
```

**Enlace Adicional:**
- ✅ Botón para acceder al **Perfil Completo de Vendedor** con comisiones y estadísticas
  ```
  /vendedores/mi-perfil
  ```

---

## 🔒 3. Lógica de Feedback y Helper Functions

### Mensajes Flash con `Session::getFlash()` ✅

Implementado en la vista:
```php
<?php if ($flash = getFlash()): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>-fill me-2"></i>
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
```

### Helper Functions Utilizadas ✅

1. **`csrfField()`** - Genera campo CSRF oculto para formularios
   ```php
   <?= csrfField() ?>
   ```

2. **`formatDateTime($date, $format)`** - Formatea fechas
   ```php
   <?= formatDateTime($user['created_at'], 'd/m/Y') ?>
   <?= formatDateTime($user['updated_at']) ?> // Formato: d/m/Y H:i
   ```

3. **`statusClass($estado)`** - Clase CSS para badges de estado
   ```php
   // Soporta estados de lotes Y vendedores
   <span class="badge <?= statusClass($perfil_vendedor['estado']) ?>">
       <?= ucfirst($perfil_vendedor['estado']) ?>
   </span>
   ```

4. **`old($key, $default)`** - Re-población de formularios
   ```php
   value="<?= e(old('nombre', $user['nombre'])) ?>"
   ```

5. **`getFlash()`** - Obtiene y limpia mensajes flash
   ```php
   if ($flash = getFlash()) { /* ... */ }
   ```

6. **`e($string)`** - Escapado HTML para prevenir XSS
   ```php
   <?= e($user['nombre']) ?>
   ```

---

## 📁 Rutas Configuradas

**Archivo:** `index.php`

```php
// ==========================================
// PERFIL DE USUARIO - MÓDULO 8 (ROBUSTECIDO)
// ==========================================

// Ver y actualizar perfil personal
$router->get('/perfil', 'PerfilController@index');
$router->post('/perfil/update', 'PerfilController@updateData');
$router->post('/perfil/update-password', 'PerfilController@updatePassword');
$router->post('/perfil/update-vendedor', 'PerfilController@updateVendedor');
```

---

## 🔐 Medidas de Seguridad Implementadas

### 1. Protección CSRF ✅
- Todos los formularios incluyen token CSRF mediante `csrfField()`
- Validación estricta en todos los métodos POST

### 2. Validación de Contraseña Estricta ✅
- Verificación de contraseña actual **ANTES** de generar el nuevo hash
- Logging de intentos fallidos para auditoría
- Contraseña hasheada con `PASSWORD_BCRYPT` (cost: 12)

### 3. Autorización Granular ✅
- Verificación de rol antes de mostrar sección de vendedor
- Validación de permisos en el backend (método `updateVendedor()`)
- Verificación de existencia de registro en tabla vendedores

### 4. Prevención de XSS ✅
- Uso de helper `e()` para escapar todo el output HTML
- Sanitización de inputs antes de almacenar

### 5. Auditoría y Logging ✅
- Registro de actualizaciones de perfil
- Registro de cambios de contraseña (exitosos y fallidos)
- Registro de actualizaciones de datos de vendedor

---

## 📊 Consistencia de Datos

### Integridad Referencial ✅

**Relación entre tablas:**
```sql
-- En tabla vendedores
CONSTRAINT `vendedores_ibfk_1` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE
```

### Consulta con JOIN ✅

```sql
SELECT 
    v.*,
    u.email as user_email,
    u.nombre as user_nombre
FROM vendedores v
INNER JOIN users u ON v.user_id = u.id
WHERE v.user_id = ?
LIMIT 1
```

Esta consulta garantiza que:
- ✅ Solo se obtengan datos de vendedores asociados a usuarios existentes
- ✅ Los datos estén sincronizados entre ambas tablas
- ✅ Se respete la integridad referencial

---

## 🎨 Interfaz de Usuario

### Diseño de Tres Tarjetas:

1. **Tarjeta Azul (Primary)** - Datos Personales
2. **Tarjeta Amarilla (Warning)** - Roles y Seguridad
3. **Tarjeta Celeste (Info)** - Datos de Vendedor (condicional)

### Características Visuales:
- ✅ Headers con colores distintivos
- ✅ Iconos Bootstrap Icons
- ✅ Badges con colores semánticos
- ✅ Formularios con validación HTML5
- ✅ Tooltips informativos
- ✅ Botones de acción claramente identificados
- ✅ Avatar circular con inicial del usuario
- ✅ Alertas flash con estilos Bootstrap

---

## 🧪 Casos de Uso Cubiertos

### Usuario con rol "consulta" ✅
- ✅ Ve Tarjeta 1: Datos Personales
- ✅ Ve Tarjeta 2: Roles y Seguridad
- ❌ NO ve Tarjeta 3: Datos de Vendedor

### Usuario con rol "vendedor" ✅
- ✅ Ve Tarjeta 1: Datos Personales
- ✅ Ve Tarjeta 2: Roles y Seguridad
- ✅ Ve Tarjeta 3: Datos de Vendedor (si existe registro en tabla vendedores)

### Usuario con rol "administrador" ✅
- ✅ Ve Tarjeta 1: Datos Personales
- ✅ Ve Tarjeta 2: Roles y Seguridad
- ✅ Ve Tarjeta 3: Datos de Vendedor (si existe registro en tabla vendedores)

### Usuario vendedor sin registro en tabla vendedores ✅
- ✅ Ve Tarjeta 1: Datos Personales
- ✅ Ve Tarjeta 2: Roles y Seguridad
- ❌ NO ve Tarjeta 3 (requiere registro en vendedores)

---

## 📝 Validaciones Implementadas

### Formulario de Datos Personales
- ✅ Nombre: obligatorio, máximo 100 caracteres
- ✅ Email: obligatorio, formato válido, único (excepto usuario actual)

### Formulario de Cambio de Contraseña
- ✅ Contraseña actual: obligatoria, verificación con hash de BD
- ✅ Nueva contraseña: obligatoria, mínimo 6 caracteres
- ✅ Confirmar contraseña: debe coincidir con nueva contraseña
- ✅ Nueva contraseña debe ser diferente a la actual

### Formulario de Datos de Vendedor
- ✅ Celular: obligatorio, máximo 20 caracteres
- ✅ Teléfono: opcional, máximo 20 caracteres
- ✅ Dirección: opcional, máximo 255 caracteres
- ✅ Ciudad: opcional, máximo 100 caracteres
- ✅ Solo accesible por usuarios vendedor/administrador
- ✅ Requiere existencia de registro en tabla vendedores

---

## ✅ Checklist de Cumplimiento

### Requerimientos del Prompt Original:

- [x] **1.1** Consulta condicional en `showProfile()` basada en rol
- [x] **1.2** JOIN a tabla vendedores mediante `user_id`
- [x] **1.3** Variable `perfil_vendedor` con datos de vendedores (o null)
- [x] **1.4** Variables de vista: `user`, `perfil_vendedor`, `title`

- [x] **2.1** Tarjeta 1: Datos Personales (siempre visible)
- [x] **2.1.1** Muestra Nombre de Usuario y Email
- [x] **2.1.2** Formulario de actualización con validación y csrfField()

- [x] **2.2** Tarjeta 2: Roles y Seguridad (siempre visible)
- [x] **2.2.1** Rol asignado con badge usando statusClass()
- [x] **2.2.2** Formulario de cambio de contraseña separado
- [x] **2.2.3** Validación estricta de contraseña actual ANTES de hash

- [x] **2.3** Tarjeta 3: Datos de Vendedor (condicional)
- [x] **2.3.1** Visible solo si rol es vendedor/administrador Y existe registro
- [x] **2.3.2** Muestra ID del vendedor de tabla vendedores
- [x] **2.3.3** Muestra fecha de ingreso con formatDateTime()
- [x] **2.3.4** Muestra métricas y campos relevantes
- [x] **2.3.5** Formulario para editar datos de contacto corporativo

- [x] **3.1** Uso de `getFlash()` para mensajes de éxito/error
- [x] **3.2** Uso de `formatDateTime()` para formatear fechas
- [x] **3.3** Uso de `statusClass()` para badges de estado
- [x] **3.4** Uso de `csrfField()` para protección CSRF
- [x] **3.5** Uso de `old()` para re-población de formularios

---

## 🚀 Mejoras Implementadas (Adicionales)

### Más allá de los requerimientos:

1. ✅ **Avatar circular** con inicial del usuario
2. ✅ **Información de fechas** formateadas en el header
3. ✅ **Badge de estado activo/inactivo** del usuario
4. ✅ **Mensajes flash** con estilos Bootstrap y auto-cierre
5. ✅ **Validación JavaScript** en frontend para contraseña
6. ✅ **Botones de mostrar/ocultar** contraseña
7. ✅ **Logging completo** de todas las operaciones
8. ✅ **Información bancaria** del vendedor (si existe)
9. ✅ **Enlace directo** al perfil completo de vendedor
10. ✅ **Diseño responsive** con Bootstrap 5
11. ✅ **Iconos semánticos** con Bootstrap Icons
12. ✅ **Tooltips informativos** en campos de formulario
13. ✅ **Actualización automática** de sesión tras cambios

---

## 📋 Resumen Final

### ✅ **ENTREGADO:**

1. **Controlador Robusto** (`PerfilController.php`):
   - Método `index()` con consulta JOIN condicional
   - Método `updateData()` para actualizar datos personales
   - Método `updatePassword()` con validación estricta
   - Método `updateVendedor()` para datos de contacto

2. **Vista Estructurada** (`perfil/index.php`):
   - **Tarjeta 1**: Datos Personales (siempre visible)
   - **Tarjeta 2**: Roles y Seguridad (siempre visible)
   - **Tarjeta 3**: Datos de Vendedor (condicional)

3. **Rutas Configuradas** (`index.php`):
   - `GET /perfil` → Ver perfil
   - `POST /perfil/update` → Actualizar datos personales
   - `POST /perfil/update-password` → Cambiar contraseña
   - `POST /perfil/update-vendedor` → Actualizar datos vendedor

4. **Helpers Actualizados** (`core/helpers.php`):
   - `statusClass()` extendido para soportar estados de vendedores

5. **Seguridad y Consistencia**:
   - Protección CSRF en todos los formularios
   - Validación estricta de contraseña actual
   - Autorización granular por rol
   - Integridad referencial con JOIN
   - Logging de auditoría

---

## 🎯 Cumplimiento Total

**Estado:** ✅ **COMPLETADO AL 100%**

Todos los requerimientos del prompt han sido implementados exitosamente, garantizando:
- ✅ Integridad de datos entre `users` y `vendedores`
- ✅ Seguridad robusta con validación estricta
- ✅ Tres secciones claramente definidas
- ✅ Funcionalidad condicional basada en roles
- ✅ Uso completo de helpers del sistema
- ✅ Consistencia en la arquitectura del proyecto

---

**Fecha de implementación:** 2 de diciembre de 2025  
**Desarrollado por:** GitHub Copilot (Claude Sonnet 4.5)  
**Proyecto:** APP INVERSIONES - Sistema de Gestión de Lotes
