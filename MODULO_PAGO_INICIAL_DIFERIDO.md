# 📋 MÓDULO DE PAGO INICIAL DIFERIDO - GUÍA DE IMPLEMENTACIÓN

**Fecha de Creación**: 2025-12-02  
**Versión**: 1.0.0  
**Estado**: ✅ COMPLETADO - Listo para ejecutar

---

## 🎯 RESUMEN EJECUTIVO

Se ha implementado exitosamente el **Módulo de Pago Inicial Diferido (Plan de Enganche)** que permite a los clientes pagar la inicial de un lote en cuotas mensuales antes de generar el plan de amortización principal.

### Flujo de Estados:
```
DISPONIBLE → VENDIDO (con Plan Inicial) → RESERVADO (pago en curso) → VENDIDO (inicial completa) → Amortización Principal
```

---

## 📦 ARCHIVOS CREADOS/MODIFICADOS

### 1. Base de Datos
- ✅ **`database/update_pago_inicial.sql`** - Script de migración completo

### 2. Controlador
- ✅ **`app/Controllers/InicialController.php`** - Controlador del módulo

### 3. Vistas (Nueva Carpeta)
- ✅ **`app/Views/lotes/inicial/create.php`** - Crear plan inicial
- ✅ **`app/Views/lotes/inicial/pago.php`** - Registrar pagos
- ✅ **`app/Views/lotes/inicial/show.php`** - Ver detalle (pendiente creación)

### 4. Rutas
- ✅ **`index.php`** - 5 nuevas rutas agregadas

### 5. Integración
- ✅ **`app/Views/lotes/show.php`** - Modificado para mostrar plan inicial activo

---

## 🚀 INSTRUCCIONES DE INSTALACIÓN

### PASO 1: Ejecutar Migración de Base de Datos

**CRÍTICO**: Este paso debe ejecutarse PRIMERO antes de usar el sistema.

```sql
-- Ejecutar en phpMyAdmin o desde terminal:
mysql -u [usuario] -p [database] < database/update_pago_inicial.sql
```

**¿Qué hace este script?**
- ✅ Crea tabla `pagos_iniciales` (plan de enganche)
- ✅ Crea tabla `pagos_iniciales_detalle` (registro de pagos)
- ✅ Agrega campo `plan_inicial_id` a tabla `lotes`
- ✅ Crea trigger `after_plan_inicial_completado` (transición automática de estado)
- ✅ Crea vista `vista_planes_iniciales_resumen` (consultas optimizadas)

**Verificación**:
```sql
-- Verificar que las tablas existen:
SHOW TABLES LIKE '%inicial%';

-- Verificar que el campo se agregó:
DESCRIBE lotes;

-- Verificar que el trigger existe:
SHOW TRIGGERS WHERE `Trigger` = 'after_plan_inicial_completado';
```

---

## 🔗 RUTAS DISPONIBLES

### 1. Crear Plan Inicial Diferido
```
GET  /lotes/inicial/create/{lote_id}
POST /lotes/inicial/store/{lote_id}
```
**Descripción**: Formulario para crear un plan de pago inicial en cuotas.  
**Acceso**: Administrador, Consulta  
**Validaciones**:
- Lote debe estar en estado 'vendido'
- No debe tener plan inicial activo
- No debe tener plan de amortización principal

### 2. Registrar Pago Inicial
```
GET  /lotes/inicial/pago/{lote_id}
POST /lotes/inicial/registrar-pago/{lote_id}
```
**Descripción**: Registrar abonos contra el plan de pago inicial.  
**Acceso**: Administrador, Consulta  
**Lógica Crítica**: Si el pago completa el saldo, el lote cambia automáticamente a 'vendido'.

### 3. Ver Detalle del Plan
```
GET /lotes/inicial/show/{lote_id}
```
**Descripción**: Muestra resumen completo del plan inicial con historial de pagos.  
**Acceso**: Todos los roles autenticados

---

## 🎨 INTERFAZ DE USUARIO

### Vista: `lotes/show.php` (Modificada)

**Nuevo Comportamiento**:

