<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-pencil"></i> Editar Lote
                </h1>
                <div>
                    <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-outline-info me-2">
                        <i class="bi bi-eye"></i> Ver Detalle
                    </a>
                    <a href="/lotes" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <?php if (!$puedeEditar): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <strong>Atención:</strong> <?= htmlspecialchars($mensajeBloqueo) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/lotes/update/<?= $lote['id'] ?>" id="formLote">
                        
                        <!-- Información del Proyecto -->
                        <h5 class="card-title border-bottom pb-2 mb-3">Información del Proyecto</h5>
                        
                        <div class="mb-3">
                            <label for="proyecto_id" class="form-label">Proyecto <span class="text-danger">*</span></label>
                            <select name="proyecto_id" id="proyecto_id" class="form-select" 
                                    <?= !$puedeEditar ? 'disabled' : '' ?> required>
                                <option value="">Seleccione un proyecto</option>
                                <?php foreach ($proyectos as $proyecto): ?>
                                    <option value="<?= $proyecto['id'] ?>" 
                                            <?= $lote['proyecto_id'] == $proyecto['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($proyecto['codigo'] . ' - ' . $proyecto['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$puedeEditar): ?>
                                <input type="hidden" name="proyecto_id" value="<?= $lote['proyecto_id'] ?>">
                            <?php endif; ?>
                        </div>

                        <!-- Información del Lote -->
                        <h5 class="card-title border-bottom pb-2 mb-3 mt-4">Información del Lote</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo_lote" class="form-label">Código del Lote <span class="text-danger">*</span></label>
                                <input type="text" name="codigo_lote" id="codigo_lote" class="form-control" 
                                       value="<?= htmlspecialchars($lote['codigo_lote']) ?>" 
                                       <?= !$puedeEditar ? 'readonly' : '' ?> required>
                                <small class="text-muted">Código único dentro del proyecto</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="area" class="form-label">Área (m²) <span class="text-danger">*</span></label>
                                <input type="number" name="area" id="area" class="form-control" 
                                       value="<?= $lote['area_m2'] ?>" 
                                       step="0.01" min="0.01" 
                                       <?= !$puedeEditar ? 'readonly' : '' ?> required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="precio_lista" class="form-label">Precio de Lista <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="precio_lista" id="precio_lista" class="form-control" 
                                           value="<?= $lote['precio_lista'] ?>" 
                                           step="1" min="1" 
                                           <?= !$puedeEditar ? 'readonly' : '' ?> required>
                                </div>
                                <?php if ($puedeEditar): ?>
                                <small class="text-muted">Formato: <span id="precio_formateado"></span></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select name="estado" id="estado" class="form-select" 
                                        <?= !$puedeEditar ? 'disabled' : '' ?>>
                                    <option value="disponible" <?= $lote['estado'] == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                    <option value="reservado" <?= $lote['estado'] == 'reservado' ? 'selected' : '' ?>>Reservado</option>
                                    <option value="vendido" <?= $lote['estado'] == 'vendido' ? 'selected' : '' ?>>Vendido</option>
                                    <option value="bloqueado" <?= $lote['estado'] == 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                                </select>
                                <?php if (!$puedeEditar): ?>
                                    <input type="hidden" name="estado" value="<?= $lote['estado'] ?>">
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Cálculo automático Precio por m² -->
                        <?php if ($puedeEditar): ?>
                        <div class="alert alert-info" id="alertPrecioM2">
                            <strong><i class="bi bi-calculator"></i> Precio por m²:</strong> 
                            <span id="precio_m2" class="fs-5"></span>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="ubicacion" class="form-label">Ubicación / Referencias</label>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control" 
                                   value="<?= htmlspecialchars($lote['ubicacion'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción / Observaciones</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?= htmlspecialchars($lote['descripcion'] ?? '') ?></textarea>
                        </div>

                        <!-- Datos de Venta (se muestran si estado = vendido) -->
                        <div id="datosVenta" style="display: <?= $lote['estado'] == 'vendido' ? 'block' : 'none' ?>;">
                            <h5 class="card-title border-bottom pb-2 mb-3 mt-4">Datos de Venta</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                                    <select name="cliente_id" id="cliente_id" class="form-select" 
                                            <?= !$puedeEditar ? 'disabled' : '' ?>>
                                        <option value="">Seleccione un cliente</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente['id'] ?>" 
                                                    <?= $lote['cliente_id'] == $cliente['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cliente['nombre'] . ' - ' . $cliente['tipo_documento'] . ' ' . $cliente['numero_documento']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!$puedeEditar): ?>
                                        <input type="hidden" name="cliente_id" value="<?= $lote['cliente_id'] ?>">
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="precio_venta" class="form-label">Precio de Venta</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="precio_venta" id="precio_venta" class="form-control" 
                                               value="<?= $lote['precio_venta'] ?? '' ?>" 
                                               step="1" min="1" 
                                               <?= !$puedeEditar ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="fecha_venta" class="form-label">Fecha de Venta</label>
                                <input type="date" name="fecha_venta" id="fecha_venta" class="form-control" 
                                       value="<?= $lote['fecha_venta'] ?? date('Y-m-d') ?>" 
                                       <?= !$puedeEditar ? 'readonly' : '' ?>>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <?php if ($puedeEditar): ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Cambios
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="bi bi-lock"></i> No editable
                            </button>
                            <?php endif; ?>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ==========================================
// CÁLCULOS AUTOMÁTICOS Y FORMATEO
// ==========================================

/**
 * Formatea un número como moneda colombiana
 */
function formatMoney(amount) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

/**
 * Calcula el precio por metro cuadrado
 */
function calcularPrecioM2() {
    const area = parseFloat(document.getElementById('area').value) || 0;
    const precioLista = parseFloat(document.getElementById('precio_lista').value) || 0;
    
    if (area > 0 && precioLista > 0) {
        const precioM2 = Math.round(precioLista / area);
        const precioM2Element = document.getElementById('precio_m2');
        if (precioM2Element) {
            precioM2Element.textContent = formatMoney(precioM2) + '/m²';
        }
    }
}

/**
 * Formatea el precio de lista mientras se escribe
 */
function formatearPrecioLista() {
    const precioLista = parseFloat(document.getElementById('precio_lista').value) || 0;
    const precioFormatElement = document.getElementById('precio_formateado');
    
    if (precioFormatElement && precioLista > 0) {
        precioFormatElement.textContent = formatMoney(precioLista);
    }
    
    // Recalcular precio/m²
    calcularPrecioM2();
}

// Event listeners para cálculos en tiempo real (si puede editar)
<?php if ($puedeEditar): ?>
document.getElementById('area').addEventListener('input', calcularPrecioM2);
document.getElementById('area').addEventListener('change', calcularPrecioM2);
document.getElementById('precio_lista').addEventListener('input', formatearPrecioLista);
document.getElementById('precio_lista').addEventListener('change', formatearPrecioLista);

// Calcular al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    formatearPrecioLista();
    calcularPrecioM2();
});
<?php endif; ?>

// ==========================================
// LÓGICA DE ESTADOS Y VALIDACIONES
// ==========================================

// Mostrar/ocultar campos de venta según estado
document.getElementById('estado').addEventListener('change', function() {
    const datosVenta = document.getElementById('datosVenta');
    const clienteSelect = document.getElementById('cliente_id');
    
    if (this.value === 'vendido' || this.value === 'reservado') {
        datosVenta.style.display = 'block';
        if (!clienteSelect.disabled) {
            clienteSelect.required = (this.value === 'vendido');
        }
    } else {
        datosVenta.style.display = 'none';
        clienteSelect.required = false;
    }
});

// Validación antes de enviar
document.getElementById('formLote').addEventListener('submit', function(e) {
    const estado = document.getElementById('estado').value;
    const clienteId = document.getElementById('cliente_id').value;
    
    if (estado === 'vendido' && !clienteId) {
        e.preventDefault();
        alert('Debe seleccionar un cliente para marcar el lote como vendido');
        return false;
    }
    
    // Confirmación antes de guardar
    if (!confirm('¿Está seguro de guardar los cambios al lote?')) {
        e.preventDefault();
        return false;
    }
});
</script>
