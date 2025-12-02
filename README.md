# Sistema de Gestión de Lotes e Inversiones

Sistema completo de gestión de proyectos inmobiliarios, lotes, clientes y ventas desarrollado con arquitectura MVC.

## ⚠️ ACTUALIZACIÓN CRÍTICA - 2 de diciembre de 2025

### 🔧 Corrección de Bug Financiero: Abonos Extraordinarios

**Problema Corregido:** El sistema aplicaba incorrectamente los abonos extraordinarios al "saldo contractual total" (capital + intereses futuros), resultando en **cuotas MAYORES** después del abono.

**Solución Implementada:** Los abonos extraordinarios ahora se aplican **únicamente al Saldo de Capital Real**, cumpliendo con el Sistema Francés estándar.

**Impacto:**
- ✅ Las cuotas **siempre disminuyen** después de un abono extraordinario
- ✅ Ahorro promedio para el cliente: 20-25% por cuota
- ✅ Cumple con método francés estándar
- ✅ Validado matemáticamente: Cuota esperada $158.145,69 ✓

**Documentación:**
- 📄 [Corrección Técnica Detallada](CORRECCION_ABONOS_EXTRAORDINARIOS.md)
- 📄 [Resumen Ejecutivo](RESUMEN_CORRECCION_ABONOS.md)
- 📄 [Guía de Despliegue y Auditoría](GUIA_DESPLIEGUE_AUDITORIA.md)
- 🧪 [Script de Validación](validar_correccion_abonos.php)

**Archivos Modificados:**
- `app/Controllers/PagoController.php` (método `aplicarAbonoCapital()`)
- `app/Controllers/AmortizacionController.php` (método `recalcular()`)

---

## 🚀 Módulo 1: Arquitectura Base y Autenticación ✅

### Características Implementadas

#### ✅ Arquitectura MVC Completa
- Estructura de carpetas organizada (Controllers, Models, Views)
- Sistema de rutas dinámicas con URLs amigables
- Patrón Singleton para conexión a base de datos
- Helpers y utilidades globales

#### ✅ Sistema de Autenticación
- **Login**: Autenticación segura con Bcrypt
- **Registro**: Creación de nuevas cuentas
- **Recuperación de Contraseña**: Sistema de tokens seguros
- **Restablecimiento**: Actualización de contraseña con validación
- **Cambio de Contraseña**: Modal para usuarios autenticados
- Protección CSRF en todos los formularios
- Gestión de sesiones y mensajes flash

#### ✅ Diseño Moderno
- Layout responsive con Bootstrap 5
- Tema personalizado corporativo/moderno
- Navbar dinámica según estado de sesión
- Iconos Font Awesome integrados

---

## 📋 Requisitos del Sistema

- PHP >= 7.4
- MySQL >= 5.7 o MariaDB >= 10.2
- Apache con mod_rewrite habilitado
- Extensiones PHP: PDO, PDO_MySQL

---

## 🔧 Instalación

### 1. Clonar o Descargar el Proyecto

```bash
git clone https://github.com/Media-Code-House/app-Inversiones.git
cd app-Inversiones
```

### 2. Configurar la Base de Datos

#### a) Crear la base de datos:

```bash
# Acceder a MySQL
mysql -u root -p

# Ejecutar el script
source database/schema.sql
```

O importar manualmente desde phpMyAdmin el archivo `database/schema.sql`

#### b) Configurar credenciales:

Editar el archivo `config/config.php` con tus datos de conexión:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_lotes');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Configurar Apache

#### Opción A: VirtualHost (Recomendado)

Crear un VirtualHost en Apache:

```apache
<VirtualHost *:80>
    ServerName sistema-lotes.local
    DocumentRoot "C:/xampp/htdocs/app-Inversiones/public"
    
    <Directory "C:/xampp/htdocs/app-Inversiones/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Agregar a `hosts` (C:\Windows\System32\drivers\etc\hosts):
```
127.0.0.1 sistema-lotes.local
```

#### Opción B: Subdirectorio

Si usas XAMPP, copiar a `C:/xampp/htdocs/` y actualizar en `config/config.php`:

```php
define('APP_URL', 'http://localhost/app-Inversiones/public');
```

### 4. Verificar Permisos

Asegurar que Apache tenga permisos de lectura en todos los archivos del proyecto.

---

## 🎯 Uso del Sistema

### Acceso Inicial

1. Abrir en navegador: `http://sistema-lotes.local` o `http://localhost/app-Inversiones/public`

2. **Credenciales de prueba:**
   - Email: `admin@sistema.com`
   - Contraseña: `admin123`

