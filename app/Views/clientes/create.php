<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-person-plus-fill"></i> Nuevo Cliente
        </h1>
        <a href="/clientes" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/clientes/store" id="formCliente">
                        <?= csrfField() ?>

                        <div class="row g-3">
                            <!-- Tipo de Documento -->
                            <div class="col-md-4">
                                <label for="tipo_documento" class="form-label">
                                    Tipo de Documento <span class="text-danger">*</span>
                                </label>
                                <select name="tipo_documento" id="tipo_documento" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="CC" <?= old('tipo_documento') == 'CC' ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                                    <option value="NIT" <?= old('tipo_documento') == 'NIT' ? 'selected' : '' ?>>NIT</option>
                                    <option value="CE" <?= old('tipo_documento') == 'CE' ? 'selected' : '' ?>>Cédula de Extranjería</option>
                                    <option value="pasaporte" <?= old('tipo_documento') == 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
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
                                       value="<?= old('numero_documento') ?>" 
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
                                       value="<?= old('nombre') ?>" 
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
                                       value="<?= old('telefono') ?>" 
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
                                       value="<?= old('email') ?>"
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
                                       value="<?= old('direccion') ?>"
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
                                       value="<?= old('ciudad') ?>"
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
                                          rows="3"><?= old('observaciones') ?></textarea>
                                <?php if (hasError('observaciones')): ?>
                                    <div class="invalid-feedback"><?= getError('observaciones') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="/clientes" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación en tiempo real del número de documento
    const tipoDocumento = document.getElementById('tipo_documento');
    const numeroDocumento = document.getElementById('numero_documento');
    
    tipoDocumento.addEventListener('change', function() {
        numeroDocumento.value = '';
        
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
