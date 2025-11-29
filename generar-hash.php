<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Contraseñas Hash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hash-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
        .result-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 15px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .copy-btn {
            transition: all 0.3s ease;
        }
        .copy-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hash-card p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                <h2 class="mb-2">Generador de Hash de Contraseña</h2>
                <p class="text-muted">Genera contraseñas hasheadas con Bcrypt (Cost 12)</p>
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">
                        <i class="fas fa-key"></i> Contraseña a Hashear
                    </label>
                    <input type="text" class="form-control form-control-lg" 
                           id="password" name="password" 
                           placeholder="Ingresa la contraseña..." 
                           required autofocus>
                    <div class="form-text">
                        <i class="fas fa-info-circle"></i> 
                        Esta herramienta NO guarda ninguna información
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-hashtag"></i> Generar Hash
                    </button>
                </div>
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
                $password = $_POST['password'];
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                ?>
                
                <div class="mt-4">
                    <hr>
                    <h5 class="mb-3">
                        <i class="fas fa-check-circle text-success"></i> Hash Generado
                    </h5>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contraseña Original:</label>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-eye"></i> <?= htmlspecialchars($password) ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hash Bcrypt (Cost 12):</label>
                        <div class="result-box" id="hashResult">
                            <?= $hash ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success copy-btn" onclick="copyHash()">
                            <i class="fas fa-copy"></i> Copiar Hash
                        </button>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-2">
                            <i class="fas fa-code"></i> Código SQL para insertar:
                        </h6>
                        <pre class="mb-0" style="font-size: 12px;"><code>UPDATE users SET password_hash = '<?= $hash ?>' WHERE email = 'admin@sistema.com';</code></pre>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="copySQL()">
                            <i class="fas fa-copy"></i> Copiar SQL
                        </button>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Importante:</strong> Elimina este archivo (generar-hash.php) después de usarlo por seguridad.
                    </div>
                </div>
                
            <?php } ?>
        </div>

        <div class="text-center mt-4">
            <p class="text-white">
                <i class="fas fa-shield-alt"></i> 
                Herramienta segura para desarrollo | No usar en producción
            </p>
        </div>
    </div>

    <script>
        function copyHash() {
            const hashText = document.getElementById('hashResult').textContent.trim();
            navigator.clipboard.writeText(hashText).then(() => {
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-dark');
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-dark');
                    btn.classList.add('btn-success');
                }, 2000);
            });
        }

        function copySQL() {
            const sqlCode = event.target.previousElementSibling.textContent.trim();
            navigator.clipboard.writeText(sqlCode).then(() => {
                const btn = event.target;
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 2000);
            });
        }
    </script>
</body>
</html>
