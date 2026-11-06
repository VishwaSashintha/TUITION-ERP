<div class="mb-4">
    <a href="<?= url('students') ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Student Directory</a>
    <h4 class="fw-bold mt-2 mb-1">New Student Admission</h4>
    <p class="text-muted mb-0">Register a student and link guardian and batch enrollment</p>
</div>

<form action="<?= url('students/store') ?>" method="POST" class="card-custom p-4">
    <?= csrf_field() ?>

    <!-- ─── SECTION 1: STUDENT INFORMATION ─────────────────────────────────── -->
    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-fill me-1"></i> 1. Student Personal Details</h5>
    
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label-custom">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control form-control-custom" value="<?= e(old('first_name', '')) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label-custom">Last Name <span class="text-danger">*</span></label>
            <input type="text" name="last_name" class="form-control form-control-custom" value="<?= e(old('last_name', '')) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label-custom">Preferred / Calling Name</label>
            <input type="text" name="preferred_name" class="form-control form-control-custom" value="<?= e(old('preferred_name', '')) ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label-custom">Date of Birth</label>
            <input type="date" name="dob" class="form-control form-control-custom" value="<?= e(old('dob', '')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label-custom">Gender</label>
            <select name="gender" class="form-select form-control-custom">
                <option value="male" selected>Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label-custom">Student Phone</label>
            <input type="text" name="phone" class="form-control form-control-custom" placeholder="0771234567" value="<?= e(old('phone', '')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label-custom">Student Email</label>
            <input type="email" name="email" class="form-control form-control-custom" placeholder="student@example.com" value="<?= e(old('email', '')) ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label-custom">School</label>
            <input type="text" name="school" class="form-control form-control-custom" placeholder="e.g. Ananda College" value="<?= e(old('school', '')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label-custom">Grade / Level</label>
            <input type="text" name="grade_level" class="form-control form-control-custom" placeholder="e.g. Grade 12" value="<?= e(old('grade_level', '')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label-custom">Branch Center <span class="text-danger">*</span></label>
            <select name="branch_id" class="form-select form-control-custom" required>
                <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label-custom">Residential Address</label>
            <textarea name="address" class="form-control form-control-custom" rows="2" placeholder="Street, City"><?= e(old('address', '')) ?></textarea>
        </div>
    </div>

    <!-- ─── SECTION 2: GUARDIAN INFORMATION ─────────────────────────────────── -->
    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-people-fill me-1"></i> 2. Parent / Guardian Details</h5>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label-custom">Guardian Full Name <span class="text-danger">*</span></label>
            <input type="text" name="guardian_name" class="form-control form-control-custom" placeholder="e.g. Nihal Perera" value="<?= e(old('guardian_name', '')) ?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label-custom">Relationship</label>
            <select name="guardian_relationship" class="form-select form-control-custom">
                <option value="Father" selected>Father</option>
                <option value="Mother">Mother</option>
                <option value="Guardian">Guardian</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label-custom">Primary Contact Phone <span class="text-danger">*</span></label>
            <input type="text" name="guardian_phone" class="form-control form-control-custom" placeholder="0712345678" value="<?= e(old('guardian_phone', '')) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label-custom">Guardian Email</label>
            <input type="email" name="guardian_email" class="form-control form-control-custom" placeholder="parent@example.com" value="<?= e(old('guardian_email', '')) ?>">
        </div>
    </div>

    <!-- ─── SECTION 3: ACADEMIC ENROLLMENT ─────────────────────────────────── -->
    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-book-fill me-1"></i> 3. Class Batch Enrollment (Optional)</h5>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label-custom">Select Initial Batch</label>
            <select name="batch_id" class="form-select form-control-custom">
                <option value="">-- Do Not Enroll Yet --</option>
                <?php foreach ($batches as $bch): ?>
                    <option value="<?= $bch['id'] ?>">
                        <?= e($bch['name']) ?> (<?= e($bch['subject_name']) ?>) — Rs. <?= number_format((float)$bch['monthly_fee'], 2) ?>/mo
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label-custom">Admission Date <span class="text-danger">*</span></label>
            <input type="date" name="admission_date" class="form-control form-control-custom" value="<?= date('Y-m-d') ?>" required>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="<?= url('students') ?>" class="btn btn-light border">Cancel</a>
        <button type="submit" class="btn btn-primary-custom px-4">
            <i class="bi bi-check2-circle me-1"></i> Complete Admission
        </button>
    </div>
</form>

