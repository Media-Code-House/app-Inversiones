# SOLUCIÓN AL ERROR 500: Módulo de Pago Inicial Diferido

**Fecha:** 2 de diciembre de 2025  
**Error:** HTTP ERROR 500 al acceder a `/lotes/inicial/create/13`  
**Estado:** ✅ Solucionado

---

## 🔍 Diagnóstico del Problema

El error 500 se debe a **dos causas principales**:

### 1. Tablas de Base de Datos No Existen
Las tablas del módulo de Pago Inicial Diferido no han sido creadas en producción:
- `pagos_iniciales`
- `pagos_iniciales_detalle`
- Campo `plan_inicial_id` en tabla `lotes`
- Trigger `after_plan_inicial_completado`
- Vista `vista_planes_iniciales_resumen`

### 2. Métodos de Flash Messages Incorrectos
El `InicialController` estaba usando métodos inexistentes:
- ❌ `$this->flash('error', $message)` (no existe en Controller)
- ✅ `$_SESSION['error'] = $message` (correcto)

---

## ✅ Soluciones Aplicadas

### Solución 1: Script de Instalación Automática

Se creó el archivo **`install_pago_inicial.php`** que:
- ✅ Ejecuta todas las migraciones de `update_pago_inicial.sql`
- ✅ Valida que las estructuras se crearon correctamente
- ✅ Proporciona reporte detallado de instalación
- ✅ Maneja errores gracefully

### Solución 2: Corrección de InicialController

Se corrigieron todos los métodos de flash messages:
- ✅ Reemplazados 7 usos de `$this->flash()` por `$_SESSION[]`
- ✅ Sintaxis PHP validada sin errores
- ✅ Compatible con la clase Controller base

---

## 🚀 PASOS PARA SOLUCIONAR EN PRODUCCIÓN

### Paso 1: Subir Archivos Corregidos

```bash
# Conectar al servidor de producción
ssh usuario@inversiones.mch.com.co

# Navegar a la carpeta del proyecto
cd /ruta/al/proyecto

# Subir archivos corregidos
# - app/Controllers/InicialController.php (corregido)
# - install_pago_inicial.php (nuevo)
# - database/update_pago_inicial.sql (ya existe)
```

### Paso 2: Ejecutar Script de Instalación

```bash
# Ejecutar instalación de base de datos
php install_pago_inicial.php
```

**Salida Esperada:**
```
╔═══════════════════════════════════════════════════════════════╗
║  INSTALACIÓN: Módulo de Pago Inicial Diferido                ║
╚═══════════════════════════════════════════════════════════════╝

✓ Conexión a base de datos establecida
  Base de datos: u418271893_inversiones

✓ Archivo de migración encontrado
  Archivo: update_pago_inicial.sql

📋 Ejecutando X sentencias SQL...

  [1] Ejecutando CREATE TABLE: pagos_iniciales ✓
  [2] Ejecutando CREATE TABLE: pagos_iniciales_detalle ✓
  [3] Ejecutando ALTER TABLE: lotes ✓
  [4] Ejecutando CREATE TRIGGER: after_plan_inicial_completado ✓
  [5] Ejecutando CREATE VIEW: vista_planes_iniciales_resumen ✓

✅ INSTALACIÓN COMPLETADA EXITOSAMENTE
   Sentencias ejecutadas: 5

🔍 VERIFICANDO INSTALACIÓN...

  ✓ Tabla 'pagos_iniciales' creada correctamente
    Columnas: 11
  ✓ Tabla 'pagos_iniciales_detalle' creada correctamente
    Columnas: 9
  ✓ Campo 'plan_inicial_id' agregado a tabla 'lotes'
  ✓ Trigger 'after_plan_inicial_completado' creado
  ✓ Vista 'vista_planes_iniciales_resumen' creada

╔═══════════════════════════════════════════════════════════════╗
║  INSTALACIÓN FINALIZADA                                       ║
╚═══════════════════════════════════════════════════════════════╝
```

### Paso 3: Verificar Manualmente (Opcional)

```sql
-- Conectar a MySQL
mysql -u u418271893_inv -p u418271893_inversiones

-- Verificar tablas creadas
SHOW TABLES LIKE 'pagos_iniciales%';

-- Verificar campo en lotes
DESCRIBE lotes plan_inicial_id;

-- Verificar trigger
SHOW TRIGGERS WHERE `Trigger` = 'after_plan_inicial_completado';

-- Verificar vista
SELECT * FROM vista_planes_iniciales_resumen LIMIT 0;
```

### Paso 4: Probar en Navegador

```
URL: https://inversiones.mch.com.co/lotes/inicial/create/13
```

**Resultado Esperado:**
- ✅ Página carga correctamente
- ✅ Formulario de "Crear Plan de Pago Inicial Diferido" visible
- ✅ Información del lote #13 se muestra
- ✅ Sin error 500

