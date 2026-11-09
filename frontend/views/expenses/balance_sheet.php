<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--primary-dark);">Balance Sheet</h3>
        <p class="text-muted mb-0 small">Statement of Financial Position as of <?= e($asOfDate) ?>. Verifies $\text{Assets} = \text{Liabilities} + \text{Equity}$.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('expenses/trial-balance') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-calculator"></i> Trial Balance
        </a>
        <a href="<?= url('accounting/profit-loss') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-graph-up"></i> Profit & Loss
        </a>
        <button onclick="window.print()" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-printer-fill"></i> Print Statement
        </button>
    </div>
</div>

<!-- Date Filter -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('accounting/balance-sheet') ?>" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">As Of Date</label>
                <input type="date" name="as_of" value="<?= e($asOfDate) ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Update Report</button>
            </div>
            <div class="col-md-6 text-end">
                <?php if ($sheet['is_balanced']): ?>
                    <span class="badge bg-success px-3 py-2 fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i> Balance Sheet In Equilibrium (Assets = Liabilities + Equity)
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger px-3 py-2 fs-6">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Discrepancy Detected
                    </span>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Assets Column -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-left: 4px solid var(--primary-brand) !important;">
                <h5 class="fw-bold mb-0" style="color: var(--primary-brand);">Assets</h5>
                <span class="badge bg-light text-success fw-bold">Debit Balances</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Account</th>
                                <th class="text-end">Balance (LKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sheet['assets'])): ?>
                                <?php foreach ($sheet['assets'] as $a): ?>
                                    <tr>
                                        <td><code><?= e($a['account_code']) ?></code></td>
                                        <td class="fw-semibold"><?= e($a['account_name']) ?></td>
                                        <td class="text-end font-monospace"><?= format_currency((float)$a['balance']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No asset ledger accounts recorded.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-uppercase fw-bold">Total Assets</th>
                                <th class="text-end font-monospace text-success fw-bold fs-5"><?= format_currency($sheet['total_assets']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Liabilities & Equity Column -->
    <div class="col-lg-6">
        <!-- Liabilities Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-left: 4px solid var(--accent-orange) !important;">
                <h5 class="fw-bold mb-0" style="color: var(--accent-orange);">Liabilities</h5>
                <span class="badge bg-light text-warning fw-bold">Credit Balances</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Account</th>
                                <th class="text-end">Balance (LKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sheet['liabilities'])): ?>
                                <?php foreach ($sheet['liabilities'] as $l): ?>
                                    <tr>
                                        <td><code><?= e($l['account_code']) ?></code></td>
                                        <td class="fw-semibold"><?= e($l['account_name']) ?></td>
                                        <td class="text-end font-monospace"><?= format_currency((float)$l['balance']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No liability obligations recorded.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-uppercase fw-bold">Total Liabilities</th>
                                <th class="text-end font-monospace text-warning fw-bold fs-5"><?= format_currency($sheet['total_liabilities']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Equity Card -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-left: 4px solid var(--accent-purple) !important;">
                <h5 class="fw-bold mb-0" style="color: var(--accent-purple);">Equity & Retained Surplus</h5>
                <span class="badge bg-light text-info fw-bold">Capital & Reserves</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Account</th>
                                <th class="text-end">Balance (LKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sheet['equity'])): ?>
                                <?php foreach ($sheet['equity'] as $eq): ?>
                                    <tr>
                                        <td><code><?= e($eq['account_code']) ?></code></td>
                                        <td class="fw-semibold"><?= e($eq['account_name']) ?></td>
                                        <td class="text-end font-monospace"><?= format_currency((float)$eq['balance']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr>
                                <td><code>RET-INC</code></td>
                                <td class="fw-semibold">Current Period Net Surplus / (Deficit)</td>
                                <td class="text-end font-monospace fw-bold <?= $sheet['net_income'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= format_currency($sheet['net_income']) ?>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-uppercase fw-bold">Total Liabilities & Equity</th>
                                <th class="text-end font-monospace text-primary fw-bold fs-5"><?= format_currency($sheet['total_liabilities_and_equity']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
