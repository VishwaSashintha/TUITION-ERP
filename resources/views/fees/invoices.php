<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Fee Invoices & Billing</h2>
        <p class="text-muted mb-0">Manage student fee invoices, installment collections, receipts, and balances.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('fees/outstanding') ?>" class="btn btn-outline-danger">
            <i class="bi bi-exclamation-triangle me-1"></i> Defaulters Report
        </a>
        <a href="<?= url('fees/create') ?>" class="btn btn-primary">
            <i class="bi bi-file-earmark-plus me-1"></i> Issue Invoice
        </a>
    </div>
</div>

<!-- Financial KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3 fs-4">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Total Invoiced</h6>
                    <h5 class="fw-bold mb-0 text-dark">Rs. <?= number_format((float)($summary['total_invoiced'] ?? 0), 2) ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-success-subtle text-success p-3 me-3 fs-4">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Total Collected</h6>
                    <h5 class="fw-bold mb-0 text-success">Rs. <?= number_format((float)($summary['total_collected'] ?? 0), 2) ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-danger-subtle text-danger p-3 me-3 fs-4">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Total Outstanding</h6>
                    <h5 class="fw-bold mb-0 text-danger">Rs. <?= number_format((float)($summary['total_outstanding'] ?? 0), 2) ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3 fs-4">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Overdue Invoices</h6>
                    <h5 class="fw-bold mb-0 text-warning"><?= (int)($summary['count_overdue'] ?? 0) ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('fees/invoices') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Student name, admission no, invoice #" value="<?= e($filters['search']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="issued" <?= $filters['status'] === 'issued' ? 'selected' : '' ?>>Issued</option>
                    <option value="partially_paid" <?= $filters['status'] === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                    <option value="paid" <?= $filters['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="overdue" <?= $filters['status'] === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Fee Period</label>
                <input type="month" name="fee_period" class="form-control form-control-sm" value="<?= e($filters['fee_period']) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <a href="<?= url('fees/invoices') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Invoice #</th>
                        <th>Student</th>
                        <th>Fee Period</th>
                        <th>Due Date</th>
                        <th class="text-end">Total (LKR)</th>
                        <th class="text-end">Paid (LKR)</th>
                        <th class="text-end">Balance (LKR)</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 opacity-50"></i>
                                <h6>No invoices found matching your criteria.</h6>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td class="ps-3 fw-bold font-monospace">
                                    <a href="<?= url('fees/invoice?id=' . $inv['id']) ?>" class="text-decoration-none">
                                        <?= e($inv['invoice_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($inv['student_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= e($inv['admission_number']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($inv['fee_period']) ?></span></td>
                                <td class="small <?= strtotime($inv['due_date']) < time() && $inv['balance_amount'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= date('d M Y', strtotime($inv['due_date'])) ?>
                                </td>
                                <td class="text-end fw-bold">Rs. <?= number_format((float)$inv['total_amount'], 2) ?></td>
                                <td class="text-end text-success">Rs. <?= number_format((float)$inv['paid_amount'], 2) ?></td>
                                <td class="text-end fw-bold <?= (float)$inv['balance_amount'] > 0 ? 'text-danger' : 'text-muted' ?>">
                                    Rs. <?= number_format((float)$inv['balance_amount'], 2) ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $badge = match($inv['status']) {
                                        'paid'           => 'bg-success',
                                        'partially_paid' => 'bg-warning text-dark',
                                        'overdue'        => 'bg-danger',
                                        'cancelled'      => 'bg-secondary',
                                        default          => 'bg-info text-dark'
                                    };
                                    ?>
                                    <span class="badge <?= $badge ?> text-capitalize"><?= str_replace('_', ' ', $inv['status']) ?></span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <?php if ((float)$inv['balance_amount'] > 0): ?>
                                            <button type="button" class="btn btn-sm btn-success" onclick="openPaymentModal(<?= htmlspecialchars(json_encode($inv)) ?>)" title="Collect Payment">
                                                <i class="bi bi-cash me-1"></i> Pay
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= url('fees/invoice?id=' . $inv['id']) ?>" class="btn btn-sm btn-outline-secondary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Collect Payment Modal -->
<div class="modal fade" id="collectPaymentModal" tabindex="-1" aria-labelledby="collectPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="collectPaymentModalLabel"><i class="bi bi-cash-coin me-2 text-success"></i>Collect Fee Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('fees/collect-payment') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="student_id" id="pay_student_id">
                <input type="hidden" name="invoice_id" id="pay_invoice_id">

                <div class="modal-body">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Student:</span>
                            <strong id="pay_student_name" class="text-dark"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Invoice No:</span>
                            <span id="pay_invoice_number" class="font-monospace fw-bold"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Outstanding Balance:</span>
                            <strong id="pay_balance_display" class="text-danger fs-6"></strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Payment Amount (LKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">Rs.</span>
                            <input type="number" step="0.01" min="1" name="amount" id="pay_amount" class="form-control fw-bold fs-5 text-success" required>
                        </div>
                        <small class="text-muted">Enter partial or full settlement amount.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash Counter</option>
                            <option value="bank_transfer">Bank Transfer / Deposit Slip</option>
                            <option value="card">Credit / Debit Card POS</option>
                            <option value="cheque">Cheque</option>
                            <option value="online">Online Payment Gateway</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reference / Slip Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. Bank slip no, Card auth code, Cheque no">
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Remarks / Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional cashier remarks"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="bi bi-printer me-1"></i> Record & Print Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPaymentModal(inv) {
    document.getElementById('pay_student_id').value = inv.student_id;
    document.getElementById('pay_invoice_id').value = inv.id;
    document.getElementById('pay_student_name').textContent = inv.student_name + ' (' + inv.admission_number + ')';
    document.getElementById('pay_invoice_number').textContent = inv.invoice_number;
    document.getElementById('pay_balance_display').textContent = 'Rs. ' + parseFloat(inv.balance_amount).toFixed(2);
    document.getElementById('pay_amount').value = parseFloat(inv.balance_amount).toFixed(2);
    document.getElementById('pay_amount').max = parseFloat(inv.balance_amount).toFixed(2);

    new bootstrap.Modal(document.getElementById('collectPaymentModal')).show();
}
</script>

