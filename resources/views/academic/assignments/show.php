<div class="mb-4">
    <a href="<?= url('academic/assignments') ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Assignments</a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-2">
        <div>
            <h4 class="fw-bold mb-1"><?= e($assignment['title']) ?></h4>
            <div class="d-flex align-items-center gap-2 small text-muted">
                <span class="badge bg-primary-subtle text-primary border"><?= e($assignment['batch_name']) ?></span>
                <span>&bull;</span>
                <span>Subject: <strong><?= e($assignment['subject_name']) ?></strong></span>
                <span>&bull;</span>
                <span>Teacher: <strong><?= e($assignment['teacher_name'] ?? 'Faculty') ?></strong></span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($assignment['attachment_path'])): ?>
                <a href="<?= url('academic/assignments/download/' . $assignment['id']) ?>" class="btn btn-outline-primary">
                    <i class="bi bi-download me-1"></i> Download Attachment
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Assignment Details & Brief -->
    <div class="col-lg-5">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Assignment Brief</h6>
                <span class="badge bg-light text-dark border">Max: <?= number_format((float)$assignment['max_marks'], 0) ?> Marks</span>
            </div>
            <div class="p-4">
                <div class="mb-3 p-3 bg-light rounded-3">
                    <div class="small text-muted text-uppercase fw-bold">Due Date</div>
                    <div class="fw-bold text-dark fs-6 mt-1">
                        <i class="bi bi-clock-history me-1 text-danger"></i>
                        <?= date('d M Y, h:i A', strtotime($assignment['due_date'])) ?>
                    </div>
                </div>

                <h6 class="fw-bold small text-uppercase text-muted mb-2">Instructions for Students</h6>
                <div class="p-3 bg-light rounded-3 font-monospace small" style="white-space: pre-wrap;"><?= e($assignment['instructions']) ?></div>
            </div>
        </div>
    </div>

    <!-- Student Roster for Submission -->
    <div class="col-lg-7">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Enrolled Students (Class Roster)</h6>
                <span class="badge bg-light text-muted border"><?= count($students) ?> Students</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Admission #</th>
                            <th>Contact</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No students currently enrolled in this batch.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $st): ?>
                                <tr>
                                    <td class="fw-semibold">
                                        <a href="<?= url('students/' . $st['id']) ?>" class="text-dark text-decoration-none">
                                            <?= e($st['full_name']) ?>
                                        </a>
                                    </td>
                                    <td class="font-monospace small"><?= e($st['admission_number']) ?></td>
                                    <td class="small text-muted"><?= e($st['phone'] ?? 'N/A') ?></td>
                                    <td class="text-end">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            Enrolled
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

