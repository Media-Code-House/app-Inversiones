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
            <?php if (empty($lotes)): ?>
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
                                <th>Área (m²)</th>
                                <th>Precio Lista</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th class="text-center">Amortización</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lotes as $lote): ?>
                                <tr>
                                    <td>
                                        <small class="text-muted d-block"><?= htmlspecialchars($lote['proyecto_codigo']) ?></small>
                                        <?= htmlspecialchars($lote['proyecto_nombre']) ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong></td>
                                    <td><?= number_format($lote['area'], 2) ?> m²</td>
                                    <td>$<?= number_format($lote['precio_lista'], 0) ?></td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <?php if (!empty($lote['cliente_nombre'])): ?>
                                            <?= htmlspecialchars($lote['cliente_nombre']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($lote['tiene_amortizacion'] > 0): ?>
                                            <i class="bi bi-check-circle-fill text-success" 
                                               data-bs-toggle="tooltip" 
                                               title="Tiene plan de amortización"></i>
                                        <?php else: ?>
                                            <i class="bi bi-dash-circle text-muted" 
                                               data-bs-toggle="tooltip" 
                                               title="Sin amortización"></i>
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
                                            <a href="/lotes/edit/<?= $lote['id'] ?>" 
                                               class="btn btn-outline-secondary" 
                                               data-bs-toggle="tooltip" 
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <p class="text-muted mb-0">
                        Total: <strong><?= count($lotes) ?></strong> lote(s)
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
