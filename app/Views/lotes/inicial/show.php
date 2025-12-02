<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-credit-card-2-front"></i> Detalle del Plan de Pago Inicial
                </h1>
                <div>
                    <?php if ($resumen['estado_plan'] === 'en_curso'): ?>
                    <a href="/lotes/inicial/pago/<?= $lote['id'] ?>" class="btn btn-warning">
                        <i class="bi bi-cash-coin"></i> Registrar Pago
                    </a>
                    <?php endif; ?>
                    <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Lote
                    </a>
                </div>
            </div>

            <!-- Estado del Plan -->
            <div class="alert <?= $resumen['estado_plan'] === 'pagado_total' ? 'alert-success' : 'alert-info' ?>" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-<?= $resumen['estado_plan'] === 'pagado_total' ? 'check-circle-fill' : 'info-circle' ?>"></i>
                    Estado del Plan: <strong><?= strtoupper(str_replace('_', ' ', $resumen['estado_plan'])) ?></strong>
                </h5>
                <p class="mb-0">
                    <?php if ($resumen['estado_plan'] === 'pagado_total'): ?>
                        ✅ El plan de pago inicial ha sido completado exitosamente. El lote está en estado VENDIDO y puede proceder a crear el plan de amortización principal.
                    <?php elseif ($resumen['estado_plan'] === 'en_curso'): ?>
                        ⏳ El plan de pago inicial está activo. El lote permanecerá en estado RESERVADO hasta que se complete el pago de la inicial.
                    <?php elseif ($resumen['estado_plan'] === 'cancelado'): ?>
                        ❌ El plan de pago inicial ha sido cancelado.
                    <?php else: ?>
                        📋 Plan de pago inicial pendiente de activación.
                    <?php endif; ?>
                </p>
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
                            <?= htmlspecialchars($resumen['proyecto_nombre']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Código Lote:</strong><br>
                            <?= htmlspecialchars($resumen['codigo_lote']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Cliente:</strong><br>
                            <?= htmlspecialchars($resumen['cliente_nombre']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Estado Lote:</strong><br>
                            <span class="badge bg-<?= statusClass($resumen['estado_lote']) ?>">
                                <?= strtoupper($resumen['estado_lote']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen Financiero -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Resumen Financiero del Plan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block">Inicial Total Requerida</small>
                                <h3 class="mb-0 text-primary">
                                    $<?= number_format($resumen['monto_inicial_total_requerido'], 0, ',', '.') ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block">Abono Inicial</small>
                                <h3 class="mb-0 text-info">
                                    $<?= number_format($resumen['monto_pagado_hoy'], 0, ',', '.') ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block">Total Pagado</small>
                                <h3 class="mb-0 text-success">
                                    $<?= number_format($resumen['total_pagado_plan'], 0, ',', '.') ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block">Saldo Pendiente</small>
                                <h3 class="mb-0 text-danger">
                                    $<?= number_format($resumen['saldo_real_pendiente'], 0, ',', '.') ?>
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Barra de Progreso -->
                    <?php 
                        $porcentajePagado = $resumen['monto_inicial_total_requerido'] > 0 
                            ? round(($resumen['total_pagado_plan'] / $resumen['monto_inicial_total_requerido']) * 100, 1) 
                            : 0;
                    ?>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <label class="form-label mb-0">Progreso del Pago Inicial</label>
                            <strong><?= $porcentajePagado ?>%</strong>
                        </div>
                        <div class="progress" style="height: 35px;">
                            <div class="progress-bar bg-gradient <?= $porcentajePagado >= 100 ? 'bg-success' : 'bg-warning' ?> progress-bar-striped <?= $porcentajePagado >= 100 ? '' : 'progress-bar-animated' ?>" 
                                 role="progressbar" 
                                 style="width: <?= $porcentajePagado ?>%;" 
                                 aria-valuenow="<?= $porcentajePagado ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span class="fw-bold"><?= $porcentajePagado ?>% Completado</span>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Plan -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <small class="text-muted">Plazo Acordado:</small><br>
                            <strong><?= $resumen['plazo_meses'] ?> meses</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Cuota Mensual:</small><br>
                            <strong>$<?= number_format($resumen['cuota_mensual'], 0, ',', '.') ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Cuotas Pagadas:</small><br>
                            <strong><?= $resumen['cuotas_pagadas'] ?> de <?= $resumen['plazo_meses'] ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Cuotas Pendientes:</small><br>
                            <strong class="text-<?= $resumen['cuotas_pendientes'] > 0 ? 'danger' : 'success' ?>">
                                <?= $resumen['cuotas_pendientes'] ?>
                            </strong>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <small class="text-muted">Fecha de Inicio:</small><br>
                            <strong><?= date('d/m/Y', strtotime($resumen['fecha_inicio'])) ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Último Pago:</small><br>
                            <strong>
                                <?= $resumen['fecha_ultimo_pago'] 
                                    ? date('d/m/Y', strtotime($resumen['fecha_ultimo_pago'])) 
                                    : 'Sin pagos registrados' ?>
                            </strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Creado:</small><br>
                            <strong><?= date('d/m/Y H:i', strtotime($resumen['fecha_creacion_plan'])) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Pagos -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Historial de Pagos
                        <span class="badge bg-white text-dark float-end"><?= count($pagos) ?> pagos</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($pagos)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Método</th>
                                    <th class="text-end">Valor Pagado</th>
                                    <th class="text-end">Saldo Después</th>
                                    <th>Recibo</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $numero = 1;
                                foreach ($pagos as $pago): 
                                ?>
                                <tr>
                                    <td><?= $numero++ ?></td>
                                    <td>
                                        <i class="bi bi-calendar3"></i>
                                        <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= ucfirst($pago['metodo_pago']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">
                                            +$<?= number_format($pago['valor_pagado'], 0, ',', '.') ?>
                                        </strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-<?= $pago['saldo_pendiente_despues'] > 0 ? 'danger' : 'success' ?>">
                                            $<?= number_format($pago['saldo_pendiente_despues'], 0, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($pago['numero_recibo'])): ?>
                                            <code><?= htmlspecialchars($pago['numero_recibo']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= !empty($pago['observaciones']) 
                                                ? htmlspecialchars($pago['observaciones']) 
                                                : '-' ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">TOTAL PAGADO:</th>
                                    <th class="text-end text-success">
                                        $<?= number_format($resumen['total_pagado_plan'], 0, ',', '.') ?>
                                    </th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-3">No hay pagos registrados aún</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="d-flex justify-content-between mt-4">
                <a href="/lotes/show/<?= $lote['id'] ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Lote
                </a>
                <?php if ($resumen['estado_plan'] === 'en_curso'): ?>
                <a href="/lotes/inicial/pago/<?= $lote['id'] ?>" class="btn btn-warning">
                    <i class="bi bi-cash-coin"></i> Registrar Nuevo Pago
                </a>
                <?php elseif ($resumen['estado_plan'] === 'pagado_total'): ?>
                <a href="/lotes/amortizacion/create/<?= $lote['id'] ?>" class="btn btn-success">
                    <i class="bi bi-calendar-plus"></i> Generar Plan de Amortización Principal
                </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
