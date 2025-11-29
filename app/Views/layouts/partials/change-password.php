<!-- Modal: Cambiar Contraseña -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="fas fa-key"></i> Cambiar Contraseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="changePasswordForm" method="POST" action="<?= url('/auth/change-password') ?>">
                <div class="modal-body">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    
                    <!-- Contraseña actual -->
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual *</label>
                        <input type="password" class="form-control" id="current_password" 
                               name="current_password" required>
                        <div class="invalid-feedback">
                            Por favor ingresa tu contraseña actual.
                        </div>
                    </div>
                    
                    <!-- Nueva contraseña -->
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña *</label>
                        <input type="password" class="form-control" id="new_password" 
                               name="new_password" required minlength="6">
                        <div class="form-text">Mínimo 6 caracteres.</div>
                        <div class="invalid-feedback">
                            La contraseña debe tener al menos 6 caracteres.
                        </div>
                    </div>
                    
                    <!-- Confirmar nueva contraseña -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña *</label>
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" required minlength="6">
                        <div class="invalid-feedback">
                            Las contraseñas no coinciden.
                        </div>
                    </div>
                    
                    <!-- Mensaje de error -->
                    <div id="changePasswordError" class="alert alert-danger d-none" role="alert"></div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación del formulario de cambio de contraseña
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const errorDiv = document.getElementById('changePasswordError');
    
    // Validar que las contraseñas coincidan
    if (newPassword !== confirmPassword) {
        errorDiv.textContent = 'Las contraseñas no coinciden';
        errorDiv.classList.remove('d-none');
        return false;
    }
    
    // Ocultar mensaje de error
    errorDiv.classList.add('d-none');
    
    // Enviar formulario
    form.submit();
});
</script>
