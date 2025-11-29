<?php
/**
 * Vista: Editar Proyecto
 * Formulario para editar un proyecto existente
 */
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-pencil-square"></i> <?= htmlspecialchars($title) ?>
        </h1>
        <div>
            <a href="/proyectos/show/<?= $proyecto['id'] ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <a href="/proyectos" class="btn btn-outline-secondary">
                <i class="bi bi-list"></i> Lista de Proyectos
            </a>
        </div>
    </div>

    <!-- Mostrar errores de validación -->
    <?php if (isset($_SESSION['errores']) && !empty($_SESSION['errores'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Errores de validación</h5>
            <ul class="mb-0">
                <?php foreach ($_SESSION['errores'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errores']); ?>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-file-text"></i> Información del Proyecto</h5>
        </div>
        <div class="card-body">
            <form action="/proyectos/update/<?= $proyecto['id'] ?>" method="POST" enctype="multipart/form-data" id="formProyecto">
                <?= csrfField() ?>

                <div class="row">
                    <!-- Código -->
                    <div class="col-md-4 mb-3">
                        <label for="codigo" class="form-label">
                            Código del Proyecto <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="codigo" 
                               id="codigo" 
                               class="form-control" 
                               placeholder="Ej: PROJ-001"
                               value="<?= e(old('codigo', $proyecto['codigo'])) ?>"
                               required
                               maxlength="50">
                        <small class="form-text text-muted">Código único del proyecto</small>
                    </div>

                    <!-- Nombre -->
                    <div class="col-md-8 mb-3">
                        <label for="nombre" class="form-label">
                            Nombre del Proyecto <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="nombre" 
                               id="nombre" 
                               class="form-control" 
                               placeholder="Ej: Villa Campestre Fase 1"
                               value="<?= e(old('nombre', $proyecto['nombre'])) ?>"
                               required
                               maxlength="200">
                    </div>
                </div>

                <div class="row">
                    <!-- Ubicación -->
                    <div class="col-md-8 mb-3">
                        <label for="ubicacion" class="form-label">
                            Ubicación <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="ubicacion" 
                               id="ubicacion" 
                               class="form-control" 
                               placeholder="Ej: Carrera 15 #45-67, Municipio"
                               value="<?= e(old('ubicacion', $proyecto['ubicacion'])) ?>"
                               required
                               maxlength="255">
                    </div>

                    <!-- Estado -->
                    <div class="col-md-4 mb-3">
                        <label for="estado" class="form-label">
                            Estado <span class="text-danger">*</span>
                        </label>
                        <select name="estado" id="estado" class="form-select" required>
                            <?= selectOptions($estados, old('estado', $proyecto['estado'])) ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Fecha de Inicio -->
                    <div class="col-md-4 mb-3">
                        <label for="fecha_inicio" class="form-label">
                            Fecha de Inicio
                        </label>
                        <input type="date" 
                               name="fecha_inicio" 
                               id="fecha_inicio" 
                               class="form-control"
                               value="<?= e(old('fecha_inicio', $proyecto['fecha_inicio'])) ?>">
                    </div>

                    <!-- Plano / Imagen -->
                    <div class="col-md-8 mb-3">
                        <label for="plano_imagen" class="form-label">
                            Plano del Proyecto (Imagen)
                        </label>
                        <input type="file" 
                               name="plano_imagen" 
                               id="plano_imagen" 
                               class="form-control"
                               accept="image/*">
                        <small class="form-text text-muted">
                            Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB
                        </small>
                        
                        <!-- Imagen actual -->
                        <?php if (!empty($proyecto['plano_imagen'])): ?>
                            <div class="mt-3">
                                <p class="mb-2"><strong>Imagen actual:</strong></p>
                                <img src="/<?= htmlspecialchars($proyecto['plano_imagen']) ?>" 
                                     alt="Plano actual" 
                                     class="img-thumbnail" 
                                     style="max-width: 300px; max-height: 300px;">
                                <p class="text-muted mt-2"><small>Suba una nueva imagen para reemplazarla</small></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Preview de nueva imagen -->
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <p class="mb-2"><strong>Nueva imagen (Vista previa):</strong></p>
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="mb-3">
                    <label for="observaciones" class="form-label">
                        Observaciones
                    </label>
                    <textarea name="observaciones" 
                              id="observaciones" 
                              class="form-control" 
                              rows="4"
                              placeholder="Notas adicionales sobre el proyecto..."><?= e(old('observaciones', $proyecto['observaciones'])) ?></textarea>
                </div>

                <!-- Información de auditoría -->
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">
                            <small><strong>Creado:</strong> <?= formatDateTime($proyecto['created_at']) ?></small>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="text-muted mb-1">
                            <small><strong>Última actualización:</strong> <?= formatDateTime($proyecto['updated_at']) ?></small>
                        </p>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="/proyectos/show/<?= $proyecto['id'] ?>" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript para preview de imagen -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputImagen = document.getElementById('plano_imagen');
    const previewContainer = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    inputImagen.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Validar que sea imagen
            if (!file.type.startsWith('image/')) {
                alert('Por favor seleccione un archivo de imagen válido');
                inputImagen.value = '';
                previewContainer.style.display = 'none';
                return;
            }

            // Validar tamaño (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('La imagen no puede superar los 5MB');
                inputImagen.value = '';
                previewContainer.style.display = 'none';
                return;
            }

            // Mostrar preview
            const reader = new FileReader();
            reader.onload = function(event) {
                previewImg.src = event.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    });

    // Validación del formulario
    document.getElementById('formProyecto').addEventListener('submit', function(e) {
        const codigo = document.getElementById('codigo').value.trim();
        const nombre = document.getElementById('nombre').value.trim();
        const ubicacion = document.getElementById('ubicacion').value.trim();
        const estado = document.getElementById('estado').value;

        if (!codigo || !nombre || !ubicacion || !estado) {
            e.preventDefault();
            alert('Por favor complete todos los campos obligatorios (*)');
            return false;
        }

        // Confirmación antes de guardar
        if (!confirm('¿Está seguro de guardar los cambios al proyecto?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