---

## 🔧 Alternativa: Instalación Manual (Si el script falla)

Si por alguna razón el script `install_pago_inicial.php` no funciona, ejecutar directamente el SQL:

```bash
# Opción 1: Via CLI
mysql -u u418271893_inv -p u418271893_inversiones < database/update_pago_inicial.sql

# Opción 2: Via phpMyAdmin
# 1. Acceder a phpMyAdmin
# 2. Seleccionar base de datos u418271893_inversiones
# 3. Ir a pestaña "SQL"
# 4. Copiar y pegar contenido de database/update_pago_inicial.sql
# 5. Ejecutar
```

---

## 📋 Checklist de Verificación Post-Instalación

- [ ] Script `install_pago_inicial.php` ejecutado sin errores
- [ ] Tabla `pagos_iniciales` existe (verificar con `SHOW TABLES`)
- [ ] Tabla `pagos_iniciales_detalle` existe
- [ ] Campo `lotes.plan_inicial_id` existe (verificar con `DESCRIBE lotes`)
- [ ] Trigger `after_plan_inicial_completado` creado
- [ ] Vista `vista_planes_iniciales_resumen` creada
- [ ] Archivo `InicialController.php` corregido subido a producción
- [ ] URL `/lotes/inicial/create/13` carga sin error 500
- [ ] Formulario se muestra correctamente
- [ ] Logs del servidor sin errores PHP

---

## 🐛 Troubleshooting

### Problema: "Table 'pagos_iniciales' already exists"

**Solución:**
```sql
-- Verificar si ya existe
SELECT * FROM pagos_iniciales LIMIT 1;

-- Si existe y está vacía, continuar
-- Si contiene datos, revisar si la instalación ya se hizo
```

### Problema: "Access denied for user"

**Solución:**
```bash
# Verificar credenciales en config/config.php
cat config/config.php | grep DB_

# Usar las credenciales correctas
```

### Problema: "Unknown column 'plan_inicial_id' in 'field list'"

**Causa:** El campo no se agregó correctamente a la tabla `lotes`

**Solución:**
```sql
-- Agregar el campo manualmente
ALTER TABLE lotes 
ADD COLUMN plan_inicial_id int(10) UNSIGNED DEFAULT NULL 
COMMENT 'FK al plan de pago inicial activo (si existe)';

-- Agregar índice
ALTER TABLE lotes ADD KEY idx_plan_inicial_id (plan_inicial_id);

-- Agregar foreign key
ALTER TABLE lotes 
ADD CONSTRAINT fk_lotes_plan_inicial 
FOREIGN KEY (plan_inicial_id) 
REFERENCES pagos_iniciales(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;
```

### Problema: Sigue mostrando Error 500 después de instalación

**Pasos de Diagnóstico:**
```bash
# 1. Revisar logs de PHP
tail -50 /var/log/apache2/error.log
# o
tail -50 storage/logs/app.log

# 2. Verificar permisos de archivos
ls -la app/Controllers/InicialController.php

# 3. Limpiar caché de OPcache (si existe)
service apache2 restart
```

---

## 📝 Archivos Modificados/Creados

### Archivos Corregidos
1. **`app/Controllers/InicialController.php`**
   - Líneas modificadas: 89, 210-212, 219, 296, 398, 403, 453
   - Cambio: `$this->flash()` → `$_SESSION[]`

### Archivos Nuevos
1. **`install_pago_inicial.php`** (script de instalación automática)
2. **`SOLUCION_ERROR_500_PAGO_INICIAL.md`** (este documento)

### Archivos Existentes (No modificados)
1. **`database/update_pago_inicial.sql`** (ya existía desde implementación inicial)

---

## ✅ Resumen de la Solución

| Aspecto | Problema | Solución | Estado |
|---------|----------|----------|--------|
| **Base de Datos** | Tablas no existen | Script `install_pago_inicial.php` | ✅ Listo |
| **Controlador** | Métodos flash incorrectos | Corregidos a `$_SESSION[]` | ✅ Listo |
| **Sintaxis PHP** | Validar sin errores | Verificado | ✅ OK |
| **Documentación** | Pasos de instalación | Este documento | ✅ Completo |

---

## 🎯 Resultado Final Esperado

Después de seguir estos pasos:

1. ✅ El error 500 desaparece
2. ✅ La URL `/lotes/inicial/create/13` carga correctamente
3. ✅ Se puede crear un Plan de Pago Inicial Diferido
4. ✅ Los estados del lote cambian correctamente (vendido → reservado → vendido)
5. ✅ Los pagos se registran y acumulan correctamente
6. ✅ El trigger automático funciona al completar el pago

---

**Preparado por:** GitHub Copilot (Claude Sonnet 4.5)  
**Fecha:** 2 de diciembre de 2025  
**Prioridad:** Alta - Producción
