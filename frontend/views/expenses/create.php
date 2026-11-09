<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Record Institute Expense</h2>
        <p class="text-muted mb-0">Create an operational expenditure voucher with automatic double-entry ledger posting.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="<?= url('expenses') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Expenses
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-danger"></i>Expense Voucher Details</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('expenses/store') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Payee / Vendor <span class="text-danger">*</span></label>
                            <input type="text" name="payee" class="form-control" placeholder="e.g. Ceylon Electricity Board, Landlord, Stationer" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Expense Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                <?php foreach ($branches as $br): ?>
                                    <option value="<?= $br['id'] ?>"><?= e($br['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Expense Amount (LKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rs.</span>
                                <input type="number" step="0.01" min="1" name="amount" class="form-control fw-bold fs-5 text-danger" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Paid From Account <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select" required>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>">
                                        <?= e($acc['account_name']) ?> (Rs. <?= number_format((float)$acc['current_balance'], 2) ?> available)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Description / Purpose <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Provide full details or billing reference for this payment" required></textarea>
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-4">
                        <i class="bi bi-shield-check me-1"></i> Saving this voucher automatically debits the respective Expense Ledger account and credits the selected Cash/Bank account.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('expenses') ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save & Post Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

