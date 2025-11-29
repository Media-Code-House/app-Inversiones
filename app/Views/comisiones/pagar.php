<?php
/**
 * Vista: Registrar Pago de Comisión
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-cash-stack"></i> Registrar Pago de Comisión
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Información de la Comisión -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Vendedor:</strong> <?= htmlspecialchars($comision['vendedor_nombre']) ?><br>
                                <strong>Lote:</strong> <?= htmlspecialchars($comision['codigo_lote']) ?> - <?= htmlspecialchars($comision['proyecto_nombre']) ?>
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Valor Comisión:</strong> <span class="fs-5 text-success">$<?= number_format($comision['valor_comision'], 0) ?></span><br>
                                <strong>Estado:</strong> 
                                <?php
                                $estadoBadge = [
                                    'pendiente' => 'warning',
                                    'pagada_parcial' => 'info'
                                ];
                                $badgeClass = $estadoBadge[$comision['estado']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', $comision['estado'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($totalPagado) && $totalPagado > 0): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Pagos anteriores:</strong> $<?= number_format($totalPagado, 0) ?>
                        <br>
                        <strong>Saldo pendiente:</strong> $<?= number_format($comision['valor_comision'] - $totalPagado, 0) ?>
                    </div>
                    <?php endif; ?>

                    <!-- Formulario de Pago -->
                    <form action="<?= url('/comisiones/procesar-pago/' . $comision['id']) ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="row">
                            <!-- Fecha de Pago -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_pago" class="form-control" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <!-- Valor Pagado -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valor Pagado <span class="text-danger">*</span></label>
                                <input type="number" name="valor_pagado" class="form-control" 
                                       step="0.01" min="0" 
                                       max="<?= $comision['valor_comision'] - ($totalPagado ?? 0) ?>"
                                       placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Método de Pago -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                                <select name="metodo_pago" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="transferencia" selected>Transferencia</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="consignacion">Consignación</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <!-- Banco -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Banco</label>
                                <input type="text" name="banco" class="form-control" 
                                       placeholder="Ej: Bancolombia"
                                       value="<?= htmlspecialchars($comision['vendedor_banco'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Número de Comprobante -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Número Comprobante</label>
                                <input type="text" name="numero_comprobante" class="form-control" 
                                       placeholder="Número de transacción o comprobante">
                            </div>

                            <!-- Referencia -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Referencia</label>
                                <input type="text" name="referencia" class="form-control" 
                                       placeholder="Referencia adicional">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Observaciones -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="3" 
                                          placeholder="Observaciones del pago..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="<?= url('/comisiones/show/' . $comision['id']) ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Registrar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
