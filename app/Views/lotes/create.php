<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-plus-circle"></i> Crear Nuevo Lote
                </h1>
                <a href="/lotes" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/lotes/store" id="formLote">
                        
                        <!-- Información del Proyecto -->
                        <h5 class="card-title border-bottom pb-2 mb-3">Información del Proyecto</h5>
                        
                        <div class="mb-3">
                            <label for="proyecto_id" class="form-label">Proyecto <span class="text-danger">*</span></label>
                            <select name="proyecto_id" id="proyecto_id" class="form-select" required>
                                <option value="">Seleccione un proyecto</option>
                                <?php foreach ($proyectos as $proyecto): ?>
                                    <option value="<?= $proyecto['id'] ?>">
                                        <?= htmlspecialchars($proyecto['codigo'] . ' - ' . $proyecto['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Información del Lote -->
                        <h5 class="card-title border-bottom pb-2 mb-3 mt-4">Información del Lote</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo_lote" class="form-label">Código del Lote <span class="text-danger">*</span></label>
                                <input type="text" name="codigo_lote" id="codigo_lote" class="form-control" 
                                       placeholder="Ej: L-001, M-15, etc." required>
                                <small class="text-muted">Código único dentro del proyecto</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="area" class="form-label">Área (m²) <span class="text-danger">*</span></label>
                                <input type="number" name="area" id="area" class="form-control" 
                                       step="0.01" min="0.01" placeholder="Ej: 120.50" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="precio_lista" class="form-label">Precio de Lista <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="precio_lista" id="precio_lista" class="form-control" 
                                           step="1" min="1" placeholder="Ej: 50000000" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select name="estado" id="estado" class="form-select">
                                    <option value="disponible" selected>Disponible</option>
                                    <option value="reservado">Reservado</option>
                                    <option value="vendido">Vendido</option>
                                    <option value="bloqueado">Bloqueado</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="ubicacion" class="form-label">Ubicación / Referencias</label>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control" 
                                   placeholder="Ej: Manzana 3, Esquina norte, etc.">
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción / Observaciones</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="3" 
                                      placeholder="Características adicionales, notas, etc."></textarea>
                        </div>

                        <!-- Datos de Venta (se muestran si estado = vendido) -->
                        <div id="datosVenta" style="display: none;">
                            <h5 class="card-title border-bottom pb-2 mb-3 mt-4">Datos de Venta</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                                    <select name="cliente_id" id="cliente_id" class="form-select">
                                        <option value="">Seleccione un cliente</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente['id'] ?>">
                                                <?= htmlspecialchars($cliente['nombre'] . ' - ' . $cliente['tipo_documento'] . ' ' . $cliente['numero_documento']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Si no existe, créelo primero en Clientes</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="precio_venta" class="form-label">Precio de Venta</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="precio_venta" id="precio_venta" class="form-control" 
                                               step="1" min="1" placeholder="Opcional (usa precio lista si está vacío)">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="fecha_venta" class="form-label">Fecha de Venta</label>
                                <input type="date" name="fecha_venta" id="fecha_venta" class="form-control" 
                                       value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="/lotes" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Lote
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar campos de venta según estado
document.getElementById('estado').addEventListener('change', function() {
    const datosVenta = document.getElementById('datosVenta');
    const clienteSelect = document.getElementById('cliente_id');
    
    if (this.value === 'vendido') {
        datosVenta.style.display = 'block';
        clienteSelect.required = true;
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
});
</script>
