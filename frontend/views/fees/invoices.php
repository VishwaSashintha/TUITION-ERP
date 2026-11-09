<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h4 class="fw-bold mb-1 text-tp-black">Fee Invoices & Billing</h4>
        <p class="text-muted mb-0">Manage student fee invoices, installment collections, receipts, and balances.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('fees/outstanding') ?>" class="btn btn-danger">
            <i class="bi bi-exclamation-triangle me-1"></i> Defaulters Report
        </a>
        <a href="<?= url('fees/create') ?>" class="btn btn-primary">
            <i class="bi bi-file-earmark-plus me-1"></i> Issue Invoice
        </a>
    </div>
</div>

<!-- ─── Financial KPI Cards (Standard 4 per row | Tabular Figures) ────────── -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-purple">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="stat-value">Rs. <?= number_format((float)($summary['total_invoiced'] ?? 0), 2) ?></div>
                <div class="stat-label">Total Invoiced</div>
                <small class="text-muted" style="font-size: 0.75rem;">Billed tuition fees</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green">Rs. <?= number_format((float)($summary['total_collected'] ?? 0), 2) ?></div>
                <div class="stat-label">Total Collected</div>
                <small class="text-muted" style="font-size: 0.75rem;">Recorded fee receipts</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="stat-value text-tp-coral">Rs. <?= number_format((float)($summary['total_outstanding'] ?? 0), 2) ?></div>
                <div class="stat-label">Total Outstanding</div>
                <small class="text-muted" style="font-size: 0.75rem;">Unsettled fee balance</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-orange">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <div>
                <div class="stat-value text-tp-orange"><?= (int)($summary['count_overdue'] ?? 0) ?></div>
                <div class="stat-label">Overdue Invoices</div>
                <small class="text-muted" style="font-size: 0.75rem;">Past due settlement</small>
            </div>
        </div>
    </div>
</div>

<!-- ─── Filter Card ───────────────────────────────────────────────────────── -->
<div class="card p-3 mb-4">
    <form method="GET" action="<?= url('fees/invoices') ?>" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label mb-1">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Student name, admission no, invoice #" value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="issued" <?= $filters['status'] === 'issued' ? 'selected' : '' ?>>Issued</option>
                <option value="partially_paid" <?= $filters['status'] === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                <option value="paid" <?= $filters['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="overdue" <?= $filters['status'] === 'overdue' ? 'selected' : '' ?>>Overdue</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1">Fee Period</label>
            <input type="month" name="fee_period" class="form-control" value="<?= e($filters['fee_period']) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="bi bi-search me-1"></i> Filter
            </button>
            <a href="<?= url('fees/invoices') ?>" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- ─── Invoices Table ───────────────────────────────────────────────────── -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Student</th>
                    <th>Fee Period</th>
                    <th>Due Date</th>
                    <th class="text-end">Total (LKR)</th>
                    <th class="text-end">Paid (LKR)</th>
                    <th class="text-end">Balance (LKR)</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
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
                            <td class="fw-bold font-monospace">
                                <a href="<?= url('fees/invoice?id=' . $inv['id']) ?>" class="text-tp-green text-decoration-none">
                                    <?= e($inv['invoice_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-tp-black"><?= e($inv['student_name']) ?></div>
                                <small class="text-muted font-monospace"><?= e($inv['admission_number']) ?></small>
                            </td>
                            <td><span class="badge badge-dark"><?= e($inv['fee_period']) ?></span></td>
                            <td class="small <?= strtotime($inv['due_date']) < time() && $inv['balance_amount'] > 0 ? 'text-tp-coral fw-bold' : 'text-muted' ?>">
                                <?= date('d M Y', strtotime($inv['due_date'])) ?>
                            </td>
                            <td class="text-end fw-bold fw-number">Rs. <?= number_format((float)$inv['total_amount'], 2) ?></td>
                            <td class="text-end text-tp-green fw-number">Rs. <?= number_format((float)$inv['paid_amount'], 2) ?></td>
                            <td class="text-end fw-bold fw-number <?= (float)$inv['balance_amount'] > 0 ? 'text-tp-coral' : 'text-muted' ?>">
                                Rs. <?= number_format((float)$inv['balance_amount'], 2) ?>
                            </td>
                            <td class="text-center">
                                <?php
                                $badgeClass = match($inv['status']) {
                                    'paid'           => 'badge-success',
                                    'partially_paid' => 'badge-warning',
                                    'overdue'        => 'badge-danger',
                                    'cancelled'      => 'badge-dark',
                                    default          => 'badge-info'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> text-capitalize"><?= str_replace('_', ' ', $inv['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?php if ((float)$inv['balance_amount'] > 0): ?>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="openPaymentModal(<?= htmlspecialchars(json_encode($inv)) ?>)" title="Collect Payment">
                                            <i class="bi bi-cash me-1"></i> Pay
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?= url('fees/invoice?id=' . $inv['id']) ?>" class="btn btn-sm btn-secondary" title="View Details">
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

<!-- Collect Payment Modal -->
<div class="modal fade" id="collectPaymentModal" tabindex="-1" aria-labelledby="collectPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-tp-black" id="collectPaymentModalLabel"><i class="bi bi-cash-coin me-2 text-tp-green"></i>Collect Fee Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('fees/collect-payment') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="student_id" id="pay_student_id">
                <input type="hidden" name="invoice_id" id="pay_invoice_id">

                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Student:</span>
                            <strong id="pay_student_name" class="text-tp-black"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Invoice No:</span>
                            <span id="pay_invoice_number" class="font-monospace fw-bold"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Outstanding Balance:</span>
                            <strong id="pay_balance_display" class="text-tp-coral fs-6 fw-number"></strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Amount (LKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">Rs.</span>
                            <input type="number" step="0.01" min="1" name="amount" id="pay_amount" class="form-control fw-bold fs-5 text-tp-green fw-number" required>
                        </div>
                        <small class="text-muted">Enter partial or full settlement amount.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash Counter</option>
                            <option value="bank_transfer">Bank Transfer / Deposit Slip</option>
                            <option value="card">Credit / Debit Card POS</option>
                            <option value="cheque">Cheque</option>
                            <option value="online">Online Payment Gateway</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference / Slip Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. Bank slip no, Card auth code, Cheque no">
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Remarks / Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional cashier remarks"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
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
