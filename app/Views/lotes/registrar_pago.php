<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-cash-coin"></i> Registrar Pago
            </h1>
            <p class="text-muted mb-0">
                Lote: <strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong> - 
                Cliente: <strong><?= htmlspecialchars($lote['cliente_nombre'] ?? 'Sin asignar') ?></strong>
            </p>
        </div>
        <a href="/lotes/amortizacion/show/<?= $lote['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Plan
        </a>
    </div>

    <!-- Información del Lote (Superior) -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center border-primary">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Código</p>
                    <h6 class="mb-0 fw-bold text-primary"><?= htmlspecialchars($lote['codigo_lote']) ?></h6>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Proyecto</p>
                    <h6 class="mb-0"><?= htmlspecialchars($lote['proyecto_nombre']) ?></h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Cliente</p>
                    <h6 class="mb-0"><?= htmlspecialchars($lote['cliente_nombre'] ?? 'Sin asignar') ?></h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Monto Financiado</p>
                    <h6 class="mb-0 fw-bold text-info"><?= formatMoney($monto_financiado) ?></h6>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="/lotes/pago/store" id="formPago">
        <?= csrfField() ?>
        <input type="hidden" name="lote_id" value="<?= $lote['id'] ?>">

        <div class="row">
            <!-- Columna Izquierda: Formulario de Pago -->
            <div class="col-md-8">
                <!-- Sección: Distribución del Pago (Placeholder Interactivo) -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Distribución del Pago</h5>
                    </div>
                    <div class="card-body">
                        <div id="distribucionPlaceholder" class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Ingrese el monto del pago para ver la distribución automática.
                        </div>
                        <div id="distribucionResultado" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Cuota #</th>
                                            <th class="text-end">Saldo Anterior</th>
                                            <th class="text-end">Monto Aplicado</th>
                                            <th class="text-end">Nuevo Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="distribucionTabla"></tbody>
                                    <tfoot class="table-light">
                                        <tr class="fw-bold">
                                            <td colspan="2" class="text-end">TOTAL APLICADO:</td>
                                            <td class="text-end text-success" id="totalAplicado">$0</td>
                                            <td></td>
                                        </tr>
                                        <tr id="excedenteRow" class="d-none">
                                            <td colspan="3" class="text-end text-warning">EXCEDENTE:</td>
                                            <td class="text-end text-warning fw-bold" id="excedenteValor">$0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección: Datos del Pago -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-wallet2"></i> Datos del Pago</h5>
                    </div>
                    <div class="card-body">
                        <!-- Monto del Pago -->
                        <div class="mb-3">
                            <label for="monto_pago" class="form-label">
                                Monto del Pago <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control form-control-lg" 
                                       name="monto_pago" id="monto_pago" 
                                       step="0.01" min="0" max="<?= $saldo_total_pendiente ?>" required>
                            </div>
                            <small class="form-text text-muted">
                                Saldo total pendiente: <strong class="text-danger"><?= formatMoney($saldo_total_pendiente) ?></strong>
                            </small>
                        </div>

                        <!-- Fecha del Pago -->
                        <div class="mb-3">
                            <label for="fecha_pago" class="form-label">
                                Fecha del Pago <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" name="fecha_pago" id="fecha_pago" 
                                   value="<?= $fecha_hoy ?>" required>
                        </div>

                        <!-- Método de Pago -->
                        <div class="mb-3">
                            <label for="metodo_pago" class="form-label">
                                Método de Pago <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="metodo_pago" id="metodo_pago" required>
                                <option value="">-- Seleccione --</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia" selected>Transferencia Bancaria</option>
                                <option value="cheque">Cheque</option>
                                <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                <option value="consignacion">Consignación</option>
                            </select>
                        </div>

                        <!-- Referencia/Comprobante -->
                        <div class="mb-3">
                            <label for="referencia" class="form-label">Referencia/Comprobante</label>
                            <input type="text" class="form-control" name="referencia" id="referencia" 
                                   placeholder="Número de transferencia, cheque, recibo, etc.">
                            <small class="text-muted">Opcional: número de transacción o comprobante</small>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" id="observaciones" 
                                      rows="3" placeholder="Notas adicionales sobre este pago"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Sección: Opciones de Excedente (Radio Buttons) -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Opciones de Excedente</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="bi bi-info-circle"></i> 
                            Si el monto ingresado excede el total de las cuotas pendientes, elija cómo manejar el excedente:
                        </p>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="opcion_excedente" 
                                   id="opcion_capital" value="aplicar_capital" checked>
                            <label class="form-check-label fw-bold" for="opcion_capital">
                                <i class="bi bi-calculator-fill text-success"></i> 
                                Aplicar como abono a capital (recalcular tabla de amortización)
                            </label>
                            <small class="d-block text-muted ms-4">
                                El excedente reduce el saldo total financiado y se recalculan las cuotas futuras.
                            </small>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="opcion_excedente" 
                                   id="opcion_siguientes" value="pagar_siguientes">
                            <label class="form-check-label fw-bold" for="opcion_siguientes">
                                <i class="bi bi-calendar-plus-fill text-info"></i> 
                                Usar excedente para pagar próximas cuotas (sin recalcular)
                            </label>
                            <small class="d-block text-muted ms-4">
                                El excedente se aplica a las siguientes cuotas en orden cronológico.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Sección: Selección de Cuotas -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-check-square"></i> Selección de Cuotas 
                            <small class="text-muted">(Opcional)</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="bi bi-info-circle"></i> 
                            Por defecto, el pago se aplicará a las cuotas más antiguas. 
                            Seleccione cuotas específicas si desea personalizar:
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Cuota #</th>
                                        <th>Vencimiento</th>
                                        <th class="text-end">Saldo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cuotas_pendientes as $cuota): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="cuotas_seleccionadas[]" 
                                                   value="<?= $cuota['id'] ?>" 
                                                   class="form-check-input cuota-check"
                                                   data-saldo="<?= $cuota['saldo_pendiente'] ?>">
                                        </td>
                                        <td><strong><?= $cuota['numero_cuota'] ?></strong></td>
                                        <td><?= formatDate($cuota['fecha_vencimiento']) ?></td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= formatMoney($cuota['saldo_pendiente']) ?>
                                        </td>
                                        <td>
                                            <?php if (isset($cuota['dias_mora']) && $cuota['dias_mora'] > 0): ?>
                                                <span class="badge bg-danger">
                                                    Mora: <?= $cuota['dias_mora'] ?> días
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="/lotes/amortizacion/show/<?= $lote['id'] ?>" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle-fill"></i> Registrar Pago
                    </button>
                </div>
            </div>

            <!-- Columna Derecha: Resumen -->
            <div class="col-md-4">
                <!-- Resumen Financiero -->
                <div class="card mb-3 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-cash-stack"></i> Resumen Financiero</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        // Obtener el valor de la cuota desde el resumen o desde la primera cuota pendiente
                        $valor_cuota = $resumen['valor_cuota_mensual'] ?? null;
                        if (!$valor_cuota && !empty($cuotas_pendientes)) {
                            $valor_cuota = $cuotas_pendientes[0]['valor_cuota'] ?? 0;
                        }
                        ?>
                        <?php if ($valor_cuota && $valor_cuota > 0): ?>
                        <div class="bg-warning bg-opacity-25 p-3 rounded mb-3 text-center border border-warning">
                            <small class="text-dark fw-bold d-block mb-1"> VALOR CUOTA MENSUAL</small>
                            <h3 class="mb-0 fw-bold text-danger"><?= formatMoney($valor_cuota) ?></h3>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted d-block">Total Pagado</small>
                                <h5 class="mb-0 text-success"><?= formatMoney($resumen['total_pagado'] ?? 0) ?></h5>
                            </div>
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted d-block">Saldo Pendiente</small>
                                <h5 class="mb-0 text-danger"><?= formatMoney($saldo_total_pendiente) ?></h5>
                            </div>
                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 2rem;"></i>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted d-block">Progreso</small>
                                <h5 class="mb-0 text-info">
                                    <?php
                                    $total_financiado = $monto_financiado;
                                    $progreso = $total_financiado > 0 
                                        ? round((($resumen['total_pagado'] ?? 0) / $total_financiado) * 100, 1) 
                                        : 0;
                                    echo $progreso;
                                    ?>%
                                </h5>
                                <div class="progress mt-2" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: <?= $progreso ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas de Cuotas -->
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Estado de Cuotas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Cuotas:</span>
                            <strong><?= $resumen['total_cuotas'] ?? 0 ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-check-circle text-success"></i> Pagadas:</span>
                            <strong class="text-success"><?= $resumen['cuotas_pagadas'] ?? 0 ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-clock text-warning"></i> Pendientes:</span>
                            <strong class="text-warning"><?= $resumen['cuotas_pendientes'] ?? 0 ?></strong>
                        </div>
                        <?php if (($resumen['cuotas_vencidas'] ?? 0) > 0): ?>
                        <div class="alert alert-danger py-2 mb-0 mt-3">
                            <small>
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                <strong><?= $resumen['cuotas_vencidas'] ?></strong> cuota(s) en mora
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Historial Reciente -->
                <?php if (!empty($historial_pagos)): ?>
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Últimos Pagos</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($historial_pagos, 0, 5) as $pago): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Cuota #<?= $pago['numero_cuota'] ?></small>
                                    <small class="text-muted"><?= formatDate($pago['fecha_pago']) ?></small>
                                </div>
                                <div class="fw-bold text-success"><?= formatMoney($pago['valor_pagado']) ?></div>
                                <small class="text-muted"><?= ucfirst($pago['metodo_pago']) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formPago = document.getElementById('formPago');
    const inputMonto = document.getElementById('monto_pago');
    const distribucionPlaceholder = document.getElementById('distribucionPlaceholder');
    const distribucionResultado = document.getElementById('distribucionResultado');
    const distribucionTabla = document.getElementById('distribucionTabla');
    const totalAplicadoElem = document.getElementById('totalAplicado');
    const excedenteRow = document.getElementById('excedenteRow');
    const excedenteValor = document.getElementById('excedenteValor');
    const selectAll = document.getElementById('selectAll');
    const cuotaChecks = document.querySelectorAll('.cuota-check');

    const saldoMaximo = <?= $saldo_total_pendiente ?>;
    const cuotasPendientes = <?= json_encode($cuotas_pendientes) ?>;

    // Validación de monto máximo
    inputMonto.addEventListener('input', function() {
        const monto = parseFloat(this.value) || 0;
        
        if (monto > saldoMaximo) {
            this.value = saldoMaximo;
            // Mostrar notificación temporal
            const toast = document.createElement('div');
            toast.className = 'alert alert-warning position-fixed top-0 start-50 translate-middle-x mt-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = '<i class="bi bi-exclamation-triangle"></i> El monto no puede exceder el saldo pendiente: ' + formatMoney(saldoMaximo);
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        if (monto > 0) {
            calcularDistribucion(monto);
        } else {
            distribucionPlaceholder.classList.remove('d-none');
            distribucionResultado.classList.add('d-none');
        }
    });

    // Calcular distribución de pago
    function calcularDistribucion(monto) {
        let montoRestante = monto;
        const distribucion = [];
        let totalAplicado = 0;

        // Obtener cuotas seleccionadas o todas en orden
        const cuotasSeleccionadas = Array.from(cuotaChecks)
            .filter(cb => cb.checked)
            .map(cb => parseInt(cb.value));

        const cuotasAPagar = cuotasSeleccionadas.length > 0
            ? cuotasPendientes.filter(c => cuotasSeleccionadas.includes(c.id))
            : cuotasPendientes;

        cuotasAPagar.forEach(cuota => {
            if (montoRestante <= 0) return;

            const saldo = parseFloat(cuota.saldo_pendiente);
            const aplicado = Math.min(montoRestante, saldo);
            const nuevoSaldo = saldo - aplicado;

            distribucion.push({
                numero: cuota.numero_cuota,
                saldoAnterior: saldo,
                aplicado: aplicado,
                nuevoSaldo: nuevoSaldo
            });

            totalAplicado += aplicado;
            montoRestante -= aplicado;
        });

        // Mostrar distribución
        distribucionTabla.innerHTML = '';
        distribucion.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>Cuota ${item.numero}</strong></td>
                <td class="text-end">${formatMoney(item.saldoAnterior)}</td>
                <td class="text-end text-success fw-bold">${formatMoney(item.aplicado)}</td>
                <td class="text-end">${formatMoney(item.nuevoSaldo)}</td>
            `;
            distribucionTabla.appendChild(tr);
        });

        totalAplicadoElem.textContent = formatMoney(totalAplicado);

        if (montoRestante > 0.01) {
            excedenteRow.classList.remove('d-none');
            excedenteValor.textContent = formatMoney(montoRestante);
        } else {
            excedenteRow.classList.add('d-none');
        }

        distribucionPlaceholder.classList.add('d-none');
        distribucionResultado.classList.remove('d-none');
    }

    // Seleccionar/deseleccionar todas las cuotas
    selectAll.addEventListener('change', function() {
        cuotaChecks.forEach(cb => cb.checked = this.checked);
    });

    // Validación del formulario
    formPago.addEventListener('submit', function(e) {
        const monto = parseFloat(inputMonto.value) || 0;
        
        if (monto <= 0) {
            e.preventDefault();
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning alert-dismissible fade show';
            alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i> El monto del pago debe ser mayor a cero <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            this.insertBefore(alertDiv, this.firstChild);
            inputMonto.focus();
            return false;
        }
        
        if (monto > saldoMaximo) {
            e.preventDefault();
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i> El monto no puede ser mayor al saldo pendiente <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            this.insertBefore(alertDiv, this.firstChild);
            inputMonto.focus();
            return false;
        }
        
        // Sin confirmación, se envía directamente
    });

    function formatMoney(amount) {
        return '$' + parseFloat(amount).toLocaleString('es-CO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
});
</script>