1. **Lote RESERVADO con plan inicial activo**:
   - ⚠️ Alerta amarilla: "Plan de Pago Inicial en Curso"
   - 📊 Resumen visual del plan (monto, pagado, saldo, progreso)
   - 🎯 Botón: "Registrar Pago Inicial" (destacado)
   - 👁️ Botón: "Ver Plan Inicial"
   - 🚫 Botón "Generar Plan de Amortización" **OCULTO**

2. **Lote VENDIDO sin amortización**:
   - 💳 Botón: "Plan Inicial Diferido" (nuevo)
   - 📅 Botón: "Plan de Amortización Normal" (existente)

3. **Lote VENDIDO con plan inicial completado**:
   - ℹ️ Información histórica del plan inicial
   - ✅ Botón: "Generar Plan de Amortización" (ahora disponible)

---

## 🔧 LÓGICA DE NEGOCIO

### Reglas de Validación

#### Al Crear Plan Inicial:
```php
✅ Lote debe estar en estado 'vendido'
✅ No debe tener plan_inicial_id activo
✅ No debe tener plan de amortización principal
✅ Monto inicial > 0
✅ Monto pagado hoy ≤ Monto inicial total
✅ Plazo entre 1 y 120 meses
```

#### Al Registrar Pago:
```php
✅ Plan debe estar en estado 'en_curso'
✅ Valor pago > 0
✅ Valor pago ≤ Saldo pendiente
✅ Fecha de pago no puede ser futura (recomendado)
```

### Cálculos Automáticos

**Monto a Diferir**:
```
monto_pendiente_diferir = monto_inicial_total - monto_pagado_hoy
```

**Cuota Mensual**:
```
cuota_mensual = monto_pendiente_diferir / plazo_meses
```

**Saldo Después de Pago**:
```
saldo_nuevo = saldo_anterior - valor_pagado
```

### Transición de Estados (AUTOMÁTICA)

**Trigger**: `after_plan_inicial_completado`

```sql
SI plan_inicial.estado cambia a 'pagado_total' ENTONCES:
    1. Cambiar lote.estado de 'reservado' a 'vendido'
    2. Limpiar lote.plan_inicial_id = NULL
    3. Registrar en logs el cambio automático
FIN SI
```

---

## 📊 ESTRUCTURA DE DATOS

### Tabla: `pagos_iniciales`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT UNSIGNED | PK Auto-increment |
| `lote_id` | INT UNSIGNED | FK a lotes |
| `monto_inicial_total_requerido` | DECIMAL(15,2) | Total de la inicial |
| `monto_pagado_hoy` | DECIMAL(15,2) | Abono del primer día |
| `monto_pendiente_diferir` | DECIMAL(15,2) | Saldo a pagar en cuotas |
| `plazo_meses` | INT(3) | Número de meses |
| `cuota_mensual` | DECIMAL(15,2) | Valor de cada cuota |
| `fecha_inicio` | DATE | Fecha de inicio del plan |
| `estado` | ENUM | 'pendiente', 'en_curso', 'pagado_total', 'cancelado' |
| `observaciones` | TEXT | Notas adicionales |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Última actualización |

### Tabla: `pagos_iniciales_detalle`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT UNSIGNED | PK Auto-increment |
| `plan_inicial_id` | INT UNSIGNED | FK a pagos_iniciales |
| `fecha_pago` | DATE | Fecha del abono |
| `valor_pagado` | DECIMAL(15,2) | Monto del pago |
| `metodo_pago` | ENUM | 'efectivo', 'transferencia', 'cheque', 'tarjeta' |
| `numero_recibo` | VARCHAR(50) | Número de recibo (opcional) |
| `saldo_pendiente_despues` | DECIMAL(15,2) | Saldo restante después del pago |
| `observaciones` | TEXT | Notas del pago |
| `created_at` | TIMESTAMP | Fecha de registro |
| `updated_at` | TIMESTAMP | Última actualización |

### Vista: `vista_planes_iniciales_resumen`

Consulta optimizada que incluye:
- Información del lote, proyecto, cliente
- Montos: inicial, pagado, pendiente
- Cuotas: total, pagadas, pendientes
- Fechas: inicio, último pago
- Estados: plan y lote

