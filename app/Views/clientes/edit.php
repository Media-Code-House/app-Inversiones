<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-pencil-square"></i> Editar Cliente
        </h1>
        <a href="/clientes/show/<?= $cliente['id'] ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/clientes/update/<?= $cliente['id'] ?>" id="formCliente">
                        <?= csrfField() ?>

                        <div class="row g-3">
                            <!-- Tipo de Documento -->
                            <div class="col-md-4">
                                <label for="tipo_documento" class="form-label">
                                    Tipo de Documento <span class="text-danger">*</span>
                                </label>
                                <select name="tipo_documento" id="tipo_documento" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="CC" <?= ($cliente['tipo_documento'] ?? old('tipo_documento')) == 'CC' ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                                    <option value="NIT" <?= ($cliente['tipo_documento'] ?? old('tipo_documento')) == 'NIT' ? 'selected' : '' ?>>NIT</option>
                                    <option value="CE" <?= ($cliente['tipo_documento'] ?? old('tipo_documento')) == 'CE' ? 'selected' : '' ?>>Cédula de Extranjería</option>
                                    <option value="pasaporte" <?= ($cliente['tipo_documento'] ?? old('tipo_documento')) == 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                </select>
                                <?php if (hasError('tipo_documento')): ?>
                                    <div class="invalid-feedback d-block"><?= getError('tipo_documento') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Número de Documento -->
                            <div class="col-md-8">
                                <label for="numero_documento" class="form-label">
                                    Número de Documento <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="numero_documento" 
                                       id="numero_documento" 
                                       class="form-control <?= hasError('numero_documento') ? 'is-invalid' : '' ?>" 
                                       value="<?= $cliente['numero_documento'] ?? old('numero_documento') ?>" 
                                       required
                                       maxlength="20">
                                <?php if (hasError('numero_documento')): ?>
                                    <div class="invalid-feedback"><?= getError('numero_documento') ?></div>
                                <?php endif; ?>
                                <div class="form-text">Sin puntos ni espacios</div>
                            </div>

                            <!-- Nombre Completo -->
                            <div class="col-12">
                                <label for="nombre" class="form-label">
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre" 
                                       id="nombre" 
                                       class="form-control <?= hasError('nombre') ? 'is-invalid' : '' ?>" 
                                       value="<?= $cliente['nombre'] ?? old('nombre') ?>" 
                                       required
                                       maxlength="200">
                                <?php if (hasError('nombre')): ?>
                                    <div class="invalid-feedback"><?= getError('nombre') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">
                                    Teléfono <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="telefono" 
                                       id="telefono" 
                                       class="form-control <?= hasError('telefono') ? 'is-invalid' : '' ?>" 
                                       value="<?= $cliente['telefono'] ?? old('telefono') ?>" 
                                       required
                                       maxlength="20">
                                <?php if (hasError('telefono')): ?>
                                    <div class="invalid-feedback"><?= getError('telefono') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    Email
                                </label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       class="form-control <?= hasError('email') ? 'is-invalid' : '' ?>" 
                                       value="<?= $cliente['email'] ?? old('email') ?>"
                                       maxlength="100">
                                <?php if (hasError('email')): ?>
                                    <div class="invalid-feedback"><?= getError('email') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Dirección -->
                            <div class="col-12">
                                <label for="direccion" class="form-label">
                                    Dirección
                                </label>
                                <input type="text" 
                                       name="direccion" 
                                       id="direccion" 
                                       class="form-control <?= hasError('direccion') ? 'is-invalid' : '' ?>" 
                                       value="<?= $cliente['direccion'] ?? old('direccion') ?>"
                                       maxlength="255">
                                <?php if (hasError('direccion')): ?>
                                    <div class="invalid-feedback"><?= getError('direccion') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Ciudad -->
                            <div class="col-md-6">
                                <label for="ciudad" class="form-label">
                                    Ciudad
                                </label>
                                <input type="text" 
                                       name="ciudad" 
                                       id="ciudad" 
                                       class="form-control <?= hasError('ciudad') ? 'is-invalid' : '' ?>" 
                                       value="<?= $cliente['ciudad'] ?? old('ciudad') ?>"
                                       maxlength="100">
                                <?php if (hasError('ciudad')): ?>
                                    <div class="invalid-feedback"><?= getError('ciudad') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Observaciones -->
                            <div class="col-12">
                                <label for="observaciones" class="form-label">
                                    Observaciones
                                </label>
                                <textarea name="observaciones" 
                                          id="observaciones" 
                                          class="form-control <?= hasError('observaciones') ? 'is-invalid' : '' ?>" 
                                          rows="3"><?= $cliente['observaciones'] ?? old('observaciones') ?></textarea>
                                <?php if (hasError('observaciones')): ?>
                                    <div class="invalid-feedback"><?= getError('observaciones') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <div>
                                <?php if (can('eliminar_clientes') && $cliente['total_propiedades'] == 0): ?>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                                    <i class="bi bi-trash"></i> Eliminar Cliente
                                </button>
                                <?php endif; ?>
                            </div>
                            <div>
                                <a href="/clientes/show/<?= $cliente['id'] ?>" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<?php if (can('eliminar_clientes') && $cliente['total_propiedades'] == 0): ?>
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarLabel">
                    <i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el cliente <strong><?= htmlspecialchars($cliente['nombre']) ?></strong>?</p>
                <p class="text-danger mb-0"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="/clientes/delete/<?= $cliente['id'] ?>" class="d-inline">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación en tiempo real del número de documento
    const tipoDocumento = document.getElementById('tipo_documento');
    const numeroDocumento = document.getElementById('numero_documento');
    
    tipoDocumento.addEventListener('change', function() {
        if (this.value === 'NIT') {
            numeroDocumento.placeholder = 'Ej: 900123456';
        } else if (this.value === 'CC') {
            numeroDocumento.placeholder = 'Ej: 1234567890';
        } else {
            numeroDocumento.placeholder = '';
        }
    });
    
    // Solo permitir números para CC y NIT
    numeroDocumento.addEventListener('input', function() {
        const tipo = tipoDocumento.value;
        if (tipo === 'CC' || tipo === 'NIT') {
            this.value = this.value.replace(/[^0-9]/g, '');
        }
    });
});
</script>
