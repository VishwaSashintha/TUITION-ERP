<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Profit & Loss Statement (Income Statement)</h2>
        <p class="text-muted mb-0">Financial performance summary comparing accrued revenues against operational and teacher expenditures.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Statement
        </button>
        <a href="<?= url('expenses') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Expenses
        </a>
    </div>
</div>

<!-- Date Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('accounting/profit-loss') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">From Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($startDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">To Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($endDate) ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-calendar3 me-1"></i> Update Statement
                </button>
                <a href="<?= url('accounting/profit-loss') ?>" class="btn btn-sm btn-outline-secondary">Current Month</a>
            </div>
        </form>
    </div>
</div>

<!-- Statement Content -->
<div class="card border-0 shadow-sm mx-auto" style="max-width: 900px;">
    <div class="card-body p-5">
        <div class="text-center border-bottom pb-4 mb-4">
            <h4 class="fw-bold mb-1">Statement of Comprehensive Income</h4>
            <div class="text-muted small">For the period <?= date('d M Y', strtotime($startDate)) ?> to <?= date('d M Y', strtotime($endDate)) ?></div>
            <div class="badge bg-light text-dark border font-monospace mt-2">Amounts in Sri Lankan Rupees (LKR)</div>
        </div>

        <!-- Section 1: Revenues -->
        <h5 class="fw-bold text-success mb-3"><i class="bi bi-arrow-up-circle me-2"></i>1. Operating Revenues</h5>
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle">
                <tbody>
                    <?php if (empty($pnl['revenues'])): ?>
                        <tr>
                            <td class="text-muted fst-italic">Tuition Fee Revenue (Accrued)</td>
                            <td class="text-end font-monospace">Rs. 0.00</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pnl['revenues'] as $rev): ?>
                            <tr>
                                <td><?= e($rev['account_name']) ?></td>
                                <td class="text-end font-monospace fw-bold">Rs. <?= number_format((float)$rev['balance'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-success fw-bold">
                        <td>Total Operating Revenue</td>
                        <td class="text-end font-monospace text-success fs-6">Rs. <?= number_format($pnl['total_revenue'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Section 2: Operating Expenses -->
        <h5 class="fw-bold text-danger mb-3"><i class="bi bi-arrow-down-circle me-2"></i>2. Operating & Teacher Expenses</h5>
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle">
                <tbody>
                    <?php if (empty($pnl['expenses'])): ?>
                        <tr>
                            <td class="text-muted fst-italic">No expense entries posted in this period</td>
                            <td class="text-end font-monospace">Rs. 0.00</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pnl['expenses'] as $exp): ?>
                            <tr>
                                <td><?= e($exp['account_name']) ?></td>
                                <td class="text-end font-monospace text-danger">Rs. <?= number_format((float)$exp['balance'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-danger fw-bold">
                        <td>Total Expenses</td>
                        <td class="text-end font-monospace text-danger fs-6">Rs. <?= number_format($pnl['total_expense'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Section 3: Net Profit / Loss -->
        <?php
        $net = $pnl['net_profit'];
        $margin = $pnl['total_revenue'] > 0 ? round(($net / $pnl['total_revenue']) * 100, 1) : 0;
        ?>
        <div class="p-4 rounded-3 <?= $net >= 0 ? 'bg-success-subtle border border-success' : 'bg-danger-subtle border border-danger' ?>">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0 <?= $net >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= $net >= 0 ? 'NET INSTITUTE PROFIT' : 'NET INSTITUTE DEFICIT' ?>
                    </h4>
                    <small class="text-muted">Net margin: <strong><?= $margin ?>%</strong> of total tuition revenue</small>
                </div>
                <div class="text-end">
                    <h2 class="fw-bold font-monospace mb-0 <?= $net >= 0 ? 'text-success' : 'text-danger' ?>">
                        Rs. <?= number_format($net, 2) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>

