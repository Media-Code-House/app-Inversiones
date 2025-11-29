<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-bar-chart-line-fill"></i> Reportes y Análisis
            </h1>
            <p class="text-muted mb-0">Business Intelligence - Panel de Reportes</p>
        </div>
        <a href="/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <!-- Cards de Reportes -->
    <div class="row g-4">
        
        <!-- Reporte 1: Lotes Vendidos -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-cash-coin text-success" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title mb-0">Lotes Vendidos</h5>
                    </div>
                    <p class="card-text text-muted flex-grow-1">
                        Reporte detallado de todos los lotes vendidos con información del cliente, 
                        vendedor, fecha de venta y comisiones generadas.
                    </p>
                    <div class="mt-auto">
                        <a href="/reportes/lotes-vendidos" class="btn btn-success w-100">
                            <i class="bi bi-arrow-right-circle"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reporte 2: Ventas por Proyecto -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title mb-0">Ventas por Proyecto</h5>
                    </div>
                    <p class="card-text text-muted flex-grow-1">
                        Análisis comparativo de ventas entre proyectos con gráficos visuales. 
                        Identifica los proyectos más rentables y su porcentaje de avance.
                    </p>
                    <div class="mt-auto">
                        <a href="/reportes/ventas-proyecto" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-right-circle"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reporte 3: Ventas por Vendedor -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                            <i class="bi bi-person-badge text-info" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title mb-0">Ventas por Vendedor</h5>
                    </div>
                    <p class="card-text text-muted flex-grow-1">
                        Desempeño individual de cada vendedor: total de ventas realizadas, 
                        montos y comisiones generadas en el periodo seleccionado.
                    </p>
                    <div class="mt-auto">
                        <a href="/reportes/ventas-vendedor" class="btn btn-info w-100">
                            <i class="bi bi-arrow-right-circle"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reporte 4: Cartera Pendiente -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title mb-0">Cartera Pendiente</h5>
                    </div>
                    <p class="card-text text-muted flex-grow-1">
                        Control de cuotas pendientes y en mora. Identifica clientes con atrasos 
                        y el valor total de la cartera por cobrar.
                    </p>
                    <div class="mt-auto">
                        <a href="/reportes/cartera" class="btn btn-danger w-100">
                            <i class="bi bi-arrow-right-circle"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reporte 5: Estado de Clientes -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                            <i class="bi bi-people text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title mb-0">Estado de Clientes</h5>
                    </div>
                    <p class="card-text text-muted flex-grow-1">
                        Resumen financiero consolidado por cliente: lotes comprados, 
                        saldo pendiente, estado de crédito y comportamiento de pago.
                    </p>
                    <div class="mt-auto">
                        <a href="/reportes/estado-clientes" class="btn btn-warning w-100">
                            <i class="bi bi-arrow-right-circle"></i> Ver Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Adicional: Información -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 bg-light">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-info-circle text-secondary" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title mb-0">Información</h5>
                    </div>
                    <p class="card-text text-muted flex-grow-1">
                        <strong>Funcionalidades disponibles:</strong><br>
                        • Filtros dinámicos por fecha y proyecto<br>
                        • Exportación a PDF y Excel<br>
                        • Gráficos interactivos<br>
                        • Análisis en tiempo real
                    </p>
                    <div class="mt-auto">
                        <button class="btn btn-outline-secondary w-100" disabled>
                            <i class="bi bi-gear"></i> Configuración (Próximamente)
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- KPIs Rápidos (Opcional) -->
    <div class="row mt-5">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-speedometer2"></i> KPIs Generales del Sistema</h5>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <i class="bi bi-cash-stack text-success" style="font-size: 2.5rem;"></i>
                    <h6 class="mt-2 text-muted">Total Ventas</h6>
                    <p class="mb-0 text-muted small">Ver en reportes detallados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <i class="bi bi-building text-primary" style="font-size: 2.5rem;"></i>
                    <h6 class="mt-2 text-muted">Proyectos Activos</h6>
                    <p class="mb-0 text-muted small">Ver en reportes detallados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <i class="bi bi-wallet2 text-danger" style="font-size: 2.5rem;"></i>
                    <h6 class="mt-2 text-muted">Cartera Pendiente</h6>
                    <p class="mb-0 text-muted small">Ver en reportes detallados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <i class="bi bi-people text-info" style="font-size: 2.5rem;"></i>
                    <h6 class="mt-2 text-muted">Clientes Activos</h6>
                    <p class="mb-0 text-muted small">Ver en reportes detallados</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.card-title {
    font-weight: 600;
}
</style>
