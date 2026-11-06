<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">General Ledger Trial Balance</h2>
        <p class="text-muted mb-0">Double-entry mathematical verification of all debits and credits across the chart of accounts.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Trial Balance
        </button>
        <a href="<?= url('expenses') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Expenses
        </a>
    </div>
</div>

<!-- Balance Status Alert -->
<?php if ($trialBalance['is_balanced']): ?>
    <div class="alert alert-success d-flex align-items-center gap-3 p-3 shadow-sm mb-4 border-0">
        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
        <div>
            <h5 class="fw-bold mb-0 text-success">Trial Balance is Perfectly Balanced</h5>
            <small>All general ledger journal lines strictly balance: Total Debits (Rs. <?= number_format($trialBalance['total_debit'], 2) ?>) = Total Credits (Rs. <?= number_format($trialBalance['total_credit'], 2) ?>).</small>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger d-flex align-items-center gap-3 p-3 shadow-sm mb-4 border-0">
        <i class="bi bi-exclamation-triangle-fill fs-2 text-danger"></i>
        <div>
            <h5 class="fw-bold mb-0 text-danger">Trial Balance Imbalance Detected!</h5>
            <small>Debit and Credit totals do not match. Please audit recent manual journal entries.</small>
        </div>
    </div>
<?php endif; ?>

<!-- Trial Balance Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 120px;">Account Code</th>
                        <th>Account Name</th>
                        <th style="width: 140px;">Type</th>
                        <th class="text-end" style="width: 180px;">Debit (LKR)</th>
                        <th class="text-end" style="width: 180px;">Credit (LKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($trialBalance['accounts'])): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                No journal transactions posted yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($trialBalance['accounts'] as $acc): ?>
                            <?php
                            $deb = (float)$acc['total_debit'];
                            $cred = (float)$acc['total_credit'];
                            if ($deb == 0.00 && $cred == 0.00) continue;
                            ?>
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-primary"><?= e($acc['account_code']) ?></td>
                                <td class="fw-bold text-dark"><?= e($acc['account_name']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border text-capitalize"><?= e($acc['account_type']) ?></span>
                                </td>
                                <td class="text-end font-monospace"><?= $deb > 0 ? 'Rs. ' . number_format($deb, 2) : '-' ?></td>
                                <td class="text-end font-monospace"><?= $cred > 0 ? 'Rs. ' . number_format($cred, 2) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-dark fs-6 fw-bold">
                        <td colspan="3" class="ps-3 text-uppercase">Total Debits & Credits</td>
                        <td class="text-end font-monospace text-warning">Rs. <?= number_format($trialBalance['total_debit'], 2) ?></td>
                        <td class="text-end font-monospace text-warning">Rs. <?= number_format($trialBalance['total_credit'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

