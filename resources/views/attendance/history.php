<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Attendance Records & History</h2>
        <p class="text-muted mb-0">Search, filter, and audit attendance logs across all batches and sessions.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('attendance/qr-scanner') ?>" class="btn btn-dark">
            <i class="bi bi-qr-code-scan me-1"></i> QR Scanner
        </a>
        <a href="<?= url('attendance/take') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Mark Attendance
        </a>
    </div>
</div>

<!-- KPI Metric Cards -->
<?php
$totalCount = (int)($summary['total_count'] ?? 0);
$presentCount = (int)($summary['present_count'] ?? 0);
$lateCount = (int)($summary['late_count'] ?? 0);
$absentCount = (int)($summary['absent_count'] ?? 0);
$rate = $totalCount > 0 ? round((($presentCount + $lateCount) / $totalCount) * 100, 1) : 0;
?>
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3 fs-4">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Present Total</h6>
                    <h4 class="fw-bold mb-0 text-success"><?= $presentCount ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3 fs-4">
                    <i class="bi bi-alarm-fill"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Late Arrivals</h6>
                    <h4 class="fw-bold mb-0 text-warning"><?= $lateCount ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-danger-subtle text-danger p-3 me-3 fs-4">
                    <i class="bi bi-person-x-fill"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Absent</h6>
                    <h4 class="fw-bold mb-0 text-danger"><?= $absentCount ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-info-subtle text-info p-3 me-3 fs-4">
                    <i class="bi bi-percent"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Turnout Rate</h6>
                    <h4 class="fw-bold mb-0 text-info"><?= $rate ?>%</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('attendance/history') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Batch</label>
                <select name="batch_id" class="form-select form-select-sm">
                    <option value="">All Batches</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $filters['batch_id'] == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="present" <?= $filters['status'] === 'present' ? 'selected' : '' ?>>Present</option>
                    <option value="late" <?= $filters['status'] === 'late' ? 'selected' : '' ?>>Late</option>
                    <option value="absent" <?= $filters['status'] === 'absent' ? 'selected' : '' ?>>Absent</option>
                    <option value="excused" <?= $filters['status'] === 'excused' ? 'selected' : '' ?>>Excused</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">From Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($filters['start_date']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">To Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($filters['end_date']) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="<?= url('attendance/history') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- History Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Student</th>
                        <th>Batch</th>
                        <th>Status</th>
                        <th>Check-in Time</th>
                        <th>Notes / Audit</th>
                        <th class="text-end pe-3">Action</th>
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
                                <td class="ps-3 fw-bold text-nowrap">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i>
                                    <?= date('d M Y', strtotime($r['session_date'])) ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($r['student_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= e($r['admission_number']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($r['batch_name']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $badge = match($r['status']) {
                                        'present' => 'bg-success',
                                        'late'    => 'bg-warning text-dark',
                                        'absent'  => 'bg-danger',
                                        default   => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badge ?> text-capitalize"><?= e($r['status']) ?></span>
                                </td>
                                <td class="text-nowrap">
                                    <?= $r['check_in_time'] ? date('h:i A', strtotime($r['check_in_time'])) : '-' ?>
                                </td>
                                <td class="small text-muted" style="max-width: 250px;">
                                    <?= e($r['notes'] ?? '-') ?>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openCorrectionModal(<?= htmlspecialchars(json_encode($r)) ?>)">
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
</div>

<!-- Attendance Correction Modal -->
<div class="modal fade" id="correctionModal" tabindex="-1" aria-labelledby="correctionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="correctionModalLabel">Correct Attendance Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('attendance/correct') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="correct_record_id">
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Student: <strong id="correct_student_name" class="text-dark"></strong><br>
                        Batch: <span id="correct_batch_name" class="text-dark"></span>
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Status <span class="text-danger">*</span></label>
                        <select name="status" id="correct_status" class="form-select" required>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                            <option value="excused">Excused</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason for Correction <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Approved medical certificate submitted, student arrived with parent note" required></textarea>
                        <small class="text-muted">A valid reason is required for administrative auditing.</small>
                    </div>
                </div>
                <div class="modal-footer">
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

