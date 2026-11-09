<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h4 class="fw-bold mb-1 text-tp-black">Attendance Records & History</h4>
        <p class="text-muted mb-0">Search, filter, and audit attendance logs across all batches and sessions.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('attendance/qr-scanner') ?>" class="btn btn-secondary">
            <i class="bi bi-qr-code-scan me-1 text-tp-green"></i> QR Scanner
        </a>
        <a href="<?= url('attendance/take') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Mark Attendance
        </a>
    </div>
</div>

<!-- KPI Metric Cards (Standard 4 per row | Tabular Figures) -->
<?php
$totalCount = (int)($summary['total_count'] ?? 0);
$presentCount = (int)($summary['present_count'] ?? 0);
$lateCount = (int)($summary['late_count'] ?? 0);
$absentCount = (int)($summary['absent_count'] ?? 0);
$rate = $totalCount > 0 ? round((($presentCount + $lateCount) / $totalCount) * 100, 1) : 0;
?>
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green"><?= number_format($presentCount) ?></div>
                <div class="stat-label">Present Total</div>
                <small class="text-muted" style="font-size: 0.75rem;">Recorded attendees</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-orange">
                <i class="bi bi-alarm-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-orange"><?= number_format($lateCount) ?></div>
                <div class="stat-label">Late Arrivals</div>
                <small class="text-muted" style="font-size: 0.75rem;">Delayed arrivals</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-person-x-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-coral"><?= number_format($absentCount) ?></div>
                <div class="stat-label">Absent</div>
                <small class="text-muted" style="font-size: 0.75rem;">Unexcused absences</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-sky-blue">
                <i class="bi bi-percent"></i>
            </div>
            <div>
                <div class="stat-value text-tp-sky-blue"><?= $rate ?>%</div>
                <div class="stat-label">Turnout Rate</div>
                <small class="text-muted" style="font-size: 0.75rem;">Overall attendance</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card p-3 mb-4">
    <form method="GET" action="<?= url('attendance/history') ?>" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label mb-1">Batch</label>
            <select name="batch_id" class="form-select">
                <option value="">All Batches</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $filters['batch_id'] == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="present" <?= $filters['status'] === 'present' ? 'selected' : '' ?>>Present</option>
                <option value="late" <?= $filters['status'] === 'late' ? 'selected' : '' ?>>Late</option>
                <option value="absent" <?= $filters['status'] === 'absent' ? 'selected' : '' ?>>Absent</option>
                <option value="excused" <?= $filters['status'] === 'excused' ? 'selected' : '' ?>>Excused</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1">From Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= e($filters['start_date']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1">To Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= e($filters['end_date']) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
            <a href="<?= url('attendance/history') ?>" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- History Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Batch</th>
                    <th>Status</th>
                    <th>Check-in Time</th>
                    <th>Notes / Audit</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-1 d-block mb-2 opacity-50"></i>
                            <h6>No attendance records match the selected filter.</h6>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td class="fw-bold font-monospace text-nowrap">
                                <i class="bi bi-calendar3 me-1 text-tp-green"></i>
                                <?= date('d M Y', strtotime($r['session_date'])) ?>
                            </td>
                            <td>
                                <div class="fw-bold text-tp-black"><?= e($r['student_name']) ?></div>
                                <small class="text-muted font-monospace"><?= e($r['admission_number']) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-dark"><?= e($r['batch_name']) ?></span>
                            </td>
                            <td>
                                <?php
                                $badgeClass = match($r['status']) {
                                    'present' => 'badge-success',
                                    'late'    => 'badge-warning',
                                    'absent'  => 'badge-danger',
                                    default   => 'badge-dark'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> text-capitalize"><?= e($r['status']) ?></span>
                            </td>
                            <td class="text-nowrap font-monospace small">
                                <?= $r['check_in_time'] ? date('h:i A', strtotime($r['check_in_time'])) : '-' ?>
                            </td>
                            <td class="small text-muted" style="max-width: 250px;">
                                <?= e($r['notes'] ?? '-') ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="openCorrectionModal(<?= htmlspecialchars(json_encode($r)) ?>)">
                                    <i class="bi bi-pencil-square me-1"></i> Correct
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Attendance Correction Modal -->
<div class="modal fade" id="correctionModal" tabindex="-1" aria-labelledby="correctionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-tp-black" id="correctionModalLabel">Correct Attendance Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('attendance/correct') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="correct_record_id">
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Student: <strong id="correct_student_name" class="text-tp-black"></strong><br>
                        Batch: <span id="correct_batch_name" class="text-tp-black"></span>
                    </p>

                    <div class="mb-3">
                        <label class="form-label">New Status <span class="text-danger">*</span></label>
                        <select name="status" id="correct_status" class="form-select" required>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                            <option value="excused">Excused</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Correction <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Approved medical certificate submitted, student arrived with parent note" required></textarea>
                        <small class="text-muted">A valid reason is required for administrative auditing.</small>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Correction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCorrectionModal(record) {
    document.getElementById('correct_record_id').value = record.id;
    document.getElementById('correct_student_name').textContent = record.student_name + ' (' + record.admission_number + ')';
    document.getElementById('correct_batch_name').textContent = record.batch_name + ' (' + record.session_date + ')';
    document.getElementById('correct_status').value = record.status;
    new bootstrap.Modal(document.getElementById('correctionModal')).show();
}
</script>
