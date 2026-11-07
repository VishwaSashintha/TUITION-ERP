<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">Teacher Academic Dashboard</h4>
            <span class="badge badge-tp-purple px-2 py-1">Faculty & Instruction</span>
        </div>
        <p class="text-muted mb-0">Welcome, <?= e($teacher['full_name'] ?? 'Instructor') ?>. Manage your assigned batches, student attendance, assignments, and exams.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('attendance') ?>" class="btn btn-primary-custom" style="background-color: var(--tp-purple); border-color: var(--tp-purple);">
            <i class="bi bi-calendar-check-fill"></i> Take Attendance
        </a>
        <a href="<?= url('academic/materials/create') ?>" class="btn btn-outline-dark">
            <i class="bi bi-file-earmark-plus"></i> Upload Study Material
        </a>
        <a href="<?= url('academic/assignments/create') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-pencil-square"></i> Create Assignment
        </a>
        <a href="<?= url('examinations/marks-entry') ?>" class="btn btn-outline-success" style="border-color: var(--tp-green); color: #2e7d32;">
            <i class="bi bi-journal-plus"></i> Enter Marks
        </a>
    </div>
</div>

<!-- ─── TEACHER KPI CARDS (Exact 6-Color Palette & Poppins 700) ───────────── -->
<div class="row g-3 mb-4">
    <!-- My Assigned Classes (Purple: #9985FB) -->
    <div class="col-xl-4 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-purple">
                <i class="bi bi-collection-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($myBatches) ?></div>
                <div class="stat-label">Assigned Batches</div>
                <small class="text-muted" style="font-size: 0.75rem;">Active courses assigned</small>
            </div>
        </div>
    </div>

    <!-- My Students (Sky Blue: #77DDFA) -->
    <div class="col-xl-4 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-sky-blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($myStudentCount) ?></div>
                <div class="stat-label">My Students</div>
                <small class="text-muted" style="font-size: 0.75rem;">Enrolled across my batches</small>
            </div>
        </div>
    </div>

    <!-- Today's Classes (Orange: #FEA339) -->
    <div class="col-xl-4 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-orange">
                <i class="bi bi-calendar3-event"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($todaySessions) ?></div>
                <div class="stat-label">Today's Sessions</div>
                <small class="text-muted" style="font-size: 0.75rem;">Lectures scheduled today</small>
            </div>
        </div>
    </div>

    <!-- Pending Attendance (Coral: #FF908D) -->
    <div class="col-xl-4 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="stat-value text-tp-coral"><?= count($pendingAttendance) ?></div>
                <div class="stat-label">Pending Attendance</div>
                <small class="text-muted" style="font-size: 0.75rem;">Sessions awaiting check-in</small>
            </div>
        </div>
    </div>

    <!-- Active Coursework Assignments (Green: #4BA95F) -->
    <div class="col-xl-4 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green"><?= count($assignments) ?></div>
                <div class="stat-label">Active Assignments</div>
                <small class="text-muted" style="font-size: 0.75rem;">Coursework published</small>
            </div>
        </div>
    </div>

    <!-- Upcoming Examinations (Coral: #FF908D) -->
    <div class="col-xl-4 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-journal-check"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($examinations) ?></div>
                <div class="stat-label">Examinations</div>
                <small class="text-muted" style="font-size: 0.75rem;">Scheduled assessments</small>
            </div>
        </div>
    </div>
</div>

<!-- ─── TODAY'S TIMETABLE & ATTENDANCE SHORTCUTS ──────────────────────────── -->
<div class="row g-3 mb-4">
    <!-- Today's Schedule -->
    <div class="col-lg-7">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-orange px-2 py-1"><i class="bi bi-calendar3"></i></span>
                    <h6 class="fw-bold mb-0">My Teaching Schedule Today</h6>
                </div>
                <a href="<?= url('timetable') ?>" class="btn btn-sm btn-link text-decoration-none">Full Timetable &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Batch & Subject</th>
                            <th>Hall / Room</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($todaySessions)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-check fs-3 d-block mb-1 text-muted"></i>
                                    No lectures scheduled for you today.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($todaySessions as $s): ?>
                                <tr>
                                    <td class="fw-number fw-semibold" style="font-size: 0.85rem;">
                                        <?= substr($s['start_time'], 0, 5) ?> - <?= substr($s['end_time'], 0, 5) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= e($s['batch_name']) ?></div>
                                        <small class="text-muted"><?= e($s['subject_name']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">Main Hall</span>
                                    </td>
                                    <td>
                                        <?php if ((int)$s['attendance_count'] > 0): ?>
                                            <span class="badge badge-tp-green"><i class="bi bi-check-circle me-1"></i>Marked</span>
                                        <?php else: ?>
                                            <a href="<?= url('attendance') ?>" class="btn btn-sm btn-primary-custom" style="font-size: 0.75rem; padding: 2px 8px; background-color: var(--tp-purple); border-color: var(--tp-purple);">
                                                Take Attendance
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Assigned Batches & Student Capacities -->
    <div class="col-lg-5">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-purple px-2 py-1"><i class="bi bi-collection-fill"></i></span>
                    <h6 class="fw-bold mb-0">My Assigned Batches</h6>
                </div>
                <a href="<?= url('academic/batches') ?>" class="btn btn-sm btn-link text-decoration-none">All Batches &rarr;</a>
            </div>
            <div class="p-3">
                <?php if (empty($myBatches)): ?>
                    <p class="text-muted text-center py-3 mb-0">No active batches assigned.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($myBatches as $b): ?>
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                                <div>
                                    <div class="fw-bold"><?= e($b['name']) ?></div>
                                    <small class="text-muted"><?= e($b['subject_name']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-tp-purple fw-number" style="font-size: 0.8rem;">
                                        <?= number_format((int)$b['enrolled_count']) ?> Students
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ─── ASSIGNMENTS, EXAMS & ANNOUNCEMENTS ───────────────────────────────── -->
<div class="row g-3">
    <!-- Active Coursework Assignments -->
    <div class="col-lg-4">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-green px-2 py-1"><i class="bi bi-pencil-square"></i></span>
                    <h6 class="fw-bold mb-0">Assignments</h6>
                </div>
                <a href="<?= url('academic/assignments') ?>" class="btn btn-sm btn-link text-decoration-none">Manage &rarr;</a>
            </div>
            <div class="p-3">
                <?php if (empty($assignments)): ?>
                    <p class="text-muted text-center py-3 mb-0">No active assignments created.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($assignments as $a): ?>
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-semibold text-truncate" style="max-width: 170px;"><?= e($a['title']) ?></div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Due: <?= format_date($a['due_date']) ?></small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted"><?= e($a['batch_name']) ?></small>
                                    <span class="badge badge-tp-sky-blue fw-number" style="font-size: 0.7rem;">
                                        <?= (int)$a['submission_count'] ?> submissions
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upcoming Examinations -->
    <div class="col-lg-4">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-coral px-2 py-1"><i class="bi bi-journal-text"></i></span>
                    <h6 class="fw-bold mb-0">Examinations</h6>
                </div>
                <a href="<?= url('examinations') ?>" class="btn btn-sm btn-link text-decoration-none">Exams &rarr;</a>
            </div>
            <div class="p-3">
                <?php if (empty($examinations)): ?>
                    <p class="text-muted text-center py-3 mb-0">No upcoming examinations.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($examinations as $ex): ?>
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-semibold text-truncate" style="max-width: 170px;"><?= e($ex['name']) ?></div>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?= format_date($ex['exam_date']) ?></small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted"><?= e($ex['batch_name']) ?></small>
                                    <a href="<?= url('examinations/marks-entry/' . $ex['id']) ?>" class="btn btn-sm btn-outline-dark" style="font-size: 0.7rem; padding: 1px 6px;">
                                        Marks
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Faculty Notices & Announcements -->
    <div class="col-lg-4">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-black px-2 py-1"><i class="bi bi-megaphone-fill"></i></span>
                    <h6 class="fw-bold mb-0">Faculty Notices</h6>
                </div>
            </div>
            <div class="p-3">
                <?php if (empty($announcements)): ?>
                    <p class="text-muted text-center py-3 mb-0">No announcements posted.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($announcements as $ann): ?>
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold text-truncate" style="max-width: 180px;"><?= e($ann['title']) ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;"><?= format_date($ann['published_at']) ?></small>
                                </div>
                                <p class="text-muted mb-0 text-truncate" style="font-size: 0.8rem;"><?= e(strip_tags($ann['content'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

