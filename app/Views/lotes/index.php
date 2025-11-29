<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-grid-3x3"></i> Gestión de Lotes
        </h1>
        <a href="/lotes/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Lote
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/lotes" class="row g-3">
                <div class="col-md-4">
                    <label for="proyecto_id" class="form-label">Proyecto</label>
                    <select name="proyecto_id" id="proyecto_id" class="form-select">
                        <option value="">Todos los proyectos</option>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <option value="<?= $proyecto['id'] ?>" 
                                <?= isset($filtros['proyecto_id']) && $filtros['proyecto_id'] == $proyecto['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($proyecto['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= $estado ?>" 
                                <?= isset($filtros['estado']) && $filtros['estado'] == $estado ? 'selected' : '' ?>>
                                <?= ucfirst($estado) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" name="busqueda" id="busqueda" class="form-control" 
                           placeholder="Código, ubicación..." 
                           value="<?= isset($filtros['busqueda']) ? htmlspecialchars($filtros['busqueda']) : '' ?>">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="/lotes" class="btn btn-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Lotes -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($lotes['data'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No se encontraron lotes con los filtros seleccionados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Proyecto</th>
                                <th>Código</th>
                                <th>Manzana</th>
                                <th>Área (m²)</th>
                                <th>Precio Lista</th>
                                <th>Precio/m²</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lotes['data'] as $lote): ?>
                                <tr>
                                    <td>
                                        <small class="text-muted d-block"><?= htmlspecialchars($lote['proyecto_codigo']) ?></small>
                                        <strong><?= htmlspecialchars($lote['proyecto_nombre']) ?></strong>
                                    </td>
                                    <td><span class="badge bg-dark"><?= htmlspecialchars($lote['codigo_lote']) ?></span></td>
                                    <td><?= htmlspecialchars($lote['manzana']) ?></td>
                                    <td><?= number_format($lote['area_m2'], 2) ?> m²</td>
                                    <td><strong>$<?= number_format($lote['precio_lista'], 0) ?></strong></td>
                                    <td class="text-muted">$<?= number_format($lote['precio_m2'], 0) ?>/m²</td>
                                    <td>
                                        <span class="badge <?= $lote['badgeClass'] ?>">
                                            <?= ucfirst($lote['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($lote['cliente_nombre'])): ?>
                                            <i class="bi bi-person-fill text-primary"></i>
                                            <?= htmlspecialchars($lote['cliente_nombre']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($lote['vendedor_nombre'])): ?>
                                            <i class="bi bi-briefcase-fill text-success"></i>
                                            <?= htmlspecialchars($lote['vendedor_nombre']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/lotes/show/<?= $lote['id'] ?>" 
                                               class="btn btn-outline-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (can('editar_lotes')): ?>
                                            <a href="/lotes/edit/<?= $lote['id'] ?>" 
                                               class="btn btn-outline-secondary" 
                                               data-bs-toggle="tooltip" 
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($lote['estado'] === 'vendido'): ?>
                                                <?php if ($lote['tiene_amortizacion'] > 0): ?>
                                                <a href="/lotes/amortizacion/show/<?= $lote['id'] ?>" 
                                                   class="btn btn-outline-info" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Ver amortización">
                                                    <i class="bi bi-calendar-check"></i>
                                                </a>
                                                <?php elseif (can('crear_amortizacion')): ?>
                                                <a href="/lotes/amortizacion/create/<?= $lote['id'] ?>" 
                                                   class="btn btn-outline-success" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Crear plan de amortización">
                                                    <i class="bi bi-calendar-plus"></i>
                                                </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($lotes['last_page'] > 1): ?>
                <nav aria-label="Paginación de lotes" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Botón Anterior -->
                        <li class="page-item <?= $lotes['current_page'] == 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $lotes['current_page'] - 1])) ?>">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        </li>

                        <!-- Páginas -->
                        <?php 
                        $startPage = max(1, $lotes['current_page'] - 2);
                        $endPage = min($lotes['last_page'], $lotes['current_page'] + 2);
                        
                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => 1])) ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $lotes['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($endPage < $lotes['last_page']): ?>
                            <?php if ($endPage < $lotes['last_page'] - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $lotes['last_page']])) ?>">
                                    <?= $lotes['last_page'] ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Botón Siguiente -->
                        <li class="page-item <?= $lotes['current_page'] == $lotes['last_page'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $lotes['current_page'] + 1])) ?>">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <p class="text-muted mb-0">
                        Mostrando <strong><?= count($lotes['data']) ?></strong> de <strong><?= number_format($lotes['total']) ?></strong> lote(s)
                    </p>
                    <p class="text-muted mb-0">
                        Página <strong><?= $lotes['current_page'] ?></strong> de <strong><?= $lotes['last_page'] ?></strong>
                    </p>
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
