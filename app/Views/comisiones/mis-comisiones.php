<?php
/**
 * Vista: Mis Comisiones (Vista para Vendedores)
 */
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="bi bi-cash-coin"></i> Mis Comisiones
            </h4>
        </div>
        <div class="card-body">
            <!-- Resumen Personal -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Comisiones</h6>
                            <h3 class="mb-0 text-primary"><?= number_format($estadisticas['total_comisiones']) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Generado</h6>
                            <h3 class="mb-0 text-info">$<?= number_format($estadisticas['total_generado'], 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Pendientes</h6>
                            <h3 class="mb-0 text-warning">$<?= number_format($estadisticas['total_pendiente'], 0) ?></h3>
                            <small class="text-muted">(<?= $estadisticas['comisiones_pendientes'] ?>)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Pagadas</h6>
                            <h3 class="mb-0 text-success">$<?= number_format($estadisticas['total_pagado'], 0) ?></h3>
                            <small class="text-muted">(<?= $estadisticas['comisiones_pagadas'] ?>)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" <?= $filtros['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                            <option value="pagada" <?= $filtros['estado'] == 'pagada' ? 'selected' : '' ?>>Pagadas</option>
                            <option value="pagada_parcial" <?= $filtros['estado'] == 'pagada_parcial' ? 'selected' : '' ?>>Pagadas Parcial</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" 
                               value="<?= $filtros['fecha_desde'] ?? '' ?>" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" 
                               value="<?= $filtros['fecha_hasta'] ?? '' ?>" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="<?= url('/comisiones/mis-comisiones') ?>" class="btn btn-secondary w-100">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <!-- Lista de Comisiones -->
            <?php if (count($comisiones) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha Venta</th>
                            <th>Lote</th>
                            <th>Proyecto</th>
                            <th>Cliente</th>
                            <th class="text-end">Valor Venta</th>
                            <th class="text-center">% Com.</th>
                            <th class="text-end">Valor Comisión</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comisiones as $comision): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($comision['fecha_venta'])) ?></td>
                            <td><strong><?= htmlspecialchars($comision['codigo_lote']) ?></strong></td>
                            <td><?= htmlspecialchars($comision['proyecto_nombre']) ?></td>
                            <td><?= htmlspecialchars($comision['cliente_nombre'] ?? 'N/A') ?></td>
                            <td class="text-end">$<?= number_format($comision['valor_venta'], 0) ?></td>
                            <td class="text-center"><?= number_format($comision['porcentaje_comision'], 2) ?>%</td>
                            <td class="text-end">
                                <strong class="text-success">$<?= number_format($comision['valor_comision'], 0) ?></strong>
                            </td>
                            <td class="text-center">
                                <?php
                                $estadoBadge = [
                                    'pendiente' => 'warning',
                                    'pagada' => 'success',
                                    'pagada_parcial' => 'info',
                                    'cancelada' => 'danger'
                                ];
                                $badgeClass = $estadoBadge[$comision['estado']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', $comision['estado'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No tienes comisiones registradas aún.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
