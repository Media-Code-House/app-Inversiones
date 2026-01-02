# 🧪 SETUP: Datos de Prueba - Sistema de Saldo a Favor Global

## 📋 Problema que Resuelve

Un cliente pagó **$2.500.000** cuando la cuota era solo **$1.977.085**.

**Situación sin saldo a favor:**
- ❌ Exceso se pierde o se aplica completo recálculo
- ❌ Cliente entra en mora en cuotas futuras
- ❌ Riesgo de embargo

**Situación CON saldo a favor (lo que implementamos):**
- ✅ Exceso se acumula: **$522.914**
- ✅ Botón "Aplicar Saldo a Favor" aparece
- ✅ Al hacer click, cuotas futuras se compensan
- ✅ Cliente sale de mora

---

## 🚀 Pasos para Probar

### **PASO 1: Ejecutar Migration SQL**

Primero, asegúrate de que la columna `saldo_a_favor` existe:

```bash
mysql -u root -p inversiones < database/migration_saldo_a_favor.sql
```

**Verifica que funcionó:**
```sql
SELECT COLUMN_NAME, COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor';
```

Resultado esperado:
```
| saldo_a_favor | decimal(15,2) |
```

---

### **PASO 2: Ejecutar Script de Datos de Prueba**

Ahora crea el cliente de prueba con mora:

```bash
mysql -u root -p inversiones < database/test_datos_mora_saldo_favor.sql
```

Este script crea:
- ✅ Cliente de prueba: "Cliente Prueba Mora" (Doc: 1234567890)
- ✅ Proyecto de prueba
- ✅ Lote con 24 cuotas de $1.977.085 cada una
- ✅ Pagos registrados creando la mora

---

### **PASO 3: Obtener el LOTE ID**

Ejecuta esta query para obtener el ID del lote creado:

```sql
SELECT id, codigo_lote, saldo_a_favor 
FROM lotes 
WHERE codigo_lote = 'LOTE-TEST-001';
```

Resultado esperado:
```
| id  | codigo_lote    | saldo_a_favor |
| 999 | LOTE-TEST-001  | 522914.17     |
```

**Anota el ID (en este ejemplo es 999)**

---

### **PASO 4: Abrir el Lote en Navegador**

Abre tu navegador y accede a:

```
https://inversiones.mch.com.co/lotes/amortizacion/show/999
```

*Reemplaza 999 con el ID obtenido en el PASO 3*

---

### **PASO 5: Ver el Estado Actual**

**Verás:**

#### ✅ En la cabecera:
```
[ Registrar Pago]  [ Aplicar Saldo a Favor ($522.914)]  [👁️ Ver Lote]
                           ↑ ESTE ES EL BOTÓN
```

#### ✅ En la tabla de amortización (primeras cuotas):
```
┌────┬──────────┬──────────┬──────────┬────────┐
│ #  │Vencim.   │ Cuota    │ Pagado   │ Estado │
├────┼──────────┼──────────┼──────────┼────────┤
│ 1  │ Feb 15   │ $1.977k  │ $2.500k  │ ✅ PAGADA
│ 2  │ Mar 15   │ $1.977k  │ $0       │ ⚠️ MORA
│ 3  │ Apr 15   │ $1.977k  │ $0       │ ⚠️ MORA
│ 4  │ May 15   │ $1.977k  │ $1.977k  │ ✅ PAGADA
│ 5  │ Jun 15   │ $1.977k  │ $0       │ ⏳ PENDIENTE
└────┴──────────┴──────────┴──────────┴────────┘

SALDO A FAVOR DISPONIBLE: $522.914
[ Aplicar Saldo a Favor ($522.914)]
```

---

### **PASO 6: Hacer Click en el Botón**

1. **Haz click en:** ` Aplicar Saldo a Favor ($522.914)`

2. **Aparecerá diálogo de confirmación:**
   ```
   ⚠️  ¿Aplicar Saldo a Favor de $522.914 para compensar 
       cuotas futuras?
   
       Esta acción reajustará el plan y evitará mora.
   
       [Cancelar]  [Aceptar]
   ```

