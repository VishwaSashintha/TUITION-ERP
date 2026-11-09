<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Fee Defaulters & Outstanding Dues</h2>
        <p class="text-muted mb-0">Track pending balances, overdue student accounts, and follow-up contacts.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
        <a href="<?= url('fees/invoices') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Invoices
        </a>
    </div>
</div>

<!-- Alert Banner -->
<div class="card border-0 shadow-sm mb-4 border-start border-danger border-4">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="fw-bold mb-1 text-danger">Total Outstanding Dues</h5>
            <p class="text-muted small mb-0">Total unpaid fee liability across all active enrolled students.</p>
        </div>
        <div class="text-end">
            <h2 class="fw-bold text-danger mb-0">Rs. <?= number_format($totalOutstanding, 2) ?></h2>
            <small class="text-muted"><?= count($defaulters) ?> Overdue Accounts</small>
        </div>
    </div>
</div>

<!-- Defaulters Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Guardian & Phone</th>
                        <th>Invoice #</th>
                        <th>Period</th>
                        <th>Due Date</th>
                        <th class="text-center">Overdue Days</th>
                        <th class="text-end">Total (LKR)</th>
                        <th class="text-end">Balance Due (LKR)</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($defaulters)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-2"></i>
                                <h6>Excellent! No outstanding fee balances found.</h6>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($defaulters as $d): ?>
                            <?php $days = (int)$d['days_overdue']; ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark"><?= e($d['student_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= e($d['admission_number']) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($d['guardian_name'])): ?>
                                        <div class="small fw-bold"><?= e($d['guardian_name']) ?></div>
                                        <a href="tel:<?= e($d['guardian_phone'] ?? $d['phone']) ?>" class="small text-decoration-none text-primary">
                                            <i class="bi bi-telephone-fill me-1"></i><?= e($d['guardian_phone'] ?? $d['phone'] ?? 'N/A') ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="tel:<?= e($d['phone']) ?>" class="small text-decoration-none text-primary">
                                            <i class="bi bi-telephone-fill me-1"></i><?= e($d['phone'] ?? 'N/A') ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="font-monospace fw-bold">
                                    <a href="<?= url('fees/invoice?id=' . $d['id']) ?>" class="text-decoration-none">
                                        <?= e($d['invoice_number']) ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($d['fee_period']) ?></span></td>
                                <td class="small"><?= date('d M Y', strtotime($d['due_date'])) ?></td>
                                <td class="text-center">
                                    <?php if ($days > 30): ?>
                                        <span class="badge bg-danger"><?= $days ?> days</span>
                                    <?php elseif ($days > 0): ?>
                                        <span class="badge bg-warning text-dark"><?= $days ?> days</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Due Soon</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">Rs. <?= number_format((float)$d['total_amount'], 2) ?></td>
                                <td class="text-end fw-bold text-danger">Rs. <?= number_format((float)$d['balance_amount'], 2) ?></td>
                                <td class="text-end pe-3">
                                    <a href="<?= url('fees/invoices?search=' . urlencode($d['admission_number'])) ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-cash me-1"></i> Settle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

