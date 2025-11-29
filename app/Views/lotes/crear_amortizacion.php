<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-calendar-plus"></i> Crear Plan de Amortización
            </h1>
            <p class="text-muted mb-0">
                Lote: <strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong> - 
                Proyecto: <strong><?= htmlspecialchars($lote['proyecto_nombre']) ?></strong>
            </p>
        </div>
        <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Información del Lote -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Información Importante</h5>
                <p class="mb-0">
                    Este formulario genera un plan de amortización con el <strong>método francés (cuota fija)</strong>.
                    El sistema calculará automáticamente la distribución de capital e intereses para cada cuota.
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formulario -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator-fill"></i> Datos del Plan</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/lotes/amortizacion/store" id="formAmortizacion">
                        <?= csrfField() ?>
                        <input type="hidden" name="lote_id" value="<?= $lote['id'] ?>">

                        <!-- Precio de Venta -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Precio de Venta del Lote</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="precio_venta" 
                                       value="<?= $precio_venta ?>" step="0.01" readonly>
                            </div>
                        </div>

                        <!-- Cuota Inicial -->
                        <div class="mb-4">
                            <label for="cuota_inicial" class="form-label fw-bold">
                                Cuota Inicial <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="cuota_inicial" id="cuota_inicial" 
                                       value="<?= $cuota_inicial_sugerida ?>" step="0.01" min="0" required>
                            </div>
                            <small class="form-text text-muted">
                                Sugerido: 30% = <?= formatMoney($cuota_inicial_sugerida) ?>
                            </small>
                        </div>

                        <!-- Monto Financiado (Calculado) -->
                        <div class="mb-4">
                            <label for="monto_financiado" class="form-label fw-bold">
                                Monto a Financiar <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-success text-white">$</span>
                                <input type="number" class="form-control fw-bold bg-light" name="monto_financiado" 
                                       id="monto_financiado" value="<?= $monto_financiado_sugerido ?>" 
                                       step="0.01" min="0" required>
                            </div>
                            <small class="form-text text-muted">
                                Se calcula automáticamente: Precio Venta - Cuota Inicial
                            </small>
                        </div>

                        <!-- Tasa de Interés -->
                        <div class="mb-4">
                            <label for="tasa_interes" class="form-label fw-bold">
                                Tasa de Interés Anual <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="number" class="form-control" name="tasa_interes" id="tasa_interes" 
                                       value="12.00" step="0.01" min="0" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="form-text text-muted">
                                Tasa de interés anual (ej: 12% = 1% mensual)
                            </small>
                        </div>

                        <!-- Número de Cuotas (Plazo) -->
                        <div class="mb-4">
                            <label for="numero_cuotas" class="form-label fw-bold">
                                Número de Cuotas (Plazo en Meses) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="number" class="form-control" name="numero_cuotas" id="numero_cuotas" 
                                       value="24" min="1" max="360" required>
                                <span class="input-group-text">meses</span>
                            </div>
                            <small class="form-text text-muted">
                                Plazo de financiamiento (1 a 360 meses / 30 años)
                            </small>
                        </div>

                        <!-- Fecha de Inicio -->
                        <div class="mb-4">
                            <label for="fecha_inicio" class="form-label fw-bold">
                                Fecha de Inicio del Plan <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg" name="fecha_inicio" 
                                   id="fecha_inicio" value="<?= date('Y-m-d') ?>" required>
                            <small class="form-text text-muted">
                                Primera cuota vencerá un mes después de esta fecha
                            </small>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-4">
                            <label for="observaciones" class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" name="observaciones" id="observaciones" 
                                      rows="3" placeholder="Notas o condiciones especiales del plan"></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#modalConfirmar">
                                <i class="bi bi-check-circle-fill"></i> Generar Plan de Amortización
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel de Simulación -->
        <div class="col-md-4">
            <!-- Vista Previa de Cálculos -->
            <div class="card shadow border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-calculator"></i> Vista Previa</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-muted mb-3">Cuota Mensual Estimada</h6>
                    <h2 class="text-primary fw-bold mb-4" id="cuotaEstimada">$0.00</h2>
                    
                    <hr>
                    
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Monto a Financiar:</dt>
                        <dd class="col-sm-6 text-end" id="previewMonto">$0</dd>

                        <dt class="col-sm-6">Tasa Mensual:</dt>
                        <dd class="col-sm-6 text-end" id="previewTasaMensual">0%</dd>

                        <dt class="col-sm-6">Plazo:</dt>
                        <dd class="col-sm-6 text-end" id="previewPlazo">0 meses</dd>

                        <dt class="col-sm-6 pt-2 border-top">Total a Pagar:</dt>
                        <dd class="col-sm-6 text-end pt-2 border-top fw-bold text-success" id="previewTotal">$0</dd>

                        <dt class="col-sm-6">Total Intereses:</dt>
                        <dd class="col-sm-6 text-end text-warning" id="previewIntereses">$0</dd>
                    </dl>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="card mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información del Lote</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-5">Proyecto:</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($lote['proyecto_nombre']) ?></dd>

                        <dt class="col-sm-5">Código:</dt>
                        <dd class="col-sm-7"><strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong></dd>

                        <dt class="col-sm-5">Cliente:</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($lote['cliente_nombre'] ?? 'Sin asignar') ?></dd>

                        <dt class="col-sm-5">Área:</dt>
                        <dd class="col-sm-7"><?= number_format($lote['area_m2'], 2) ?> m²</dd>

                        <dt class="col-sm-5">Estado:</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-success"><?= ucfirst($lote['estado']) ?></span>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Tips -->
            <div class="card mt-3 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Consejos</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li>Una cuota inicial más alta reduce los intereses totales.</li>
                        <li>Menor plazo = menos intereses, pero cuota más alta.</li>
                        <li>El método francés mantiene la cuota fija durante todo el plazo.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="modalConfirmarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalConfirmarLabel">
                    <i class="bi bi-check-circle"></i> Confirmar Generación de Plan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>¿Desea generar el plan de amortización con los siguientes datos?</strong>
                </div>
                
                <dl class="row mb-0">
                    <dt class="col-sm-6">Monto a Financiar:</dt>
                    <dd class="col-sm-6 text-end fw-bold" id="modalMonto">$0</dd>
                    
                    <dt class="col-sm-6">Tasa de Interés:</dt>
                    <dd class="col-sm-6 text-end" id="modalTasa">0%</dd>
                    
                    <dt class="col-sm-6">Número de Cuotas:</dt>
                    <dd class="col-sm-6 text-end" id="modalCuotas">0 meses</dd>
                    
                    <dt class="col-sm-6 pt-2 border-top">Cuota Mensual:</dt>
                    <dd class="col-sm-6 text-end pt-2 border-top fw-bold text-success" id="modalCuotaMensual">$0</dd>
                </dl>
                
                <div class="alert alert-warning mt-3 mb-0">
                    <small><i class="bi bi-exclamation-triangle"></i> Esta acción generará automáticamente todas las cuotas del plan.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmarGenerar">
                    <i class="bi bi-check-circle-fill"></i> Sí, Generar Plan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const precioVenta = parseFloat(document.getElementById('precio_venta').value);
    const inputCuotaInicial = document.getElementById('cuota_inicial');
    const inputMontoFinanciado = document.getElementById('monto_financiado');
    const inputTasa = document.getElementById('tasa_interes');
    const inputCuotas = document.getElementById('numero_cuotas');
    
    // Calcular monto financiado cuando cambia cuota inicial
    inputCuotaInicial.addEventListener('input', function() {
        const cuotaInicial = parseFloat(this.value) || 0;
        const montoFinanciado = precioVenta - cuotaInicial;
        inputMontoFinanciado.value = Math.max(0, montoFinanciado).toFixed(2);
        actualizarVistaPrevia();
    });

    // Actualizar vista previa en tiempo real
    [inputMontoFinanciado, inputTasa, inputCuotas].forEach(input => {
        input.addEventListener('input', actualizarVistaPrevia);
    });

    function actualizarVistaPrevia() {
        const monto = parseFloat(inputMontoFinanciado.value) || 0;
        const tasaAnual = parseFloat(inputTasa.value) || 0;
        const numCuotas = parseInt(inputCuotas.value) || 1;

        // Calcular tasa mensual
        const tasaMensual = (tasaAnual / 100) / 12;

        // Fórmula del método francés
        let cuotaFija = 0;
        if (tasaMensual > 0) {
            const factor = Math.pow(1 + tasaMensual, numCuotas);
            cuotaFija = monto * (tasaMensual * factor) / (factor - 1);
        } else {
            cuotaFija = monto / numCuotas;
        }

        const totalAPagar = cuotaFija * numCuotas;
        const totalIntereses = totalAPagar - monto;

        // Actualizar vista
        document.getElementById('cuotaEstimada').textContent = formatMoney(cuotaFija);
        document.getElementById('previewMonto').textContent = formatMoney(monto);
        document.getElementById('previewTasaMensual').textContent = (tasaMensual * 100).toFixed(2) + '%';
        document.getElementById('previewPlazo').textContent = numCuotas + ' meses';
        document.getElementById('previewTotal').textContent = formatMoney(totalAPagar);
        document.getElementById('previewIntereses').textContent = formatMoney(totalIntereses);
    }

    function formatMoney(amount) {
        return '$' + parseFloat(amount).toLocaleString('es-CO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Actualizar datos en el modal cuando se abre
    const modal = document.getElementById('modalConfirmar');
    modal.addEventListener('show.bs.modal', function(e) {
        const monto = parseFloat(inputMontoFinanciado.value) || 0;
        const cuotas = parseInt(inputCuotas.value) || 0;
        
        // Validaciones antes de mostrar modal
        if (monto <= 0) {
            e.preventDefault();
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i> El monto a financiar debe ser mayor a cero <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
            return false;
        }
        
        if (cuotas < 1 || cuotas > 360) {
            e.preventDefault();
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i> El número de cuotas debe estar entre 1 y 360 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
            return false;
        }
        
        const tasaAnual = parseFloat(inputTasa.value) || 0;
        const tasaMensual = (tasaAnual / 100) / 12;
        
        let cuotaFija = 0;
        if (tasaMensual > 0) {
            const factor = Math.pow(1 + tasaMensual, cuotas);
            cuotaFija = monto * (tasaMensual * factor) / (factor - 1);
        } else {
            cuotaFija = monto / cuotas;
        }
        
        // Actualizar valores en el modal
        document.getElementById('modalMonto').textContent = formatMoney(monto);
        document.getElementById('modalTasa').textContent = tasaAnual.toFixed(2) + '% anual';
        document.getElementById('modalCuotas').textContent = cuotas + ' meses';
        document.getElementById('modalCuotaMensual').textContent = formatMoney(cuotaFija);
    });
    
    // Botón de confirmar en el modal
    document.getElementById('btnConfirmarGenerar').addEventListener('click', function() {
        const btn = this;
        const form = document.getElementById('formAmortizacion');
        const modalElement = document.getElementById('modalConfirmar');
        
        // Validar que todos los campos requeridos tengan valor
        const camposRequeridos = [
            'lote_id', 'cuota_inicial', 'monto_financiado', 
            'tasa_interes', 'numero_cuotas', 'fecha_inicio'
        ];
        
        let todosCompletos = true;
        let camposFaltantes = [];
        camposRequeridos.forEach(campo => {
            const input = form.elements[campo];
            if (!input || !input.value || input.value.trim() === '') {
                console.error('Campo vacío:', campo);
                camposFaltantes.push(campo);
                todosCompletos = false;
            } else {
                console.log('Campo ' + campo + ':', input.value);
            }
        });
        
        if (!todosCompletos) {
            alert('Campos faltantes: ' + camposFaltantes.join(', '));
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Sí, Generar Plan';
            return;
        }
        
        // Deshabilitar botón y mostrar estado de carga
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';
        
        // Agregar overlay de carga en toda la página
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;';
        overlay.innerHTML = '<div style="background:white;padding:30px;border-radius:10px;text-align:center;"><div class="spinner-border text-primary mb-3" role="status"></div><h4>Generando Plan de Amortización...</h4><p class="text-muted">Por favor espere...</p></div>';
        document.body.appendChild(overlay);
        
        // Cerrar modal
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            modalInstance.hide();
        }
        
        // Enviar formulario después de un pequeño delay
        setTimeout(function() {
            console.log('=== ENVIANDO FORMULARIO ===');
            console.log('Action:', form.action);
            console.log('Method:', form.method);
            console.log('Datos del formulario:');
            const formData = new FormData(form);
            for (let pair of formData.entries()) {
                console.log(pair[0] + ':', pair[1]);
            }
            form.submit();
        }, 300);
    });

    // Inicializar vista previa
    actualizarVistaPrevia();
});
</script>
