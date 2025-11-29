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

                            <!-- Selector de opción: Cliente existente o nuevo -->
                            <div class="mb-3">
                                <label class="form-label">Opciones de Cliente</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="opcion_cliente" id="opcion_existente" value="existente" checked>
                                    <label class="btn btn-outline-primary" for="opcion_existente">Cliente Existente</label>
                                    
                                    <input type="radio" class="btn-check" name="opcion_cliente" id="opcion_nuevo" value="nuevo">
                                    <label class="btn btn-outline-success" for="opcion_nuevo">Crear Cliente Rápido</label>
                                </div>
                            </div>

                            <!-- Cliente Existente -->
                            <div id="clienteExistente" class="mb-3">
                                <label for="cliente_id" class="form-label">Seleccionar Cliente <span class="text-danger">*</span></label>
                                <select name="cliente_id" id="cliente_id" class="form-select">
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= $cliente['id'] ?>">
                                            <?= htmlspecialchars($cliente['nombre'] . ' - ' . $cliente['tipo_documento'] . ' ' . $cliente['numero_documento']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Crear Cliente Nuevo (formulario rápido) -->
                            <div id="clienteNuevo" style="display: none;">
                                <input type="hidden" name="nuevo_cliente" id="nuevo_cliente" value="0">
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> <strong>Creación Rápida:</strong> Solo datos esenciales. Podrás completar la información después.
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="cliente_tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                        <select name="cliente_tipo_documento" id="cliente_tipo_documento" class="form-select">
                                            <option value="CC">Cédula de Ciudadanía</option>
                                            <option value="CE">Cédula de Extranjería</option>
                                            <option value="NIT">NIT</option>
                                            <option value="Pasaporte">Pasaporte</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <label for="cliente_numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
                                        <input type="text" name="cliente_numero_documento" id="cliente_numero_documento" class="form-control" 
                                               placeholder="Ej: 1234567890">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="cliente_nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" name="cliente_nombre" id="cliente_nombre" class="form-control" 
                                           placeholder="Nombre completo del cliente">
                                </div>

                                <div class="mb-3">
                                    <label for="cliente_telefono" class="form-label">Teléfono</label>
                                    <input type="text" name="cliente_telefono" id="cliente_telefono" class="form-control" 
                                           placeholder="Opcional">
                                </div>
                            </div>

                            <!-- Datos financieros de la venta -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio_venta" class="form-label">Precio de Venta</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="precio_venta" id="precio_venta" class="form-control" 
                                               step="1" min="1" placeholder="Opcional (usa precio lista si está vacío)">
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
    
    if (this.value === 'vendido') {
        datosVenta.style.display = 'block';
    } else {
        datosVenta.style.display = 'none';
    }
});

// Alternar entre cliente existente y nuevo
document.querySelectorAll('input[name="opcion_cliente"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const clienteExistente = document.getElementById('clienteExistente');
        const clienteNuevo = document.getElementById('clienteNuevo');
        const nuevoClienteInput = document.getElementById('nuevo_cliente');
        const clienteSelect = document.getElementById('cliente_id');
        
        if (this.value === 'existente') {
            clienteExistente.style.display = 'block';
            clienteNuevo.style.display = 'none';
            nuevoClienteInput.value = '0';
            clienteSelect.required = true;
            
            // Limpiar campos de nuevo cliente
            document.getElementById('cliente_tipo_documento').required = false;
            document.getElementById('cliente_numero_documento').required = false;
            document.getElementById('cliente_nombre').required = false;
        } else {
            clienteExistente.style.display = 'none';
            clienteNuevo.style.display = 'block';
            nuevoClienteInput.value = '1';
            clienteSelect.required = false;
            clienteSelect.value = '';
            
            // Hacer requeridos los campos de nuevo cliente
            document.getElementById('cliente_tipo_documento').required = true;
            document.getElementById('cliente_numero_documento').required = true;
            document.getElementById('cliente_nombre').required = true;
        }
    });
});

// Validación antes de enviar
document.getElementById('formLote').addEventListener('submit', function(e) {
    const estado = document.getElementById('estado').value;
    
    if (estado === 'vendido') {
        const opcionCliente = document.querySelector('input[name="opcion_cliente"]:checked').value;
        
        if (opcionCliente === 'existente') {
            const clienteId = document.getElementById('cliente_id').value;
            if (!clienteId) {
                e.preventDefault();
                alert('Debe seleccionar un cliente para marcar el lote como vendido');
                return false;
            }
        } else {
            const tipoDoc = document.getElementById('cliente_tipo_documento').value;
            const numDoc = document.getElementById('cliente_numero_documento').value;
            const nombre = document.getElementById('cliente_nombre').value;
            
            if (!tipoDoc || !numDoc || !nombre) {
                e.preventDefault();
                alert('Para crear un nuevo cliente, complete: Tipo de documento, Número de documento y Nombre');
                return false;
            }
        }
    }
});
</script>
