<div class="row align-items-center mb-4 d-print-none">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Teacher Payslip & Compensation Statement</h2>
        <p class="text-muted mb-0">Official compensation voucher breakdown and disbursement certificate.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Statement
        </button>
        <a href="<?= url('teacher-payments') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Payroll
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-5">
                <!-- Header -->
                <div class="row align-items-center border-bottom pb-4 mb-4">
                    <div class="col-sm-7">
                        <h4 class="fw-bold mb-1 text-primary"><?= e($tenant['name'] ?? 'TuitionPro ERP') ?></h4>
                        <div class="small text-muted">
                            Teacher Compensation & Payroll Division<br>
                            Tel: <?= e($tenant['phone'] ?? 'N/A') ?> | Email: <?= e($tenant['email'] ?? 'admin@tuitionpro.lk') ?>
                        </div>
                    </div>
                    <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                        <span class="badge bg-primary px-3 py-1 fs-6 font-monospace mb-2">PAYSLIP / STATEMENT</span>
                        <h6 class="fw-bold mb-1 font-monospace text-dark">Statement #TPC-<?= str_pad((string)$calc['id'], 5, '0', STR_PAD_LEFT) ?></h6>
                        <div class="small text-muted">Period: <strong><?= e($calc['period_name']) ?></strong></div>
                    </div>
                </div>

                <!-- Teacher Details -->
                <div class="row g-3 mb-4 p-3 bg-light rounded-3">
                    <div class="col-sm-6">
                        <span class="text-muted small text-uppercase fw-bold d-block">Teacher Details:</span>
                        <strong class="fs-6 text-dark d-block"><?= e($calc['teacher_name']) ?></strong>
                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= e($calc['phone'] ?? 'N/A') ?></div>
                        <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= e($calc['email'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <span class="text-muted small text-uppercase fw-bold d-block">Bank Remittance:</span>
                        <strong class="fs-6 text-dark d-block"><?= e($calc['bank_name'] ?? 'Bank Account Not Set') ?></strong>
                        <div class="small text-muted font-monospace"><?= e($calc['bank_account_number'] ?? '-') ?></div>
                        <div class="small text-muted"><?= e($calc['bank_account_name'] ?? '') ?></div>
                    </div>
                </div>

                <!-- Metrics & Compensation Model -->
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gear-fill me-2 text-primary"></i>Operational Metrics & Rate Basis</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Compensation Model</th>
                                <th class="text-center">Sessions Held</th>
                                <th class="text-center">Enrolled Students</th>
                                <th class="text-end">Batch Fee Collections</th>
                                <th class="text-end">Commission Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-capitalize"><?= str_replace('_', ' ', $calc['compensation_model']) ?></td>
                                <td class="text-center"><?= $calc['total_sessions'] ?></td>
                                <td class="text-center"><?= $calc['total_students'] ?></td>
                                <td class="text-end">Rs. <?= number_format((float)$calc['eligible_revenue'], 2) ?></td>
                                <td class="text-end">
                                    <?php if ($calc['compensation_model'] === 'revenue_share'): ?>
                                        <?= $calc['percentage'] ?>%
                                    <?php else: ?>
                                        Rs. <?= number_format((float)$calc['rate'], 2) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Financial Statement Summary -->
                <div class="row justify-content-end mb-4">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Gross Calculated Compensation:</span>
                            <strong>Rs. <?= number_format((float)$calc['calculated_amount'], 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Adjustments / Allowances (+):</span>
                            <span>Rs. <?= number_format((float)$calc['adjustments'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Deductions / Penalties (-):</span>
                            <span class="text-danger">- Rs. <?= number_format((float)$calc['deductions'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top bg-light p-2 rounded">
                            <strong class="fs-5 text-success">Net Payable Amount:</strong>
                            <strong class="fs-5 text-success font-monospace">Rs. <?= number_format((float)$calc['net_payable'], 2) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Status & Voucher Section -->
                <?php if ($voucher): ?>
                    <div class="alert alert-success p-3 rounded-3 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                            <h6 class="fw-bold mb-0 text-success">PAYMENT DISBURSED</h6>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-sm-6">Voucher Number: <strong class="font-monospace"><?= e($voucher['voucher_number']) ?></strong></div>
                            <div class="col-sm-6 text-sm-end">Disbursed Date: <strong><?= date('d M Y', strtotime($voucher['payment_date'])) ?></strong></div>
                            <div class="col-sm-6">Method: <strong class="text-capitalize"><?= str_replace('_', ' ', $voucher['payment_method']) ?></strong></div>
                            <div class="col-sm-6 text-sm-end">Reference: <strong class="font-monospace"><?= e($voucher['reference_number'] ?? 'N/A') ?></strong></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center d-print-none">
                        <div>
                            <strong class="d-block"><i class="bi bi-clock-history me-1"></i> Status: Approved — Pending Disbursement</strong>
                            <small class="text-muted">Ready to issue payment voucher and update financial accounts.</small>
                        </div>
                        <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#disburseModal">
                            <i class="bi bi-cash-stack me-1"></i> Disburse Payment
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Signatures -->
                <div class="row pt-5 mt-4 text-center">
                    <div class="col-6">
                        <div class="border-top pt-2 small text-muted">Authorized Signatory / Accountant</div>
                    </div>
                    <div class="col-6">
                        <div class="border-top pt-2 small text-muted">Teacher Signature / Acknowledgment</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disburse Payment Modal -->
<?php if (!$voucher): ?>
<div class="modal fade" id="disburseModal" tabindex="-1" aria-labelledby="disburseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="disburseModalLabel"><i class="bi bi-cash-stack me-2 text-success"></i>Disburse Teacher Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('teacher-payments/disburse') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="calculation_id" value="<?= $calc['id'] ?>">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Disbursement Amount (LKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">Rs.</span>
                            <input type="number" step="0.01" name="amount" class="form-control fw-bold fs-5 text-success" value="<?= (float)$calc['net_payable'] ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash Counter</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Bank Reference / Cheque No</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. TXN-894829 or Cheque #">
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Remarks / Internal Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="bi bi-check-circle me-1"></i> Confirm Disbursement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

