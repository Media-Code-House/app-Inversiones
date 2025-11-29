<?php
/**
 * Vista: Crear Vendedor
 * Formulario para registrar un nuevo vendedor
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus"></i> Registrar Nuevo Vendedor
                    </h4>
                </div>
                <div class="card-body">
                    <form action="<?= url('/vendedores/store') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="row">
                            <!-- Selección de Usuario -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Usuario Asociado <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">Seleccione un usuario...</option>
                                    <?php foreach ($usuariosDisponibles as $user): ?>
                                    <option value="<?= $user['id'] ?>">
                                        <?= htmlspecialchars($user['nombre']) ?> - <?= htmlspecialchars($user['email']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Solo usuarios sin perfil de vendedor</small>
                            </div>

                            <!-- Código de Vendedor -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código de Vendedor <span class="text-danger">*</span></label>
                                <input type="text" name="codigo_vendedor" class="form-control" 
                                       value="<?= $codigoSugerido ?>" required>
                                <small class="text-muted">Código único para identificar al vendedor</small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tipo de Documento -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                <select name="tipo_documento" class="form-select" required>
                                    <option value="CC" selected>Cédula de Ciudadanía</option>
                                    <option value="NIT">NIT</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="pasaporte">Pasaporte</option>
                                </select>
                            </div>

                            <!-- Número de Documento -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Número Documento <span class="text-danger">*</span></label>
                                <input type="text" name="numero_documento" class="form-control" required>
                            </div>

                            <!-- Nombres -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombres" class="form-control" required>
                            </div>

                            <!-- Apellidos -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control">
                            </div>

                            <!-- Celular -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Celular</label>
                                <input type="text" name="celular" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Dirección -->
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control">
                            </div>

                            <!-- Ciudad -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="ciudad" class="form-control">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-briefcase"></i> Información Laboral</h5>

                        <div class="row">
                            <!-- Fecha de Ingreso -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Fecha Ingreso <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_ingreso" class="form-control" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <!-- Tipo de Contrato -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo Contrato <span class="text-danger">*</span></label>
                                <select name="tipo_contrato" class="form-select" required>
                                    <option value="indefinido" selected>Indefinido</option>
                                    <option value="fijo">Término Fijo</option>
                                    <option value="prestacion_servicios">Prestación Servicios</option>
                                    <option value="freelance">Freelance</option>
                                </select>
                            </div>

                            <!-- Porcentaje Comisión -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">% Comisión <span class="text-danger">*</span></label>
                                <input type="number" name="porcentaje_comision_default" class="form-control" 
                                       value="3.00" step="0.01" min="0" max="100" required>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estado <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo" selected>Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="suspendido">Suspendido</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-bank"></i> Datos Bancarios (Opcional)</h5>

                        <div class="row">
                            <!-- Banco -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Banco</label>
                                <input type="text" name="banco" class="form-control" 
                                       placeholder="Ej: Bancolombia">
                            </div>

                            <!-- Tipo de Cuenta -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo Cuenta</label>
                                <select name="tipo_cuenta" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <option value="ahorros">Ahorros</option>
                                    <option value="corriente">Corriente</option>
                                </select>
                            </div>

                            <!-- Número de Cuenta -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Número Cuenta</label>
                                <input type="text" name="numero_cuenta" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Observaciones -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="<?= url('/vendedores') ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Vendedor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
