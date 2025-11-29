<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($cliente['nombre']) ?>
        </h1>
        <div>
            <?php if (can('editar_clientes')): ?>
            <a href="/clientes/edit/<?= $cliente['id'] ?>" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="/clientes" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-vcard"></i> Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Tipo de Documento</label>
                            <p class="mb-0">
                                <span class="badge bg-secondary"><?= htmlspecialchars($cliente['tipo_documento']) ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Número de Documento</label>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($cliente['numero_documento']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Teléfono</label>
                            <p class="mb-0">
                                <?php if (!empty($cliente['telefono'])): ?>
                                    <i class="bi bi-telephone-fill text-success"></i>
                                    <?= htmlspecialchars($cliente['telefono']) ?>
                                <?php else: ?>
                                    <span class="text-muted">No registrado</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Email</label>
                            <p class="mb-0">
                                <?php if (!empty($cliente['email'])): ?>
                                    <i class="bi bi-envelope-fill text-primary"></i>
                                    <a href="mailto:<?= htmlspecialchars($cliente['email']) ?>">
                                        <?= htmlspecialchars($cliente['email']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No registrado</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Dirección</label>
                            <p class="mb-0">
                                <?= !empty($cliente['direccion']) ? htmlspecialchars($cliente['direccion']) : '<span class="text-muted">No registrada</span>' ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Ciudad</label>
                            <p class="mb-0">
                                <?= !empty($cliente['ciudad']) ? htmlspecialchars($cliente['ciudad']) : '<span class="text-muted">No registrada</span>' ?>
                            </p>
                        </div>
                        <?php if (!empty($cliente['observaciones'])): ?>
                        <div class="col-12">
                            <label class="text-muted small">Observaciones</label>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($cliente['observaciones'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <i class="bi bi-house-fill fs-1 text-primary"></i>
                    <h3 class="mb-0 mt-2"><?= $estadisticas['total_propiedades'] ?></h3>
                    <p class="text-muted mb-0">
                        <?= $estadisticas['total_propiedades'] == 1 ? 'Propiedad' : 'Propiedades' ?> Total
                    </p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Vendidas</span>
                        <span class="badge bg-success"><?= $estadisticas['propiedades_vendidas'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Reservadas</span>
                        <span class="badge bg-warning"><?= $estadisticas['propiedades_reservadas'] ?></span>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <p class="text-muted small mb-1">Valor Total de Compras</p>
                    <h4 class="mb-0 text-success">
                        $<?= number_format($estadisticas['valor_total_compras'], 2, ',', '.') ?>
                    </h4>
                </div>
            </div>

            <?php if ($estadisticas['saldo_pendiente'] > 0): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <p class="text-muted small mb-1">Saldo Pendiente</p>
                    <h4 class="mb-0 text-danger">
                        $<?= number_format($estadisticas['saldo_pendiente'], 2, ',', '.') ?>
                    </h4>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Pagado</p>
                    <h4 class="mb-0 text-info">
                        $<?= number_format($estadisticas['total_pagado'], 2, ',', '.') ?>
                    </h4>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Propiedades del Cliente -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-buildings"></i> Propiedades Asociadas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($lotes)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Este cliente no tiene propiedades asociadas.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Proyecto</th>
                                <th>Código</th>
                                <th>Área (m²)</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Fecha de Venta</th>
                                <th class="text-center">Amortización</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lotes as $lote): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($lote['proyecto_nombre']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($lote['proyecto_codigo']) ?></small>
                                    </td>
                                    <td><code><?= htmlspecialchars($lote['codigo']) ?></code></td>
                                    <td><?= number_format($lote['area'], 2, ',', '.') ?></td>
                                    <td>$<?= number_format($lote['precio_venta'] ?? $lote['precio_lista'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        $estadoBadge = [
                                            'disponible' => 'secondary',
                                            'reservado' => 'warning',
                                            'vendido' => 'success'
                                        ];
                                        $estadoColor = $estadoBadge[$lote['estado']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $estadoColor ?>">
                                            <?= ucfirst($lote['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($lote['fecha_venta'])): ?>
                                            <i class="bi bi-calendar-check text-success"></i>
                                            <?= date('d/m/Y', strtotime($lote['fecha_venta'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($lote['amortizacion_activa'] > 0): ?>
                                            <span class="badge bg-info">
                                                <i class="bi bi-calendar-check"></i> Activa
                                            </span>
                                        <?php elseif ($lote['tiene_amortizacion'] > 0): ?>
                                            <span class="badge bg-secondary">Inactiva</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/lotes/show/<?= $lote['id'] ?>" 
                                               class="btn btn-outline-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Ver lote">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($lote['amortizacion_activa'] > 0): ?>
                                            <a href="/lotes/amortizacion/show/<?= $lote['id'] ?>" 
                                               class="btn btn-outline-info" 
                                               data-bs-toggle="tooltip" 
                                               title="Ver amortización">
                                                <i class="bi bi-calendar-week"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
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

<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
