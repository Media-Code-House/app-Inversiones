<!-- Perfil de Usuario - Módulo Robustecido con Integridad de Datos -->
<div class="container-fluid py-4">
    
    <!-- Mensajes Flash -->
    <?php if ($flash = getFlash()): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>-fill me-2"></i>
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Header con Información del Usuario -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <!-- Avatar -->
                        <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                             style="width: 80px; height: 80px; border-radius: 50%; font-size: 32px; font-weight: bold;">
                            <?= strtoupper(substr($user['nombre'], 0, 1)) ?>
                        </div>
                        
                        <!-- Información Principal -->
                        <div class="flex-grow-1">
                            <h3 class="mb-1"><?= e($user['nombre']) ?></h3>
                            <p class="text-muted mb-2">
                                <i class="bi bi-envelope me-2"></i><?= e($user['email']) ?>
                            </p>
                            
                            <!-- Badge de Rol -->
                            <?php
                            $rolBadges = [
                                'administrador' => ['class' => 'bg-danger', 'icon' => 'bi-shield-fill-check', 'text' => 'Administrador'],
                                'consulta' => ['class' => 'bg-warning text-dark', 'icon' => 'bi-eye-fill', 'text' => 'Consulta'],
                                'vendedor' => ['class' => 'bg-info text-white', 'icon' => 'bi-person-badge-fill', 'text' => 'Vendedor']
                            ];
                            $rolInfo = $rolBadges[$user['rol']] ?? ['class' => 'bg-secondary', 'icon' => 'bi-person', 'text' => ucfirst($user['rol'])];
                            ?>
                            
                            <span class="badge <?= $rolInfo['class'] ?> px-3 py-2">
                                <i class="bi <?= $rolInfo['icon'] ?> me-1"></i>
                                <?= $rolInfo['text'] ?>
                            </span>
                            
                            <?php if ($user['activo']): ?>
                                <span class="badge bg-success ms-2 px-3 py-2">
                                    <i class="bi bi-check-circle-fill me-1"></i>Activo
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-2 px-3 py-2">
                                    <i class="bi bi-x-circle-fill me-1"></i>Inactivo
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Información de Fechas usando Helper formatDateTime -->
                        <div class="text-end text-muted small">
                            <div><i class="bi bi-calendar-plus me-1"></i>Registrado: <?= formatDateTime($user['created_at'], 'd/m/Y') ?></div>
                            <div><i class="bi bi-calendar-check me-1"></i>Actualizado: <?= formatDateTime($user['updated_at']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TARJETA 1: DATOS PERSONALES (Siempre Visible) -->
    <!-- ============================================ -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary bg-gradient text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        1. Datos Personales
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Formulario de Actualización de Datos Personales -->
                    <form action="/perfil/update" method="POST" id="formDatosPersonales">
                        <?= csrfField() ?>
                        
                        <!-- Nombre de Usuario -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-bold">
                                <i class="bi bi-person me-1"></i>Nombre de Usuario
                                <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg" 
                                id="nombre" 
                                name="nombre" 
                                value="<?= e(old('nombre', $user['nombre'])) ?>"
                                required
                                maxlength="100"
                            >
                            <small class="text-muted">Tu nombre de usuario visible en el sistema</small>
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">
                                <i class="bi bi-envelope me-1"></i>Correo Electrónico
                                <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="email" 
                                class="form-control form-control-lg" 
                                id="email" 
                                name="email" 
                                value="<?= e(old('email', $user['email'])) ?>"
                                required
                                maxlength="255"
                            >
                            <small class="text-muted">Correo utilizado para iniciar sesión</small>
                        </div>

                        <!-- Botón Guardar Cambios -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i>
                                Actualizar Datos Personales
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TARJETA 2: ROLES Y SEGURIDAD (Siempre Visible) -->
        <!-- ============================================ -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning bg-gradient text-dark py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>
                        2. Roles y Seguridad
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Rol Asignado con statusClass -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-shield-check me-1"></i>Rol Asignado
                        </label>
                        <div class="d-flex align-items-center p-3 border rounded bg-light">
                            <span class="badge <?= $rolInfo['class'] ?> px-3 py-2 me-3">
                                <i class="bi <?= $rolInfo['icon'] ?> me-1"></i>
                                <?= $rolInfo['text'] ?>
                            </span>
                            <span class="text-muted small">
                                Solo puede ser modificado por un administrador
                            </span>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <!-- Sección: Cambio de Contraseña -->
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-key-fill text-warning me-2"></i>
                        Cambio de Contraseña
                    </h6>
                    
                    <!-- Mensaje Informativo de Seguridad -->
                    <div class="alert alert-warning border-0 mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Validación Estricta:</strong> Debes ingresar tu contraseña actual correctamente antes de establecer una nueva contraseña (mínimo 6 caracteres).
                    </div>

                    <!-- Formulario de Actualización de Contraseña con Validación Estricta -->
                    <form action="/perfil/update-password" method="POST" id="formActualizarPassword">
                        <?= csrfField() ?>
                        
                        <!-- Contraseña Actual -->
                        <div class="mb-3">
                            <label for="contrasena_actual" class="form-label fw-bold">
                                <i class="bi bi-lock me-1"></i>Contraseña Actual
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg" 
                                    id="contrasena_actual" 
                                    name="contrasena_actual" 
                                    required
                                    autocomplete="current-password"
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('contrasena_actual')">
                                    <i class="bi bi-eye" id="icon-contrasena_actual"></i>
                                </button>
                            </div>
                            <small class="text-muted">Ingresa tu contraseña actual para verificar tu identidad</small>
                        </div>

                        <hr class="my-4">

                        <!-- Nueva Contraseña -->
                        <div class="mb-3">
                            <label for="nueva_contrasena" class="form-label fw-bold">
                                <i class="bi bi-key me-1"></i>Nueva Contraseña
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg" 
                                    id="nueva_contrasena" 
                                    name="nueva_contrasena" 
                                    required
                                    minlength="6"
                                    autocomplete="new-password"
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('nueva_contrasena')">
                                    <i class="bi bi-eye" id="icon-nueva_contrasena"></i>
                                </button>
                            </div>
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>

                        <!-- Confirmar Nueva Contraseña -->
                        <div class="mb-4">
                            <label for="confirmar_contrasena" class="form-label fw-bold">
                                <i class="bi bi-key-fill me-1"></i>Confirmar Nueva Contraseña
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg" 
                                    id="confirmar_contrasena" 
                                    name="confirmar_contrasena" 
                                    required
                                    minlength="6"
                                    autocomplete="new-password"
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmar_contrasena')">
                                    <i class="bi bi-eye" id="icon-confirmar_contrasena"></i>
                                </button>
                            </div>
                            <small class="text-muted">Repite la nueva contraseña</small>
                        </div>

                        <!-- Botón Actualizar Contraseña -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="bi bi-shield-check me-2"></i>
                                Cambiar Contraseña
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TARJETA 3: DATOS DE VENDEDOR (Condicional) -->
    <!-- Solo visible si user->rol es 'vendedor' o 'administrador' -->
    <!-- Y existe un registro en la tabla vendedores -->
    <!-- ============================================ -->
    
    <?php if (($user['rol'] === 'vendedor' || $user['rol'] === 'administrador') && $perfil_vendedor): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info bg-gradient text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-briefcase-fill me-2"></i>
                        3. Datos de Vendedor
                    </h5>
                    <small>Información adicional asociada a tu perfil de vendedor</small>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Grid de Información del Vendedor -->
                    <div class="row g-4 mb-4">
                        
                        <!-- ID del Vendedor -->
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-hash"></i> ID Vendedor
                                </small>
                                <h4 class="mb-0 text-primary"><?= e($perfil_vendedor['id']) ?></h4>
                            </div>
                        </div>
                        
                        <!-- Código de Vendedor -->
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-upc-scan"></i> Código
                                </small>
                                <h5 class="mb-0"><?= e($perfil_vendedor['codigo_vendedor']) ?></h5>
                            </div>
                        </div>
                        
                        <!-- Estado del Vendedor usando statusClass -->
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-circle-fill"></i> Estado
                                </small>
                                <span class="badge <?= statusClass($perfil_vendedor['estado']) ?> px-3 py-2">
                                    <?= ucfirst($perfil_vendedor['estado']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Comisión Default -->
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-percent"></i> Comisión
                                </small>
                                <h5 class="mb-0 text-success"><?= number_format($perfil_vendedor['porcentaje_comision_default'], 2) ?>%</h5>
                            </div>
                        </div>
                        
                    </div>

                    <!-- Información Personal del Vendedor -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Nombres</label>
                            <p class="mb-0"><?= e($perfil_vendedor['nombres']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Apellidos</label>
                            <p class="mb-0"><?= e($perfil_vendedor['apellidos']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tipo de Documento</label>
                            <p class="mb-0"><?= strtoupper($perfil_vendedor['tipo_documento']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Número de Documento</label>
                            <p class="mb-0"><?= e($perfil_vendedor['numero_documento']) ?></p>
                        </div>
                    </div>

                    <!-- Formulario de Actualización de Datos de Contacto del Vendedor -->
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-telephone-fill me-2"></i>
                        Datos de Contacto Corporativo
                    </h6>
                    
                    <form action="/perfil/update-vendedor" method="POST" id="formDatosVendedor">
                        <?= csrfField() ?>
                        
                        <div class="row g-3">
                            <!-- Teléfono -->
                            <div class="col-md-4">
                                <label for="telefono" class="form-label fw-bold">
                                    <i class="bi bi-telephone me-1"></i>Teléfono
                                </label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="telefono" 
                                    name="telefono" 
                                    value="<?= e(old('telefono', $perfil_vendedor['telefono'] ?? '')) ?>"
                                    maxlength="20"
                                    placeholder="Ej: (555) 123-4567"
                                >
                            </div>

                            <!-- Celular Corporativo -->
                            <div class="col-md-4">
                                <label for="celular" class="form-label fw-bold">
                                    <i class="bi bi-phone me-1"></i>Celular Corporativo
                                    <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="celular" 
                                    name="celular" 
                                    value="<?= e(old('celular', $perfil_vendedor['celular'] ?? '')) ?>"
                                    maxlength="20"
                                    placeholder="Ej: +57 300 123 4567"
                                    required
                                >
                            </div>

                            <!-- Dirección -->
                            <div class="col-md-4">
                                <label for="ciudad" class="form-label fw-bold">
                                    <i class="bi bi-geo-alt me-1"></i>Ciudad
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="ciudad" 
                                    name="ciudad" 
                                    value="<?= e(old('ciudad', $perfil_vendedor['ciudad'] ?? '')) ?>"
                                    maxlength="100"
                                    placeholder="Ciudad de trabajo"
                                >
                            </div>

                            <!-- Dirección Completa -->
                            <div class="col-12">
                                <label for="direccion" class="form-label fw-bold">
                                    <i class="bi bi-house me-1"></i>Dirección Completa
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="direccion" 
                                    name="direccion" 
                                    value="<?= e(old('direccion', $perfil_vendedor['direccion'] ?? '')) ?>"
                                    maxlength="255"
                                    placeholder="Dirección de residencia o corporativa"
                                >
                            </div>
                        </div>

                        <!-- Botón Actualizar Datos de Vendedor -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-info btn-lg text-white">
                                <i class="bi bi-save me-2"></i>
                                Actualizar Datos de Contacto
                            </button>
                        </div>
                    </form>

                    <!-- Información Adicional del Vendedor -->
                    <hr class="my-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">
                                <i class="bi bi-calendar-event"></i> Fecha de Ingreso
                            </label>
                            <p class="mb-0"><?= formatDateTime($perfil_vendedor['fecha_ingreso'], 'd/m/Y') ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">
                                <i class="bi bi-file-earmark-text"></i> Tipo de Contrato
                            </label>
                            <p class="mb-0"><?= ucwords(str_replace('_', ' ', $perfil_vendedor['tipo_contrato'])) ?></p>
                        </div>
                        <?php if (!empty($perfil_vendedor['banco'])): ?>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">
                                <i class="bi bi-bank"></i> Banco
                            </label>
                            <p class="mb-0"><?= e($perfil_vendedor['banco']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">Tipo de Cuenta</label>
                            <p class="mb-0"><?= ucfirst($perfil_vendedor['tipo_cuenta']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">Número de Cuenta</label>
                            <p class="mb-0"><?= e($perfil_vendedor['numero_cuenta']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Enlace al Perfil Completo de Vendedor -->
                    <div class="mt-4 pt-3 border-top text-center">
                        <a href="/vendedores/mi-perfil" class="btn btn-outline-info">
                            <i class="bi bi-person-lines-fill me-2"></i>
                            Ver Perfil Completo de Vendedor con Comisiones y Estadísticas
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- JavaScript para mostrar/ocultar contraseñas -->
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('icon-' + fieldId);
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Validación de confirmación de contraseña
document.getElementById('formActualizarPassword').addEventListener('submit', function(e) {
    const nueva = document.getElementById('nueva_contrasena').value;
    const confirmar = document.getElementById('confirmar_contrasena').value;
    
    if (nueva !== confirmar) {
        e.preventDefault();
        alert('La nueva contraseña y su confirmación no coinciden');
        return false;
    }
    
    if (nueva.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        return false;
    }
});
</script>

<style>
.avatar-circle {
    flex-shrink: 0;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}

.form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.input-group .btn {
    z-index: 10;
}
</style>
