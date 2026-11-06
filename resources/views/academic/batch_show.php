<div class="mb-4">
    <a href="<?= url('academic/batches') ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Batches</a>
</div>

<?php 
$enrolled = (int)$batch['enrolled_students_count'];
$capacity = (int)$batch['max_capacity'];
$pct = $capacity > 0 ? min(100, round(($enrolled / $capacity) * 100)) : 0;
?>

<div class="card-custom p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-light text-dark border font-monospace fw-bold"><?= e($batch['code']) ?></span>
                <span class="badge bg-light text-muted border"><?= e($batch['subject_name']) ?></span>
            </div>
            <h4 class="fw-bold mb-1"><?= e($batch['name']) ?></h4>
            <div class="text-muted" style="font-size: 0.85rem;">
                <i class="bi bi-person me-1"></i>Teacher: <strong><?= e($batch['teacher_name']) ?></strong> &bull;
                <i class="bi bi-geo-alt me-1"></i><?= e($batch['branch_name'] ?? 'Main Branch') ?> (<?= e($batch['classroom'] ?: 'Classroom TBD') ?>)
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <div class="fw-bold fs-5 text-primary"><?= format_currency($batch['monthly_fee']) ?> / mo</div>
                <small class="text-muted"><?= $enrolled ?> / <?= $capacity ?> Seats Booked (<?= $pct ?>%)</small>
            </div>
            <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#enrollModal" <?= $enrolled >= $capacity ? 'disabled' : '' ?>>
                <i class="bi bi-person-plus-fill me-1"></i> Enroll Student
            </button>
        </div>
    </div>
</div>

<!-- ─── STUDENT ROSTER ─────────────────────────────────────────────────────── -->
<div class="card-custom">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-people-fill me-2 text-primary"></i> Enrolled Student Roster (<?= count($students) ?>)</h6>
        <span class="badge badge-custom badge-<?= $enrolled >= $capacity ? 'danger' : 'success' ?>">
            <?= $enrolled >= $capacity ? 'Batch Full' : 'Seats Available' ?>
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>School</th>
                    <th>Phone</th>
                    <th>Enrollment Date</th>
                    <th class="text-end">Profile</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No students currently enrolled in this batch.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border font-monospace fw-bold"><?= e($s['admission_number']) ?></span></td>
                            <td><div class="fw-bold text-dark"><?= e($s['full_name']) ?></div></td>
                            <td><?= e($s['school'] ?: '—') ?></td>
                            <td><?= e($s['phone'] ?: '—') ?></td>
                            <td><?= format_date($s['enrollment_date']) ?></td>
                            <td class="text-end">
                                <a href="<?= url("students/{$s['id']}") ?>" class="btn btn-sm btn-light border">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Enroll Student Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('academic/batches/enroll') ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="batch_id" value="<?= $batch['id'] ?>">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Enroll Student in Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size: 0.85rem;">Enrolling into: <strong><?= e($batch['name']) ?></strong></p>
                <div class="mb-3">
                    <label class="form-label-custom">Select Student <span class="text-danger">*</span></label>
                    <select name="student_id" class="form-select form-control-custom" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($availableStudents as $as): ?>
                            <option value="<?= $as['id'] ?>">
                                [<?= e($as['admission_number']) ?>] <?= e($as['full_name']) ?> (<?= e($as['grade_level'] ?: 'Grade N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary-custom">Confirm Enrollment</button>
            </div>
        </form>
    </div>
</div>

