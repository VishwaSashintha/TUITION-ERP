<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Examinations & Student Performance</h2>
        <p class="text-muted mb-0">Schedule tests, record marks, compute class rankings, and publish result sheets.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleExamModal">
            <i class="bi bi-plus-circle me-1"></i> Schedule Exam
        </button>
    </div>
</div>

<!-- Examinations Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Exam Name</th>
                        <th>Batch / Subject</th>
                        <th>Branch</th>
                        <th>Exam Date</th>
                        <th>Type</th>
                        <th class="text-center">Max / Pass</th>
                        <th class="text-center">Graded</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-check fs-1 d-block mb-2 opacity-50"></i>
                                <h6>No examinations scheduled yet.</h6>
                                <p class="small mb-3">Schedule a monthly, model, or term test to track student academic progress.</p>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleExamModal">
                                    Schedule Exam
                                </button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($exams as $ex): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark">
                                    <a href="<?= url('examinations/results?id=' . $ex['id']) ?>" class="text-decoration-none">
                                        <?= e($ex['name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= e($ex['batch_name']) ?></div>
                                    <small class="text-muted"><i class="bi bi-book me-1"></i><?= e($ex['subject_name']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e($ex['branch_name']) ?></span></td>
                                <td class="text-nowrap"><i class="bi bi-calendar3 me-1 text-primary"></i><?= date('d M Y', strtotime($ex['exam_date'])) ?></td>
                                <td><span class="badge bg-info-subtle text-info border text-capitalize"><?= e($ex['exam_type']) ?></span></td>
                                <td class="text-center font-monospace small">
                                    <?= (int)$ex['max_marks'] ?> / <span class="text-muted"><?= (int)$ex['pass_mark'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        <?= $ex['graded_students_count'] ?> / <?= $ex['total_students_count'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $stBadge = match($ex['status']) {
                                        'published' => 'bg-success',
                                        'completed' => 'bg-primary',
                                        default     => 'bg-warning text-dark'
                                    };
                                    ?>
                                    <span class="badge <?= $stBadge ?> text-capitalize"><?= $ex['status'] ?></span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= url('examinations/marks-entry?id=' . $ex['id']) ?>" class="btn btn-sm btn-outline-primary" title="Enter Marks">
                                            <i class="bi bi-pencil-square me-1"></i> Marks
                                        </a>
                                        <a href="<?= url('examinations/results?id=' . $ex['id']) ?>" class="btn btn-sm btn-outline-secondary" title="View Results Sheet">
                                            <i class="bi bi-bar-chart-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Schedule Exam Modal -->
<div class="modal fade" id="scheduleExamModal" tabindex="-1" aria-labelledby="scheduleExamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="scheduleExamModalLabel"><i class="bi bi-journal-plus me-2 text-primary"></i>Schedule Examination</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('examinations/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Exam Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. November 2026 Monthly Evaluation" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Choose Batch --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Exam Date <span class="text-danger">*</span></label>
                            <input type="date" name="exam_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Exam Type <span class="text-danger">*</span></label>
                            <select name="exam_type" class="form-select" required>
                                <option value="monthly">Monthly Test</option>
                                <option value="term">Term Exam</option>
                                <option value="model">Model Paper</option>
                                <option value="quiz">Spot Quiz</option>
                                <option value="final">Final Exam</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-0">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Maximum Marks <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" name="max_marks" class="form-control" value="100.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Pass Mark <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" name="pass_mark" class="form-control" value="40.00" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Schedule Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>