### Funcionalidades Disponibles (Módulo 1)

- ✅ Iniciar sesión
- ✅ Registrar nueva cuenta
- ✅ Recuperar contraseña
- ✅ Restablecer contraseña
- ✅ Cambiar contraseña (usuario autenticado)
- ✅ Cerrar sesión

---

## 📁 Estructura del Proyecto

```
app-Inversiones/
├── app/
│   ├── Controllers/         # Controladores MVC
│   │   ├── AuthController.php
│   │   └── HomeController.php
│   ├── Models/              # Modelos de datos
│   │   └── AuthModel.php
│   └── Views/               # Vistas
│       ├── layouts/
│       │   ├── app.php
│       │   └── partials/
│       │       └── change-password.php
│       ├── auth/
│       │   ├── login.php
│       │   ├── register.php
│       │   ├── recover.php
│       │   └── reset.php
│       └── home/
│           └── dashboard.php
├── config/
│   └── config.php           # Configuración general
├── core/
│   ├── Database.php         # Conexión DB (Singleton)
│   ├── Router.php           # Sistema de rutas
│   └── helpers.php          # Funciones auxiliares
├── database/
│   └── schema.sql           # Script DDL
├── public/                  # Punto de entrada público
│   ├── assets/
│   │   ├── css/
│   │   │   └── theme.css    # Tema personalizado
│   │   └── js/
│   │       └── app.js       # JavaScript principal
│   ├── .htaccess
│   └── index.php            # Front controller
├── .htaccess
└── README.md
```

---

## 🔐 Seguridad Implementada

- ✅ Contraseñas hasheadas con Bcrypt (cost 12)
- ✅ Protección CSRF en formularios
- ✅ Validación de entrada (backend y frontend)
- ✅ Prepared Statements (PDO) para prevenir SQL Injection
- ✅ Escape de HTML para prevenir XSS
- ✅ Tokens seguros para recuperación de contraseña
- ✅ Validación de emails
- ✅ Sesiones seguras

---

## 🎨 Personalización del Tema

El archivo `public/assets/css/theme.css` contiene todas las variables CSS:

```css
:root {
    --primary: #007BFF;
    --secondary: #6C757D;
    --accent: #28A745;
    /* ... más variables ... */
}
```

Modificar estos valores para personalizar los colores del sistema.

---

## 🐛 Solución de Problemas

### Error 404 en todas las rutas

**Problema**: mod_rewrite no está habilitado

**Solución**:
```bash
# Habilitar en Apache
sudo a2enmod rewrite
sudo service apache2 restart
```

### Error de conexión a la base de datos

**Problema**: Credenciales incorrectas

**Solución**: Verificar y actualizar `config/config.php`

### Estilos no se cargan

**Problema**: Rutas incorrectas en config

**Solución**: Verificar `APP_URL` en `config/config.php`

---

## 📊 Próximos Módulos

### Módulo 2: Sistema de Diseño Personalizado
- Refinamiento del theme.css
- Componentes personalizados
- Diseño corporativo/moderno completo

### Módulo 3: Dashboard y Lógica de Negocio
- Modelos: Proyectos y Lotes
- Estadísticas en tiempo real
- Gráficos y visualizaciones

### Módulo 4: CRUD de Proyectos
- Listado con filtros y paginación
- Crear, editar y eliminar proyectos
- Vista de detalle con métricas

---

## 👨‍💻 Desarrollo

**Framework**: MVC Personalizado  
**Tecnologías**: PHP, MySQL, Bootstrap 5, Font Awesome  
**Patrón de Diseño**: Singleton (DB), MVC  

---

## 📝 Licencia

Todos los derechos reservados © 2024

---

## 📧 Soporte

Para reportar problemas o sugerencias, crear un issue en el repositorio.

---

## ✅ Checklist Módulo 1

- [x] Estructura de carpetas MVC
- [x] Sistema de rutas dinámicas
- [x] Conexión a base de datos (Singleton)
- [x] Tabla users en DB
- [x] Layout principal con navbar
- [x] AuthController y AuthModel
- [x] Vista: Login
- [x] Vista: Registro
- [x] Vista: Recuperar contraseña
- [x] Vista: Restablecer contraseña
- [x] Modal: Cambiar contraseña
- [x] Theme.css personalizado
- [x] Helpers y utilidades
- [x] Protección CSRF
- [x] Validaciones frontend y backend
- [x] Mensajes flash
- [x] Dashboard placeholder

**Estado**: ✅ COMPLETADO
