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

<div class="row g-4 mb-4">
    <!-- Assignment Details & Brief -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-left: 4px solid var(--primary-brand) !important;">
                <h6 class="fw-bold mb-0 text-success"><i class="bi bi-file-earmark-text me-2"></i>Assignment Brief</h6>
                <span class="badge bg-light text-dark border">Max: <?= number_format((float)$assignment['max_marks'], 0) ?> Marks</span>
            </div>
            <div class="card-body p-4">
                <div class="mb-3 p-3 bg-light rounded-3">
                    <div class="small text-muted text-uppercase fw-bold">Due Date</div>
                    <div class="fw-bold text-dark fs-6 mt-1">
                        <i class="bi bi-clock-history me-1 text-danger"></i>
                        <?= date('d M Y, h:i A', strtotime($assignment['due_date'])) ?>
                    </div>
                </div>

                <h6 class="fw-bold small text-uppercase text-muted mb-2">Instructions for Students</h6>
                <div class="p-3 bg-light rounded-3 font-monospace small" style="white-space: pre-wrap;"><?= e($assignment['instructions']) ?></div>

                <!-- Submit Coursework Box -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold mb-2"><i class="bi bi-upload me-2 text-primary"></i>Submit Coursework Solution</h6>
                    <form method="POST" action="<?= url('academic/assignments/submit') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Select Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">-- Choose Enrolled Student --</option>
                                <?php foreach ($students as $st): ?>
                                    <option value="<?= $st['id'] ?>"><?= e($st['full_name']) ?> (<?= e($st['admission_number']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Solution Text / Submission Notes / Links</label>
                            <textarea name="submission_text" rows="3" class="form-control" placeholder="Paste solution answer, working steps, or Google Drive / PDF link..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send-fill me-1"></i> Submit Coursework
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Submissions & Grading Table -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-left: 4px solid var(--secondary) !important;">
                <h6 class="fw-bold mb-0 text-info"><i class="bi bi-journal-check me-2"></i>Received Submissions & Grading</h6>
                <span class="badge bg-light text-dark border"><?= count($submissions ?? []) ?> Submitted</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Submitted At</th>
                                <th>Marks / Score</th>
                                <th>Feedback</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($submissions)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No student submissions received yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($submissions as $sub): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= e($sub['student_name']) ?></div>
                                            <small class="text-muted font-monospace"><?= e($sub['admission_number']) ?></small>
                                        </td>
                                        <td class="small text-muted">
                                            <?= e($sub['submitted_at']) ?>
                                        </td>
                                        <td>
                                            <?php if ($sub['marks_awarded'] !== null): ?>
                                                <span class="badge bg-success fs-6">
                                                    <?= number_format((float)$sub['marks_awarded'], 1) ?> / <?= number_format((float)$assignment['max_marks'], 0) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending Grade</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted" style="max-width: 150px;">
                                            <?= e($sub['feedback'] ?: '—') ?>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#gradeForm-<?= $sub['id'] ?>">
                                                <i class="bi bi-pencil-square"></i> Grade
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="collapse bg-light" id="gradeForm-<?= $sub['id'] ?>">
                                        <td colspan="5" class="p-3">
                                            <div class="mb-2">
                                                <span class="small fw-bold text-muted">Student's Submitted Work:</span>
                                                <div class="p-2 bg-white rounded border small mt-1 font-monospace"><?= nl2br(e($sub['submission_text'])) ?></div>
                                            </div>
                                            <form method="POST" action="<?= url('academic/assignments/grade') ?>" class="row g-2 align-items-end">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                                <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">

                                                <div class="col-md-3">
                                                    <label class="form-label small fw-semibold">Marks (Max <?= $assignment['max_marks'] ?>)</label>
                                                    <input type="number" step="0.5" name="marks_awarded" value="<?= e($sub['marks_awarded'] ?? '') ?>" max="<?= $assignment['max_marks'] ?>" class="form-control form-control-sm" required>
                                                </div>
                                                <div class="col-md-7">
                                                    <label class="form-label small fw-semibold">Teacher Feedback</label>
                                                    <input type="text" name="feedback" value="<?= e($sub['feedback'] ?? '') ?>" class="form-control form-control-sm" placeholder="Great effort, review question 3...">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="submit" class="btn btn-sm btn-success w-100">Save Grade</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Student Roster -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-people me-2"></i>Enrolled Students (Class Roster)</h6>
                <span class="badge bg-light text-muted border"><?= count($students) ?> Enrolled</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
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
