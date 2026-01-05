<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inversiones SAG">
    <title><?= $title ?? APP_NAME ?></title>
    
    <!-- DEBUG: Layout cargado correctamente -->
    
    <!-- Bootstrap 5 CSS (Local) -->
    <link rel="stylesheet" href="<?= asset('css/bootstrap/bootstrap.min.css') ?>">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Theme CSS (Corporativo) -->
    <link rel="stylesheet" href="<?= asset('css/theme.css') ?>">
    
    <?= $styles ?? '' ?>
</head>
<body>
    
    <?php if (isAuthenticated()): ?>
        <!-- Navbar para usuarios autenticados -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?= url('/') ?>">
                    <i class="fas fa-building"></i> <?= APP_NAME ?>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/dashboard') ?>">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/proyectos') ?>">
                                <i class="fas fa-project-diagram"></i> Proyectos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/lotes') ?>">
                                <i class="fas fa-th"></i> Lotes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/clientes') ?>">
                                <i class="fas fa-users"></i> Clientes
                            </a>
                        </li>
                        <?php if (hasRole('administrador')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/vendedores') ?>">
                                <i class="fas fa-user-tie"></i> Vendedores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/comisiones') ?>">
                                <i class="fas fa-money-bill-wave"></i> Comisiones
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/reportes') ?>">
                                <i class="fas fa-chart-line"></i> Reportes
                            </a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> <?= e(user()['nombre'] ?? 'Usuario') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="<?= url('/perfil') ?>">
                                    <i class="fas fa-user"></i> Mi Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="fas fa-key"></i> Cambiar Contraseña
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= url('/auth/logout') ?>">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Mensajes Flash -->
    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contenido principal -->
    <main class="py-4">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© <?= date('Y') ?> <?= APP_NAME ?>. Todos los derechos reservados.</span>
        </div>
    </footer>

    <?php if (isAuthenticated()): ?>
        <!-- Modal Cambiar Contraseña -->
        <?php include __DIR__ . '/partials/change-password.php'; ?>
    <?php endif; ?>

    <!-- Bootstrap JS Bundle (Local) -->
    <script src="<?= asset('js/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    
    <!-- jQuery (opcional, pero útil) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- App JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
    
    <?= $scripts ?? '' ?>
</body>
</html>
