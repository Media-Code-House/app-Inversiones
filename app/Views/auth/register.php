<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm mt-5">
                <div class="card-header text-center bg-primary text-white">
                    <h4><i class="fas fa-user-plus"></i> Crear Cuenta</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= url('/auth/register') ?>" id="registerForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <!-- Nombre completo -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                <i class="fas fa-user"></i> Nombre Completo *
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   placeholder="Juan Pérez" required autofocus>
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email *
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="correo@ejemplo.com" required>
                        </div>
                        
                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña *
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="••••••••" required minlength="6">
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
                        
                        <!-- Términos y condiciones -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Acepto los <a href="#" class="text-decoration-none">términos y condiciones</a>
                            </label>
                        </div>
                        
                        <!-- Botón -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Registrarse
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center bg-light">
                    <p class="mb-0">
                        ¿Ya tienes cuenta? 
                        <a href="<?= url('/auth/login') ?>" class="text-decoration-none">
                            <strong>Inicia sesión aquí</strong>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de contraseñas
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }
});
</script>
