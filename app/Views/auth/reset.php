<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm mt-5">
                <div class="card-header text-center bg-primary text-white">
                    <h4><i class="fas fa-lock-open"></i> Restablecer Contraseña</h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted text-center mb-4">
                        Ingresa tu nueva contraseña.
                    </p>
                    
                    <form method="POST" action="<?= url('/auth/reset') ?>" id="resetForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        
                        <!-- Nueva contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Nueva Contraseña *
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="••••••••" required minlength="6" autofocus>
                            <div class="form-text">Mínimo 6 caracteres.</div>
                        </div>
                        
                        <!-- Confirmar contraseña -->
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i> Confirmar Contraseña *
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="••••••••" required minlength="6">
                        </div>
                        
                        <!-- Botón -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Restablecer Contraseña
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center bg-light">
                    <p class="mb-0">
                        <a href="<?= url('/auth/login') ?>" class="text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Volver al login
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de contraseñas
document.getElementById('resetForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }
});
</script>