3. **Haz click en `Aceptar`**

---

### **PASO 7: Ver Resultado**

**Después de la confirmación:**

✅ **Mensaje de éxito:**
```
✅ Reajuste completado exitosamente
   Cuotas compensadas: 1
   Saldo a favor restante: $0.00
```

✅ **Tabla actualizada:**
```
┌────┬──────────┬──────────┬──────────┬────────┐
│ #  │Vencim.   │ Cuota    │ Pagado   │ Estado │
├────┼──────────┼──────────┼──────────┼────────┤
│ 1  │ Feb 15   │ $1.977k  │ $2.500k  │ ✅ PAGADA
│ 2  │ Mar 15   │ $1.977k  │ $1.977k  │ ✅ PAGADA   ← COMPENSADA
│ 3  │ Apr 15   │ $1.977k  │ $522.914 │ ⏳ PENDIENTE ← PARCIAL
│ 4  │ May 15   │ $1.977k  │ $1.977k  │ ✅ PAGADA
│ 5  │ Jun 15   │ $1.977k  │ $0       │ ⏳ PENDIENTE
└────┴──────────┴──────────┴──────────┴────────┘

SALDO A FAVOR DISPONIBLE: $0.00
(Botón desapareció)
```

✅ **Cliente está FUERA de mora en Cuota 2**

---

## 🔍 Validaciones Técnicas

### En MySQL - Ver el estado actual:

```sql
-- 1. Ver el lote creado
SELECT id, codigo_lote, saldo_a_favor, numero_cuotas 
FROM lotes 
WHERE codigo_lote = 'LOTE-TEST-001';

-- 2. Ver cuotas (primeras 5)
SELECT numero_cuota, fecha_vencimiento, valor_cuota, estado, valor_pagado 
FROM amortizaciones 
WHERE lote_id = 999
ORDER BY numero_cuota ASC 
LIMIT 5;

-- 3. Ver pagos registrados
SELECT a.numero_cuota, p.fecha_pago, p.valor_pagado, p.metodo_pago 
FROM pagos p
JOIN amortizaciones a ON p.amortizacion_id = a.id
WHERE a.lote_id = 999
ORDER BY p.fecha_pago ASC;

-- 4. Ver saldo a favor actual
SELECT saldo_a_favor FROM lotes WHERE id = 999;
```

---

## 📊 Detalles del Escenario

### Cliente de Prueba:
```
Tipo Documento: CC
Número Documento: 1234567890
Nombre: Cliente Prueba Mora
Email: prueba@test.com
Teléfono: 3001234567
Ciudad: Medellín
```

### Proyecto de Prueba:
```
Código: PRY-TEST
Nombre: Proyecto Prueba Saldo a Favor
Ubicación: Medellín
Estado: activo
```

### Configuración del Lote:
```
Código: LOTE-TEST-001
Monto: $20.000.000
Plazo: 24 meses (2 años)
Tasa: 12% anual (1% mensual)
Cuota fija: $1.977.085,83
Método: Francés (amortización con cuota fija)
```

### Pagos Registrados:

| # | Fecha | Concepto | Monto | Estado |
|---|-------|----------|-------|--------|
| 1 | 2025-02-10 | Cuota 1 - Exceso $522.914 | $2.500.000 | Pagada |
| 4 | 2025-05-10 | Cuota 4 Normal | $1.977.085 | Pagada |

### Cuotas:

| # | Vencimiento | Monto | Pagado | Estado | Motivo |
|---|-------------|-------|--------|--------|--------|
| 1 | 2025-02-15 | $1.977.085 | $2.500.000 | ✅ PAGADA | Pago excedente |
| 2 | 2025-03-15 | $1.977.085 | $0 | ⚠️ MORA | No pagada (para prueba) |
| 3 | 2025-04-15 | $1.977.085 | $0 | ⚠️ MORA | No pagada (para prueba) |
| 4 | 2025-05-15 | $1.977.085 | $1.977.085 | ✅ PAGADA | Pago normal |
| 5+ | ... | $1.977.085 | $0 | ⏳ PENDIENTE | Futuras |

