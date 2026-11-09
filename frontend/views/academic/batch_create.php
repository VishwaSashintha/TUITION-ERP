<div class="mb-4">
    <a href="<?= url('academic/batches') ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Batches</a>
    <h4 class="fw-bold mt-2 mb-1">Create Class Batch</h4>
    <p class="text-muted mb-0">Set up a batch with teacher assignment, classroom, and seat limits</p>
</div>

<form action="<?= url('academic/batches/store') ?>" method="POST" class="card-custom p-4">
    <?= csrf_field() ?>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label-custom">Batch Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-control-custom" placeholder="e.g. 2026 Combined Maths (Saturday Theory)" required>
        </div>
        <div class="col-md-6">
            <label class="form-label-custom">Batch Code <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control form-control-custom" placeholder="e.g. APX-MAT-26" required>
        </div>

        <div class="col-md-4">
            <label class="form-label-custom">Subject <span class="text-danger">*</span></label>
            <select name="subject_id" class="form-select form-control-custom" required>
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['grade_level'] ?: 'A/L') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label-custom">Assigned Teacher <span class="text-danger">*</span></label>
            <select name="teacher_id" class="form-select form-control-custom" required>
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= e($t['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label-custom">Branch Center <span class="text-danger">*</span></label>
            <select name="branch_id" class="form-select form-control-custom" required>
                <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label-custom">Monthly Fee (LKR) <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">Rs.</span>
                <input type="number" step="0.01" name="monthly_fee" class="form-control form-control-custom" placeholder="3500.00" value="3500.00" required>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label-custom">Seat Capacity Limit <span class="text-danger">*</span></label>
            <input type="number" name="max_capacity" class="form-control form-control-custom" placeholder="e.g. 150" value="100" required>
        </div>

        <div class="col-md-4">
            <label class="form-label-custom">Classroom / Hall Name</label>
            <input type="text" name="classroom" class="form-control form-control-custom" placeholder="e.g. Auditorium A">
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="<?= url('academic/batches') ?>" class="btn btn-light border">Cancel</a>
        <button type="submit" class="btn btn-primary-custom px-4">
            <i class="bi bi-check2-circle me-1"></i> Save Batch
        </button>
    </div>
</form>

