<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Issue Fee Invoice</h2>
        <p class="text-muted mb-0">Create a customized tuition fee invoice with batch link and discounts.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="<?= url('fees/invoices') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>
</div>

<form action="<?= url('fees/store') ?>" method="POST" id="invoiceForm">
    <?= csrf_field() ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0">Invoice Line Items</h5>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Description <span class="text-danger">*</span></th>
                                    <th>Linked Batch</th>
                                    <th style="width: 150px;">Fee (LKR) <span class="text-danger">*</span></th>
                                    <th style="width: 130px;">Discount (LKR)</th>
                                    <th style="width: 140px;" class="text-end">Net Total</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr>
                                    <td>
                                        <input type="text" name="item_description[]" class="form-control form-control-sm" placeholder="e.g. Monthly Tuition Fee" value="Monthly Tuition Fee" required>
                                    </td>
                                    <td>
                                        <select name="item_batch_id[]" class="form-select form-select-sm batch-select" onchange="autoFillFee(this)">
                                            <option value="">-- Optional Batch --</option>
                                            <?php foreach ($batches as $b): ?>
                                                <option value="<?= $b['id'] ?>" data-fee="<?= (float)$b['monthly_fee'] ?>"><?= e($b['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="item_amount[]" class="form-control form-control-sm item-amt" value="5000.00" required oninput="calcTotals()">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="item_discount[]" class="form-control form-control-sm item-disc" value="0.00" oninput="calcTotals()">
                                    </td>
                                    <td class="text-end fw-bold item-net">
                                        Rs. 5,000.00
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)" disabled><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Another Item
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <label class="form-label small fw-bold">Internal Cashier / Accountant Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional internal notes or remarks"></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0">Billing Details</h5>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select" required>
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $st): ?>
                                <option value="<?= $st['id'] ?>" <?= $selectedStudentId == $st['id'] ? 'selected' : '' ?>>
                                    <?= e($st['full_name']) ?> (<?= e($st['admission_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fee Period <span class="text-danger">*</span></label>
                        <input type="month" name="fee_period" class="form-control" value="<?= date('Y-m') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-10', strtotime('+1 month')) ?>" required>
                    </div>
                </div>
            </div>

            <!-- Summary Totals Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0">Summary</h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <strong id="displaySubtotal">Rs. 5,000.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Discount:</span>
                        <strong class="text-danger" id="displayDiscount">- Rs. 0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-6">Grand Total:</span>
                        <strong class="text-primary fs-5" id="displayTotal">Rs. 5,000.00</strong>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                        <i class="bi bi-check2-circle me-1"></i> Issue Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function autoFillFee(selectEl) {
    const fee = selectEl.options[selectEl.selectedIndex].getAttribute('data-fee');
    if (fee) {
        const row = selectEl.closest('tr');
        row.querySelector('.item-amt').value = parseFloat(fee).toFixed(2);
        calcTotals();
    }
}

function addRow() {
    const tbody = document.getElementById('itemsBody');
    const firstRow = tbody.querySelector('tr');
    const clone = firstRow.cloneNode(true);
    clone.querySelector('input[name="item_description[]"]').value = '';
    clone.querySelector('.item-amt').value = '0.00';
    clone.querySelector('.item-disc').value = '0.00';
    clone.querySelector('.item-net').textContent = 'Rs. 0.00';
    clone.querySelector('button').disabled = false;
    tbody.appendChild(clone);
    calcTotals();
}

function removeRow(btn) {
    const tbody = document.getElementById('itemsBody');
    if (tbody.querySelectorAll('tr').length > 1) {
        btn.closest('tr').remove();
        calcTotals();
    }
}

function calcTotals() {
    let subtotal = 0;
    let discount = 0;

    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const amt = parseFloat(row.querySelector('.item-amt').value) || 0;
        const disc = parseFloat(row.querySelector('.item-disc').value) || 0;
        const net = Math.max(0, amt - disc);
        row.querySelector('.item-net').textContent = 'Rs. ' + net.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        subtotal += amt;
        discount += disc;
    });

    const total = Math.max(0, subtotal - discount);
    document.getElementById('displaySubtotal').textContent = 'Rs. ' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('displayDiscount').textContent = '- Rs. ' + discount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('displayTotal').textContent = 'Rs. ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
</script>

