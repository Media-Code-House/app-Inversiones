# 📝 Guía para Crear Usuarios de Prueba RBAC

## ✅ Respuesta a tu Pregunta

**¿Hiciste algún cambio en la base de datos?**

**NO.** La implementación RBAC se hizo **completamente en código** (PHP). La tabla `users` ya tenía el campo `rol` con los valores:
- `administrador`
- `consulta` 
- `vendedor`

No se modificó ninguna estructura de base de datos.

---

## 👥 Cómo Crear los Usuarios de Prueba

### Opción 1: Ejecutar Script SQL (RECOMENDADO)

#### Paso 1: Abrir phpMyAdmin
1. Ve a: http://localhost/phpmyadmin/
2. Selecciona la base de datos `inversiones_db`

#### Paso 2: Ejecutar Script
1. Clic en la pestaña **SQL**
2. Abre el archivo: `database/crear_usuarios_prueba_rbac.sql`
3. Copia todo el contenido
4. Pégalo en el área de texto de phpMyAdmin
5. Clic en **Continuar** o **Go**

#### Paso 3: Verificar
Al final del script SQL se ejecuta automáticamente una consulta de verificación. Deberías ver:

```
ID | Email                    | Nombre           | Rol           | Activo | Codigo | Nombre Vendedor
1  | admin@sistema.com        | Administrador    | administrador | 1      | NULL   | NULL
2  | consulta@sistema.com     | Usuario Consulta | consulta      | 1      | NULL   | NULL
3  | vendedor@sistema.com     | María Vendedor   | vendedor      | 1      | V001   | María Vendedor
```

---

### Opción 2: Crear Manualmente (Paso a Paso)

#### 1. Crear Usuario CONSULTA

**SQL:**
```sql
INSERT INTO `users` (`email`, `password`, `nombre`, `rol`, `activo`) VALUES
('consulta@sistema.com', '$2y$12$37tBHKBBuHq5jxfwbuxiie5eYHlfvUgnI.XeWzyYiRzEQTSKyAxzi', 'Usuario Consulta', 'consulta', 1);
```

**Credenciales:**
- Email: `consulta@sistema.com`
- Password: `Consulta123`
- Rol: `consulta`

#### 2. Crear Usuario VENDEDOR (2 Pasos)

**Paso A: Crear usuario en tabla `users`:**
```sql
INSERT INTO `users` (`email`, `password`, `nombre`, `rol`, `activo`) VALUES
('vendedor@sistema.com', '$2y$12$qvoj5UqVT8t9Ux6/JCFgjuNd4FncLCEqmvMd44jsWrsAchteXN3zK', 'María Vendedor', 'vendedor', 1);
```

**Paso B: Crear registro en tabla `vendedores`:**
```sql
-- Obtener el ID del usuario vendedor recién creado
SET @vendedor_user_id = (SELECT id FROM users WHERE email = 'vendedor@sistema.com');

-- Crear registro en vendedores
INSERT INTO `vendedores` (
    `user_id`,
    `codigo_vendedor`,
    `tipo_documento`,
    `numero_documento`,
    `nombres`,
    `apellidos`,
    `email`,
    `celular`,
    `fecha_nacimiento`,
    `direccion`,
    `ciudad`,
    `fecha_ingreso`,
    `tipo_contrato`,
    `porcentaje_comision_default`,
    `estado`
) VALUES (
    @vendedor_user_id,
    'V001',
    'CC',
    '1234567890',
    'María',
    'Vendedor',
    'vendedor@sistema.com',
    '3001234567',
    '1990-01-15',
    'Calle 123 #45-67',
    'Bogotá',
    CURDATE(),
    'indefinido',
    3.00,
    'activo'
);
```

**Credenciales:**
- Email: `vendedor@sistema.com`
- Password: `Vendedor123`
- Rol: `vendedor`
- Código: `V001`

---

## 🔑 Credenciales de Todos los Usuarios

| Email | Password | Rol | Descripción |
|-------|----------|-----|-------------|
| `admin@sistema.com` | `Admin123` | administrador | Ya existe - Acceso completo |
| `consulta@sistema.com` | `Consulta123` | consulta | Crear/Editar (NO eliminar) |
| `vendedor@sistema.com` | `Vendedor123` | vendedor | Solo lectura + datos filtrados |

---

## 🧪 Pruebas Recomendadas

### Prueba 1: Login como ADMINISTRADOR
```
Email: admin@sistema.com
Password: Admin123
```

**Verificar:**
- ✅ Puedes acceder a `/proyectos/create`
- ✅ Puedes crear un proyecto
- ✅ Puedes eliminar un proyecto
- ✅ Puedes acceder a `/vendedores`
- ✅ Puedes acceder a `/comisiones`
- ✅ Ves TODOS los lotes sin filtros

