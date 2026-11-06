<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Weekly Timetable</h2>
        <p class="text-muted mb-0">Master recurring schedule matrix with conflict detection and automated session generation.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('timetable/sessions') ?>" class="btn btn-outline-primary">
            <i class="bi bi-calendar2-check me-1"></i> View Date Sessions
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlotModal">
            <i class="bi bi-plus-circle me-1"></i> Add Schedule Slot
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 bg-light rounded d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-magic fs-4 text-primary"></i>
            <div>
                <strong class="d-block">Bulk Session Generator</strong>
                <small class="text-muted">Generate actual calendar session dates from active weekly recurring schedules.</small>
            </div>
        </div>
        <form action="<?= url('timetable/generate-sessions') ?>" method="POST" class="d-flex align-items-center gap-2">
            <?= csrf_field() ?>
            <input type="month" name="year_month" class="form-control form-control-sm" value="<?= date('Y-m') ?>" required>
            <button type="submit" class="btn btn-sm btn-dark text-nowrap">
                <i class="bi bi-gear-fill me-1"></i> Generate Month Sessions
            </button>
        </form>
    </div>
</div>

<!-- Weekly Grid Matrix -->
<div class="row g-3">
    <?php
    $dayNames = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        0 => 'Sunday'
    ];
    foreach ($dayNames as $dow => $dayName):
        $slots = $byDay[$dow] ?? [];
    ?>
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <span class="badge bg-primary me-2"><?= substr($dayName, 0, 3) ?></span><?= $dayName ?>
                </h5>
                <span class="badge bg-light text-muted border"><?= count($slots) ?> Slots</span>
            </div>
            <div class="card-body p-3">
                <?php if (empty($slots)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-x fs-3 d-block mb-1 opacity-50"></i>
                        <small>No recurring slots scheduled</small>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($slots as $slot): ?>
                            <div class="p-3 border rounded bg-light hover-shadow transition">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                        <i class="bi bi-clock me-1"></i><?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary small">
                                        <i class="bi bi-geo-alt me-1"></i><?= e($slot['classroom'] ?? 'Room 1') ?>
                                    </span>
                                </div>
                                <h6 class="fw-bold mb-1 text-primary"><?= e($slot['batch_name']) ?></h6>
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-book me-1"></i><?= e($slot['subject_name']) ?>
                                </div>
                                <div class="small text-muted d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span><i class="bi bi-person me-1"></i><?= e($slot['teacher_name']) ?></span>
                                    <span class="text-truncate" style="max-width: 110px;"><i class="bi bi-building me-1"></i><?= e($slot['branch_name']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Schedule Slot Modal -->
<div class="modal fade" id="addSlotModal" tabindex="-1" aria-labelledby="addSlotModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addSlotModalLabel">Add Recurring Schedule Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('timetable/schedule') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Choose Batch --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Teacher and Branch are automatically linked from batch settings.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Day of Week <span class="text-danger">*</span></label>
                        <select name="day_of_week" class="form-select" required>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                            <option value="0">Sunday</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Classroom / Hall</label>
                        <input type="text" name="classroom" class="form-control" placeholder="e.g. Hall A, Lab 2, Room 101">
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="bi bi-shield-check me-1"></i> The system automatically checks for teacher and classroom conflicts before scheduling.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Schedule Slot</button>
                </div>
            </form>
        </div>
    </div>
</div>

