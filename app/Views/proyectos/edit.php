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
                    <div>
                        <a href="/proyectos/show/<?= $proyecto['id'] ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <?php if (can('eliminar_proyectos')): ?>
                            <button type="button" class="btn btn-danger" onclick="confirmarEliminacion()">
                                <i class="bi bi-trash"></i> Eliminar Proyecto
                            </button>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Editor de Plano Interactivo -->
    <?php if (!empty($proyecto['plano_imagen']) && $proyecto['total_lotes'] > 0): ?>
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-map"></i> Editor de Plano Interactivo</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Crea puntos en el plano, arrástralos para posicionarlos y cambia su color haciendo clic derecho.
            </p>
            
            <div class="mb-3">
                <button type="button" class="btn btn-primary" id="btnCrearPunto">
                    <i class="bi bi-plus-circle"></i> Crear Punto
                </button>
                <button type="button" class="btn btn-danger" id="btnEliminarPunto">
                    <i class="bi bi-trash"></i> Eliminar Punto Seleccionado
                </button>
            </div>
            
            <div class="mb-3">
                <strong>Colores disponibles:</strong>
                <span class="badge bg-success ms-2">Disponible</span>
                <span class="badge bg-warning ms-2">Reservado</span>
                <span class="badge bg-info ms-2">Vendido</span>
                <span class="badge bg-secondary ms-2">Bloqueado</span>
                <br>
                <small class="text-muted">Haz clic derecho sobre un punto para cambiar su color</small>
            </div>

            <div class="position-relative" id="planoContainer" style="max-width: 100%; border: 2px solid #ccc; background: #f8f9fa;">
                <img src="/<?= htmlspecialchars($proyecto['plano_imagen']) ?>" 
                     id="planoImagen" 
                     class="img-fluid" 
                     style="width: 100%; height: auto; display: block;">
                <div id="lotesLayer" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></div>
                <input type="hidden" id="csrfTokenPlano" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Total de puntos: <span id="totalPuntos" class="fw-bold">0</span>
                </small>
                <button type="button" class="btn btn-success" id="btnGuardarCoordenadas">
                    <i class="bi bi-save"></i> Guardar Posiciones
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Formulario oculto para eliminar -->
<?php if (can('eliminar_proyectos')): ?>
<form id="formEliminar" method="POST" action="/proyectos/delete/<?= $proyecto['id'] ?>" style="display: none;">
    <?= csrfField() ?>
</form>
<?php endif; ?>

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

function confirmarEliminacion() {
    <?php 
    // Obtener el total de lotes
    $totalLotes = $proyecto['total_lotes'] ?? 0;
    ?>
    const totalLotes = <?= $totalLotes ?>;
    
    if (totalLotes > 0) {
        alert(`No se puede eliminar el proyecto porque tiene ${totalLotes} lote(s) asociado(s).\n\nDebes eliminar los lotes primero.`);
        return;
    }
    
    const mensaje = '¿Estás seguro de que deseas eliminar este proyecto?\n\n' +
                    'Proyecto: <?= htmlspecialchars($proyecto['nombre']) ?>\n' +
                    'Código: <?= htmlspecialchars($proyecto['codigo']) ?>\n\n' +
                    'Esta acción NO se puede deshacer.';
    
    if (confirm(mensaje)) {
        document.getElementById('formEliminar').submit();
    }
}
</script>

