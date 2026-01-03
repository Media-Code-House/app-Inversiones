<div class="container-fluid">
    <!-- MENSAJE DE PRUEBA -->
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong> ¡ACTUALIZADO!</strong> Versión: <?= date('Y-m-d H:i:s') ?> - ProyectoModel corregido (sin observaciones)
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    
    <!-- Header del Dashboard-->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-0">
                        <i class="fas fa-chart-line text-primary"></i> Dashboard
                    </h1>
                    <p class="text-muted">Bienvenido, <?= e(user()['nombre']) ?></p>
                </div>
                <div>
                    <span class="badge bg-secondary">
                        <i class="fas fa-calendar"></i> <?= date('d/m/Y') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas principales -->
    <div class="row mb-4">
        <!-- Total Proyectos Activos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Proyectos Activos</div>
                            <div class="stat-value"><?= $totalProyectosActivos ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lotes Disponibles -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Lotes Disponibles</div>
                            <div class="stat-value"><?= $lotesDisponibles ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon text-success">
                                <i class="fas fa-th"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lotes Vendidos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Lotes Vendidos</div>
                            <div class="stat-value"><?= $lotesVendidos ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon text-info">
                                <i class="fas fa-handshake"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Clientes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Total Clientes</div>
                            <div class="stat-value"><?= $totalClientes ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon text-secondary">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas financieras -->
    <div class="row mb-4">
        <!-- Valor Inventario -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Valor Inventario</div>
                            <div class="stat-value"><?= formatMoney($valorInventario) ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon text-warning">
                                <i class="fas fa-warehouse"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Valor Ventas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Valor Ventas</div>
                            <div class="stat-value"><?= formatMoney($valorVentas) ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon text-success">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartera Pendiente -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card danger">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Cartera Pendiente</div>
                            <div class="stat-value"><?= formatMoney($carteraPendiente) ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon text-danger">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recaudado Este Mes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Recaudado Este Mes</div>
                            <div class="stat-value"><?= formatMoney($totalRecaudadoMes) ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon text-info">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fila de alertas -->
    <div class="row mb-4">
        <!-- Cuotas en Mora -->
        <?php if ($cuotasVencidas > 0): ?>
        <div class="col-lg-6 mb-4">
            <div class="alert alert-danger d-flex align-items-center mb-0">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <strong>Atención:</strong> Hay <strong><?= $cuotasVencidas ?></strong> cuotas en mora por un total de <strong><?= formatMoney($carteraVencida) ?></strong>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Lotes Reservados -->
        <?php if ($lotesReservados > 0): ?>
        <div class="col-lg-6 mb-4">
            <div class="alert alert-warning d-flex align-items-center mb-0">
                <i class="fas fa-clock fa-2x me-3"></i>
                <div>
                    <strong>Pendiente:</strong> Hay <strong><?= $lotesReservados ?></strong> lotes en estado reservado
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Resumen de Proyectos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-project-diagram text-primary"></i> Resumen de Proyectos</h5>
                    <a href="<?= url('/proyectos') ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Proyecto
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($resumenProyectos)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No hay proyectos registrados. Crea tu primer proyecto para comenzar.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Proyecto</th>
                                        <th>Ubicación</th>
                                        <th class="text-center">Total Lotes</th>
                                        <th class="text-center">Disponibles</th>
                                        <th class="text-center">Vendidos</th>
                                        <th class="text-end">Valor Inventario</th>
                                        <th class="text-end">Valor Ventas</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resumenProyectos as $proyecto): ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($proyecto['codigo']) ?></strong><br>
                                            <small class="text-muted"><?= e($proyecto['nombre']) ?></small>
                                        </td>
                                        <td><?= e($proyecto['ubicacion'] ?? '-') ?></td>
                                        <td class="text-center"><?= $proyecto['total_lotes'] ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= $proyecto['lotes_disponibles'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?= $proyecto['lotes_vendidos'] ?></span>
                                        </td>
                                        <td class="text-end"><?= formatMoney($proyecto['valor_inventario']) ?></td>
                                        <td class="text-end"><?= formatMoney($proyecto['valor_ventas']) ?></td>
                                        <td class="text-center">
                                            <?php if ($proyecto['estado'] === 'activo'): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= ucfirst($proyecto['estado']) ?></span>
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
    </div>

    <!-- Fila de tablas: Cuotas en Mora y Próximas Cuotas -->
    <div class="row mb-4">
        <!-- Cuotas en Mora -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Cuotas en Mora</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($cuotasMora)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mt-3">No hay cuotas en mora</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($cuotasMora as $cuota): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= e($cuota['cliente_nombre']) ?></h6>
                                        <p class="mb-1 small">
                                            <strong><?= e($cuota['proyecto_nombre']) ?></strong> - Lote <?= e($cuota['codigo_lote']) ?>
                                        </p>
                                        <p class="mb-0 small text-muted">
                                            Cuota #<?= $cuota['numero_cuota'] ?> - Vence: <?= formatDate($cuota['fecha_vencimiento']) ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-danger"><?= $cuota['dias_mora'] ?> días</span>
                                        <div class="mt-1">
                                            <strong><?= formatMoney($cuota['saldo_pendiente']) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Próximas Cuotas -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Próximas Cuotas (15 días)</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($proximasCuotas)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mt-3">No hay cuotas próximas a vencer</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($proximasCuotas as $cuota): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= e($cuota['cliente_nombre']) ?></h6>
                                        <p class="mb-1 small">
                                            <strong><?= e($cuota['proyecto_nombre']) ?></strong> - Lote <?= e($cuota['codigo_lote']) ?>
                                        </p>
                                        <p class="mb-0 small text-muted">
                                            Cuota #<?= $cuota['numero_cuota'] ?> - Vence: <?= formatDate($cuota['fecha_vencimiento']) ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <strong><?= formatMoney($cuota['valor_cuota']) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Pagos Registrados -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Últimos Pagos Registrados</h5>
                    <a href="<?= url('/pagos') ?>" class="btn btn-sm btn-light">Ver todos</a>
                </div>
                <div class="card-body">
                    <?php if (empty($ultimosPagos)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No hay pagos registrados</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Proyecto</th>
                                        <th>Lote</th>
                                        <th>Cuota #</th>
                                        <th>Método</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimosPagos as $pago): ?>
                                    <tr>
                                        <td><?= formatDate($pago['fecha_pago']) ?></td>
                                        <td><?= e($pago['cliente_nombre']) ?></td>
                                        <td><?= e($pago['proyecto_nombre']) ?></td>
                                        <td><?= e($pago['codigo_lote']) ?></td>
                                        <td class="text-center"><?= $pago['numero_cuota'] ?></td>
                                        <td>
                                            <?php
                                            $metodoBadge = [
                                                'efectivo' => 'success',
                                                'transferencia' => 'info',
                                                'cheque' => 'warning',
                                                'tarjeta' => 'primary'
                                            ];
                                            $badge = $metodoBadge[$pago['metodo_pago']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $badge ?>"><?= ucfirst($pago['metodo_pago']) ?></span>
                                        </td>
                                        <td class="text-end"><strong><?= formatMoney($pago['valor_pagado']) ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt text-warning"></i> Accesos Rápidos</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= url('/proyectos/crear') ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus-circle"></i> Nuevo Proyecto
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= url('/lotes/crear') ?>" class="btn btn-outline-success w-100">
                                <i class="fas fa-th-large"></i> Nuevo Lote
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= url('/clientes/crear') ?>" class="btn btn-outline-info w-100">
                                <i class="fas fa-user-plus"></i> Nuevo Cliente
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= url('/pagos/registrar') ?>" class="btn btn-outline-warning w-100">
                                <i class="fas fa-hand-holding-usd"></i> Registrar Pago
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