---

## 🎯 Flujo Completo

```
┌──────────────────────────────────────────────────────────┐
│ SITUACIÓN INICIAL                                        │
├──────────────────────────────────────────────────────────┤
│ • Cliente pagó de más en cuota 1                        │
│ • Saldo a Favor: $522.914                              │
│ • Cuota 2: MORA ($1.977.085 sin pagar)                 │
│ • Cuota 3: MORA ($1.977.085 sin pagar)                 │
│ • Cliente: EN RIESGO DE EMBARGO                        │
└──────────────────────────────────────────────────────────┘
                          ↓
         [Click: Aplicar Saldo a Favor]
                          ↓
┌──────────────────────────────────────────────────────────┐
│ PROCESAMIENTO (Backend)                                  │
├──────────────────────────────────────────────────────────┤
│ 1. Validar permisos ✅                                   │
│ 2. Validar CSRF token ✅                                │
│ 3. Iniciar transacción ACID                            │
│ 4. Compensar Cuota 2: $522.914 (PAGADA)               │
│ 5. Cuota 3: queda con saldo ($1.454.171)              │
│ 6. saldo_a_favor = 0                                   │
│ 7. COMMIT transacción                                  │
│ 8. Logging completo                                    │
└──────────────────────────────────────────────────────────┘
                          ↓
┌──────────────────────────────────────────────────────────┐
│ SITUACIÓN FINAL                                          │
├──────────────────────────────────────────────────────────┤
│ • Saldo a Favor: $0.00 ✅                              │
│ • Cuota 2: PAGADA (compensada) ✅                      │
│ • Cuota 3: PENDIENTE (parcial) ⏳                      │
│ • Cliente: FUERA DE MORA ✅                            │
│ • Botón: DESAPARECE                                    │
└──────────────────────────────────────────────────────────┘
```

---

## ✅ Checklist de Éxito

- [ ] Ejecuté migration SQL sin errores
- [ ] Ejecuté script de datos sin errores
- [ ] Vi el lote en amortizacion/show/{id}
- [ ] Vi el botón "Aplicar Saldo a Favor" en azul
- [ ] Hice click y apareció confirmación
- [ ] Acepté la confirmación
- [ ] Cuota 2 ahora dice "PAGADA"
- [ ] Botón desapareció
- [ ] Saldo a favor es $0.00

**Si todo ✅ = ¡Sistema funcionando 100%!** 🎉

---

## 🐛 Troubleshooting

### "El botón no aparece"

**Posibles causas:**

1. La migration SQL no se ejecutó
   ```bash
   mysql -u root -p inversiones < database/migration_saldo_a_favor.sql
   ```

2. El saldo_a_favor es 0
   ```sql
   SELECT saldo_a_favor FROM lotes WHERE codigo_lote = 'LOTE-TEST-001';
   ```

3. No tienes permisos `registrar_pagos`
   - Inicia sesión como admin

### "Error al ejecutar SQL"

**Solución:**

Verifica que las tablas existen:
```sql
SHOW TABLES LIKE 'clientes';
SHOW TABLES LIKE 'lotes';
SHOW TABLES LIKE 'amortizaciones';
SHOW TABLES LIKE 'pagos';
```

### "El reajuste no funciona"

1. Revisa los logs: `storage/logs/`
2. Verifica permisos en BD
3. Valida que hay cuotas PENDIENTE

---

## 📚 Archivos Relacionados

- `database/migration_saldo_a_favor.sql` - Migration para crear columna
- `database/test_datos_mora_saldo_favor.sql` - Datos de prueba
- `DOCUMENTACION_SALDO_FAVOR.md` - Documentación técnica
- `GUIA_PRUEBAS_SALDO_FAVOR.md` - Guía de pruebas completa
- `setup_test_data.php` - Script PHP para crear datos (alternativa)

---

## 📞 Soporte

Si tienes problemas:

1. Lee esta guía completa
2. Verifica los logs
3. Ejecuta las queries de verificación
4. Revisa la documentación técnica

¡Éxito probando el sistema! 🚀
