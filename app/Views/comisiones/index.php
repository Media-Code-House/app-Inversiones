<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-cash-coin"></i> Gestión de Comisiones
        </h1>
        <div>
            <a href="/comisiones/resumen" class="btn btn-info me-2">
                <i class="bi bi-bar-chart-fill"></i> Resumen por Vendedor
            </a>
            <a href="/comisiones/configuracion" class="btn btn-warning">
                <i class="bi bi-gear-fill"></i> Configurar Porcentajes
            </a>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Ventas</h6>
                    <h3 class="mb-0"><?= $totales['total_ventas'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Comisiones</h6>
                    <h3 class="mb-0"><?= formatMoney($totales['total_comisiones']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Pendientes</h6>
                    <h3 class="mb-0"><?= formatMoney($totales['total_pendiente']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Pagadas</h6>
                    <h3 class="mb-0"><?= formatMoney($totales['total_pagado']) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/comisiones" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Vendedor</label>
                    <select name="vendedor_id" class="form-select">
                        <option value="">Todos los vendedores</option>
                        <?php foreach ($vendedores as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= $filtros['vendedor_id'] == $v['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente" <?= $filtros['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="pagada" <?= $filtros['estado'] == 'pagada' ? 'selected' : '' ?>>Pagada</option>
                        <option value="cancelada" <?= $filtros['estado'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="<?= $filtros['fecha_desde'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="<?= $filtros['fecha_hasta'] ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Comisiones -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($comisiones)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay comisiones registradas con los filtros seleccionados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha Venta</th>
                                <th>Vendedor</th>
                                <th>Proyecto</th>
                                <th>Lote</th>
                                <th>Cliente</th>
                                <th class="text-end">Valor Venta</th>
                                <th class="text-center">% Comisión</th>
                                <th class="text-end">Comisión</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comisiones as $c): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($c['fecha_venta'])) ?></td>
                                    <td><?= htmlspecialchars($c['vendedor_nombre']) ?></td>
                                    <td><?= htmlspecialchars($c['proyecto_nombre']) ?></td>
                                    <td><strong><?= htmlspecialchars($c['codigo_lote']) ?></strong></td>
                                    <td><?= htmlspecialchars($c['cliente_nombre'] ?? 'N/A') ?></td>
                                    <td class="text-end"><?= formatMoney($c['valor_venta']) ?></td>
                                    <td class="text-center"><?= number_format($c['porcentaje_comision'], 2) ?>%</td>
                                    <td class="text-end"><strong><?= formatMoney($c['valor_comision']) ?></strong></td>
                                    <td class="text-center">
                                        <?php if ($c['estado'] === 'pendiente'): ?>
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        <?php elseif ($c['estado'] === 'pagada'): ?>
                                            <span class="badge bg-success">Pagada</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Cancelada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= url('/comisiones/show/' . $c['id']) ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($c['estado'] === 'pendiente'): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-success" 
                                                    title="Registrar pago"
                                                    onclick="window.location.href='<?= url('/comisiones/pagar/' . $c['id']) ?>';">
                                                <i class="bi bi-cash-stack"></i> Pagar
                                            </button>
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
</div>
