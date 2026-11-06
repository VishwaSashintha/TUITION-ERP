<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Operational Expenses</h2>
        <p class="text-muted mb-0">Record and track institute operational costs with automated double-entry ledger postings.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('accounting/trial-balance') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-scale me-1"></i> Trial Balance
        </a>
        <a href="<?= url('accounting/profit-loss') ?>" class="btn btn-outline-info">
            <i class="bi bi-graph-up me-1"></i> P&L Statement
        </a>
        <a href="<?= url('expenses/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Record Expense
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-danger-subtle text-danger p-3 me-3 fs-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Total Expenses (Selected Range)</h6>
                    <h3 class="fw-bold mb-0 text-danger">Rs. <?= number_format((float)($summary['total_expense'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3 fs-3">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Total Vouchers Logged</h6>
                    <h3 class="fw-bold mb-0 text-primary"><?= (int)($summary['count_expense'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('expenses') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Expense Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">From Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($filters['start_date']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">To Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($filters['end_date']) ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="<?= url('expenses') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Expenses Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Voucher #</th>
                        <th>Date</th>
                        <th>Payee / Vendor</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Branch</th>
                        <th class="text-end">Amount (LKR)</th>
                        <th class="pe-3">Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 opacity-50"></i>
                                <h6>No expense records found for the selected period.</h6>
                                <p class="small mb-3">Keep your accounts balanced by logging daily institute expenses.</p>
                                <a href="<?= url('expenses/create') ?>" class="btn btn-sm btn-primary">Record New Expense</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-primary"><?= e($exp['expense_number']) ?></td>
                                <td><?= date('d M Y', strtotime($exp['expense_date'])) ?></td>
                                <td class="fw-bold"><?= e($exp['payee']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= e($exp['category_name']) ?></span></td>
                                <td class="small text-muted" style="max-width: 250px;"><?= e($exp['description']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= e($exp['branch_name']) ?></span></td>
                                <td class="text-end fw-bold text-danger">Rs. <?= number_format((float)$exp['amount'], 2) ?></td>
                                <td class="pe-3 small text-muted"><?= e($exp['created_by_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

