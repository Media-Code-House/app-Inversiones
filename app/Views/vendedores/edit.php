<?php
/**
 * Vista: Editar Vendedor
 * Formulario para actualizar información del vendedor
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil-square"></i> Editar Vendedor: <?= htmlspecialchars($vendedor['nombre_completo']) ?>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="<?= url('/vendedores/update/' . $vendedor['id']) ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Usuario asociado: <strong><?= htmlspecialchars($vendedor['user_email']) ?></strong>
                            <small class="d-block">El usuario asociado no puede ser cambiado</small>
                        </div>

                        <div class="row">
                            <!-- Código de Vendedor -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código de Vendedor <span class="text-danger">*</span></label>
                                <input type="text" name="codigo_vendedor" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['codigo_vendedor']) ?>" required readonly>
                                <small class="text-muted">El código no puede ser modificado</small>
                            </div>

                            <!-- Tipo de Documento -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                <select name="tipo_documento" class="form-select" required>
                                    <option value="CC" <?= $vendedor['tipo_documento'] == 'CC' ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
                                    <option value="NIT" <?= $vendedor['tipo_documento'] == 'NIT' ? 'selected' : '' ?>>NIT</option>
                                    <option value="CE" <?= $vendedor['tipo_documento'] == 'CE' ? 'selected' : '' ?>>Cédula de Extranjería</option>
                                    <option value="pasaporte" <?= $vendedor['tipo_documento'] == 'pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                </select>
                            </div>

                            <!-- Número de Documento -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Número Documento <span class="text-danger">*</span></label>
                                <input type="text" name="numero_documento" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['numero_documento']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Nombres -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombres" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['nombres']) ?>" required>
                            </div>

                            <!-- Apellidos -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['apellidos']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['email']) ?>" required>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['telefono'] ?? '') ?>">
                            </div>

                            <!-- Celular -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Celular</label>
                                <input type="text" name="celular" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['celular'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Dirección -->
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['direccion'] ?? '') ?>">
                            </div>

                            <!-- Ciudad -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="ciudad" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['ciudad'] ?? '') ?>">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-briefcase"></i> Información Laboral</h5>

                        <div class="row">
                            <!-- Fecha de Ingreso -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Fecha Ingreso <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_ingreso" class="form-control" 
                                       value="<?= $vendedor['fecha_ingreso'] ?>" required>
                            </div>

                            <!-- Fecha de Salida -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Fecha Salida</label>
                                <input type="date" name="fecha_salida" class="form-control" 
                                       value="<?= $vendedor['fecha_salida'] ?? '' ?>">
                            </div>

                            <!-- Tipo de Contrato -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo Contrato <span class="text-danger">*</span></label>
                                <select name="tipo_contrato" class="form-select" required>
                                    <option value="indefinido" <?= $vendedor['tipo_contrato'] == 'indefinido' ? 'selected' : '' ?>>Indefinido</option>
                                    <option value="fijo" <?= $vendedor['tipo_contrato'] == 'fijo' ? 'selected' : '' ?>>Término Fijo</option>
                                    <option value="prestacion_servicios" <?= $vendedor['tipo_contrato'] == 'prestacion_servicios' ? 'selected' : '' ?>>Prestación Servicios</option>
                                    <option value="freelance" <?= $vendedor['tipo_contrato'] == 'freelance' ? 'selected' : '' ?>>Freelance</option>
                                </select>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estado <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo" <?= $vendedor['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactivo" <?= $vendedor['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                    <option value="suspendido" <?= $vendedor['estado'] == 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Porcentaje Comisión -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">% Comisión por Defecto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="porcentaje_comision_default" class="form-control" 
                                           value="<?= $vendedor['porcentaje_comision_default'] ?>" 
                                           step="0.01" min="0" max="100" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    El cambio de porcentaje se registrará en el historial
                                </small>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-bank"></i> Datos Bancarios</h5>

                        <div class="row">
                            <!-- Banco -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Banco</label>
                                <input type="text" name="banco" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['banco'] ?? '') ?>">
                            </div>

                            <!-- Tipo de Cuenta -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo Cuenta</label>
                                <select name="tipo_cuenta" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <option value="ahorros" <?= ($vendedor['tipo_cuenta'] ?? '') == 'ahorros' ? 'selected' : '' ?>>Ahorros</option>
                                    <option value="corriente" <?= ($vendedor['tipo_cuenta'] ?? '') == 'corriente' ? 'selected' : '' ?>>Corriente</option>
                                </select>
                            </div>

                            <!-- Número de Cuenta -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Número Cuenta</label>
                                <input type="text" name="numero_cuenta" class="form-control" 
                                       value="<?= htmlspecialchars($vendedor['numero_cuenta'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Observaciones -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($vendedor['observaciones'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="<?= url('/vendedores/show/' . $vendedor['id']) ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle"></i> Actualizar Vendedor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