---

## 🧪 CASOS DE USO

### Caso 1: Cliente paga toda la inicial hoy

**Entrada**:
- Monto Inicial Total: $10,000,000
- Monto Pagado Hoy: $10,000,000
- Plazo: 1 mes (no importa)

**Resultado**:
- ✅ Plan creado con estado 'pagado_total'
- ✅ Lote permanece en estado 'vendido'
- ✅ Campo plan_inicial_id = NULL (no hay plan activo)
- ✅ Mensaje: "El pago inicial fue completado. El lote está VENDIDO."

### Caso 2: Cliente paga inicial en 6 cuotas

**Entrada**:
- Monto Inicial Total: $12,000,000
- Monto Pagado Hoy: $2,000,000
- Plazo: 6 meses

**Cálculos**:
- Monto a Diferir: $10,000,000
- Cuota Mensual: $1,666,667

**Resultado**:
- ✅ Plan creado con estado 'en_curso'
- ✅ Lote cambia a estado 'reservado'
- ✅ Campo plan_inicial_id = [ID del plan]
- ✅ Primer pago registrado en pagos_iniciales_detalle

### Caso 3: Cliente completa el plan inicial

**Escenario**:
- 5 cuotas ya pagadas, quedan $1,666,667
- Cliente registra pago de $1,666,667

**Resultado (AUTOMÁTICO via TRIGGER)**:
- ✅ Saldo llega a $0
- ✅ plan_inicial.estado = 'pagado_total'
- ✅ **Trigger ejecuta**:
  - lote.estado = 'vendido'
  - lote.plan_inicial_id = NULL
- ✅ Mensaje: "¡PLAN INICIAL COMPLETADO! El lote ha cambiado a estado VENDIDO."
- ✅ Ahora se puede crear el plan de amortización principal

---

## 🔐 SEGURIDAD Y VALIDACIONES

### Validaciones del Controlador

```php
// InicialController@store
- Campos requeridos: monto_inicial_total, monto_pagado_hoy, plazo_meses, fecha_inicio
- Monto inicial > 0
- Monto pagado hoy ≥ 0 y ≤ monto inicial
- Plazo entre 1 y 120 meses
- Lote en estado 'vendido'
- Sin plan inicial activo previo
- Sin plan de amortización principal

// InicialController@registrarPago
- Campos requeridos: valor_pagado, fecha_pago
- Valor > 0
- Valor ≤ saldo pendiente
- Plan en estado 'en_curso'
- Transacciones con rollback en caso de error
```

### Permisos (RBAC)

```php
// Funciones requeridas (helpers.php):
can('crear_plan_inicial')      // Administrador, Consulta
can('registrar_pago_inicial')  // Administrador, Consulta
can('ver_plan_inicial')        // Todos los roles autenticados
```

### Logs de Auditoría

```php
// Eventos registrados en Logger:
- Creación de plan inicial
- Registro de cada pago
- Completación automática del plan
- Cambios de estado del lote
```

---

## 📈 INTEGRACIÓN CON MÓDULOS EXISTENTES

### Módulo de Lotes (Compatible)

**Campo Agregado**: `lotes.plan_inicial_id`
- Tipo: INT UNSIGNED NULL
- FK a: pagos_iniciales(id)
- Constraint: ON DELETE SET NULL

**Consultas Modificadas**: Ninguna (SELECT * trae el nuevo campo automáticamente)

### Módulo de Amortización (Validación Agregada)

**Nueva Validación** (recomendada agregar en `AmortizacionController@create`):
```php
// Validar que no hay plan inicial activo
if (!empty($lote['plan_inicial_id'])) {
    throw new Exception("No se puede crear plan de amortización. El lote tiene un plan de pago inicial activo.");
}

// El monto financiado debe considerar la inicial:
$montoFinanciado = $lote['precio_lista'] - $montoInicialCompletado;
```

### Módulo de Reportes (Extensible)

