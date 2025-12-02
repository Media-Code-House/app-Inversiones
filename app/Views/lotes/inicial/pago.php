<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-cash-coin"></i> Registrar Pago Inicial
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
                        <div class="col-md-3">
                            <strong>Proyecto:</strong><br>
                            <?= htmlspecialchars($proyecto['nombre']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Código Lote:</strong><br>
                            <?= htmlspecialchars($lote['codigo_lote']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Cliente:</strong><br>
                            <?= htmlspecialchars($cliente['nombre']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Estado:</strong><br>
                            <span class="badge bg-<?= statusClass($lote['estado']) ?>">
                                <?= strtoupper($lote['estado']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen del Plan Inicial -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-pie-chart"></i> Resumen del Plan de Pago Inicial
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <small class="text-muted">Monto Inicial Total</small>
                                <h4 class="mb-0 text-primary">
                                    $<?= number_format($planInicial['monto_inicial_total_requerido'], 0, ',', '.') ?>
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <small class="text-muted">Total Pagado</small>
                                <h4 class="mb-0 text-success">
                                    $<?= number_format($totalPagado, 0, ',', '.') ?>
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <small class="text-muted">Saldo Pendiente</small>
                                <h4 class="mb-0 text-danger">
                                    $<?= number_format($saldoPendiente, 0, ',', '.') ?>
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <small class="text-muted">Plazo</small>
                                <h4 class="mb-0">
                                    <?= $planInicial['plazo_meses'] ?> meses
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Barra de Progreso -->
                    <div class="mt-3">
                        <?php 
                            $porcentajePagado = $planInicial['monto_inicial_total_requerido'] > 0 
                                ? round(($totalPagado / $planInicial['monto_inicial_total_requerido']) * 100, 1) 
                                : 0;
                        ?>
                        <label class="form-label">Progreso del Pago Inicial</label>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?= $porcentajePagado ?>%;" 
                                 aria-valuenow="<?= $porcentajePagado ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= $porcentajePagado ?>%
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <small class="text-muted">Cuota Mensual Estimada:</small><br>
                            <strong>$<?= number_format($planInicial['cuota_mensual'], 0, ',', '.') ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Fecha de Inicio:</small><br>
                            <strong><?= date('d/m/Y', strtotime($planInicial['fecha_inicio'])) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Registro de Pago -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-plus-circle"></i> Registrar Nuevo Pago
                </div>
                <div class="card-body">
                    <form method="POST" action="/lotes/inicial/registrar-pago/<?= $lote['id'] ?>" id="formPago">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="valor_pagado" class="form-label">
                                    Valor del Pago <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="valor_pagado" id="valor_pagado" 
                                           class="form-control" step="1" min="1" 
                                           max="<?= $saldoPendiente ?>" required
                                           placeholder="Ingrese el valor">
                                </div>
                                <small class="text-muted">
                                    Máximo permitido: $<?= number_format($saldoPendiente, 0, ',', '.') ?>
                                </small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="fecha_pago" class="form-label">
                                    Fecha del Pago <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="fecha_pago" id="fecha_pago" 
                                       class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

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

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" 
                                      rows="2" placeholder="Notas adicionales..."></textarea>
                        </div>

                        <!-- Botones de Acciones Rápidas -->
                        <div class="mb-3">
                            <label class="form-label">Acciones Rápidas:</label><br>
                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                    onclick="setValor(<?= $planInicial['cuota_mensual'] ?>)">
                                Pagar Cuota Mensual ($<?= number_format($planInicial['cuota_mensual'], 0) ?>)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" 
                                    onclick="setValor(<?= $saldoPendiente ?>)">
                                Pagar Todo el Saldo ($<?= number_format($saldoPendiente, 0) ?>)
                            </button>
                        </div>

                        <!-- Alerta informativa -->
                        <?php if ($saldoPendiente <= $planInicial['cuota_mensual'] * 1.5): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> 
                            <strong>¡Casi termina!</strong> El saldo pendiente es menor o igual a una cuota y media.
                            Al completar el pago, el lote cambiará automáticamente a estado VENDIDO.
                        </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Registrar Pago
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Historial de Pagos -->
            <?php if (!empty($pagos)): ?>
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-clock-history"></i> Historial de Pagos
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Método</th>
                                    <th class="text-end">Valor Pagado</th>
                                    <th class="text-end">Saldo Después</th>
                                    <th>Recibo</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= ucfirst($pago['metodo_pago']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end text-success">
                                        <strong>$<?= number_format($pago['valor_pagado'], 0, ',', '.') ?></strong>
                                    </td>
                                    <td class="text-end">
                                        $<?= number_format($pago['saldo_pendiente_despues'], 0, ',', '.') ?>
                                    </td>
                                    <td><?= htmlspecialchars($pago['numero_recibo'] ?? '-') ?></td>
                                    <td>
                                        <small><?= htmlspecialchars($pago['observaciones'] ?? '-') ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function setValor(valor) {
    document.getElementById('valor_pagado').value = Math.round(valor);
}

// Validación en tiempo real
document.getElementById('valor_pagado').addEventListener('input', function() {
    const valorPagado = parseFloat(this.value) || 0;
    const saldoPendiente = <?= $saldoPendiente ?>;

    if (valorPagado > saldoPendiente) {
        this.setCustomValidity('El valor no puede exceder el saldo pendiente');
    } else if (valorPagado <= 0) {
        this.setCustomValidity('El valor debe ser mayor a cero');
    } else {
        this.setCustomValidity('');
    }
});

// Validación antes de enviar
document.getElementById('formPago').addEventListener('submit', function(e) {
    const valorPagado = parseFloat(document.getElementById('valor_pagado').value) || 0;
    const saldoPendiente = <?= $saldoPendiente ?>;

    if (valorPagado > saldoPendiente) {
        e.preventDefault();
        alert('El valor del pago no puede exceder el saldo pendiente de $' + saldoPendiente.toLocaleString('es-CO'));
        return false;
    }

    if (valorPagado <= 0) {
        e.preventDefault();
        alert('El valor del pago debe ser mayor a cero');
        return false;
    }
});
</script>