### Prueba 2: Login como CONSULTA
```
Email: consulta@sistema.com
Password: Consulta123
```

**Verificar:**
- ✅ Puedes acceder a `/proyectos/create`
- ✅ Puedes crear un proyecto
- ✅ Puedes editar un proyecto
- ❌ Al intentar eliminar proyecto → Error: "El rol consulta no tiene permisos para eliminar proyectos"
- ✅ Puedes acceder a `/lotes/create`
- ❌ Al intentar eliminar lote → Error: "El rol consulta no tiene permisos para eliminar lotes"
- ❌ Al intentar acceder a `/vendedores` → Redirige al dashboard
- ❌ Al intentar acceder a `/comisiones` → Redirige al dashboard
- ✅ Ves TODOS los lotes sin filtros

### Prueba 3: Login como VENDEDOR
```
Email: vendedor@sistema.com
Password: Vendedor123
```

**Verificar:**
- ✅ Puedes ver proyectos en `/proyectos`
- ❌ Al intentar acceder a `/proyectos/create` → Error: "No tienes permisos para acceder a esta página"
- ✅ Puedes ver lotes en `/lotes`
- ❌ Solo ves lotes donde `vendedor_id = tu_id` (al inicio no verás ninguno porque no tienes lotes asignados)
- ❌ Al intentar acceder a `/lotes/create` → Error: "No tienes permisos para acceder a esta página"
- ✅ Puedes acceder a `/comisiones/mis-comisiones`
- ❌ Al intentar acceder a `/comisiones` → Error: "No tienes permisos"
- ❌ Al intentar acceder a `/vendedores` → Error: "No tienes permisos"
- ✅ En `/reportes/lotes-vendidos` solo ves TUS ventas
- ✅ En `/reportes/ventas-vendedor` solo ves TUS estadísticas

---

## 🎯 Para Probar Filtrado de Vendedor

Para que el vendedor vea datos, necesitas asignarle lotes:

### Paso 1: Login como Administrador

### Paso 2: Ir a un Lote Vendido
```
/lotes/edit/[ID]
```

### Paso 3: Asignar Vendedor
En el formulario de edición:
- Campo **"Vendedor"**: Seleccionar "María Vendedor (V001)"
- Campo **"Estado"**: Debe ser "vendido"
- Guardar

### Paso 4: Logout y Login como Vendedor
Ahora el vendedor verá ese lote en:
- `/lotes` (lista)
- `/lotes/show/[ID]` (detalle)
- `/reportes/lotes-vendidos`
- `/reportes/ventas-vendedor`
- `/comisiones/mis-comisiones` (si se generó comisión)

---

## 🚨 Solución de Problemas

### Error: "Email already exists"
- El usuario ya existe en la base de datos
- Solución: Cambiar el email o eliminar el usuario existente

### Error: "Cannot login"
- Verificar que la contraseña sea correcta
- Verificar que el campo `activo` = 1
- Verificar el hash de password

### Vendedor no ve ningún lote
- Normal si es usuario nuevo
- Solución: Asignarle lotes desde el rol administrador

### No puedo crear vendedor
- Verificar que el script SQL creó ambos registros (users + vendedores)
- Verificar que el `user_id` en vendedores coincida con el `id` en users

---

## 📋 Checklist de Verificación RBAC

- [ ] Creé los 3 usuarios (admin, consulta, vendedor)
- [ ] Puedo hacer login con cada usuario
- [ ] Administrador puede eliminar proyectos
- [ ] Consulta NO puede eliminar proyectos
- [ ] Vendedor NO puede crear lotes
- [ ] Vendedor solo ve sus propios lotes asignados
- [ ] Vendedor solo ve sus propias comisiones
- [ ] Reportes del vendedor están filtrados por su ID

---

## 📞 ¿Necesitas Ayuda?

Si tienes problemas:

1. **Verifica la tabla users:**
```sql
SELECT id, email, nombre, rol, activo FROM users;
```

2. **Verifica la tabla vendedores:**
```sql
SELECT v.id, v.user_id, v.codigo_vendedor, 
       CONCAT(v.nombres, ' ', v.apellidos) as nombre,
       u.email, u.rol
FROM vendedores v
LEFT JOIN users u ON v.user_id = u.id;
```

3. **Verifica lotes asignados al vendedor:**
```sql
SELECT l.id, l.codigo_lote, l.vendedor_id,
       u.nombre as vendedor_nombre
FROM lotes l
LEFT JOIN users u ON l.vendedor_id = u.id
WHERE l.vendedor_id IS NOT NULL;
```

---

**Última Actualización:** 2024-11-29
