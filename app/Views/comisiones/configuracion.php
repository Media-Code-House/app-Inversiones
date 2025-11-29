<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-gear-fill"></i> Configuración de Comisiones
        </h1>
        <a href="/comisiones" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Comisiones
        </a>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        <strong>Información:</strong> El porcentaje configurado se aplicará automáticamente a todas las ventas futuras de cada vendedor.
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th class="text-center">% Comisión Actual</th>
                            <th>Última Actualización</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendedores as $v): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($v['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars($v['email']) ?></td>
                                <td><span class="badge bg-primary"><?= ucfirst($v['rol']) ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6"><?= number_format($v['porcentaje_comision'], 2) ?>%</span>
                                </td>
                                <td>
                                    <?php if ($v['fecha_actualizacion']): ?>
                                        <?= date('d/m/Y H:i', strtotime($v['fecha_actualizacion'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Por defecto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditar"
                                            onclick="editarComision(<?= $v['id'] ?>, '<?= htmlspecialchars($v['nombre'], ENT_QUOTES) ?>', <?= $v['porcentaje_comision'] ?>, '<?= htmlspecialchars($v['observaciones'] ?? '', ENT_QUOTES) ?>')">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Comisión -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEditar">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Porcentaje de Comisión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Vendedor</label>
                        <input type="text" class="form-control" id="nombreVendedor" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="porcentaje_comision" class="form-label">
                            Porcentaje de Comisión <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="porcentaje_comision" id="porcentaje_comision" 
                                   class="form-control" step="0.01" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Ingrese un valor entre 0 y 100</small>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarComision(id, nombre, porcentaje, observaciones) {
    document.getElementById('nombreVendedor').value = nombre;
    document.getElementById('porcentaje_comision').value = porcentaje;
    document.getElementById('observaciones').value = observaciones || '';
    document.getElementById('formEditar').action = '/comisiones/actualizar-configuracion/' + id;
}
</script>
