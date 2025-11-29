<?php
/**
 * Vista: Ranking de Vendedores
 * Top 10 vendedores por ventas en el periodo seleccionado
 */
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-trophy"></i> Ranking de Vendedores
                </h4>
                <a href="<?= url('/vendedores') ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtro por Periodo -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Periodo</label>
                        <select name="periodo" class="form-select" onchange="this.form.submit()">
                            <option value="mes" <?= $periodo == 'mes' ? 'selected' : '' ?>>Este Mes</option>
                            <option value="trimestre" <?= $periodo == 'trimestre' ? 'selected' : '' ?>>Este Trimestre</option>
                            <option value="semestre" <?= $periodo == 'semestre' ? 'selected' : '' ?>>Este Semestre</option>
                            <option value="anio" <?= $periodo == 'anio' ? 'selected' : '' ?>>Este Año</option>
                            <option value="todo" <?= $periodo == 'todo' ? 'selected' : '' ?>>Todo el Tiempo</option>
                        </select>
                    </div>
                </div>
            </form>

            <?php if (count($ranking) > 0): ?>
            <!-- Tabla de Ranking -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">Posición</th>
                            <th>Vendedor</th>
                            <th class="text-center">Lotes Vendidos</th>
                            <th class="text-end">Valor Total</th>
                            <th class="text-end">Comisiones Generadas</th>
                            <th class="text-center">% Comisión</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $posicion = 1;
                        foreach ($ranking as $vendedor): 
                            // Medallas para top 3
                            $medalla = '';
                            if ($posicion == 1) $medalla = '<i class="bi bi-trophy-fill text-warning fs-4"></i>';
                            elseif ($posicion == 2) $medalla = '<i class="bi bi-trophy-fill text-secondary fs-5"></i>';
                            elseif ($posicion == 3) $medalla = '<i class="bi bi-trophy-fill" style="color: #CD7F32"></i>';
                            
                            $rowClass = $posicion <= 3 ? 'table-success' : '';
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="text-center">
                                <?php if ($medalla): ?>
                                    <?= $medalla ?>
                                <?php else: ?>
                                    <strong class="fs-5">#<?= $posicion ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($vendedor['nombre_completo']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($vendedor['codigo_vendedor']) ?></small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary fs-6"><?= number_format($vendedor['total_lotes_vendidos']) ?></span>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">$<?= number_format($vendedor['valor_total_vendido'], 0) ?></strong>
                            </td>
                            <td class="text-end">
                                <strong class="text-info">$<?= number_format($vendedor['total_comisiones_generadas'], 0) ?></strong>
                            </td>
                            <td class="text-center">
                                <?= number_format($vendedor['porcentaje_comision_default'], 2) ?>%
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/vendedores/show/' . $vendedor['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        <?php 
                            $posicion++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Resumen Estadístico -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Lotes Vendidos</h6>
                            <h3 class="mb-0 text-primary">
                                <?= number_format(array_sum(array_column($ranking, 'total_lotes_vendidos'))) ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Valor Total Vendido</h6>
                            <h3 class="mb-0 text-success">
                                $<?= number_format(array_sum(array_column($ranking, 'valor_total_vendido')), 0) ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Comisiones Generadas</h6>
                            <h3 class="mb-0 text-info">
                                $<?= number_format(array_sum(array_column($ranking, 'total_comisiones_generadas')), 0) ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay datos de ventas para el periodo seleccionado.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
