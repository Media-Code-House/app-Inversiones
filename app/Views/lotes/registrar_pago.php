<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-cash-coin"></i> Registrar Pago
            </h1>
            <p class="text-muted mb-0">
                Lote: <strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong> - 
                Cliente: <strong><?= htmlspecialchars($lote['cliente_nombre']) ?></strong>
            </p>
        </div>
        <a href="/lotes/amortizacion/<?= $lote['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Plan
        </a>
    </div>

    <div class="row">
        <!-- Formulario de Pago -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-wallet2"></i> Datos del Pago</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/pagos/store" id="formPago">
                        <?= csrfField() ?>
                        <input type="hidden" name="lote_id" value="<?= $lote['id'] ?>">

                        <!-- Selección de Cuota -->
                        <div class="mb-3">
                            <label for="amortizacion_id" class="form-label">
                                Cuota a Pagar <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="amortizacion_id" id="amortizacion_id" required>
                                <option value="">-- Seleccione una cuota --</option>
                                <?php foreach ($cuotas_pendientes as $cuota): ?>
                                    <?php
                                    $selected = (!empty($_GET['cuota_id']) && $_GET['cuota_id'] == $cuota['id']) ? 'selected' : '';
                                    $mora = $cuota['dias_mora'] > 0 ? ' (MORA: ' . $cuota['dias_mora'] . ' días)' : '';
                                    ?>
                                    <option value="<?= $cuota['id'] ?>" 
                                            data-valor="<?= $cuota['valor_cuota'] ?>"
                                            data-pagado="<?= $cuota['valor_pagado'] ?>"
                                            data-saldo="<?= $cuota['saldo_pendiente'] ?>"
                                            <?= $selected ?>>
                                        Cuota N° <?= $cuota['numero_cuota'] ?> - 
                                        Venc: <?= formatDate($cuota['fecha_vencimiento']) ?> - 
                                        Saldo: <?= formatMoney($cuota['saldo_pendiente']) ?>
                                        <?= $mora ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Seleccione la cuota que desea abonar o pagar completamente
                            </small>
                        </div>

                        <!-- Información de la Cuota Seleccionada -->
                        <div id="infoCuota" class="alert alert-info d-none mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Valor Cuota:</strong><br>
                                    <span id="valorCuota" class="fs-5">$0</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Ya Pagado:</strong><br>
                                    <span id="yaPagado" class="fs-5 text-success">$0</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Saldo Pendiente:</strong><br>
                                    <span id="saldoPendiente" class="fs-5 text-danger">$0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Monto del Pago -->
                        <div class="mb-3">
                            <label for="monto" class="form-label">
                                Monto del Pago <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="monto" id="monto" 
                                       step="0.01" min="0" required>
                            </div>
                            <small class="form-text text-muted">
                                Puede pagar el valor total o hacer un abono parcial
                            </small>
                        </div>

                        <!-- Botones de Pago Rápido -->
                        <div class="mb-3">
                            <label class="form-label">Opciones Rápidas</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnPagarSaldo">
                                    Pagar Saldo Total
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPagar50">
                                    Pagar 50%
                                </button>
                            </div>
                        </div>

                        <!-- Fecha del Pago -->
                        <div class="mb-3">
                            <label for="fecha_pago" class="form-label">
                                Fecha del Pago <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" name="fecha_pago" id="fecha_pago" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Método de Pago -->
                        <div class="mb-3">
                            <label for="metodo_pago" class="form-label">
                                Método de Pago <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="metodo_pago" id="metodo_pago" required>
                                <option value="">-- Seleccione --</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                                <option value="cheque">Cheque</option>
                                <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                            </select>
                        </div>

                        <!-- Referencia/Comprobante -->
                        <div class="mb-3">
                            <label for="referencia" class="form-label">Referencia/Comprobante</label>
                            <input type="text" class="form-control" name="referencia" id="referencia" 
                                   placeholder="Número de transferencia, cheque, etc.">
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" id="observaciones" 
                                      rows="3" placeholder="Notas adicionales sobre este pago"></textarea>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/lotes/amortizacion/<?= $lote['id'] ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Registrar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Resumen del Lote -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información del Lote</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Proyecto:</dt>
                        <dd class="col-sm-6"><?= htmlspecialchars($lote['proyecto_nombre']) ?></dd>

                        <dt class="col-sm-6">Código Lote:</dt>
                        <dd class="col-sm-6"><strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong></dd>

                        <dt class="col-sm-6">Cliente:</dt>
                        <dd class="col-sm-6"><?= htmlspecialchars($lote['cliente_nombre']) ?></dd>

                        <dt class="col-sm-6">Área:</dt>
                        <dd class="col-sm-6"><?= number_format($lote['area_m2'], 2) ?> m²</dd>

                        <dt class="col-sm-6">Precio Lista:</dt>
                        <dd class="col-sm-6"><strong><?= formatMoney($lote['precio_lista']) ?></strong></dd>
                    </dl>
                </div>
            </div>

            <!-- Cuotas Pendientes -->
            <?php if (!empty($cuotas_pendientes)): ?>
            <div class="card mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-calendar-x"></i> Resumen de Cuotas Pendientes</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Total Cuotas Pendientes:</strong> <?= count($cuotas_pendientes) ?>
                    </p>
                    <p class="mb-2">
                        <strong>Total Adeudado:</strong> 
                        <span class="text-danger">
                            <?php
                            $totalAdeudado = array_sum(array_column($cuotas_pendientes, 'saldo_pendiente'));
                            echo formatMoney($totalAdeudado);
                            ?>
                        </span>
                    </p>
                    <?php
                    $cuotasVencidas = array_filter($cuotas_pendientes, fn($c) => $c['dias_mora'] > 0);
                    if (!empty($cuotasVencidas)):
                    ?>
                    <div class="alert alert-danger py-2 mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i> 
                            <strong><?= count($cuotasVencidas) ?></strong> cuota(s) en mora
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectCuota = document.getElementById('amortizacion_id');
    const inputMonto = document.getElementById('monto');
    const infoCuota = document.getElementById('infoCuota');
    const btnPagarSaldo = document.getElementById('btnPagarSaldo');
    const btnPagar50 = document.getElementById('btnPagar50');
    
    let saldoActual = 0;

    // Actualizar información cuando se selecciona una cuota
    selectCuota.addEventListener('change', function() {
        const option = this.selectedOptions[0];
        
        if (option.value) {
            const valorCuota = parseFloat(option.dataset.valor);
            const valorPagado = parseFloat(option.dataset.pagado);
            const saldoPendiente = parseFloat(option.dataset.saldo);
            
            saldoActual = saldoPendiente;
            
            document.getElementById('valorCuota').textContent = formatMoney(valorCuota);
            document.getElementById('yaPagado').textContent = formatMoney(valorPagado);
            document.getElementById('saldoPendiente').textContent = formatMoney(saldoPendiente);
            
            infoCuota.classList.remove('d-none');
            inputMonto.max = saldoPendiente;
            inputMonto.value = saldoPendiente;
        } else {
            infoCuota.classList.add('d-none');
            inputMonto.value = '';
            saldoActual = 0;
        }
    });

    // Botón pagar saldo total
    btnPagarSaldo.addEventListener('click', function() {
        if (saldoActual > 0) {
            inputMonto.value = saldoActual.toFixed(2);
        }
    });

    // Botón pagar 50%
    btnPagar50.addEventListener('click', function() {
        if (saldoActual > 0) {
            inputMonto.value = (saldoActual / 2).toFixed(2);
        }
    });

    // Validación del formulario
    document.getElementById('formPago').addEventListener('submit', function(e) {
        const monto = parseFloat(inputMonto.value);
        
        if (monto <= 0) {
            e.preventDefault();
            alert('El monto debe ser mayor a cero');
            return false;
        }
        
        if (monto > saldoActual) {
            e.preventDefault();
            alert('El monto no puede ser mayor al saldo pendiente');
            return false;
        }
        
        if (!confirm('¿Confirma el registro de este pago por ' + formatMoney(monto) + '?')) {
            e.preventDefault();
            return false;
        }
    });

    // Si hay cuota_id en URL, disparar el evento change
    if (selectCuota.value) {
        selectCuota.dispatchEvent(new Event('change'));
    }
});

function formatMoney(amount) {
    return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}
</script>
