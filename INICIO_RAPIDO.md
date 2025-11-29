# 🚀 INICIO RÁPIDO - Sistema de Gestión de Lotes

## ⚡ Instalación Express (5 minutos)

### 1️⃣ Importar Base de Datos
```bash
# Opción A: Línea de comandos
mysql -u root -p < database/schema.sql

# Opción B: phpMyAdmin
# - Crear base de datos "sistema_lotes"
# - Importar archivo database/schema.sql
```

### 2️⃣ Configurar Conexión
Editar `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_lotes');
define('DB_USER', 'root');           // ← Tu usuario
define('DB_PASS', '');               // ← Tu contraseña
define('APP_URL', 'http://localhost/app-Inversiones/public'); // ← Tu URL
```

### 3️⃣ Iniciar Servidor

#### XAMPP/WAMP:
```bash
# Copiar proyecto a:
C:/xampp/htdocs/app-Inversiones

# Abrir en navegador:
http://localhost/app-Inversiones/public
```

#### Servidor PHP incorporado:
```bash
cd public
php -S localhost:8000

# Abrir en navegador:
http://localhost:8000
```

### 4️⃣ Iniciar Sesión
```
URL: http://localhost/app-Inversiones/public/auth/login
Email: admin@sistema.com
Contraseña: admin123
```

---

## ✅ Verificación

### ¿Todo funciona?
- [ ] La página de login se muestra correctamente
- [ ] Los estilos CSS se cargan (navbar azul)
- [ ] Puedes iniciar sesión con admin@sistema.com
- [ ] El dashboard se muestra después del login
- [ ] La navbar muestra tu nombre de usuario

### ¿Tienes errores?

#### Error 404 en todas las páginas
```bash
# Verificar que mod_rewrite está activo en Apache
# XAMPP: httpd.conf → Descomentar línea:
LoadModule rewrite_module modules/mod_rewrite.so
```

#### Error de conexión DB
```bash
# Verificar credenciales en config/config.php
# Verificar que MySQL está ejecutándose
# Verificar que la base de datos existe
mysql -u root -p -e "SHOW DATABASES LIKE 'sistema_lotes';"
```

#### Página en blanco
```bash
# Activar errores en config/config.php:
define('DEBUG_MODE', true);
```

---

## 🎯 Funcionalidades Disponibles

### ✅ Ya Implementado (Módulo 1)
- Login / Logout
- Registro de usuarios
- Recuperación de contraseña
- Cambio de contraseña
- Dashboard básico

### ⏳ Próximamente (Módulos 2-4)
- Gestión de proyectos
- Gestión de lotes
- Gestión de clientes
- Reportes y estadísticas

---

## 📱 Contacto y Soporte

**Documentación completa**: Ver `README.md`
**Detalles técnicos**: Ver `MODULO_1_COMPLETADO.md`

---

## 🔑 Credenciales de Prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@sistema.com | admin123 |

**Nota**: Cambiar estas credenciales en producción.

---

## 🎨 Personalizar

### Cambiar Colores
Editar `public/assets/css/theme.css`:
```css
:root {
    --primary: #007BFF;    /* Color principal */
    --secondary: #6C757D;  /* Color secundario */
    --accent: #28A745;     /* Color de acento */
}
```

### Cambiar Logo/Nombre
Editar `config/config.php`:
```php
define('APP_NAME', 'Tu Nombre Aquí');
```

---

## ✨ ¡Listo para usar!

El sistema está completamente funcional y listo para comenzar a construir el resto de módulos.

**Siguiente paso**: Ejecutar Módulo 3 para agregar la lógica de proyectos y lotes.
