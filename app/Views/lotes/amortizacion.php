<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-calendar-check"></i> Plan de Amortización
            </h1>
            <p class="text-muted mb-0">
                Lote: <strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong> - 
                Proyecto: <strong><?= htmlspecialchars($lote['proyecto_nombre']) ?></strong>
            </p>
        </div>
        <div>
            <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <?php if (can('registrar_pagos')): ?>
            <a href="/lotes/registrar-pago/<?= $lote['id'] ?>" class="btn btn-success">
                <i class="bi bi-cash-coin"></i> Registrar Pago
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumen del Plan -->
    <?php if ($resumen_plan): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Valor Total</p>
                            <h4 class="mb-0"><?= formatMoney($resumen_plan['valor_total']) ?></h4>
                        </div>
                        <i class="bi bi-currency-dollar text-primary fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Pagado</p>
                            <h4 class="mb-0 text-success"><?= formatMoney($resumen_plan['total_pagado']) ?></h4>
                        </div>
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Saldo Pendiente</p>
                            <h4 class="mb-0 text-warning"><?= formatMoney($resumen_plan['saldo_pendiente']) ?></h4>
                        </div>
                        <i class="bi bi-hourglass-split text-warning fs-1"></i>
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
                            <h4 class="mb-0 text-info"><?= number_format($resumen_plan['porcentaje_pagado'], 1) ?>%</h4>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: <?= $resumen_plan['porcentaje_pagado'] ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Total Cuotas</p>
                    <h5 class="mb-0"><?= $resumen_plan['total_cuotas'] ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Cuotas Pagadas</p>
                    <h5 class="mb-0 text-success"><?= $resumen_plan['cuotas_pagadas'] ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Cuotas Pendientes</p>
                    <h5 class="mb-0 text-warning"><?= $resumen_plan['cuotas_pendientes'] ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Cuotas en Mora</p>
                    <h5 class="mb-0 text-danger"><?= $resumen_plan['cuotas_mora'] ?></h5>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de Cuotas -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-list-ol"></i> Detalle de Cuotas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($cuotas)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay cuotas registradas para este lote.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">N°</th>
                                <th>Fecha Vencimiento</th>
                                <th class="text-end">Valor Cuota</th>
                                <th class="text-end">Pagado</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-center">Días Mora</th>
                                <th class="text-center">Estado</th>
                                <th>Fecha Pago</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cuotas as $cuota): ?>
                                <?php
                                $rowClass = '';
                                if ($cuota['estado'] == 'pagada') {
                                    $rowClass = 'table-success';
                                } elseif ($cuota['dias_mora'] > 0) {
                                    $rowClass = 'table-danger';
                                } elseif (strtotime($cuota['fecha_vencimiento']) <= strtotime('+7 days')) {
                                    $rowClass = 'table-warning';
                                }
                                ?>
                                <tr class="<?= $rowClass ?>">
                                    <td class="text-center"><strong><?= $cuota['numero_cuota'] ?></strong></td>
                                    <td><?= formatDate($cuota['fecha_vencimiento']) ?></td>
                                    <td class="text-end"><strong><?= formatMoney($cuota['valor_cuota']) ?></strong></td>
                                    <td class="text-end text-success"><?= formatMoney($cuota['valor_pagado']) ?></td>
                                    <td class="text-end text-danger"><?= formatMoney($cuota['saldo_pendiente']) ?></td>
                                    <td class="text-center">
                                        <?php if ($cuota['dias_mora'] > 0): ?>
                                            <span class="badge bg-danger"><?= $cuota['dias_mora'] ?> días</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $estadoBadge = [
                                            'pendiente' => 'bg-warning text-dark',
                                            'pagada' => 'bg-success',
                                            'vencida' => 'bg-danger'
                                        ];
                                        $badge = $estadoBadge[$cuota['estado']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $badge ?>">
                                            <?= ucfirst($cuota['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($cuota['fecha_pago']): ?>
                                            <?= formatDate($cuota['fecha_pago']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cuota['estado'] == 'pendiente' && can('registrar_pagos')): ?>
                                            <a href="/lotes/registrar-pago/<?= $lote['id'] ?>?cuota_id=<?= $cuota['id'] ?>" 
                                               class="btn btn-sm btn-success"
                                               data-bs-toggle="tooltip"
                                               title="Registrar pago">
                                                <i class="bi bi-cash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($cuota['observaciones'])): ?>
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-chat-left-text"></i> Observaciones del Plan</h6>
        </div>
        <div class="card-body">
            <p class="mb-0"><?= nl2br(htmlspecialchars($cuota['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Activar tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
