# 🗺️ SOLUCIÓN: Mapa No Se Muestra en el Servidor

## 📋 Problema Identificado

El plano interactivo **funciona en local pero NO en el servidor** en producción.

---

## 🔍 Diagnóstico Realizado

### Hallazgos:

1. ✅ **5 proyectos tienen plano asignado** en la base de datos
2. ❌ **3 archivos de imagen NO EXISTEN** físicamente
3. ✅ **2 archivos existen correctamente**
4. ✅ Directorio `uploads/planos/` existe con permisos 0777
5. ✅ `.htaccess` configurado correctamente
6. ✅ Lotes tienen coordenadas guardadas (7 lotes posicionados)

### Proyectos Afectados:

| ID | Proyecto | Estado Imagen |
|----|----------|---------------|
| 18 | URBANIZACION MONACO | ❌ Faltante |
| 19 | SENDEROS DEL PESCADOR | ✅ OK |
| 21 | PALMA REAL | ❌ Faltante |
| 23 | GETEMANI | ❌ Faltante |
| 24 | URBANIZACION BDQ | ✅ OK |

---

## 🚨 Causas Probables

### 1. Imágenes No Subidas al Servidor
**Problema:** Las imágenes existen en local pero no se transfirieron al servidor de producción.

**Solución:**
```bash
# Subir imágenes faltantes por FTP/SFTP
scp uploads/planos/* usuario@servidor:/ruta/al/proyecto/uploads/planos/
```

### 2. Permisos Incorrectos en el Servidor
**Problema:** El servidor web no puede leer las imágenes.

**Solución en servidor:**
```bash
chmod 755 uploads/
chmod 755 uploads/planos/
chmod 644 uploads/planos/*.jpg
chmod 644 uploads/planos/*.png
```

### 3. Ruta Incorrecta en Producción
**Problema:** La estructura de directorios es diferente en el servidor.

**Verificar:**
- ✅ `.htaccess` permite servir archivos estáticos
- ✅ Ruta en BD: `uploads/planos/archivo.jpg`
- ✅ HTML generado: `<img src="/uploads/planos/archivo.jpg" />`

---

## ✅ Soluciones Inmediatas

### Opción 1: Limpiar Referencias y Re-subir

1. **Ejecutar script de limpieza:**
   ```bash
   php corregir_planos_faltantes.php
   ```
   Esto elimina las referencias a imágenes faltantes en la BD.

2. **Re-subir imágenes:**
   - Ir a `/proyectos/edit/{id}`
   - Subir nuevamente la imagen del plano
   - Posicionar los lotes

### Opción 2: Transferir Archivos al Servidor

1. **Identificar archivos que funcionan en local:**
   ```
   694ab7e741f3f_1766504423.png (28 KB) ✅
   6957ffc3b5cf6_1767374787.jpg (364 KB) ✅
   ```

2. **Subir al servidor por FTP:**
   - Conectar al servidor FTP
   - Navegar a `/uploads/planos/`
   - Subir archivos en modo binario

3. **Verificar permisos después de subir**

### Opción 3: Probar Acceso Directo

**En el servidor, probar URL directa:**
```
https://inversionesdevelop.mch.com.co/uploads/planos/6957ffc3b5cf6_1767374787.jpg
```

**Si no funciona:**
- ❌ El archivo no está en el servidor
- ❌ Los permisos están mal
- ❌ La ruta del directorio es incorrecta

---

## 🔧 Script de Corrección

### Archivo: `corregir_planos_faltantes.php`

**Funciones:**
1. ✅ Detecta proyectos con imágenes faltantes
2. ✅ Lista imágenes disponibles
3. ✅ Limpia referencias incorrectas en BD
4. ✅ Genera checklist para servidor

**Uso:**
```bash
php corregir_planos_faltantes.php
```

---

## 📝 Checklist para el Servidor

Verifica cada punto en el servidor de producción:

