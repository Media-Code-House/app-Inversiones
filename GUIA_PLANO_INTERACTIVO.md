# PLANO INTERACTIVO DE PROYECTOS - GUÍA DE IMPLEMENTACIÓN

## 📋 Descripción

Sistema de mapeo interactivo para proyectos inmobiliarios que permite visualizar y gestionar lotes sobre un plano/imagen del proyecto. Los lotes se representan con puntos de colores que cambian según su estado.

## 🎨 Colores por Estado

- 🟢 **Verde** → Disponible
- 🟡 **Amarillo** → Reservado  
- 🔵 **Azul** → Vendido
- ⚫ **Gris** → Bloqueado

## 🚀 Instalación

### 1. Ejecutar Script SQL

Primero, ejecuta el script para agregar los campos necesarios:

```sql
-- Desde phpMyAdmin o consola MySQL
SOURCE database/agregar_plano_interactivo.sql;
```

O manualmente:

```sql
ALTER TABLE `proyectos` 
ADD COLUMN `plano_imagen` VARCHAR(255) NULL COMMENT 'Ruta de la imagen del plano del proyecto' 
AFTER `descripcion`;

ALTER TABLE `lotes` 
ADD COLUMN `plano_x` DECIMAL(6,2) NULL COMMENT 'Coordenada X en el plano (porcentaje 0-100)' 
AFTER `observaciones`,
ADD COLUMN `plano_y` DECIMAL(6,2) NULL COMMENT 'Coordenada Y en el plano (porcentaje 0-100)' 
AFTER `plano_x`;

CREATE INDEX idx_lotes_plano ON lotes(proyecto_id, plano_x, plano_y);
```

### 2. Verificar Archivos Actualizados

Los siguientes archivos fueron modificados/creados:

**Modelo (Models):**
- ✅ `app/Models/LoteModel.php` - Métodos para coordenadas

**Controlador (Controllers):**
- ✅ `app/Controllers/ProyectoController.php` - Métodos AJAX

**Vista (Views):**
- ✅ `app/Views/proyectos/show.php` - Visualización del plano
- ✅ `app/Views/proyectos/edit.php` - Editor interactivo

**Rutas:**
- ✅ `index.php` - Nuevas rutas AJAX

**Base de Datos:**
- ✅ `database/agregar_plano_interactivo.sql` - Script de migración

## 📖 Uso

### Para Administradores/Consulta:

#### 1. Subir Plano del Proyecto

1. Ve a **Proyectos** → Selecciona un proyecto → **Editar Proyecto**
2. En la sección "Plano del Proyecto (Imagen)", haz clic en **Seleccionar archivo**
3. Carga una imagen del plano (JPG, PNG, GIF - máximo 5MB)
4. Haz clic en **Guardar Cambios**

#### 2. Posicionar Lotes en el Plano

1. Después de guardar el plano, aparecerá la sección **"Editor de Plano Interactivo"**
2. Haz clic en el plano donde deseas colocar cada lote
3. Los puntos aparecerán automáticamente (uno por cada lote sin posición)
4. Arrastra los puntos para ajustar su posición
5. Haz clic en **"Guardar Posiciones"** cuando termines

**Leyenda de colores:**
- 🟢 Verde = Disponible
- 🟡 Amarillo = Reservado
- 🔵 Azul = Vendido
- ⚫ Gris = Bloqueado

#### 3. Ver Plano con Lotes

1. Ve a **Proyectos** → Selecciona un proyecto → **Ver Proyecto**
2. En la sección "Plano del Proyecto" verás el mapa interactivo
3. Haz clic en cualquier punto para ver información del lote:
   - Código del lote
   - Estado actual
   - Manzana
   - Área (m²)
   - Precio
   - Cliente (si está vendido)
4. Haz clic en **"Ver Detalles"** para ir a la ficha completa del lote

## 🔧 Características Técnicas

### Arquitectura MVC

**Modelo (LoteModel.php):**
- `updateCoordenadas($id, $x, $y)` - Actualiza posición de un lote
- `getLotesConCoordenadas($proyectoId)` - Obtiene lotes con coordenadas

**Controlador (ProyectoController.php):**
- `updateCoordenadas($id)` - Endpoint AJAX para guardar posiciones
- `getLotesCoordenadas($id)` - Endpoint AJAX para cargar lotes

**Vista (show.php / edit.php):**
- Editor drag & drop interactivo
- Visualización responsiva
- Tooltips informativos

### Sistema de Coordenadas

Las coordenadas se guardan como **porcentajes (0-100)** relativos al tamaño de la imagen:
- `plano_x`: Posición horizontal (0 = izquierda, 100 = derecha)
- `plano_y`: Posición vertical (0 = arriba, 100 = abajo)

Esto permite que el plano sea **responsive** y se adapte a cualquier tamaño de pantalla.

### Seguridad

- ✅ Validación CSRF en todas las peticiones AJAX
- ✅ Control de permisos RBAC (solo administrador/consulta)
- ✅ Validación de tipos de archivo (solo imágenes)
- ✅ Límite de tamaño de archivo (5MB)

## 🎯 Casos de Uso

### Caso 1: Proyecto Nuevo con Plano

1. Crear proyecto
2. Editar proyecto → Subir plano
3. Guardar
4. Posicionar lotes en el editor
5. Guardar posiciones

### Caso 2: Proyecto Existente sin Plano

1. Editar proyecto
2. Subir imagen del plano
3. Guardar
4. Recargar página o volver a editar
5. Posicionar lotes
6. Guardar posiciones

### Caso 3: Actualizar Posiciones

1. Editar proyecto
2. Scroll hasta "Editor de Plano Interactivo"
3. Arrastrar puntos a nuevas posiciones
4. Guardar posiciones

## 🐛 Solución de Problemas

### El editor no aparece

**Causa:** No hay plano o no hay lotes
**Solución:** 
1. Verifica que el proyecto tenga una imagen de plano
2. Verifica que el proyecto tenga al menos 1 lote creado

### Los puntos no se guardan

**Causa:** Error de permisos o CSRF
**Solución:**
1. Verifica que el usuario tenga rol administrador o consulta
2. Recarga la página para renovar el token CSRF
3. Revisa la consola del navegador (F12) para errores

### La imagen no se carga

**Causa:** Permisos de carpeta o ruta incorrecta
**Solución:**
1. Verifica que la carpeta `uploads/planos/` tenga permisos 777
2. Verifica que la ruta en la base de datos sea relativa (ej: `uploads/planos/imagen.jpg`)

### Los puntos no son visibles

**Causa:** Coordenadas fuera de rango
**Solución:**
1. Ve a editar proyecto
2. Reposiciona los lotes dentro del área visible
3. Guarda de nuevo

## 📞 Soporte

Para problemas o mejoras, contacta al equipo de desarrollo o revisa:
- Logs de errores: `Check Chrome DevTools → Console (F12)`
- Logs PHP: Revisar archivo de logs del servidor
- Base de datos: Verificar campos `plano_x`, `plano_y` en tabla `lotes`

---

**Versión:** 1.0  
**Fecha:** 22 de diciembre de 2025  
**Patrón:** MVC (Modelo-Vista-Controlador)
