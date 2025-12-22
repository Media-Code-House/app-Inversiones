<?php
/**
 * Vista: Detalle del Proyecto
 * Vista principal para gestionar proyecto y sus lotes asociados
 */
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-building"></i> <?= htmlspecialchars($proyecto['nombre']) ?>
            </h1>
            <small class="text-muted">Código: <?= htmlspecialchars($proyecto['codigo']) ?></small>
        </div>
        <div>
            <?php if (can('crear_lotes')): ?>
                <a href="/lotes/create?proyecto_id=<?= $proyecto['id'] ?>" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Nuevo Lote
                </a>
            <?php endif; ?>
            <?php if (can('editar_proyectos')): ?>
                <a href="/proyectos/edit/<?= $proyecto['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar Proyecto
                </a>
            <?php endif; ?>
            <?php if (can('eliminar_proyectos')): ?>
                <button type="button" class="btn btn-danger" onclick="confirmarEliminacionProyecto()">
                    <i class="bi bi-trash"></i> Eliminar Proyecto
                </button>
            <?php endif; ?>
            <a href="/proyectos" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Mensajes -->
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

            <!-- Plano del Proyecto Interactivo -->
            <?php if (!empty($proyecto['plano_imagen'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-map"></i> Plano del Proyecto</h5>
                        <?php if (can('editar_proyectos')): ?>
                            <a href="/proyectos/edit/<?= $proyecto['id'] ?>#planoContainer" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Editar Posiciones
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Leyenda:</strong>
                            <span class="badge bg-success ms-2">Disponible</span>
                            <span class="badge bg-warning ms-2">Reservado</span>
                            <span class="badge bg-info ms-2">Vendido</span>
                            <span class="badge bg-secondary ms-2">Bloqueado</span>
                        </div>
                        
                        <div class="position-relative" id="planoContainer">
                            <img src="/<?= htmlspecialchars($proyecto['plano_imagen']) ?>" 
                                 id="planoImagen"
                                 alt="Plano del proyecto" 
                                 class="img-fluid border rounded shadow"
                                 style="width: 100%; height: auto; display: block;">
                            <div id="lotesLayer" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></div>
                        </div>
                        
                        <div id="loteInfo" class="alert alert-info mt-3" style="display: none;">
                            <h6 class="mb-2"><strong id="infoCodigoLote"></strong></h6>
                            <p class="mb-1"><strong>Estado:</strong> <span id="infoEstado"></span></p>
                            <p class="mb-1"><strong>Manzana:</strong> <span id="infoManzana"></span></p>
                            <p class="mb-1"><strong>Área:</strong> <span id="infoArea"></span> m²</p>
                            <p class="mb-1"><strong>Precio:</strong> $<span id="infoPrecio"></span></p>
                            <p class="mb-0" id="infoClienteContainer" style="display: none;">
                                <strong>Cliente:</strong> <span id="infoCliente"></span>
                            </p>
                            <div class="mt-2">
                                <a id="infoVerLote" href="#" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Estadísticas y Valores -->
        <div class="col-lg-4">
            <!-- Estadísticas de Lotes -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Resumen de Lotes</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <label class="text-muted">Total de Lotes</label>
                        <h2 class="text-primary mb-0"><?= $estadisticas['total_lotes_registrados'] ?? 0 ?></h2>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-check-circle text-success"></i> Disponibles</span>
                            <span class="badge bg-success"><?= $estadisticas['lotes_disponibles'] ?? 0 ?></span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-clock text-warning"></i> Reservados</span>
                            <span class="badge bg-warning"><?= $estadisticas['lotes_reservados'] ?? 0 ?></span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-cart-check text-primary"></i> Vendidos</span>
                            <span class="badge bg-primary"><?= $estadisticas['lotes_vendidos'] ?? 0 ?></span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-lock text-danger"></i> Bloqueados</span>
                            <span class="badge bg-danger"><?= $estadisticas['lotes_bloqueados'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valores Financieros -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Valores</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Inventario Disponible</label>
                        <p class="h5 text-success mb-0">
                            $<?= number_format($estadisticas['valor_inventario'] ?? 0, 0) ?>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Total Vendido</label>
                        <p class="h5 text-primary mb-0">
                            $<?= number_format($estadisticas['valor_ventas'] ?? 0, 0) ?>
                        </p>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted">Precio Promedio:</small><br>
                        <strong>$<?= number_format($estadisticas['precio_promedio'] ?? 0, 0) ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Rango de Precios:</small><br>
                        <strong>$<?= number_format($estadisticas['precio_minimo'] ?? 0, 0) ?> - $<?= number_format($estadisticas['precio_maximo'] ?? 0, 0) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Lotes del Proyecto -->
    <div class="card mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-grid-3x3"></i> Lotes del Proyecto</h5>
            <?php if (can('crear_lotes')): ?>
                <a href="/lotes/create?proyecto_id=<?= $proyecto['id'] ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-circle"></i> Nuevo Lote
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($lotes)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-3 text-muted"></i>
                    <p class="lead text-muted mt-3">No hay lotes registrados en este proyecto</p>
                    <?php if (can('crear_lotes')): ?>
                        <a href="/lotes/create?proyecto_id=<?= $proyecto['id'] ?>" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Crear Primer Lote
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Área (m²)</th>
                                <th>Precio Lista</th>
                                <th>Precio/m²</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lotes as $lote): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong>
                                        <?php if (!empty($lote['ubicacion'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($lote['ubicacion']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($lote['area_m2'], 2) ?> m²</td>
                                    <td class="fw-bold text-success">
                                        $<?= number_format($lote['precio_lista'], 0) ?>
                                    </td>
                                    <td class="text-muted">
                                        $<?= number_format($lote['precio_m2'], 0) ?>/m²
                                    </td>
                                    <td>
                                        <?php
                                        $estadoBadge = [
                                            'disponible' => 'bg-success',
                                            'reservado' => 'bg-warning',
                                            'vendido' => 'bg-primary',
                                            'bloqueado' => 'bg-danger'
                                        ];
                                        $badgeClass = $estadoBadge[$lote['estado']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst($lote['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($lote['cliente_nombre'])): ?>
                                            <small>
                                                <?= htmlspecialchars($lote['cliente_nombre']) ?><br>
                                                <span class="text-muted"><?= htmlspecialchars($lote['cliente_documento'] ?? '') ?></span>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">Sin asignar</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/lotes/show/<?= $lote['id'] ?>" 
                                               class="btn btn-outline-info" 
                                               title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (can('editar_lotes')): ?>
                                                <a href="/lotes/edit/<?= $lote['id'] ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (can('eliminar_lotes')): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        title="Eliminar"
                                                        onclick="confirmarEliminacionLote(<?= $lote['id'] ?>, '<?= htmlspecialchars($lote['codigo_lote']) ?>', '<?= $lote['estado'] ?>')">
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

                <div class="mt-3">
                    <small class="text-muted">
                        Mostrando <?= count($lotes) ?> lote(s) de <?= $estadisticas['total_lotes_registrados'] ?? 0 ?> total
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar proyecto -->
<form id="formEliminarProyecto" method="POST" action="/proyectos/delete/<?= $proyecto['id'] ?>" style="display: none;">
    <?= csrfField() ?>
</form>

<!-- Formulario oculto para eliminar lote -->
<form id="formEliminarLote" method="POST" style="display: none;">
    <?= csrfField() ?>
</form>

<script>
function confirmarEliminacionProyecto() {
    const totalLotes = <?= $estadisticas['total_lotes_registrados'] ?? 0 ?>;
    
    if (totalLotes > 0) {
        alert(`No se puede eliminar el proyecto porque tiene ${totalLotes} lote(s) asociado(s).\n\nDebes eliminar todos los lotes primero.`);
        return;
    }
    
    const mensaje = '¿Estás seguro de que deseas eliminar este proyecto?\n\n' +
                    'Proyecto: <?= htmlspecialchars($proyecto['nombre']) ?>\n' +
                    'Código: <?= htmlspecialchars($proyecto['codigo']) ?>\n\n' +
                    '⚠️ Esta acción NO se puede deshacer.';
    
    if (confirm(mensaje)) {
        document.getElementById('formEliminarProyecto').submit();
    }
}

function confirmarEliminacionLote(loteId, codigoLote, estado) {
    let advertencia = '';
    
    if (estado === 'vendido') {
        advertencia = '\n\n⚠️ ATENCIÓN: Este lote está VENDIDO. Se perderá toda la información de venta y pagos.';
    } else if (estado === 'reservado') {
        advertencia = '\n\n⚠️ ATENCIÓN: Este lote está RESERVADO.';
    }
    
    const mensaje = `¿Estás seguro de que deseas eliminar el lote "${codigoLote}"?${advertencia}\n\nEsta acción NO se puede deshacer.`;
    
    if (confirm(mensaje)) {
        const form = document.getElementById('formEliminarLote');
        form.action = `/lotes/delete/${loteId}`;
        form.submit();
    }
}
</script>

<!-- JavaScript para visualizar lotes en el plano -->
<?php if (!empty($proyecto['plano_imagen'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const planoImagen = document.getElementById('planoImagen');
    const lotesLayer = document.getElementById('lotesLayer');
    const loteInfo = document.getElementById('loteInfo');
    
    let lotes = [];
    
    // Colores según estado
    const coloresEstado = {
        'disponible': '#28a745',
        'reservado': '#ffc107',
        'vendido': '#17a2b8',
        'bloqueado': '#6c757d'
    };
    
    // Cargar lotes desde el servidor
    fetch('/proyectos/lotes-coordenadas/<?= $proyecto['id'] ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                lotes = data.lotes;
                renderLotes();
            }
        })
        .catch(error => console.error('Error al cargar lotes:', error));
    
    // Renderizar puntos de lotes en el plano
    function renderLotes() {
        lotesLayer.innerHTML = '';
        
        lotes.forEach(lote => {
            const x = parseFloat(lote.plano_x);
            const y = parseFloat(lote.plano_y);
            
            // Solo mostrar lotes con coordenadas válidas guardadas
            if (lote.plano_x !== null && lote.plano_y !== null && 
                !isNaN(x) && !isNaN(y)) {
                crearPunto(lote);
            }
        });
    }
    
    // Crear punto visual para un lote
    function crearPunto(lote) {
        const punto = document.createElement('div');
        punto.className = 'lote-punto';
        punto.dataset.loteId = lote.id;
        punto.style.cssText = `
            position: absolute;
            left: ${lote.plano_x}%;
            top: ${lote.plano_y}%;
            width: 24px;
            height: 24px;
            background-color: ${coloresEstado[lote.estado] || '#6c757d'};
            border: 3px solid white;
            border-radius: 50%;
            cursor: pointer;
            transform: translate(-50%, -50%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            z-index: 10;
            pointer-events: all;
            transition: all 0.2s ease;
        `;
        
        // Hover effect
        punto.addEventListener('mouseenter', function() {
            this.style.width = '32px';
            this.style.height = '32px';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.5)';
            this.style.zIndex = '20';
        });
        
        punto.addEventListener('mouseleave', function() {
            this.style.width = '24px';
            this.style.height = '24px';
            this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.3)';
            this.style.zIndex = '10';
        });
        
        // Click para mostrar información
        punto.addEventListener('click', function(e) {
            e.stopPropagation();
            mostrarInfoLote(lote);
        });
        
        lotesLayer.appendChild(punto);
    }
    
    // Mostrar información del lote
    function mostrarInfoLote(lote) {
        document.getElementById('infoCodigoLote').textContent = lote.codigo_lote;
        
        const estadoBadge = {
            'disponible': '<span class="badge bg-success">Disponible</span>',
            'reservado': '<span class="badge bg-warning">Reservado</span>',
            'vendido': '<span class="badge bg-info">Vendido</span>',
            'bloqueado': '<span class="badge bg-secondary">Bloqueado</span>'
        };
        document.getElementById('infoEstado').innerHTML = estadoBadge[lote.estado] || lote.estado;
        
        document.getElementById('infoManzana').textContent = lote.manzana || 'N/A';
        document.getElementById('infoArea').textContent = parseFloat(lote.area_m2).toFixed(2);
        document.getElementById('infoPrecio').textContent = new Intl.NumberFormat('es-CO').format(lote.precio_lista);
        
        const clienteContainer = document.getElementById('infoClienteContainer');
        if (lote.cliente_nombre) {
            document.getElementById('infoCliente').textContent = lote.cliente_nombre;
            clienteContainer.style.display = 'block';
        } else {
            clienteContainer.style.display = 'none';
        }
        
        document.getElementById('infoVerLote').href = '/lotes/show/' + lote.id;
        
        loteInfo.style.display = 'block';
        loteInfo.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Click fuera del info para ocultarlo
    document.addEventListener('click', function(e) {
        if (!loteInfo.contains(e.target) && !e.target.classList.contains('lote-punto')) {
            loteInfo.style.display = 'none';
        }
    });
});
</script>
<?php endif; ?>
