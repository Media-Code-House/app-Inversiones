<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm mt-5">
                <div class="card-header text-center bg-primary text-white">
                    <h4><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= url('/auth/login') ?>" id="loginForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="correo@ejemplo.com" required autofocus>
                        </div>
                        
                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="••••••••" required>
                        </div>
                        
                        <!-- Recordar sesión -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Recordar sesión
                            </label>
                        </div>
                        
                        <!-- Botón -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Ingresar
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center bg-light">
                    <p class="mb-2">
                        <a href="<?= url('/auth/recover') ?>" class="text-decoration-none">
                            <i class="fas fa-question-circle"></i> ¿Olvidaste tu contraseña?
                        </a>
                    </p>
                    <p class="mb-0">
                        ¿No tienes cuenta? 
                        <a href="<?= url('/auth/register') ?>" class="text-decoration-none">
                            <strong>Regístrate aquí</strong>
                        </a>
                    </p>
                </div>
            </div>
            
            <!-- Info adicional -->
            <div class="text-center mt-3 text-muted">
                <small>
                    <i class="fas fa-info-circle"></i> 
                    Credenciales de prueba: admin@sistema.com / admin123
                </small>
            </div>
        </div>
    </div>
</div>
