<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-tp-black">Front-Desk Reception Dashboard</h4>
            <span class="badge badge-info px-2 py-1">Student Services & Admissions</span>
        </div>
        <p class="text-muted mb-0">Daily admissions, fast QR attendance check-in, fee receipting, and student follow-ups.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('students/create') ?>" class="btn btn-primary">
            <i class="bi bi-person-plus-fill"></i> Register Student
        </a>
        <a href="<?= url('attendance/qr-scanner') ?>" class="btn btn-secondary">
            <i class="bi bi-qr-code-scan"></i> QR Fast Scanner
        </a>
        <a href="<?= url('fees/invoices/create') ?>" class="btn btn-secondary">
            <i class="bi bi-receipt"></i> Issue Invoice
        </a>
    </div>
</div>

<!-- ─── QUICK ACTION BAR ─────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-2 col-md-4 col-6">
        <a href="<?= url('students/enquiries') ?>" class="quick-action-card">
            <div class="quick-action-icon tp-sky-blue" style="background: var(--info-light);">
                <i class="bi bi-chat-left-dots-fill text-tp-sky-blue"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 0.85rem;">Enquiries</div>
                <small class="text-muted" style="font-size: 0.725rem;"><?= number_format((int)($enquiryStats['followups_due_today'] ?? 0)) ?> due today</small>
            </div>
        </a>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <a href="<?= url('students/create') ?>" class="quick-action-card">
            <div class="quick-action-icon tp-purple" style="background: var(--purple-light);">
                <i class="bi bi-person-plus-fill text-tp-purple"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 0.85rem;">Register Student</div>
                <small class="text-muted" style="font-size: 0.725rem;">New admission</small>
            </div>
        </a>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <a href="<?= url('attendance/take') ?>" class="quick-action-card">
            <div class="quick-action-icon tp-green" style="background: var(--success-light);">
                <i class="bi bi-calendar-check-fill text-tp-green"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 0.85rem;">Attendance</div>
                <small class="text-muted" style="font-size: 0.725rem;">Manual roll call</small>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <a href="<?= url('fees/invoices') ?>" class="quick-action-card">
            <div class="quick-action-icon tp-orange" style="background: var(--accent-light);">
                <i class="bi bi-cash-stack text-tp-orange"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 0.85rem;">Record Payment</div>
                <small class="text-muted" style="font-size: 0.725rem;">Print official receipt</small>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <a href="<?= url('timetable') ?>" class="quick-action-card">
            <div class="quick-action-icon tp-coral" style="background: var(--danger-light);">
                <i class="bi bi-clock-history text-tp-coral"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 0.85rem;">Class Timetable</div>
                <small class="text-muted" style="font-size: 0.725rem;">Today's hall allocations</small>
            </div>
        </a>
    </div>
</div>

<!-- ─── RECEPTIONIST KPI STAT CARDS (4 per row | Tabular Figures) ────────── -->
<div class="row g-3 mb-4">
    <!-- Total Active Students (Purple) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-purple">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($studentCount) ?></div>
                <div class="stat-label">Active Students</div>
                <small class="text-muted" style="font-size: 0.75rem;">Enrolled directory</small>
            </div>
        </div>
    </div>

    <!-- Today's New Registrations (Sky Blue) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-sky-blue">
                <i class="bi bi-person-badge"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($todayAdmissionsCount) ?></div>
                <div class="stat-label">Today's Admissions</div>
                <small class="text-muted" style="font-size: 0.75rem;">Registered today</small>
            </div>
        </div>
    </div>

    <!-- Today's Attendance Check-ins (Green) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green"><?= number_format((int)($attendanceStats['present_count'] ?? 0)) ?></div>
                <div class="stat-label">Present Today</div>
                <small class="text-muted" style="font-size: 0.75rem;"><?= number_format($attendanceRate, 1) ?>% attendance rate</small>
            </div>
        </div>
    </div>

    <!-- Today's Front-Desk Collections (Green) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green"><?= format_currency($todayCollections) ?></div>
                <div class="stat-label">Today's Collections</div>
                <small class="text-muted" style="font-size: 0.75rem;">Collected at counter</small>
            </div>
        </div>
    </div>

    <!-- Today's Scheduled Classes (Orange) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-orange">
                <i class="bi bi-calendar3-event"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($todaySessions) ?></div>
                <div class="stat-label">Classes Today</div>
                <small class="text-muted" style="font-size: 0.75rem;">Scheduled sessions</small>
            </div>
        </div>
    </div>

    <!-- Pending Invoices Count (Coral) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div>
                <div class="stat-value text-tp-coral"><?= number_format((int)($pendingFees['pending_count'] ?? 0)) ?></div>
                <div class="stat-label">Pending Invoices</div>
                <small class="text-muted" style="font-size: 0.75rem;">Awaiting settlement</small>
            </div>
        </div>
    </div>

    <!-- Pending Amount for Collection (Coral) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-coral"><?= format_currency($pendingFees['pending_total'] ?? 0) ?></div>
                <div class="stat-label">Unsettled Balance</div>
                <small class="text-muted" style="font-size: 0.75rem;">Eligible for collection</small>
            </div>
        </div>
    </div>

    <!-- Students Requiring Follow-up (Charcoal) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-black">
                <i class="bi bi-telephone-outbound-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($followUpStudents) ?></div>
                <div class="stat-label">Follow-up List</div>
                <small class="text-muted" style="font-size: 0.75rem;">Overdue payment alerts</small>
            </div>
        </div>
    </div>
