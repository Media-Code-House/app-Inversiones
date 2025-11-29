<?php
/**
 * Vista: Resumen de Comisiones por Vendedor
 */
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-bar-chart"></i> Resumen de Comisiones por Vendedor
                </h4>
                <a href="<?= url('/comisiones') ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($resumen) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Vendedor</th>
                            <th class="text-center">Total Comisiones</th>
                            <th class="text-end">Total Generado</th>
                            <th class="text-end">Pendientes</th>
                            <th class="text-end">Pagadas</th>
                            <th class="text-center">% Pagado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumen as $item): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['nombre_completo']) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($item['codigo_vendedor']) ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= number_format($item['total_comisiones']) ?></span>
                            </td>
                            <td class="text-end">
                                <strong>$<?= number_format($item['total_generado'], 0) ?></strong>
                            </td>
                            <td class="text-end">
                                <span class="text-warning">$<?= number_format($item['total_pendiente'], 0) ?></span>
                                <br>
                                <small class="text-muted">(<?= $item['comisiones_pendientes'] ?>)</small>
                            </td>
                            <td class="text-end">
                                <span class="text-success">$<?= number_format($item['total_pagado'], 0) ?></span>
                                <br>
                                <small class="text-muted">(<?= $item['comisiones_pagadas'] ?>)</small>
                            </td>
                            <td class="text-center">
                                <?php 
                                $porcentajePagado = $item['total_generado'] > 0 
                                    ? ($item['total_pagado'] / $item['total_generado'] * 100) 
                                    : 0;
                                $colorBadge = $porcentajePagado >= 80 ? 'success' : ($porcentajePagado >= 40 ? 'warning' : 'danger');
                                ?>
                                <span class="badge bg-<?= $colorBadge ?>">
                                    <?= number_format($porcentajePagado, 1) ?>%
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/vendedores/show/' . $item['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>TOTALES</th>
                            <th class="text-center">
                                <?= number_format(array_sum(array_column($resumen, 'total_comisiones'))) ?>
                            </th>
                            <th class="text-end">
                                $<?= number_format(array_sum(array_column($resumen, 'total_generado')), 0) ?>
                            </th>
                            <th class="text-end">
                                $<?= number_format(array_sum(array_column($resumen, 'total_pendiente')), 0) ?>
                            </th>
                            <th class="text-end">
                                $<?= number_format(array_sum(array_column($resumen, 'total_pagado')), 0) ?>
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay comisiones registradas.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
