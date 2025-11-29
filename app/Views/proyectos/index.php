<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-building"></i> Gestión de Proyectos
        </h1>
        <?php if (can('crear_proyectos')): ?>
            <a href="/proyectos/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Proyecto
            </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/proyectos" class="row g-3">
                <div class="col-md-4">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" name="busqueda" id="busqueda" class="form-control" 
                           placeholder="Código, nombre, ubicación..." 
                           value="<?= htmlspecialchars($filtros['busqueda'] ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= $estado ?>" 
                                <?= isset($filtros['estado']) && $filtros['estado'] === $estado ? 'selected' : '' ?>>
                                <?= ucfirst($estado) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <a href="/proyectos" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Mensajes de éxito/error -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Tabla de Proyectos -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($proyectos)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="lead text-muted mt-3">No se encontraron proyectos</p>
                    <?php if (can('crear_proyectos')): ?>
                        <a href="/proyectos/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Crear Primer Proyecto
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Ubicación</th>
                                <th>Total Lotes</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proyectos as $proyecto): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($proyecto['codigo']) ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($proyecto['nombre']) ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($proyecto['ubicacion']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= $proyecto['total_lotes'] ?? 0 ?> lotes
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $estadoBadge = [
                                            'planificacion' => 'bg-warning',
                                            'activo' => 'bg-success',
                                            'vendido' => 'bg-info',
                                            'suspendido' => 'bg-danger'
                                        ];
                                        $badgeClass = $estadoBadge[$proyecto['estado']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst($proyecto['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/proyectos/show/<?= $proyecto['id'] ?>" 
                                               class="btn btn-outline-info" 
                                               title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (can('editar_proyectos')): ?>
                                                <a href="/proyectos/edit/<?= $proyecto['id'] ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (can('eliminar_proyectos')): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        title="Eliminar"
                                                        onclick="confirmarEliminacion(<?= $proyecto['id'] ?>, '<?= htmlspecialchars($proyecto['nombre']) ?>', <?= $proyecto['total_lotes'] ?? 0 ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Resumen -->
                <div class="mt-3">
                    <small class="text-muted">
                        Mostrando <?= count($proyectos) ?> proyecto(s)
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" method="POST" style="display: none;">
    <?= csrfField() ?>
</form>

<script>
function confirmarEliminacion(proyectoId, nombreProyecto, totalLotes) {
    if (totalLotes > 0) {
        alert(`No se puede eliminar el proyecto "${nombreProyecto}" porque tiene ${totalLotes} lote(s) asociado(s).\n\nDebes eliminar los lotes primero.`);
        return;
    }
    
    const mensaje = `¿Estás seguro de que deseas eliminar el proyecto "${nombreProyecto}"?\n\nEsta acción NO se puede deshacer.`;
    
    if (confirm(mensaje)) {
        const form = document.getElementById('formEliminar');
        form.action = `/proyectos/delete/${proyectoId}`;
        form.submit();
    }
}
</script>