</div>

<!-- ─── RECEPTIONIST CHARTS (Attendance Doughnut & Notices) ──────────────── -->
<div class="row g-3 mb-4">
    <!-- Today's Attendance Breakdown -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-green px-2 py-1"><i class="bi bi-pie-chart-fill"></i></span>
                    <h6 class="fw-bold mb-0">Today's Check-in Status</h6>
                </div>
                <a href="<?= url('attendance') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-green">Roll Call &rarr;</a>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="width: 170px; height: 170px;">
                    <canvas id="receptionAttendanceChart"></canvas>
                </div>
                <div class="d-flex justify-content-around w-100 mt-3 pt-2 border-top text-center" style="font-size: 0.8rem;">
                    <div>
                        <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: var(--tp-green);"></span>
                        <div class="fw-bold fw-number"><?= (int)($attendanceStats['present_count'] ?? 0) ?></div>
                        <small class="text-muted">Present</small>
                    </div>
                    <div>
                        <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: var(--tp-orange);"></span>
                        <div class="fw-bold fw-number"><?= (int)($attendanceStats['late_count'] ?? 0) ?></div>
                        <small class="text-muted">Late</small>
                    </div>
                    <div>
                        <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: var(--tp-coral);"></span>
                        <div class="fw-bold fw-number"><?= (int)($attendanceStats['absent_count'] ?? 0) ?></div>
                        <small class="text-muted">Absent</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Important Front-Desk Announcements & Notices -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-purple px-2 py-1"><i class="bi bi-megaphone-fill"></i></span>
                    <h6 class="fw-bold mb-0">Front-Desk Announcements & Notices</h6>
                </div>
                <a href="<?= url('communication/announcements') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-green">View All &rarr;</a>
            </div>
            <div class="card-body">
                <?php if (empty($announcements)): ?>
                    <p class="text-muted text-center py-4 mb-0">No active announcements posted.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($announcements as $ann): ?>
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="fw-bold text-truncate text-tp-black" style="max-width: 380px;"><?= e($ann['title']) ?></div>
                                    <span class="badge badge-purple" style="font-size: 0.7rem;"><?= format_date($ann['published_at']) ?></span>
                                </div>
                                <p class="text-muted mb-0 text-truncate" style="font-size: 0.825rem;"><?= e(strip_tags($ann['content'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ─── TODAY'S SCHEDULE & RECENT RECEIPTS ───────────────────────────────── -->
<div class="row g-3 mb-4">
    <!-- Today's Class Sessions -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-orange px-2 py-1"><i class="bi bi-calendar3"></i></span>
                    <h6 class="fw-bold mb-0">Today's Class Schedule</h6>
                </div>
                <a href="<?= url('timetable') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-green">Timetable &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Batch & Subject</th>
                            <th>Teacher</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($todaySessions)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-check fs-3 d-block mb-1 text-muted"></i>
                                    No classes scheduled for today.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($todaySessions as $s): ?>
                                <tr>
                                    <td class="fw-number fw-semibold" style="font-size: 0.85rem;">
                                        <?= substr($s['start_time'], 0, 5) ?> - <?= substr($s['end_time'], 0, 5) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-tp-black"><?= e($s['batch_name']) ?></div>
                                        <small class="text-muted"><?= e($s['subject_name']) ?></small>
                                    </td>
                                    <td><?= e($s['teacher_name']) ?></td>
                                    <td>
                                        <a href="<?= url('attendance') ?>" class="btn btn-sm btn-secondary" style="font-size: 0.75rem; padding: 2px 8px;">
                                            Attendance
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

    <!-- Today's Payment Collections (Front-Desk) -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-green px-2 py-1"><i class="bi bi-cash-stack"></i></span>
                    <h6 class="fw-bold mb-0">Recent Counter Collections</h6>
                </div>
                <a href="<?= url('fees/invoices') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-green">Invoices &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Student</th>
                            <th>Amount</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPayments)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-1 text-muted"></i>
                                    No collections recorded today.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentPayments as $p): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-dark"><?= e($p['receipt_number']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-tp-black"><?= e($p['student_name']) ?></div>
                                        <small class="text-muted"><?= e($p['admission_number']) ?></small>
                                    </td>
                                    <td class="fw-number fw-bold text-tp-green">
                                        <?= format_currency($p['amount']) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-dark text-uppercase" style="font-size: 0.7rem;">
                                            <?= e($p['payment_method']) ?>
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

<!-- ─── RECENT ADMISSIONS & FOLLOW-UP LIST ────────────────────────────────── -->
<div class="row g-3">
    <!-- Recent Student Admissions -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-purple px-2 py-1"><i class="bi bi-person-lines-fill"></i></span>
                    <h6 class="fw-bold mb-0">Recent Front-Desk Admissions</h6>
                </div>
                <a href="<?= url('students') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-green">Student Directory &rarr;</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentAdmissions)): ?>
                    <p class="text-muted text-center py-3 mb-0">No new student admissions found.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentAdmissions as $adm): ?>
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                                <div>
                                    <div class="fw-semibold text-tp-black"><?= e($adm['full_name']) ?></div>
                                    <small class="text-muted"><?= e($adm['admission_number']) ?> &bull; Phone: <?= e($adm['phone'] ?? '—') ?></small>
                                </div>
                                <a href="<?= url('students/' . $adm['id']) ?>" class="btn btn-sm btn-secondary" style="font-size: 0.75rem; padding: 2px 8px;">
                                    Profile
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Students Requiring Payment Follow-up -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-coral px-2 py-1"><i class="bi bi-telephone-fill"></i></span>
                    <h6 class="fw-bold mb-0">Fee Collection Follow-ups</h6>
                </div>
                <a href="<?= url('fees/outstanding') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-coral">All Overdue &rarr;</a>
            </div>
            <div class="card-body">
                <?php if (empty($followUpStudents)): ?>
                    <p class="text-muted text-center py-3 mb-0">No overdue fee balances pending follow-up.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($followUpStudents as $fu): ?>
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                                <div>
                                    <div class="fw-semibold text-truncate text-tp-black" style="max-width: 240px;"><?= e($fu['student_name']) ?></div>
                                    <small class="text-muted">
                                        <i class="bi bi-telephone me-1"></i><?= e($fu['phone'] ?: 'No Phone') ?> &bull; <?= e($fu['invoice_number']) ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-number fw-bold text-tp-coral"><?= format_currency($fu['balance_amount']) ?></div>
                                    <a href="<?= url('fees/invoices') ?>" class="btn btn-sm btn-secondary mt-1" style="font-size: 0.7rem; padding: 2px 8px;">
                                        Collect
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ─── Chart.js Execution (Receptionist) ─────────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('receptionAttendanceChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Late', 'Absent'],
                datasets: [{
                    data: [
                        <?= (int)($attendanceStats['present_count'] ?? 0) ?>,
                        <?= (int)($attendanceStats['late_count'] ?? 0) ?>,
                        <?= (int)($attendanceStats['absent_count'] ?? 0) ?>
                    ],
                    backgroundColor: [
                        '#4BA95F', // Green
                        '#FEA339', // Orange
                        '#FF908D'  // Coral
                    ],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
