<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-credit-card-2-front"></i> Crear Plan de Pago Inicial Diferido
                </h1>
                <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Lote
                </a>
            </div>

            <!-- Información del Lote -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle"></i> Información del Lote
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Proyecto:</strong><br>
                            <?= htmlspecialchars($proyecto['nombre']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Código Lote:</strong><br>
                            <?= htmlspecialchars($lote['codigo_lote']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Cliente:</strong><br>
                            <?= htmlspecialchars($cliente['nombre']) ?>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <strong>Precio Lista:</strong><br>
                            <span class="fs-5 text-primary">
                                $<?= number_format($lote['precio_lista'], 0, ',', '.') ?>
                            </span>
                        </div>
                        <div class="col-md-4">
                            <strong>Área:</strong><br>
                            <?= number_format($lote['area_m2'], 2) ?> m²
                        </div>
                        <div class="col-md-4">
                            <strong>Estado Actual:</strong><br>
                            <span class="badge bg-<?= statusClass($lote['estado']) ?>">
                                <?= strtoupper($lote['estado']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerta Informativa -->
            <div class="alert alert-info" role="alert">
                <h5 class="alert-heading"><i class="bi bi-lightbulb"></i> ¿Qué es el Plan de Pago Inicial Diferido?</h5>
                <p class="mb-0">
                    Este plan permite al cliente pagar la <strong>inicial del lote en cuotas mensuales</strong> 
                    antes de generar el plan de amortización principal. 
                    <br><br>
                    <strong>Flujo:</strong>
                </p>
                <ol class="mb-0">
                    <li>El lote cambiará a estado <span class="badge bg-warning">RESERVADO</span></li>
                    <li>El cliente pagará la inicial en las cuotas que defina aquí</li>
                    <li>Al completar el pago inicial, el lote cambiará automáticamente a <span class="badge bg-success">VENDIDO</span></li>
                    <li>Recién entonces podrá crear el plan de amortización principal</li>
                </ol>
            </div>

            <!-- Formulario -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/lotes/inicial/store/<?= $lote['id'] ?>" id="formPlanInicial">
                        
                        <h5 class="card-title border-bottom pb-2 mb-4">Configuración del Plan</h5>

                        <div class="row">
                            <!-- Monto Inicial Total -->
                            <div class="col-md-6 mb-3">
                                <label for="monto_inicial_total" class="form-label">
                                    Monto Inicial Total Requerido <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="monto_inicial_total" id="monto_inicial_total" 
                                           class="form-control" step="1" min="1" required
                                           placeholder="Ej: 10000000">
                                </div>
                                <small class="text-muted">Total de la inicial que el cliente debe pagar</small>
                            </div>

                            <!-- Monto Pagado Hoy -->
                            <div class="col-md-6 mb-3">
                                <label for="monto_pagado_hoy" class="form-label">
                                    Abono Inicial (Hoy) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="monto_pagado_hoy" id="monto_pagado_hoy" 
                                           class="form-control" step="1" min="0" value="0" required
                                           placeholder="Ej: 2000000">
                                </div>
                                <small class="text-muted">Cantidad que el cliente paga hoy (puede ser 0)</small>
                            </div>
                        </div>

                        <!-- Cálculo Automático -->
                        <div class="alert alert-secondary" id="calculoAutomatico">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Monto a Diferir:</strong>
                                    <p class="mb-0 fs-5 text-primary">
                                        $<span id="monto_diferir">0</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Plazo en Meses -->
                            <div class="col-md-4 mb-3">
                                <label for="plazo_meses" class="form-label">
                                    Plazo (Meses) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="plazo_meses" id="plazo_meses" 
                                       class="form-control" min="1" max="120" value="6" required>
                                <small class="text-muted">Número de cuotas mensuales</small>
                            </div>

                            <!-- Cuota Mensual Calculada -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Cuota Mensual Estimada</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" id="cuota_mensual_display" class="form-control" 
                                           readonly value="0" style="background-color: #e9ecef;">
                                </div>
                                <small class="text-muted">Se calcula automáticamente</small>
                            </div>

                            <!-- Fecha de Inicio -->
                            <div class="col-md-4 mb-3">
                                <label for="fecha_inicio" class="form-label">
                                    Fecha de Inicio <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" 
                                       class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <!-- Datos del Pago Inicial (si hay abono hoy) -->
                        <div id="datosPargoHoy" style="display: none;">
                            <h5 class="card-title border-bottom pb-2 mb-3 mt-4">
                                Datos del Pago Inicial (Hoy)
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                                    <select name="metodo_pago" id="metodo_pago" class="form-select">
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia" selected>Transferencia</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="tarjeta">Tarjeta</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="numero_recibo" class="form-label">Número de Recibo</label>
                                    <input type="text" name="numero_recibo" id="numero_recibo" 
                                           class="form-control" placeholder="Opcional">
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" 
                                      rows="3" placeholder="Notas adicionales sobre el plan..."></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crear Plan de Pago Inicial
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ==========================================
// CÁLCULOS AUTOMÁTICOS
// ==========================================

function formatMoney(amount) {
    return new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

function calcularPlanInicial() {
    const montoTotal = parseFloat(document.getElementById('monto_inicial_total').value) || 0;
    const montoPagadoHoy = parseFloat(document.getElementById('monto_pagado_hoy').value) || 0;
    const plazoMeses = parseInt(document.getElementById('plazo_meses').value) || 1;

    // Calcular monto a diferir
    const montoDiferir = Math.max(0, montoTotal - montoPagadoHoy);
    
    // Calcular cuota mensual
    const cuotaMensual = montoDiferir > 0 ? Math.round(montoDiferir / plazoMeses) : 0;

    // Actualizar interfaz
    document.getElementById('monto_diferir').textContent = formatMoney(montoDiferir);
    document.getElementById('cuota_mensual_display').value = formatMoney(cuotaMensual);

    // Mostrar/ocultar sección de datos del pago si hay abono hoy
    const datosPagoHoy = document.getElementById('datosPargoHoy');
    if (montoPagadoHoy > 0) {
        datosPagoHoy.style.display = 'block';
    } else {
        datosPagoHoy.style.display = 'none';
    }

    // Validación
    if (montoPagadoHoy > montoTotal) {
        document.getElementById('monto_pagado_hoy').setCustomValidity('El abono no puede ser mayor al monto total');
    } else {
        document.getElementById('monto_pagado_hoy').setCustomValidity('');
    }
}

// Event listeners
document.getElementById('monto_inicial_total').addEventListener('input', calcularPlanInicial);
document.getElementById('monto_inicial_total').addEventListener('change', calcularPlanInicial);
document.getElementById('monto_pagado_hoy').addEventListener('input', calcularPlanInicial);
document.getElementById('monto_pagado_hoy').addEventListener('change', calcularPlanInicial);
document.getElementById('plazo_meses').addEventListener('input', calcularPlanInicial);
document.getElementById('plazo_meses').addEventListener('change', calcularPlanInicial);

// Calcular al cargar
document.addEventListener('DOMContentLoaded', calcularPlanInicial);

// Validación antes de enviar
document.getElementById('formPlanInicial').addEventListener('submit', function(e) {
    const montoTotal = parseFloat(document.getElementById('monto_inicial_total').value) || 0;
    const montoPagadoHoy = parseFloat(document.getElementById('monto_pagado_hoy').value) || 0;

    if (montoPagadoHoy > montoTotal) {
        e.preventDefault();
        alert('El abono inicial no puede ser mayor al monto total requerido');
        document.getElementById('monto_pagado_hoy').focus();
        return false;
    }

    if (montoTotal <= 0) {
        e.preventDefault();
        alert('El monto inicial total debe ser mayor a cero');
        document.getElementById('monto_inicial_total').focus();
        return false;
    }
});
</script>
