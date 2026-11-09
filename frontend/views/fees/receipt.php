<div class="row align-items-center mb-4 d-print-none">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Payment Receipt</h2>
        <p class="text-muted mb-0">Official student payment receipt and financial proof.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Print Receipt
        </button>
        <a href="<?= url('fees/invoices') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>
</div>

<!-- Printable Receipt Container -->
<div class="card border-0 shadow-sm mx-auto" style="max-width: 780px;">
    <div class="card-body p-5">
        <!-- Receipt Header -->
        <div class="row align-items-center border-bottom pb-4 mb-4">
            <div class="col-sm-7">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <img src="<?= institute_logo() ?>" alt="<?= e(institute_name()) ?>" height="38" class="img-fluid">
                    <div>
                        <small class="text-muted d-block fw-semibold"><?= e($payment['branch_name'] ?? 'Main') ?> Branch</small>
                    </div>
                </div>
                <div class="small text-muted">
                    <?= e($payment['branch_address'] ?? 'Sri Lanka') ?><br>
                    Tel: <?= e($payment['branch_phone'] ?? $tenant['phone'] ?? '+94 11 234 5678') ?> | Email: <?= e($tenant['email'] ?? 'info@apexeducation.lk') ?>
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fs-6 font-monospace mb-2">
                    OFFICIAL RECEIPT
                </span>
                <h5 class="fw-bold mb-1 font-monospace text-primary"><?= e($payment['receipt_number']) ?></h5>
                <div class="small text-muted">Date: <strong><?= date('d M Y', strtotime($payment['payment_date'])) ?></strong></div>
                <div class="small text-muted">Payment #: <span class="font-monospace"><?= e($payment['payment_number']) ?></span></div>
            </div>
        </div>

        <!-- Student & Payment Meta -->
        <div class="row g-3 mb-4 p-3 bg-light rounded-3">
            <div class="col-sm-6">
                <span class="text-muted small text-uppercase fw-bold d-block">Received From:</span>
                <strong class="fs-6 text-dark d-block"><?= e($payment['student_name']) ?></strong>
                <span class="small text-muted font-monospace">Admission: <?= e($payment['admission_number']) ?></span>
                <?php if (!empty($payment['phone'])): ?>
                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= e($payment['phone']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6 text-sm-end">
                <span class="text-muted small text-uppercase fw-bold d-block">Payment Mode:</span>
                <strong class="fs-6 text-dark text-capitalize d-block"><?= str_replace('_', ' ', $payment['payment_method']) ?></strong>
                <?php if (!empty($payment['reference_number'])): ?>
                    <span class="small text-muted">Ref / Slip: <strong class="font-monospace text-dark"><?= e($payment['reference_number']) ?></strong></span><br>
                <?php endif; ?>
                <span class="small text-muted">Cashier: <?= e($payment['cashier_name'] ?? 'System Counter') ?></span>
            </div>
        </div>

        <!-- Allocation Breakdown Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice Number</th>
                        <th>Period</th>
                        <th class="text-end">Invoice Total</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Remaining Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allocations)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">Direct fee payment recorded without specific invoice allocation.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allocations as $idx => $al): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td class="font-monospace fw-bold"><?= e($al['invoice_number']) ?></td>
                                <td><?= e($al['fee_period']) ?></td>
                                <td class="text-end">Rs. <?= number_format((float)$al['total_amount'], 2) ?></td>
                                <td class="text-end fw-bold text-success">Rs. <?= number_format((float)$al['amount'], 2) ?></td>
                                <td class="text-end <?= (float)$al['balance_amount'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    Rs. <?= number_format((float)$al['balance_amount'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="4" class="text-end fs-6">Total Amount Received:</th>
                        <th colspan="2" class="text-end fs-5 text-success font-monospace fw-bold">
                            Rs. <?= number_format((float)$payment['amount'], 2) ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (!empty($payment['notes'])): ?>
            <div class="small text-muted mb-4 p-2 border-start border-3 border-info bg-light">
                <strong>Remarks:</strong> <?= e($payment['notes']) ?>
            </div>
        <?php endif; ?>

        <!-- Signature Section -->
        <div class="row pt-5 mt-4 text-center">
            <div class="col-4">
                <div class="border-top pt-2 small text-muted">Student / Guardian</div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 small text-muted">Cashier Signature</div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2 small text-muted">Official Stamp</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-3 border-top">
            Thank you for your payment! This is an electronically generated official receipt.
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background: #fff !important;
        color: #000 !important;
    }
    .sidebar, .topbar, .btn, .d-print-none {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

