<div class="mb-4">
    <a href="<?= url('teachers') ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Teacher Directory</a>
    <h4 class="fw-bold mt-2 mb-1">Register New Teacher</h4>
    <p class="text-muted mb-0">Create teacher profile and login portal account</p>
</div>

<form action="<?= url('teachers/store') ?>" method="POST" class="card-custom p-4">
    <?= csrf_field() ?>

    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge me-1"></i> 1. Teacher Information</h5>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label-custom">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control form-control-custom" placeholder="e.g. Chandana" value="<?= e(old('first_name', '')) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label-custom">Last Name <span class="text-danger">*</span></label>
            <input type="text" name="last_name" class="form-control form-control-custom" placeholder="e.g. Jayasuriya" value="<?= e(old('last_name', '')) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label-custom">Branch Center</label>
            <select name="branch_id" class="form-select form-control-custom">
                <option value="">All Branches</option>
                <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label-custom">Phone Number <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control form-control-custom" placeholder="0771234567" value="<?= e(old('phone', '')) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label-custom">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control form-control-custom" placeholder="teacher@tuitionpro.lk" value="<?= e(old('email', '')) ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label-custom">Academic Qualifications</label>
            <input type="text" name="qualifications" class="form-control form-control-custom" placeholder="e.g. B.Sc (Hons), M.Sc" value="<?= e(old('qualifications', '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label-custom">Subject Specialization</label>
            <input type="text" name="specialization" class="form-control form-control-custom" placeholder="e.g. Physics (A/L)" value="<?= e(old('specialization', '')) ?>">
        </div>
    </div>

    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-cash-coin me-1"></i> 2. Compensation Agreement</h5>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label-custom">Payment Model <span class="text-danger">*</span></label>
            <select name="compensation_model" id="compModel" class="form-select form-control-custom" onchange="toggleCompFields()">
                <option value="revenue_share" selected>Revenue Share Percentage (%)</option>
                <option value="per_session">Per-Session Fixed Rate (Rs.)</option>
                <option value="per_student">Per-Student Fixed Rate (Rs.)</option>
                <option value="fixed_salary">Fixed Monthly Salary (Rs.)</option>
                <option value="custom">Custom Arrangement</option>
            </select>
        </div>
        <div class="col-md-4" id="percentageGroup">
            <label class="form-label-custom">Commission Share (%)</label>
            <div class="input-group">
                <input type="number" step="0.01" name="default_percentage" class="form-control form-control-custom" placeholder="e.g. 40.00" value="40.00">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-md-4" id="rateGroup">
            <label class="form-label-custom">Fixed Rate / Salary (Rs.)</label>
            <div class="input-group">
                <span class="input-group-text">Rs.</span>
                <input type="number" step="0.01" name="default_rate" class="form-control form-control-custom" placeholder="0.00" value="0.00">
            </div>
        </div>
        <div class="col-12">
            <label class="form-label-custom">Bank Account Remittance Details</label>
            <textarea name="bank_account_details" class="form-control form-control-custom" rows="2" placeholder="Bank Name, Branch Code, Account Number, Account Name"></textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="<?= url('teachers') ?>" class="btn btn-light border">Cancel</a>
        <button type="submit" class="btn btn-primary-custom px-4">
            <i class="bi bi-check2-circle me-1"></i> Register Teacher
        </button>
    </div>
</form>

<script>
function toggleCompFields() {
    const model = document.getElementById('compModel').value;
    const pGroup = document.getElementById('percentageGroup');
    const rGroup = document.getElementById('rateGroup');

    if (model === 'revenue_share') {
        pGroup.style.display = 'block';
        rGroup.style.display = 'none';
    } else if (model === 'custom') {
        pGroup.style.display = 'block';
        rGroup.style.display = 'block';
    } else {
        pGroup.style.display = 'none';
        rGroup.style.display = 'block';
    }
}
toggleCompFields();
</script>

