<?php
/**
 * Vista: Detalle del Proyecto
 * Muestra información completa de un proyecto
 */
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-building"></i> <?= htmlspecialchars($proyecto['nombre']) ?>
        </h1>
        <div>
            <?php if (can('editar_proyectos')): ?>
                <a href="/proyectos/edit/<?= $proyecto['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            <?php endif; ?>
            <a href="/proyectos" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información del Proyecto -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Código del Proyecto</label>
                            <p class="fs-5"><strong><?= htmlspecialchars($proyecto['codigo']) ?></strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Estado</label>
                            <p>
                                <?php
                                $estadoBadge = [
                                    'planificacion' => 'bg-warning',
                                    'activo' => 'bg-success',
                                    'vendido' => 'bg-info',
                                    'suspendido' => 'bg-danger'
                                ];
                                $badgeClass = $estadoBadge[$proyecto['estado']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $badgeClass ?> fs-6">
                                    <?= ucfirst($proyecto['estado']) ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted">Ubicación</label>
                        <p class="fs-5"><?= htmlspecialchars($proyecto['ubicacion']) ?></p>
                    </div>

                    <?php if (!empty($proyecto['fecha_inicio'])): ?>
                        <div class="mb-3">
                            <label class="text-muted">Fecha de Inicio</label>
                            <p><?= date('d/m/Y', strtotime($proyecto['fecha_inicio'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($proyecto['observaciones'])): ?>
                        <div class="mb-3">
                            <label class="text-muted">Observaciones</label>
                            <p><?= nl2br(htmlspecialchars($proyecto['observaciones'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <div class="row text-muted small">
                        <div class="col-md-6">
                            <strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($proyecto['created_at'])) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Última actualización:</strong> <?= date('d/m/Y H:i', strtotime($proyecto['updated_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plano del Proyecto -->
            <?php if (!empty($proyecto['plano_imagen'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-image"></i> Plano del Proyecto</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="/<?= htmlspecialchars($proyecto['plano_imagen']) ?>" 
                             alt="Plano del proyecto" 
                             class="img-fluid rounded shadow"
                             style="max-height: 500px;">
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Estadísticas -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted">Total de Lotes</label>
                        <h3 class="text-primary"><?= $estadisticas['total_lotes_registrados'] ?? 0 ?></h3>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-check-circle text-success"></i> Disponibles:</span>
                            <strong><?= $estadisticas['lotes_disponibles'] ?? 0 ?></strong>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-clock text-warning"></i> Reservados:</span>
                            <strong><?= $estadisticas['lotes_reservados'] ?? 0 ?></strong>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-cart-check text-primary"></i> Vendidos:</span>
                            <strong><?= $estadisticas['lotes_vendidos'] ?? 0 ?></strong>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-lock text-danger"></i> Bloqueados:</span>
                            <strong><?= $estadisticas['lotes_bloqueados'] ?? 0 ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <?php if (can('crear_lotes')): ?>
                        <a href="/lotes/create?proyecto_id=<?= $proyecto['id'] ?>" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-plus-circle"></i> Agregar Lote
                        </a>
                    <?php endif; ?>
                    
                    <a href="/lotes?proyecto_id=<?= $proyecto['id'] ?>" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-grid-3x3"></i> Ver Todos los Lotes
                    </a>

                    <?php if (can('editar_proyectos')): ?>
                        <a href="/proyectos/edit/<?= $proyecto['id'] ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-pencil"></i> Editar Proyecto
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