<!-- JavaScript para el editor de plano interactivo -->
<?php if (!empty($proyecto['plano_imagen']) && $proyecto['total_lotes'] > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const proyectoId = <?= $proyecto['id'] ?>;
    const planoImagen = document.getElementById('planoImagen');
    const lotesLayer = document.getElementById('lotesLayer');
    const btnCrearPunto = document.getElementById('btnCrearPunto');
    const btnEliminarPunto = document.getElementById('btnEliminarPunto');
    const btnGuardar = document.getElementById('btnGuardarCoordenadas');
    
    let puntos = [];
    let puntoIdCounter = 1;
    let puntoSeleccionado = null;
    
    // Colores disponibles
    const coloresEstado = {
        'disponible': { color: '#28a745', nombre: 'Disponible' },
        'reservado': { color: '#ffc107', nombre: 'Reservado' },
        'vendido': { color: '#17a2b8', nombre: 'Vendido' },
        'bloqueado': { color: '#6c757d', nombre: 'Bloqueado' }
    };
    
    const estadosArray = ['disponible', 'reservado', 'vendido', 'bloqueado'];
    
    // Cargar puntos existentes desde el servidor
    fetch('/proyectos/lotes-coordenadas/<?= $proyecto['id'] ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.lotes) {
                data.lotes.forEach(lote => {
                    // Cargar solo lotes que ya tienen coordenadas guardadas (válidas)
                    const x = parseFloat(lote.plano_x);
                    const y = parseFloat(lote.plano_y);
                    
                    if (lote.plano_x !== null && lote.plano_y !== null && 
                        !isNaN(x) && !isNaN(y)) {
                        puntos.push({
                            id: puntoIdCounter++,
                            loteId: lote.id,
                            x: x,
                            y: y,
                            estado: lote.estado,
                            codigo: lote.codigo_lote
                        });
                    }
                });
                renderPuntos();
                actualizarContador();
                console.log('✓ Cargados ' + puntos.length + ' puntos guardados del proyecto');
            }
        })
        .catch(error => console.error('Error al cargar puntos:', error));
    
    // Crear nuevo punto
    btnCrearPunto.addEventListener('click', function() {
        // Buscar un lote sin coordenadas para asignarle
        const lotesSinCoordenadas = [];
        fetch('/proyectos/lotes-coordenadas/<?= $proyecto['id'] ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.lotes) {
                    console.log('📊 Total de lotes en proyecto:', data.lotes.length);
                    
                    // Encontrar lotes que no tengan puntos asignados
                    const lotesConPuntos = puntos.map(p => p.loteId).filter(id => id !== null);
                    console.log('📍 Lotes ya con puntos asignados:', lotesConPuntos);
                    
                    const loteSinAsignar = data.lotes.find(l => !lotesConPuntos.includes(l.id));
                    
                    if (!loteSinAsignar) {
                        alert('Todos los lotes ya tienen puntos asignados. Elimina un punto existente para crear uno nuevo.');
                        return;
                    }
                    
                    console.log('✅ Asignando punto a lote:', loteSinAsignar.codigo_lote, '(ID: ' + loteSinAsignar.id + ')');
                    
                    // Crear punto en el centro
                    const x = 50;
                    const y = 50;
                    
                    const nuevoPunto = {
                        id: puntoIdCounter++,
                        loteId: loteSinAsignar.id,
                        x: x,
                        y: y,
                        estado: loteSinAsignar.estado,
                        codigo: loteSinAsignar.codigo_lote
                    };
                    
                    puntos.push(nuevoPunto);
                    console.log('➕ Punto creado:', nuevoPunto);
                    
                    renderPuntos();
                    actualizarContador();
                }
            });
    });
    
    // Eliminar punto seleccionado
    btnEliminarPunto.addEventListener('click', function() {
        if (puntoSeleccionado !== null) {
            if (confirm('¿Eliminar este punto?')) {
                puntos = puntos.filter(p => p.id !== puntoSeleccionado);
                puntoSeleccionado = null;
                renderPuntos();
                actualizarContador();
            }
        } else {
            alert('Selecciona un punto primero haciendo clic en él');
        }
    });
    
    // Renderizar todos los puntos
    function renderPuntos() {
        lotesLayer.innerHTML = '';
        puntos.forEach(punto => crearPuntoDOM(punto));
    }
    
    // Crear elemento DOM para un punto
    function crearPuntoDOM(punto) {
        const puntoDiv = document.createElement('div');
        puntoDiv.className = 'punto-lote';
        puntoDiv.dataset.puntoId = punto.id;
        
        const colorInfo = coloresEstado[punto.estado] || coloresEstado['disponible'];
        
        puntoDiv.style.cssText = `
            position: absolute;
            left: ${punto.x}%;
            top: ${punto.y}%;
            width: 35px;
            height: 35px;
            background-color: ${colorInfo.color};
            border: 3px solid ${puntoSeleccionado === punto.id ? '#000' : '#fff'};
            border-radius: 50%;
            cursor: move;
            transform: translate(-50%, -50%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            z-index: ${puntoSeleccionado === punto.id ? 100 : 10};
            transition: border-color 0.2s, box-shadow 0.2s;
        `;
        
        puntoDiv.title = `${punto.codigo}\n${colorInfo.nombre}\nClic derecho para cambiar color`;
        
        // Click izquierdo: seleccionar
        puntoDiv.addEventListener('click', function(e) {
            e.stopPropagation();
            puntoSeleccionado = punto.id;
            renderPuntos();
        });
        
        // Click derecho: cambiar color
        puntoDiv.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            e.stopPropagation();
            mostrarMenuColor(punto, e);
        });
        
        // Drag & drop
        puntoDiv.addEventListener('mousedown', function(e) {
            if (e.button === 0) { // Solo botón izquierdo
                iniciarArrastre(punto, e);
            }
        });
        
        lotesLayer.appendChild(puntoDiv);
    }
    
    // Menú contextual para cambiar color
    function mostrarMenuColor(punto, evento) {
        // Crear menú si no existe
        let menu = document.getElementById('menuColor');
        if (menu) menu.remove();
        
        menu = document.createElement('div');
        menu.id = 'menuColor';
        menu.style.cssText = `
            position: fixed;
            left: ${evento.clientX}px;
            top: ${evento.clientY}px;
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 10000;
        `;
        
        const titulo = document.createElement('div');
        titulo.textContent = 'Cambiar estado:';
        titulo.style.cssText = 'font-weight: bold; margin-bottom: 8px; font-size: 12px;';
        menu.appendChild(titulo);
        
        estadosArray.forEach(estado => {
            const colorInfo = coloresEstado[estado];
            const btn = document.createElement('button');
            btn.textContent = colorInfo.nombre;
            btn.className = 'btn btn-sm w-100 mb-1';
            btn.style.cssText = `
                background-color: ${colorInfo.color};
                color: white;
                border: none;
                text-align: left;
                padding: 5px 10px;
            `;
            
            btn.addEventListener('click', function() {
                punto.estado = estado;
                renderPuntos();
                menu.remove();
            });
            
            menu.appendChild(btn);
        });
        
        document.body.appendChild(menu);
        
        // Cerrar al hacer clic fuera
        setTimeout(() => {
            document.addEventListener('click', function cerrarMenu() {
                menu.remove();
                document.removeEventListener('click', cerrarMenu);
            });
        }, 100);
    }
    
    // Sistema de arrastre
    let isDragging = false;
    let currentPunto = null;
    
    function iniciarArrastre(punto, e) {
        e.stopPropagation();
        isDragging = true;
        currentPunto = punto;
        puntoSeleccionado = punto.id;
        renderPuntos();
    }
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging || !currentPunto) return;
        
        const rect = planoImagen.getBoundingClientRect();
        let x = ((e.clientX - rect.left) / rect.width) * 100;
        let y = ((e.clientY - rect.top) / rect.height) * 100;
        
        // Limitar a los bordes
        x = Math.max(0, Math.min(100, x));
        y = Math.max(0, Math.min(100, y));
        
        currentPunto.x = x;
        currentPunto.y = y;
        
        // Actualizar solo el punto que se está arrastrando
        const puntoDOM = document.querySelector(`[data-punto-id="${currentPunto.id}"]`);
        if (puntoDOM) {
            puntoDOM.style.left = x + '%';
            puntoDOM.style.top = y + '%';
        }
    });
    
    document.addEventListener('mouseup', function() {
        isDragging = false;
        currentPunto = null;
    });
    
    // Click en el fondo para deseleccionar
    lotesLayer.addEventListener('click', function() {
        puntoSeleccionado = null;
        renderPuntos();
    });
    
    // Actualizar contador
    function actualizarContador() {
        document.getElementById('totalPuntos').textContent = puntos.length;
    }
    
    // Guardar posiciones
    btnGuardar.addEventListener('click', function() {
        if (puntos.length === 0) {
            alert('No hay puntos para guardar');
            return;
        }
        
        if (!confirm('¿Guardar las posiciones de todos los puntos?')) {
            return;
        }
        
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        
        // Obtener token CSRF
        const csrfToken = document.getElementById('csrfTokenPlano').value;
        
        // Preparar datos
        const puntosData = puntos.map(p => ({
            id: p.loteId,
            x: p.x.toFixed(2),
            y: p.y.toFixed(2)
        })).filter(p => p.id !== null);
        
        console.log('📤 Enviando al servidor:', { lotes: puntosData });
        console.log('Total de puntos a guardar:', puntosData.length);
        
        if (puntosData.length === 0) {
            alert('❌ No hay puntos válidos para guardar. Asegúrate de que los puntos estén vinculados a lotes.');
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Posiciones';
            btnGuardar.disabled = false;
            return;
        }
        
        // Enviar al servidor
        fetch('/proyectos/update-coordenadas/<?= $proyecto['id'] ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ lotes: puntosData })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Posiciones guardadas exitosamente');
                btnGuardar.innerHTML = '<i class="bi bi-check-circle"></i> Guardado';
                setTimeout(() => {
                    btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Posiciones';
                    btnGuardar.disabled = false;
                }, 2000);
            } else {
                alert('Error al guardar: ' + data.message);
                btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Posiciones';
                btnGuardar.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al guardar');
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Posiciones';
            btnGuardar.disabled = false;
        });
    });
});
</script>
<?php endif; ?>
