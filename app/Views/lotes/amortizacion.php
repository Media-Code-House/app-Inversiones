<div class="container-fluid py-4">
    <!-- Header con Botones de Acción -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-calendar-check-fill"></i> Amortización del Lote
            </h1>
            <p class="text-muted mb-0">
                <strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong> - 
                Proyecto: <strong><?= htmlspecialchars($lote['proyecto_nombre']) ?></strong> - 
                Cliente: <strong><?= htmlspecialchars($lote['cliente_nombre'] ?? 'Sin asignar') ?></strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <?php if (can('registrar_pagos')): ?>
            <a href="/lotes/pago/create/<?= $lote['id'] ?>" class="btn btn-success">
                <i class="bi bi-cash-coin"></i> Registrar Pago
            </a>
            <?php endif; ?>
            
            <!-- Botón de Reajuste (Solo si hay Saldo a Favor) -->
            <?php if (isset($saldo_a_favor) && $saldo_a_favor > 0.01 && can('registrar_pagos')): ?>
            <form method="POST" action="/lotes/amortizacion/reajustar/<?= $lote['id'] ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-info text-white" 
                        onclick="return confirm('¿Aplicar Saldo a Favor de ' + formatCurrency(<?= $saldo_a_favor ?>) + ' para compensar cuotas futuras?\n\nEsta acción reajustará el plan y evitará mora.');">
                    <i class="bi bi-cash-coin"></i> Aplicar Saldo a Favor (<?= formatMoney($saldo_a_favor) ?>)
                </button>
            </form>
            <?php endif; ?>
            
            <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-info text-white">
                <i class="bi bi-eye-fill"></i> Ver Lote
            </a>
            <a href="/lotes" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Lotes
            </a>
        </div>
    </div>

    <!-- Información del Lote (Cards Superiores) -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Código</p>
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($lote['codigo_lote']) ?></h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Proyecto</p>
                    <h6 class="mb-0"><?= htmlspecialchars($lote['proyecto_nombre']) ?></h6>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Manzana</p>
                    <h6 class="mb-0"><?= htmlspecialchars($lote['manzana'] ?? 'N/A') ?></h6>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body py-3">
                    <p class="text-muted mb-1 small">Área</p>
                    <h6 class="mb-0"><?= number_format($lote['area_m2'], 2) ?> m²</h6>
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
    </div>

    <!-- 6 MÉTRICAS CLAVE DEL RESUMEN FINANCIERO -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Resumen Financiero</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="border-end pe-3">
                                <p class="text-muted mb-1 small">Valor Lote</p>
                                <h5 class="mb-0 text-primary fw-bold"><?= formatMoney($metricas['valor_lote']) ?></h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end pe-3">
                                <p class="text-muted mb-1 small">Cuota Inicial</p>
                                <h5 class="mb-0 text-success fw-bold"><?= formatMoney($metricas['cuota_inicial']) ?></h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end pe-3">
                                <p class="text-muted mb-1 small">Monto Financiado</p>
                                <h5 class="mb-0 text-info fw-bold"><?= formatMoney($metricas['monto_financiado']) ?></h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end pe-3">
                                <p class="text-muted mb-1 small">Tasa Interés</p>
                                <h5 class="mb-0 text-warning fw-bold"><?= number_format($metricas['tasa_interes'], 2) ?>% <small>anual</small></h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border-end pe-3">
                                <p class="text-muted mb-1 small">Número de Cuotas</p>
                                <h5 class="mb-0 text-secondary fw-bold"><?= $metricas['numero_cuotas'] ?></h5>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-warning bg-opacity-10 p-2 rounded">
                                <p class="text-muted mb-1 small fw-bold">💰 VALOR CUOTA MENSUAL</p>
                                <h4 class="mb-0 text-danger fw-bold"><?= formatMoney($metricas['valor_cuota']) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPIs VISUALES INFERIORES -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Pagado</p>
                            <h4 class="mb-0 text-success fw-bold"><?= formatMoney($kpis['total_pagado']) ?></h4>
                        </div>
                        <i class="bi bi-cash-stack text-success" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Saldo Pendiente</p>
                            <h4 class="mb-0 text-danger fw-bold"><?= formatMoney($kpis['saldo_pendiente']) ?></h4>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Progreso</p>
                            <h4 class="mb-0 text-info fw-bold"><?= $kpis['progreso'] ?>%</h4>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: <?= $kpis['progreso'] ?>%" 
                                     aria-valuenow="<?= $kpis['progreso'] ?>" 
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <i class="bi bi-bar-chart-fill text-info" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Cuotas</p>
                            <div class="d-flex gap-2 align-items-baseline">
                                <span class="badge bg-success"><?= $kpis['cuotas_info']['pagadas'] ?> pagadas</span>
                                <span class="badge bg-warning text-dark"><?= $kpis['cuotas_info']['pendientes'] ?> pend.</span>
                                <?php if ($kpis['cuotas_info']['vencidas'] > 0): ?>
                                <span class="badge bg-danger"><?= $kpis['cuotas_info']['vencidas'] ?> mora</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Total: <?= $kpis['cuotas_info']['total'] ?></small>
                        </div>
                        <i class="bi bi-calendar-check text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE AMORTIZACIÓN CON HEADER OSCURO -->
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-table"></i> Tabla de Amortización</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($cuotas)): ?>
                <div class="alert alert-info m-3">
                    <i class="bi bi-info-circle"></i> No hay cuotas registradas para este lote.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">Cuota #</th>
                                <th>Fecha Vencimiento</th>
                                <th class="text-end">Cuota Total</th>
                                <th class="text-end">Capital</th>
                                <th class="text-end">Interés</th>
                                <th class="text-end">Pagado</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cuotas as $cuota): ?>
                                <tr class="<?= $cuota['clase_fila'] ?? '' ?>">
                                    <td class="text-center">
                                        <strong class="badge bg-secondary"><?= $cuota['numero_cuota'] ?></strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar-event"></i> 
                                        <?= formatDate($cuota['fecha_vencimiento']) ?>
                                    </td>
                                    <td class="text-end">
                                        <strong><?= formatMoney($cuota['valor_cuota']) ?></strong>
                                    </td>
                                    <td class="text-end text-primary">
                                        <?= formatMoney($cuota['capital'] ?? 0) ?>
                                    </td>
                                    <td class="text-end text-warning">
                                        <?= formatMoney($cuota['interes'] ?? 0) ?>
                                    </td>
                                    <td class="text-end text-success">
                                        <strong><?= formatMoney($cuota['valor_pagado']) ?></strong>
                                    </td>
                                    <td class="text-end text-danger">
                                        <strong><?= formatMoney($cuota['saldo_pendiente']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $cuota['clase_badge'] ?? 'bg-secondary' ?>">
                                            <?= $cuota['etiqueta_estado'] ?? 'Pendiente' ?>
                                        </span>
                                        <?php if (isset($cuota['dias_mora']) && $cuota['dias_mora'] > 0): ?>
                                            <br><small class="text-danger fw-bold"><?= $cuota['dias_mora'] ?> días mora</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cuota['estado'] == 'pendiente' && can('registrar_pagos')): ?>
                                            <a href="/lotes/pago/create/<?= $lote['id'] ?>?cuota_id=<?= $cuota['id'] ?>" 
                                               class="btn btn-sm btn-success"
                                               data-bs-toggle="tooltip"
                                               title="Registrar pago de esta cuota">
                                                <i class="bi bi-cash-coin"></i> Pagar
                                            </a>
                                        <?php elseif ($cuota['estado'] == 'pagada'): ?>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="tooltip"
                                                    title="Fecha de pago: <?= formatDate($cuota['fecha_pago'] ?? '') ?>">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">TOTALES:</td>
                                <td class="text-end"><?= formatMoney(array_sum(array_column($cuotas, 'valor_cuota'))) ?></td>
                                <td class="text-end text-primary"><?= formatMoney(array_sum(array_column($cuotas, 'capital'))) ?></td>
                                <td class="text-end text-warning"><?= formatMoney(array_sum(array_column($cuotas, 'interes'))) ?></td>
                                <td class="text-end text-success"><?= formatMoney(array_sum(array_column($cuotas, 'valor_pagado'))) ?></td>
                                <td class="text-end text-danger"><?= formatMoney(array_sum(array_column($cuotas, 'saldo_pendiente'))) ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Función para formatear moneda en confirmación
function formatCurrency(value) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

// Activar tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
