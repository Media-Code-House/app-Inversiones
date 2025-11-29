<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-people-fill"></i> Gestión de Vendedores
        </h1>
        <div>
            <a href="/vendedores/ranking" class="btn btn-info me-2">
                <i class="bi bi-trophy-fill"></i> Ranking
            </a>
            <a href="/vendedores/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Vendedor
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/vendedores" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nombre, código, documento..." 
                           value="<?= htmlspecialchars($filtros['search']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" <?= $filtros['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= $filtros['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        <option value="suspendido" <?= $filtros['estado'] == 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="/vendedores" class="btn btn-secondary w-100">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Vendedores -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($vendedores)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay vendedores registrados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Vendedor</th>
                                <th>Documento</th>
                                <th>Contacto</th>
                                <th>Fecha Ingreso</th>
                                <th class="text-center">% Comisión</th>
                                <th class="text-center">Ventas</th>
                                <th class="text-end">Total Vendido</th>
                                <th class="text-end">Comisiones</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendedores as $v): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($v['codigo_vendedor']) ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($v['nombre_completo']) ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($v['email']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($v['tipo_documento']) ?>
                                        <?= htmlspecialchars($v['numero_documento']) ?>
                                    </td>
                                    <td>
                                        <?php if ($v['celular']): ?>
                                            <i class="bi bi-phone"></i> <?= htmlspecialchars($v['celular']) ?>
                                        <?php elseif ($v['telefono']): ?>
                                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($v['telefono']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($v['fecha_ingreso'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?= number_format($v['porcentaje_comision_default'], 2) ?>%</span>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= $v['total_lotes_vendidos'] ?></strong>
                                    </td>
                                    <td class="text-end"><?= formatMoney($v['valor_total_vendido']) ?></td>
                                    <td class="text-end">
                                        <div><?= formatMoney($v['total_comisiones_generadas']) ?></div>
                                        <small class="text-success">Pagadas: <?= formatMoney($v['comisiones_pagadas']) ?></small>
                                        <small class="text-warning d-block">Pendientes: <?= formatMoney($v['comisiones_pendientes']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($v['estado'] === 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php elseif ($v['estado'] === 'inactivo'): ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Suspendido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="/vendedores/show/<?= $v['id'] ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/vendedores/edit/<?= $v['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
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
