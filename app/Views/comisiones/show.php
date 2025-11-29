<?php
/**
 * Vista: Detalle de Comisión
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-cash-coin"></i> Detalle de Comisión #<?= $comision['id'] ?>
                        </h4>
                        <a href="<?= url('/comisiones') ?>" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Información del Vendedor -->
                        <div class="col-md-6 mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-person-badge"></i> Vendedor</h5>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Nombre</small>
                                        <strong><?= htmlspecialchars($comision['vendedor_nombre']) ?></strong>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Código</small>
                                        <strong><?= htmlspecialchars($comision['codigo_vendedor'] ?? 'N/A') ?></strong>
                                    </div>
                                    <a href="<?= url('/vendedores/show/' . $comision['vendedor_id']) ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="bi bi-eye"></i> Ver Perfil
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Lote -->
                        <div class="col-md-6 mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-house"></i> Lote Vendido</h5>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Código Lote</small>
                                        <strong><?= htmlspecialchars($comision['codigo_lote']) ?></strong>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Proyecto</small>
                                        <strong><?= htmlspecialchars($comision['proyecto_nombre']) ?></strong>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Cliente</small>
                                        <strong><?= htmlspecialchars($comision['cliente_nombre'] ?? 'N/A') ?></strong>
                                    </div>
                                    <a href="<?= url('/lotes/show/' . $comision['lote_id']) ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="bi bi-eye"></i> Ver Lote
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Valores de la Comisión -->
                        <div class="col-md-12 mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-currency-dollar"></i> Valores</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Valor Venta</small>
                                            <h4 class="text-primary">$<?= number_format($comision['valor_venta'], 0) ?></h4>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">% Comisión</small>
                                            <h4 class="text-info"><?= number_format($comision['porcentaje_comision'], 2) ?>%</h4>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Valor Comisión</small>
                                            <h4 class="text-success">$<?= number_format($comision['valor_comision'], 0) ?></h4>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Estado</small>
                                            <?php
                                            $estadoBadge = [
                                                'pendiente' => 'warning',
                                                'pagada' => 'success',
                                                'pagada_parcial' => 'info',
                                                'cancelada' => 'danger'
                                            ];
                                            $badgeClass = $estadoBadge[$comision['estado']] ?? 'secondary';
                                            ?>
                                            <h4><span class="badge bg-<?= $badgeClass ?>">
                                                <?= ucfirst(str_replace('_', ' ', $comision['estado'])) ?>
                                            </span></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fechas -->
                        <div class="col-md-6 mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-calendar"></i> Fechas</h5>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Fecha de Venta</small>
                                        <strong><?= date('d/m/Y', strtotime($comision['fecha_venta'])) ?></strong>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Fecha de Cálculo</small>
                                        <strong><?= date('d/m/Y H:i', strtotime($comision['fecha_calculo'])) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-md-6 mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-chat-text"></i> Observaciones</h5>
                                    <?php if (!empty($comision['observaciones'])): ?>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($comision['observaciones'])) ?></p>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">Sin observaciones</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Pagos -->
                    <?php if (isset($pagos) && count($pagos) > 0): ?>
                    <div class="card bg-light mt-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-clock-history"></i> Historial de Pagos</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Método</th>
                                            <th>Valor</th>
                                            <th>Banco</th>
                                            <th>Comprobante</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pagos as $pago): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                                            <td><?= ucfirst($pago['metodo_pago']) ?></td>
                                            <td class="text-success"><strong>$<?= number_format($pago['valor_pagado'], 0) ?></strong></td>
                                            <td><?= htmlspecialchars($pago['banco'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($pago['numero_comprobante'] ?? 'N/A') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Acciones -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <?php if ($comision['estado'] == 'pendiente' || $comision['estado'] == 'pagada_parcial'): ?>
                        <a href="<?= url('/comisiones/pagar/' . $comision['id']) ?>" class="btn btn-success">
                            <i class="bi bi-cash"></i> Registrar Pago
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
