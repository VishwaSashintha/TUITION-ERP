<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h4 class="fw-bold mb-1 text-tp-black">Session Attendance</h4>
        <p class="text-muted mb-0">Record and verify student attendance for the scheduled class session.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('attendance/qr-scanner?session_id=' . $session['id']) ?>" class="btn btn-secondary">
            <i class="bi bi-qr-code-scan me-1 text-tp-green"></i> Open QR Scanner
        </a>
        <a href="<?= url('attendance/history') ?>" class="btn btn-secondary">
            <i class="bi bi-clock-history me-1"></i> Attendance Log
        </a>
    </div>
</div>

<!-- Session Details Card -->
<div class="card mb-4">
    <div class="card-body p-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge badge-purple px-3 py-1 fs-6"><?= e($session['batch_name']) ?></span>
                    <span class="badge badge-dark"><?= e($session['subject_name']) ?></span>
                    <span class="badge badge-info"><?= e($session['branch_name']) ?></span>
                </div>
                <div class="d-flex flex-wrap gap-4 text-muted small mt-2">
                    <div><i class="bi bi-calendar-event me-1 text-tp-green"></i> <strong>Date:</strong> <?= date('l, d M Y', strtotime($session['session_date'])) ?></div>
                    <div><i class="bi bi-clock me-1 text-tp-green"></i> <strong>Time:</strong> <?= date('h:i A', strtotime($session['start_time'])) ?> - <?= date('h:i A', strtotime($session['end_time'])) ?></div>
                    <div><i class="bi bi-person-video3 me-1 text-tp-green"></i> <strong>Teacher:</strong> <?= e($session['teacher_name']) ?></div>
                    <div><i class="bi bi-geo-alt me-1 text-tp-green"></i> <strong>Room:</strong> <?= e($session['classroom'] ?? 'Standard Hall') ?></div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <?php if (!empty($daySessions) && count($daySessions) > 1): ?>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-arrow-left-right me-1"></i> Switch Session Today
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border">
                            <?php foreach ($daySessions as $ds): ?>
                                <li>
                                    <a class="dropdown-item <?= $ds['id'] == $session['id'] ? 'active' : '' ?>" href="<?= url('attendance/take?session_id=' . $ds['id']) ?>">
                                        <?= date('h:i A', strtotime($ds['start_time'])) ?> — <?= e($ds['batch_name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Form -->
<form action="<?= url('attendance/save') ?>" method="POST" id="attendanceForm">
    <?= csrf_field() ?>
    <input type="hidden" name="session_id" value="<?= $session['id'] ?>">

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-tp-black">Enrolled Students (<?= count($students) ?>)</h6>
                <small class="text-muted">Set student statuses and press Save Attendance.</small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="markAll('present')">
                    <i class="bi bi-check-all me-1"></i> All Present
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">
                    <i class="bi bi-x-circle me-1"></i> All Absent
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th style="min-width: 320px;">Attendance Status</th>
                            <th>Notes / Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                    <h6>No students currently enrolled in this batch.</h6>
                                    <p class="small mb-3">Enroll students in the batch to record their attendance.</p>
                                    <a href="<?= url('academic/batches') ?>" class="btn btn-sm btn-primary">Go to Batches</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $idx => $st): ?>
                                <?php
                                $status = $st['attendance_status'] ?? 'present';
                                ?>
                                <tr>
                                    <td class="text-muted fw-number"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar bg-tp-green text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                <?= strtoupper(substr($st['full_name'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-tp-black"><?= e($st['full_name']) ?></div>
                                                <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($st['phone'] ?? 'No Phone') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-dark font-monospace"><?= e($st['admission_number']) ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <input type="radio" class="btn-check" name="attendance[<?= $st['id'] ?>]" id="pres_<?= $st['id'] ?>" value="present" <?= $status === 'present' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-primary" for="pres_<?= $st['id'] ?>"><i class="bi bi-check-lg me-1"></i> Present</label>

                                            <input type="radio" class="btn-check" name="attendance[<?= $st['id'] ?>]" id="late_<?= $st['id'] ?>" value="late" <?= $status === 'late' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-warning" for="late_<?= $st['id'] ?>"><i class="bi bi-alarm me-1"></i> Late</label>

                                            <input type="radio" class="btn-check" name="attendance[<?= $st['id'] ?>]" id="abs_<?= $st['id'] ?>" value="absent" <?= $status === 'absent' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-danger" for="abs_<?= $st['id'] ?>"><i class="bi bi-x-lg me-1"></i> Absent</label>

                                            <input type="radio" class="btn-check" name="attendance[<?= $st['id'] ?>]" id="exc_<?= $st['id'] ?>" value="excused" <?= $status === 'excused' ? 'checked' : '' ?>>
                                            <label class="btn btn-secondary" for="exc_<?= $st['id'] ?>">Excused</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="notes[<?= $st['id'] ?>]" class="form-control" style="min-height: 36px;" placeholder="Optional notes" value="<?= e($st['notes'] ?? '') ?>">
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
                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Saving marks session as completed and logs full audit trail.</span>
                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Save Attendance
                </button>
            </div>
        <?php endif; ?>
    </div>
</form>

<script>
function markAll(type) {
    document.querySelectorAll(`input[type="radio"][value="${type}"]`).forEach(el => {
        el.checked = true;
    });
}
</script>
