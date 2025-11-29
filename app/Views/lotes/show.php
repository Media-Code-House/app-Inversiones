<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-info-circle"></i> Detalle del Lote: <?= htmlspecialchars($lote['codigo_lote']) ?>
        </h1>
        <div class="btn-group" role="group">
            <!-- Botón Editar (con permiso) -->
            <?php if (can('editar_lotes')): ?>
            <a href="/lotes/edit/<?= $lote['id'] ?>" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Editar Lote
            </a>
            <?php endif; ?>
            
            <!-- Botones Financieros (Módulo 5) -->
            <?php if ($lote['estado'] === 'vendido'): ?>
                <?php if (empty($amortizacion) || $amortizacion['total_cuotas'] == 0): ?>
                    <!-- Si NO tiene plan de amortización: Botón para crear plan -->
                    <a href="/lotes/amortizacion/create/<?= $lote['id'] ?>" class="btn btn-success">
                        <i class="bi bi-calendar-plus"></i> Generar Plan de Amortización
                    </a>
                <?php else: ?>
                    <!-- Si SÍ tiene plan de amortización: Botón para ver y registrar pago -->
                    <a href="/lotes/amortizacion/<?= $lote['id'] ?>" class="btn btn-info">
                        <i class="bi bi-calendar-check"></i> Ver Amortización
                    </a>
                    <a href="/lotes/registrar-pago/<?= $lote['id'] ?>" class="btn btn-warning">
                        <i class="bi bi-cash-coin"></i> Registrar Pago
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Botón Volver -->
            <a href="/lotes" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información General del Lote -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-grid-3x3"></i> Información del Lote</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="text-muted small">Código del Lote</label>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($lote['codigo_lote']) ?></p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small">Estado</label>
                            <p class="mb-0">
                                <?php
                                $badgeClass = [
                                    'disponible' => 'bg-success',
                                    'reservado' => 'bg-warning text-dark',
                                    'vendido' => 'bg-primary',
                                    'bloqueado' => 'bg-secondary'
                                ];
                                $class = $badgeClass[$lote['estado']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $class ?>">
                                    <?= ucfirst($lote['estado']) ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="text-muted small">Área</label>
                            <p class="mb-0"><?= number_format($lote['area'], 2) ?> m²</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small">Precio de Lista</label>
                            <p class="mb-0 fw-bold text-success">$<?= number_format($lote['precio_lista'], 0) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($lote['ubicacion'])): ?>
                        <div class="mb-3">
                            <label class="text-muted small">Ubicación / Referencias</label>
                            <p class="mb-0"><?= htmlspecialchars($lote['ubicacion']) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($lote['descripcion'])): ?>
                        <div class="mb-3">
                            <label class="text-muted small">Descripción</label>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($lote['descripcion'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-6">
                            <label class="text-muted small">Creado</label>
                            <p class="mb-0 small"><?= date('d/m/Y H:i', strtotime($lote['created_at'])) ?></p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small">Última actualización</label>
                            <p class="mb-0 small"><?= date('d/m/Y H:i', strtotime($lote['updated_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Proyecto -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Proyecto</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Nombre del Proyecto</label>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($lote['proyecto_nombre']) ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Código del Proyecto</label>
                        <p class="mb-0"><?= htmlspecialchars($lote['proyecto_codigo']) ?></p>
                    </div>

                    <?php if (!empty($lote['proyecto_ubicacion'])): ?>
                        <div class="mb-3">
                            <label class="text-muted small">Ubicación del Proyecto</label>
                            <p class="mb-0"><?= htmlspecialchars($lote['proyecto_ubicacion']) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="/proyectos/show/<?= $lote['proyecto_id'] ?>" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-eye"></i> Ver Proyecto Completo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Cliente (si está vendido) -->
    <?php if ($lote['estado'] === 'vendido' && !empty($lote['cliente_id'])): ?>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-person-circle"></i> Información del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Nombre del Cliente</label>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($lote['cliente_nombre']) ?></p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="text-muted small">Documento</label>
                                <p class="mb-0"><?= htmlspecialchars($lote['cliente_documento'] ?? 'N/A') ?></p>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small">Teléfono</label>
                                <p class="mb-0"><?= htmlspecialchars($lote['cliente_telefono'] ?? 'N/A') ?></p>
                            </div>
                        </div>

                        <?php if (!empty($lote['cliente_email'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small">Email</label>
                                <p class="mb-0"><?= htmlspecialchars($lote['cliente_email']) ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="text-muted small">Precio de Venta</label>
                                <p class="mb-0 fw-bold text-success">
                                    $<?= number_format($lote['precio_venta'] ?? $lote['precio_lista'], 0) ?>
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small">Fecha de Venta</label>
                                <p class="mb-0"><?= $lote['fecha_venta'] ? date('d/m/Y', strtotime($lote['fecha_venta'])) : 'N/A' ?></p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="/clientes/show/<?= $lote['cliente_id'] ?>" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-eye"></i> Ver Cliente Completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen de Amortización -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Resumen de Amortización</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($resumenAmortizacion && $resumenAmortizacion['total_cuotas'] > 0): ?>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="text-muted small">Total Cuotas</label>
                                    <p class="fw-bold mb-0"><?= $resumenAmortizacion['total_cuotas'] ?></p>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small">Valor Total Financiado</label>
                                    <p class="fw-bold text-primary mb-0">$<?= number_format($resumenAmortizacion['valor_total_financiado'], 0) ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="text-muted small">Cuotas Pagadas</label>
                                    <p class="mb-0 text-success">
                                        <i class="bi bi-check-circle-fill"></i> <?= $resumenAmortizacion['cuotas_pagadas'] ?>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small">Cuotas Pendientes</label>
                                    <p class="mb-0 text-warning">
                                        <i class="bi bi-clock-fill"></i> <?= $resumenAmortizacion['cuotas_pendientes'] ?>
                                    </p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="text-muted small">Total Pagado</label>
                                    <p class="fw-bold text-success mb-0">$<?= number_format($resumenAmortizacion['total_pagado'], 0) ?></p>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small">Saldo Pendiente</label>
                                    <p class="fw-bold text-danger mb-0">$<?= number_format($resumenAmortizacion['saldo_total'], 0) ?></p>
                                </div>
                            </div>

                            <?php if ($resumenAmortizacion['cuotas_vencidas'] > 0): ?>
                                <div class="alert alert-danger py-2 mb-3">
                                    <small>
                                        <i class="bi bi-exclamation-triangle-fill"></i> 
                                        <strong><?= $resumenAmortizacion['cuotas_vencidas'] ?></strong> cuota(s) vencida(s)
                                        <br>
                                        Días máximo de mora: <strong><?= $resumenAmortizacion['max_dias_mora'] ?></strong>
                                    </small>
                                </div>
                            <?php endif; ?>

                            <div class="mt-4">
                                <a href="/amortizaciones/lote/<?= $lote['id'] ?>" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-list-task"></i> Ver Plan de Amortización
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Este lote no tiene un plan de amortización configurado.
                                <?php if ($lote['amortizacion_activa'] == 0): ?>
                                    <hr>
                                    <a href="/amortizaciones/crear/<?= $lote['id'] ?>" class="btn btn-sm btn-primary mt-2">
                                        <i class="bi bi-plus-circle"></i> Crear Plan de Amortización
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
