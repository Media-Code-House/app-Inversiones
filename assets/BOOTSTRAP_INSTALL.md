# Instrucciones para Bootstrap Local

Este proyecto está configurado para usar Bootstrap de forma local.

## Archivos Necesarios

Descarga los siguientes archivos y colócalos en las rutas indicadas:

### CSS
- **Archivo**: `bootstrap.min.css` (v5.3.0 o superior)
- **Descargar de**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css
- **Colocar en**: `assets/css/bootstrap/bootstrap.min.css`

### JavaScript
- **Archivo**: `bootstrap.bundle.min.js` (incluye Popper.js)
- **Descargar de**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js
- **Colocar en**: `assets/js/bootstrap/bootstrap.bundle.min.js`

## Instalación Rápida

### Opción 1: Descarga Manual
1. Visita: https://getbootstrap.com/docs/5.3/getting-started/download/
2. Descarga los archivos compilados
3. Copia `bootstrap.min.css` a `assets/css/bootstrap/`
4. Copia `bootstrap.bundle.min.js` a `assets/js/bootstrap/`

### Opción 2: PowerShell (Windows)
```powershell
# Descargar Bootstrap CSS
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" -OutFile "assets/css/bootstrap/bootstrap.min.css"

# Descargar Bootstrap JS
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" -OutFile "assets/js/bootstrap/bootstrap.bundle.min.js"
```

### Opción 3: cURL (Linux/Mac)
```bash
# Descargar Bootstrap CSS
curl -o assets/css/bootstrap/bootstrap.min.css https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css

# Descargar Bootstrap JS
curl -o assets/js/bootstrap/bootstrap.bundle.min.js https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js
```

## Verificación

Después de descargar, verifica que los archivos existan:
```
assets/
├── css/
│   ├── bootstrap/
│   │   └── bootstrap.min.css ✓
│   └── theme.css
└── js/
    ├── bootstrap/
    │   └── bootstrap.bundle.min.js ✓
    └── app.js
```

## Nota
El sistema está configurado para cargar Bootstrap desde estas rutas locales. Si no están presentes, aparecerán errores 404 en la consola del navegador.
