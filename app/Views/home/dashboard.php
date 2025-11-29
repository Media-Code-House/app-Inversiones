<div class="container-fluid">
    <!-- Header del Dashboard -->
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

    <!-- Tarjetas de estadísticas (placeholder) -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Total Proyectos</div>
                            <div class="stat-value">0</div>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Lotes Disponibles</div>
                            <div class="stat-value">0</div>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Valor Inventario</div>
                            <div class="stat-value">$0</div>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card danger">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stat-label">Valor Ventas</div>
                            <div class="stat-value">$0</div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon text-danger">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensaje de inicio -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">¡Sistema Configurado Exitosamente!</h3>
                    <p class="text-muted mb-4">
                        El Módulo 1 (Arquitectura Base y Autenticación) ha sido completado.<br>
                        Las estadísticas se activarán en el Módulo 3.
                    </p>
                    <div class="alert alert-info mx-auto" style="max-width: 600px;">
                        <strong><i class="fas fa-info-circle"></i> Próximos pasos:</strong>
                        <ul class="list-unstyled mb-0 mt-2">
                            <li>✅ Módulo 1: Arquitectura Base (Completado)</li>
                            <li>⏳ Módulo 2: Sistema de Diseño Personalizado</li>
                            <li>⏳ Módulo 3: Dashboard y Lógica de Negocio</li>
                            <li>⏳ Módulo 4: CRUD de Proyectos</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
