<?php $pageTitle = 'Attendance Report'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Attendance</li>
        </ol></nav>
        <h4 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>Attendance Report</h4>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i> Print
    </button>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Month</label>
                <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Batch</label>
                <select name="batch_id" class="form-select">
                    <option value="0">All Batches</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="0">All Branches</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $branchId == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-primary"><?= number_format($totalSessions) ?></div>
                <div class="text-muted small">Total Session Records</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-success"><?= number_format($totalPresent) ?></div>
                <div class="text-muted small">Present Count</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold <?= $overallPct >= 75 ? 'text-success' : ($overallPct >= 50 ? 'text-warning' : 'text-danger') ?>">
                    <?= $overallPct ?>%
                </div>
                <div class="text-muted small">Overall Attendance</div>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <span class="fw-semibold">Student Attendance — <?= date('F Y', strtotime($startDate)) ?></span>
        <span class="badge bg-primary"><?= count($rows) ?> Students</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Admission No.</th>
                    <th>Student Name</th>
                    <th class="text-center">Sessions</th>
                    <th class="text-center text-success">Present</th>
                    <th class="text-center text-danger">Absent</th>
                    <th class="text-center text-warning">Late</th>
                    <th class="text-center">Attendance %</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No attendance records found for the selected period.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <?php
                            $pct = (float)$r['attendance_pct'];
                            $pctClass = $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                            $badge = $pct >= 75 ? 'bg-success' : ($pct >= 50 ? 'bg-warning text-dark' : 'bg-danger');
                        ?>
                        <tr>
                            <td><code><?= htmlspecialchars($r['admission_number']) ?></code></td>
                            <td class="fw-semibold"><?= htmlspecialchars($r['full_name']) ?></td>
                            <td class="text-center"><?= (int)$r['total_sessions'] ?></td>
                            <td class="text-center text-success fw-semibold"><?= (int)$r['present_count'] ?></td>
                            <td class="text-center text-danger"><?= (int)$r['absent_count'] ?></td>
                            <td class="text-center text-warning"><?= (int)$r['late_count'] ?></td>
                            <td class="text-center">
                                <div class="progress" style="height:8px; width:80px; display:inline-flex; vertical-align:middle; margin-right:6px;">
                                    <div class="progress-bar bg-<?= $pctClass ?>" style="width:<?= min(100, $pct) ?>%"></div>
                                </div>
                                <span class="fw-semibold text-<?= $pctClass ?>"><?= $pct ?>%</span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $badge ?>">
                                    <?= $pct >= 75 ? 'Good' : ($pct >= 50 ? 'At Risk' : 'Critical') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require_once VIEW_PATH . '/layouts/app.php'; ?>

