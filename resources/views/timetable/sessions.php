<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Class Sessions</h2>
        <p class="text-muted mb-0">Daily scheduled class sessions with attendance link and status tracking.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('timetable') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Weekly Timetable
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('timetable/sessions') ?>" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="fw-bold small mb-0"><i class="bi bi-calendar3 me-1"></i> Select Date:</label>
            </div>
            <div class="col-auto">
                <input type="date" name="date" class="form-control form-control-sm" value="<?= e($date) ?>" onchange="this.form.submit()">
            </div>
            <div class="col-auto">
                <a href="<?= url('timetable/sessions?date=' . date('Y-m-d')) ?>" class="btn btn-sm btn-outline-primary">Today</a>
            </div>
            <div class="col-auto ms-auto text-muted small">
                Showing sessions for <strong><?= date('l, d F Y', strtotime($date)) ?></strong>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Time</th>
                        <th>Batch / Subject</th>
                        <th>Teacher</th>
                        <th>Classroom</th>
                        <th>Status</th>
                        <th>Attendance</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                                <h6>No class sessions scheduled for this date.</h6>
                                <p class="small mb-3">You can generate sessions from weekly templates or pick another date.</p>
                                <a href="<?= url('timetable') ?>" class="btn btn-sm btn-primary">Go to Timetable Generator</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $s): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-nowrap">
                                    <i class="bi bi-clock me-1 text-primary"></i>
                                    <?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($s['batch_name']) ?></div>
                                    <small class="text-muted"><i class="bi bi-book me-1"></i><?= e($s['subject_name']) ?></small>
                                </td>
                                <td>
                                    <i class="bi bi-person me-1 text-muted"></i><?= e($s['teacher_name']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($s['classroom'] ?? 'Standard Hall') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $statusBadge = match($s['status']) {
                                        'completed' => 'bg-success',
                                        'in_progress' => 'bg-warning text-dark',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-primary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusBadge ?> text-capitalize"><?= e($s['status']) ?></span>
                                </td>
                                <td>
                                    <?php if ($s['attendance_count'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle-fill me-1"></i> <?= $s['attendance_count'] ?> Marked
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= url('attendance/take?session_id=' . $s['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-clipboard-check me-1"></i> Mark Attendance
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

