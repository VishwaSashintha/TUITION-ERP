<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bank2 text-primary me-2"></i>Cash & Bank Account Balances</h4>
        <p class="text-muted mb-0">Overview of physical cash drawers, commercial bank balances, and recent financial transactions.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('expenses/trial-balance') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-scale"></i> Trial Balance
        </a>
        <a href="<?= url('accounting/profit-loss') ?>" class="btn btn-outline-primary">
            <i class="bi bi-graph-up"></i> P&L Statement
        </a>
    </div>
</div>

<!-- Total Summary KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-value"><?= format_currency($totalCash) ?></div>
                <div class="stat-label">Total Cash in Hand</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-bank"></i>
            </div>
            <div>
                <div class="stat-value"><?= format_currency($totalBank) ?></div>
                <div class="stat-label">Total Bank Deposits</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon accent">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <div class="stat-value"><?= format_currency($totalCash + $totalBank) ?></div>
                <div class="stat-label">Total Liquid Reserves</div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Accounts List -->
<div class="card-custom mb-4">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-credit-card-2-front me-2 text-primary"></i>Configured Liquid Accounts</h6>
        <span class="badge bg-light text-muted border"><?= count($accounts) ?> Accounts</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Type</th>
                    <th>Institution / Drawer</th>
                    <th>Account #</th>
                    <th>Branch</th>
                    <th class="text-end">Current Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No financial accounts found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($accounts as $acc): ?>
                        <tr>
                            <td class="fw-bold text-dark">
                                <i class="bi bi-<?= $acc['account_type'] === 'cash' ? 'cash' : 'bank' ?> me-2 text-<?= $acc['account_type'] === 'cash' ? 'success' : 'primary' ?>"></i>
                                <?= e($acc['account_name']) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $acc['account_type'] === 'cash' ? 'success-subtle text-success' : 'primary-subtle text-primary' ?> border text-uppercase">
                                    <?= e($acc['account_type']) ?>
                                </span>
                            </td>
                            <td><?= e($acc['bank_name'] ?? 'Counter') ?></td>
                            <td class="font-monospace small"><?= e($acc['account_number'] ?? '—') ?></td>
                            <td><?= e($acc['branch_name'] ?? 'Main') ?></td>
                            <td class="text-end fw-bold fs-6 text-dark">
                                <?= format_currency($acc['current_balance']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card-custom">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Inflows & Settlements</h6>
        <span class="badge bg-light text-muted border">Live Log</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Payer / Party</th>
                    <th>Transaction Type</th>
                    <th>Method</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentTransactions)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No transactions recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentTransactions as $tx): ?>
                        <tr>
                            <td class="small text-muted font-monospace"><?= date('d M Y', strtotime($tx['tx_date'])) ?></td>
                            <td><span class="badge bg-light text-primary border font-monospace"><?= e($tx['ref']) ?></span></td>
                            <td class="fw-semibold"><?= e($tx['party']) ?></td>
                            <td><span class="badge bg-success-subtle text-success border"><?= e($tx['tx_type']) ?></span></td>
                            <td class="text-capitalize small"><?= e($tx['payment_method']) ?></td>
                            <td class="text-end fw-bold text-success">+<?= format_currency($tx['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