- [ ] 1. Directorio `uploads/planos/` existe
- [ ] 2. Directorio tiene permisos `755`
- [ ] 3. Imágenes tienen permisos `644`
- [ ] 4. `.htaccess` está en la raíz
- [ ] 5. `mod_rewrite` habilitado en Apache
- [ ] 6. Imágenes fueron subidas (FTP/SFTP)
- [ ] 7. Probar acceso directo: `https://servidor.com/uploads/planos/imagen.jpg`
- [ ] 8. Revisar logs de error de Apache: `/var/log/apache2/error.log`

---

## 🧪 Pruebas

### En el Navegador (Servidor)

1. **Abrir consola del navegador (F12)**

2. **Ir a la pestaña Network**

3. **Visitar:** `https://inversionesdevelop.mch.com.co/proyectos/show/24`

4. **Buscar la petición de la imagen:**
   - ✅ Status 200 = Imagen cargó correctamente
   - ❌ Status 404 = Archivo no encontrado
   - ❌ Status 403 = Permisos denegados
   - ❌ Status 500 = Error del servidor

5. **Revisar Console por errores JavaScript**

### Prueba de Acceso Directo

```bash
# Desde terminal o navegador
curl -I https://inversionesdevelop.mch.com.co/uploads/planos/6957ffc3b5cf6_1767374787.jpg
```

**Respuesta esperada:**
```
HTTP/1.1 200 OK
Content-Type: image/jpeg
Content-Length: 364406
```

---

## 🎯 Plan de Acción Recomendado

### Paso 1: Verificar en Servidor
```bash
ssh usuario@servidor.mch.com.co
cd /ruta/proyecto/
ls -la uploads/planos/
```

### Paso 2: Si faltan archivos, subirlos
```bash
# Desde tu máquina local
scp uploads/planos/*.jpg usuario@servidor:/ruta/proyecto/uploads/planos/
scp uploads/planos/*.png usuario@servidor:/ruta/proyecto/uploads/planos/
```

### Paso 3: Ajustar permisos
```bash
# En el servidor
chmod 755 uploads/planos/
chmod 644 uploads/planos/*
```

### Paso 4: Limpiar BD si es necesario
```bash
# En tu máquina local
php corregir_planos_faltantes.php
```

### Paso 5: Probar en navegador
- Abrir: https://inversionesdevelop.mch.com.co/proyectos/show/24
- Verificar que el plano se muestra
- Verificar que los puntos de lotes aparecen

---

## 📊 Comparación Local vs Servidor

| Aspecto | Local | Servidor |
|---------|-------|----------|
| Imágenes disponibles | 5 | ❓ Verificar |
| Directorio uploads/ | ✅ Existe | ❓ Verificar |
| Permisos | 0777 | ❓ Ajustar a 755 |
| .htaccess | ✅ OK | ✅ OK |
| APP_URL | localhost | mch.com.co |
| Lotes posicionados | 7 | 7 (misma BD) |

---

## 🚀 Resultado Esperado

Después de aplicar las correcciones:

✅ Las imágenes de planos se mostrarán en `/proyectos/show/{id}`  
✅ Los puntos de lotes aparecerán en sus posiciones  
✅ Al hacer clic en un punto, se mostrará la información del lote  
✅ El sistema funcionará igual en local y en producción

---

## 📞 Si el Problema Persiste

1. **Revisar logs del servidor:**
   ```bash
   tail -f /var/log/apache2/error.log
   ```

2. **Verificar configuración de PHP:**
   ```bash
   php -i | grep upload
   ```

3. **Revisar .htaccess completo:**
   ```bash
   cat .htaccess
   ```

4. **Contactar con soporte del hosting** si:
   - mod_rewrite no está habilitado
   - Restricciones de permisos
   - Problemas con directivas de Apache

---

## ✅ Resumen

**Problema:** Plano no se muestra en servidor  
**Causa Principal:** Archivos de imagen no están en el servidor  
**Solución:** Subir archivos faltantes y verificar permisos  
**Scripts Creados:**
- `diagnostico_mapa_servidor.php` - Identificar problemas
- `corregir_planos_faltantes.php` - Limpiar y corregir BD
