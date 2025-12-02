<!-- Perfil de Usuario - Módulo 8 -->
<div class="container-fluid py-4">
    
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
                        
                        <!-- Información de Fechas -->
                        <div class="text-end text-muted small">
                            <div><i class="bi bi-calendar-plus me-1"></i>Registrado: <?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
                            <div><i class="bi bi-calendar-check me-1"></i>Actualizado: <?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- SECCIÓN 1: Datos Personales -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Datos Personales
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Formulario de Actualización de Datos -->
                    <form action="/perfil/update" method="POST" id="formDatosPersonales">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-bold">
                                <i class="bi bi-person me-1"></i>Nombre Completo
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
                            <small class="text-muted">Tu nombre será visible en el sistema</small>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
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
                            <small class="text-muted">Utilizado para iniciar sesión</small>
                        </div>

                        <!-- Rol (Solo Lectura) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-shield-lock me-1"></i>Rol Asignado
                            </label>
                            <div class="alert alert-light border mb-0">
                                <div class="d-flex align-items-center">
                                    <span class="badge <?= $rolInfo['class'] ?> me-2">
                                        <i class="bi <?= $rolInfo['icon'] ?> me-1"></i>
                                        <?= $rolInfo['text'] ?>
                                    </span>
                                    <span class="text-muted small">
                                        El rol solo puede ser modificado por un administrador
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Guardar -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>

                    <?php if ($vendedorInfo): ?>
                    <!-- Información adicional para vendedores -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-briefcase text-info me-2"></i>
                            Información de Vendedor
                        </h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Código</small>
                                <strong><?= e($vendedorInfo['codigo_vendedor']) ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Comisión</small>
                                <strong><?= number_format($vendedorInfo['porcentaje_comision_default'], 2) ?>%</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Celular</small>
                                <strong><?= e($vendedorInfo['celular'] ?? 'No registrado') ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Estado</small>
                                <span class="badge bg-<?= $vendedorInfo['estado'] === 'activo' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($vendedorInfo['estado']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="/vendedores/mi-perfil" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-person-lines-fill me-1"></i>
                                Ver Perfil Completo de Vendedor
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: Actualizar Contraseña -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock text-warning me-2"></i>
                        Seguridad de la Cuenta
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Mensaje Informativo -->
                    <div class="alert alert-info border-0 mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Por seguridad, actualiza tu contraseña periódicamente. Debe tener al menos 6 caracteres.
                    </div>

                    <!-- Formulario de Actualización de Contraseña -->
                    <form action="/perfil/update-password" method="POST" id="formActualizarPassword">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
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
                                Actualizar Contraseña
                            </button>
                        </div>
                    </form>

                    <!-- Consejos de Seguridad -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-lightbulb text-warning me-2"></i>
                            Consejos de Seguridad
                        </h6>
                        <ul class="small text-muted mb-0">
                            <li class="mb-2">Usa una combinación de letras, números y símbolos</li>
                            <li class="mb-2">No compartas tu contraseña con nadie</li>
                            <li class="mb-2">Cambia tu contraseña periódicamente</li>
                            <li>No uses la misma contraseña en múltiples sitios</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>

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
