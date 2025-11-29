<?php
/**
 * Vista: Detalle de Vendedor
 * Muestra información completa del vendedor, sus ventas y comisiones
 */
?>

<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-person-badge"></i>
                <?= htmlspecialchars($vendedor['nombre_completo']) ?>
            </h2>
            <p class="text-muted mb-0">
                <small>Código: <?= htmlspecialchars($vendedor['codigo_vendedor']) ?></small>
            </p>
        </div>
        <div>
            <a href="<?= url('/vendedores') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <a href="<?= url('/vendedores/edit/' . $vendedor['id']) ?>" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Editar
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información Personal -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Estado</small>
                        <?php
                        $estadoBadge = [
                            'activo' => 'success',
                            'inactivo' => 'secondary',
                            'suspendido' => 'danger'
                        ];
                        $badgeClass = $estadoBadge[$vendedor['estado']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $badgeClass ?>">
                            <?= ucfirst($vendedor['estado']) ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Documento</small>
                        <strong><?= htmlspecialchars($vendedor['tipo_documento']) ?>: <?= htmlspecialchars($vendedor['numero_documento']) ?></strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong><?= htmlspecialchars($vendedor['email']) ?></strong>
                    </div>

                    <?php if (!empty($vendedor['telefono'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Teléfono</small>
                        <strong><?= htmlspecialchars($vendedor['telefono']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($vendedor['celular'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Celular</small>
                        <strong><?= htmlspecialchars($vendedor['celular']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($vendedor['direccion'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Dirección</small>
                        <strong><?= htmlspecialchars($vendedor['direccion']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($vendedor['ciudad'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Ciudad</small>
                        <strong><?= htmlspecialchars($vendedor['ciudad']) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Información Laboral -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-briefcase"></i> Información Laboral</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Fecha de Ingreso</small>
                        <strong><?= date('d/m/Y', strtotime($vendedor['fecha_ingreso'])) ?></strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Tipo de Contrato</small>
                        <strong><?= ucfirst(str_replace('_', ' ', $vendedor['tipo_contrato'])) ?></strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Comisión por Defecto</small>
                        <strong class="text-primary fs-4"><?= number_format($vendedor['porcentaje_comision_default'], 2) ?>%</strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Rol de Usuario</small>
                        <strong><?= ucfirst($vendedor['user_rol']) ?></strong>
                    </div>

                    <?php if (!empty($vendedor['banco'])): ?>
                    <hr>
                    <h6 class="text-muted mb-3">Datos Bancarios</h6>
                    
                    <div class="mb-2">
                        <small class="text-muted d-block">Banco</small>
                        <strong><?= htmlspecialchars($vendedor['banco']) ?></strong>
                    </div>

                    <?php if (!empty($vendedor['tipo_cuenta'])): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Tipo de Cuenta</small>
                        <strong><?= ucfirst($vendedor['tipo_cuenta']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($vendedor['numero_cuenta'])): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Número de Cuenta</small>
                        <strong><?= htmlspecialchars($vendedor['numero_cuenta']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <small class="text-muted d-block">Total Lotes Vendidos</small>
                        <h3 class="mb-0 text-primary"><?= number_format($vendedor['total_lotes_vendidos']) ?></h3>
                    </div>

                    <div class="mb-4">
                        <small class="text-muted d-block">Valor Total Vendido</small>
                        <h4 class="mb-0 text-success">$<?= number_format($vendedor['valor_total_vendido'], 0) ?></h4>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <small class="text-muted d-block">Total Comisiones Generadas</small>
                        <strong class="text-info">$<?= number_format($vendedor['total_comisiones_generadas'], 0) ?></strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Comisiones Pendientes</small>
                        <strong class="text-warning">$<?= number_format($vendedor['comisiones_pendientes'], 0) ?></strong>
                        <small class="text-muted">(<?= $estadisticas['comisiones_pendientes_count'] ?>)</small>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Comisiones Pagadas</small>
                        <strong class="text-success">$<?= number_format($vendedor['comisiones_pagadas'], 0) ?></strong>
                        <small class="text-muted">(<?= $estadisticas['comisiones_pagadas_count'] ?>)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lotes Vendidos Recientes -->
    <?php if (count($lotesVendidos) > 0): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-house-check"></i> Lotes Vendidos Recientes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Proyecto</th>
                            <th>Cliente</th>
                            <th>Área (m²)</th>
                            <th>Precio Venta</th>
                            <th>Fecha Venta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lotesVendidos as $lote): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong></td>
                            <td><?= htmlspecialchars($lote['proyecto_nombre']) ?></td>
                            <td><?= htmlspecialchars($lote['cliente_nombre'] ?? 'N/A') ?></td>
                            <td><?= number_format($lote['area_m2'], 2) ?></td>
                            <td class="text-success">
                                <strong>$<?= number_format($lote['precio_venta'] ?? $lote['precio_lista'], 0) ?></strong>
                            </td>
                            <td><?= $lote['fecha_venta'] ? date('d/m/Y', strtotime($lote['fecha_venta'])) : 'N/A' ?></td>
                            <td>
                                <a href="<?= url('/lotes/show/' . $lote['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Este vendedor aún no tiene lotes vendidos.
    </div>
    <?php endif; ?>

    <!-- Comisiones -->
    <?php if (count($comisiones) > 0): ?>
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Comisiones</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Proyecto</th>
                            <th>Cliente</th>
                            <th>Valor Venta</th>
                            <th>% Comisión</th>
                            <th>Valor Comisión</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comisiones as $comision): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($comision['codigo_lote']) ?></strong></td>
                            <td><?= htmlspecialchars($comision['proyecto_nombre']) ?></td>
                            <td><?= htmlspecialchars($comision['cliente_nombre'] ?? 'N/A') ?></td>
                            <td>$<?= number_format($comision['valor_venta'], 0) ?></td>
                            <td><?= number_format($comision['porcentaje_comision'], 2) ?>%</td>
                            <td class="text-success">
                                <strong>$<?= number_format($comision['valor_comision'], 0) ?></strong>
                            </td>
                            <td>
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
                            <td><?= date('d/m/Y', strtotime($comision['fecha_venta'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info mt-3">
        <i class="bi bi-info-circle"></i> Este vendedor aún no tiene comisiones registradas.
    </div>
    <?php endif; ?>

    <?php if (!empty($vendedor['observaciones'])): ?>
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-chat-left-text"></i> Observaciones</h5>
        </div>
        <div class="card-body">
            <p class="mb-0"><?= nl2br(htmlspecialchars($vendedor['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>
