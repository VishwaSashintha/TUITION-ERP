<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Invoice #<?= e($invoice['invoice_number']) ?></h2>
        <p class="text-muted mb-0">Detailed view of tuition fee invoice, line items, and payment history.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Invoice
        </button>
        <a href="<?= url('fees/invoices') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> All Invoices
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Invoice Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                    <div>
                        <span class="badge bg-primary px-3 py-1 mb-2 font-monospace fs-6">INVOICE</span>
                        <h4 class="fw-bold mb-1 font-monospace"><?= e($invoice['invoice_number']) ?></h4>
                        <span class="text-muted small">Fee Period: <strong><?= e($invoice['fee_period']) ?></strong></span>
                    </div>
                    <div class="text-end">
                        <?php
                        $badge = match($invoice['status']) {
                            'paid'           => 'bg-success',
                            'partially_paid' => 'bg-warning text-dark',
                            'overdue'        => 'bg-danger',
                            'cancelled'      => 'bg-secondary',
                            default          => 'bg-info text-dark'
                        };
                        ?>
                        <span class="badge <?= $badge ?> fs-6 text-capitalize px-3 py-2"><?= str_replace('_', ' ', $invoice['status']) ?></span>
                        <div class="text-muted small mt-2">
                            Issued: <?= date('d M Y', strtotime($invoice['issue_date'])) ?><br>
                            Due: <strong class="<?= strtotime($invoice['due_date']) < time() && $invoice['balance_amount'] > 0 ? 'text-danger' : '' ?>"><?= date('d M Y', strtotime($invoice['due_date'])) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Billed To -->
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted small text-uppercase fw-bold mb-2">Billed To:</h6>
                        <h5 class="fw-bold mb-1 text-dark"><?= e($invoice['student_name']) ?></h5>
                        <div class="small text-muted font-monospace">Admission: <?= e($invoice['admission_number']) ?></div>
                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= e($invoice['phone'] ?? 'N/A') ?></div>
                        <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= e($invoice['email'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="text-muted small text-uppercase fw-bold mb-2">Institute Branch:</h6>
                        <h6 class="fw-bold mb-1 text-dark"><?= e($invoice['branch_name']) ?></h6>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Batch</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Net Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $idx => $it): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td class="fw-bold"><?= e($it['description']) ?></td>
                                    <td><?= e($it['batch_name'] ?? '-') ?></td>
                                    <td class="text-end">Rs. <?= number_format((float)$it['amount'], 2) ?></td>
                                    <td class="text-end text-danger">- Rs. <?= number_format((float)$it['discount'], 2) ?></td>
                                    <td class="text-end fw-bold">Rs. <?= number_format((float)$it['net_amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Totals Section -->
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span>Rs. <?= number_format((float)$invoice['subtotal'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount:</span>
                            <span class="text-danger">- Rs. <?= number_format((float)$invoice['discount_amount'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pt-2 border-top">
                            <strong class="fs-6">Total Amount:</strong>
                            <strong class="fs-6">Rs. <?= number_format((float)$invoice['total_amount'], 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Paid Amount:</span>
                            <span class="fw-bold">Rs. <?= number_format((float)$invoice['paid_amount'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top bg-light p-2 rounded">
                            <strong class="fs-5 text-danger">Balance Due:</strong>
                            <strong class="fs-5 text-danger">Rs. <?= number_format((float)$invoice['balance_amount'], 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History Side Column -->
    <div class="col-lg-4">
        <?php if ((float)$invoice['balance_amount'] > 0): ?>
            <div class="card border-0 shadow-sm mb-4 border-start border-success border-4">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-1">Outstanding Balance</h5>
                    <h2 class="text-danger fw-bold my-2">Rs. <?= number_format((float)$invoice['balance_amount'], 2) ?></h2>
                    <p class="small text-muted mb-3">Record student payment against this invoice.</p>
                    <a href="<?= url('fees/invoices') ?>" class="btn btn-success fw-bold w-100">
                        <i class="bi bi-cash-coin me-1"></i> Collect Payment
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Payment History</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($allocations)): ?>
                    <div class="p-4 text-center text-muted">
                        <small>No payments recorded for this invoice yet.</small>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($allocations as $pa): ?>
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-success">Rs. <?= number_format((float)$pa['amount'], 2) ?></strong>
                                    <div>
                                        <a href="<?= url('fees/receipt?id=' . $pa['payment_id']) ?>" class="badge bg-light text-primary border text-decoration-none me-1">
                                            <i class="bi bi-receipt me-1"></i> <?= e($pa['receipt_number']) ?>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;" onclick="openRefundModal(<?= $pa['payment_id'] ?>, <?= $pa['amount'] ?>, '<?= e($pa['receipt_number']) ?>')">
                                            <i class="bi bi-arrow-counterclockwise"></i> Refund
                                        </button>
                                    </div>
                                </div>
                                <div class="small text-muted d-flex justify-content-between">
                                    <span><?= date('d M Y', strtotime($pa['payment_date'])) ?></span>
                                    <span class="text-capitalize"><?= str_replace('_', ' ', $pa['payment_method']) ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-arrow-counterclockwise me-2"></i>Process Payment Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('fees/refund') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_id" id="refund_payment_id" value="">
                <div class="modal-body">
                    <p class="mb-2">Receipt: <strong id="refund_receipt_number"></strong></p>
                    <div class="mb-3">
                        <label class="form-label-custom">Refund Amount (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="refund_amount" class="form-control form-control-custom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Reason / Justification <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control form-control-custom" rows="2" placeholder="e.g. Student dropped course within refund window" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Confirm refund? This will reopen the invoice balance and update cash ledger.')">Issue Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRefundModal(paymentId, amount, receipt) {
    document.getElementById('refund_payment_id').value = paymentId;
    document.getElementById('refund_amount').value = amount;
    document.getElementById('refund_receipt_number').textContent = receipt;
    new bootstrap.Modal(document.getElementById('refundModal')).show();
}
</script>