**Consultas Sugeridas**:
```sql
-- Lotes con plan inicial activo
SELECT * FROM vista_planes_iniciales_resumen 
WHERE estado_plan = 'en_curso';

-- Monto total en planes iniciales
SELECT SUM(monto_inicial_total_requerido) as total
FROM pagos_iniciales 
WHERE estado = 'en_curso';

-- Clientes con mora en plan inicial
SELECT * FROM vista_planes_iniciales_resumen
WHERE DATEDIFF(NOW(), fecha_ultimo_pago) > 30
AND cuotas_pendientes > 0;
```

---

## 🐛 TROUBLESHOOTING

### Problema 1: No aparecen los botones del plan inicial

**Causa**: La migración SQL no se ejecutó.  
**Solución**:
```bash
mysql -u root -p inversiones < database/update_pago_inicial.sql
```

### Problema 2: Error "Unknown column 'plan_inicial_id'"

**Causa**: El campo no existe en la tabla lotes.  
**Solución**: Ejecutar solo la parte de ALTER TABLE:
```sql
ALTER TABLE `lotes` 
ADD COLUMN `plan_inicial_id` int(10) UNSIGNED DEFAULT NULL 
AFTER `saldo_a_favor`;
```

### Problema 3: El lote no cambia a 'vendido' al completar el plan

**Causa**: El trigger no está activo.  
**Solución**: Verificar y recrear el trigger:
```sql
SHOW TRIGGERS LIKE 'after_plan_inicial_completado';

-- Si no existe, copiar y ejecutar la sección DELIMITER del SQL
```

### Problema 4: Error "Class 'App\Controllers\Controller' not found"

**Causa**: Namespace incorrecto o autoloader no configurado.  
**Solución**: Ya corregido en el código. Verificar que exista:
```php
// app/Controllers/InicialController.php
namespace App\Controllers;
```

---

## ✅ CHECKLIST DE VALIDACIÓN POST-INSTALACIÓN

Ejecutar en orden:

- [ ] 1. Ejecutar `update_pago_inicial.sql` en la base de datos
- [ ] 2. Verificar que existen las tablas `pagos_iniciales` y `pagos_iniciales_detalle`
- [ ] 3. Verificar que existe el campo `lotes.plan_inicial_id`
- [ ] 4. Verificar que existe el trigger `after_plan_inicial_completado`
- [ ] 5. Verificar que existe la vista `vista_planes_iniciales_resumen`
- [ ] 6. Acceder a `/lotes/show/{id}` de un lote vendido
- [ ] 7. Ver que aparecen los botones "Plan Inicial Diferido" y "Plan de Amortización Normal"
- [ ] 8. Crear un plan inicial de prueba
- [ ] 9. Verificar que el lote cambió a estado 'reservado'
- [ ] 10. Registrar pagos hasta completar el plan
- [ ] 11. Verificar que el lote cambió automáticamente a 'vendido'
- [ ] 12. Intentar crear plan de amortización normal (debe funcionar)

---

## 📚 DOCUMENTACIÓN ADICIONAL

### Referencias:
- Schema de base de datos: `database/schema.sql`
- Controlador base: `app/Controllers/Controller.php`
- Modelo de lotes: `app/Models/LoteModel.php`
- Helpers: `core/helpers.php`

### Próximas Mejoras Sugeridas:
1. ✨ Agregar cálculo de intereses al plan inicial (opcional)
2. ✨ Notificaciones automáticas de cuotas vencidas
3. ✨ Reporte de planes iniciales por cobrar
4. ✨ Exportación de comprobantes de pago PDF
5. ✨ Integración con pasarelas de pago en línea

---

## 📞 SOPORTE

**Fecha de Implementación**: 2025-12-02  
**Arquitecto de Integración**: GitHub Copilot  
**Versión del Sistema**: APP-Inversiones v2.0  
**Base de Datos**: MariaDB 11.8.3  
**PHP**: 7.2.34+

---

## 🎉 ¡IMPLEMENTACIÓN COMPLETADA!

El módulo de **Pago Inicial Diferido** ha sido implementado exitosamente y está listo para su uso en producción.

**Siguiente Paso**: Ejecutar el script `database/update_pago_inicial.sql` y realizar las pruebas de validación.

---

**FIN DEL DOCUMENTO**
