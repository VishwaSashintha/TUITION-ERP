<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Enter Exam Marks</h2>
        <p class="text-muted mb-0">Record test scores and automatically compute standard grades and student rankings.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('examinations/results?id=' . $exam['id']) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-bar-chart-fill me-1"></i> View Results
        </a>
        <a href="<?= url('examinations') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Exams
        </a>
    </div>
</div>

<!-- Exam Summary Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 bg-light rounded d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="fw-bold mb-1 text-primary"><?= e($exam['name']) ?></h5>
            <div class="d-flex flex-wrap gap-3 small text-muted">
                <span><i class="bi bi-mortarboard me-1"></i><strong>Batch:</strong> <?= e($exam['batch_name']) ?></span>
                <span><i class="bi bi-book me-1"></i><strong>Subject:</strong> <?= e($exam['subject_name']) ?></span>
                <span><i class="bi bi-calendar3 me-1"></i><strong>Date:</strong> <?= date('d M Y', strtotime($exam['exam_date'])) ?></span>
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="text-center px-3 py-1 bg-white rounded border">
                <small class="text-muted d-block">Max Score</small>
                <strong class="fs-6"><?= (int)$exam['max_marks'] ?></strong>
            </div>
            <div class="text-center px-3 py-1 bg-white rounded border">
                <small class="text-muted d-block">Pass Mark</small>
                <strong class="fs-6 text-success"><?= (int)$exam['pass_mark'] ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- Marks Entry Form -->
<form action="<?= url('examinations/save-marks') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="examination_id" value="<?= $exam['id'] ?>">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th style="width: 160px;">Marks Obtained</th>
                            <th class="text-center" style="width: 120px;">Absent</th>
                            <th class="text-center" style="width: 90px;">Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                    <h6>No enrolled students found in this batch.</h6>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $idx => $st): ?>
                                <?php $isAbs = (bool)($st['is_absent'] ?? false); ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $idx + 1 ?></td>
                                    <td class="fw-bold text-dark"><?= e($st['full_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border font-monospace"><?= e($st['admission_number']) ?></span></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.5" min="0" max="<?= (float)$exam['max_marks'] ?>" 
                                                   name="marks[<?= $st['student_id'] ?>]" 
                                                   id="marks_<?= $st['student_id'] ?>"
                                                   class="form-control fw-bold fs-6 text-center" 
                                                   value="<?= $isAbs ? '' : ($st['marks_obtained'] !== null ? (float)$st['marks_obtained'] : '') ?>"
                                                   <?= $isAbs ? 'disabled' : '' ?>>
                                            <span class="input-group-text">/ <?= (int)$exam['max_marks'] ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="absent[<?= $st['student_id'] ?>]" value="1" id="abs_<?= $st['student_id'] ?>" <?= $isAbs ? 'checked' : '' ?> onchange="toggleAbsent(<?= $st['student_id'] ?>)">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($st['grade'])): ?>
                                            <span class="badge <?= $st['grade'] === 'A' ? 'bg-success' : ($st['grade'] === 'F' ? 'bg-danger' : 'bg-primary') ?> fs-6 font-monospace">
                                                <?= e($st['grade']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-3">
                                        <input type="text" name="remarks[<?= $st['student_id'] ?>]" class="form-control form-control-sm" placeholder="Optional remark" value="<?= e($st['remarks'] ?? '') ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($students)): ?>
            <div class="card-footer bg-white p-3 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small"><i class="bi bi-calculator me-1"></i> Grading scale: A (75%+), B (65-74%), C (55-64%), S (40-54%), F (<40%). Student ranks are recalculated automatically.</span>
                <button type="submit" class="btn btn-primary fw-bold px-4">
                    <i class="bi bi-check2-circle me-1"></i> Save Marks & Compute Ranks
                </button>
            </div>
        <?php endif; ?>
    </div>
</form>

<script>
function toggleAbsent(studentId) {
    const chk = document.getElementById('abs_' + studentId);
    const input = document.getElementById('marks_' + studentId);
    if (chk.checked) {
        input.value = '';
        input.disabled = true;
    } else {
        input.disabled = false;
        input.focus();
    }
}
</script>

