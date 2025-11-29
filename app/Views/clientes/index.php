<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-people-fill"></i> Gestión de Clientes
        </h1>
        <?php if (can('crear_clientes')): ?>
        <a href="/clientes/create" class="btn btn-primary">
            <i class="bi bi-person-plus-fill"></i> Nuevo Cliente
        </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/clientes" class="row g-3">
                <div class="col-md-4">
                    <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                    <select name="tipo_documento" id="tipo_documento" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="CC" <?= isset($filtros['tipo_documento']) && $filtros['tipo_documento'] == 'CC' ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                        <option value="NIT" <?= isset($filtros['tipo_documento']) && $filtros['tipo_documento'] == 'NIT' ? 'selected' : '' ?>>NIT</option>
                        <option value="CE" <?= isset($filtros['tipo_documento']) && $filtros['tipo_documento'] == 'CE' ? 'selected' : '' ?>>Cédula de Extranjería</option>
                        <option value="pasaporte" <?= isset($filtros['tipo_documento']) && $filtros['tipo_documento'] == 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Nombre, documento, email..." 
                           value="<?= isset($filtros['search']) ? htmlspecialchars($filtros['search']) : '' ?>">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="/clientes" class="btn btn-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($clientes['data'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No se encontraron clientes con los filtros seleccionados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Ciudad</th>
                                <th class="text-center">Propiedades</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes['data'] as $cliente): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($cliente['tipo_documento']) ?></span>
                                        <br><?= htmlspecialchars($cliente['numero_documento']) ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($cliente['nombre']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($cliente['telefono'])): ?>
                                            <i class="bi bi-telephone-fill text-success"></i>
                                            <?= htmlspecialchars($cliente['telefono']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($cliente['email'])): ?>
                                            <i class="bi bi-envelope-fill text-primary"></i>
                                            <small><?= htmlspecialchars($cliente['email']) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($cliente['ciudad'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <?php if ($cliente['total_propiedades'] > 0): ?>
                                            <span class="badge bg-info">
                                                <?= $cliente['total_propiedades'] ?> 
                                                <?= $cliente['total_propiedades'] == 1 ? 'propiedad' : 'propiedades' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/clientes/show/<?= $cliente['id'] ?>" 
                                               class="btn btn-outline-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (can('editar_clientes')): ?>
                                            <a href="/clientes/edit/<?= $cliente['id'] ?>" 
                                               class="btn btn-outline-secondary" 
                                               data-bs-toggle="tooltip" 
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($clientes['last_page'] > 1): ?>
                <nav aria-label="Paginación de clientes" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $clientes['current_page'] == 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $clientes['current_page'] - 1])) ?>">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        </li>

                        <?php 
                        $startPage = max(1, $clientes['current_page'] - 2);
                        $endPage = min($clientes['last_page'], $clientes['current_page'] + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $clientes['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $clientes['current_page'] == $clientes['last_page'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['page' => $clientes['current_page'] + 1])) ?>">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <p class="text-muted mb-0">
                        Mostrando <strong><?= count($clientes['data']) ?></strong> de <strong><?= number_format($clientes['total']) ?></strong> cliente(s)
                    </p>
                    <p class="text-muted mb-0">
                        Página <strong><?= $clientes['current_page'] ?></strong> de <strong><?= $clientes['last_page'] ?></strong>
                    </p>
                </div>
                <?php endif; ?>
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
