<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm mt-5">
                <div class="card-header text-center bg-primary text-white">
                    <h4><i class="fas fa-key"></i> Recuperar Contraseña</h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted text-center mb-4">
                        Ingresa tu email y te enviaremos un link para restablecer tu contraseña.
                    </p>
                    
                    <form method="POST" action="<?= url('/auth/recover') ?>" id="recoverForm">
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
                        
                        <!-- Botón -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Enviar Link de Recuperación
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
