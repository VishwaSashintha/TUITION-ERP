<?php $pageTitle = 'Student Performance Report'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Student Performance</li>
        </ol></nav>
        <h4 class="fw-bold mb-0"><i class="bi bi-award me-2" style="color:#9C27B0;"></i>Student Performance Report</h4>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i> Print
    </button>
</div>

<!-- Batch Selector Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Select Batch</label>
                <select name="batch_id" class="form-select" required>
                    <option value="">— Choose a Batch to View Rankings —</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn w-100" style="background:#9C27B0;color:white;">
                    <i class="bi bi-bar-chart-steps me-1"></i> View Performance
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($batchId <= 0): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-journal-check fs-1 text-muted d-block mb-3"></i>
            <h5 class="fw-bold text-muted">Select a Batch Above</h5>
            <p class="text-muted small mb-0">Choose an active academic batch to view comprehensive student examination rankings, GPA, and pass rates.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <span class="fw-semibold">Batch Performance Rankings</span>
            <span class="badge bg-primary"><?= count($rows) ?> Enrolled Candidates</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:70px;">Rank</th>
                        <th>Admission No.</th>
                        <th>Student Name</th>
                        <th class="text-center">Exams</th>
                        <th class="text-center">Average %</th>
                        <th class="text-center">Highest Score</th>
                        <th class="text-center">Lowest Score</th>
                        <th class="text-center">Pass / Fail</th>
                        <th class="text-center">Performance Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">No examination results recorded for this batch yet.</td></tr>
                    <?php else: ?>
                        <?php $rank = 1; foreach ($rows as $r): ?>
                            <?php
                                $avg = (float)$r['avg_percentage'];
                                $rating = $avg >= 75 ? 'Distinction' : ($avg >= 65 ? 'Very Good' : ($avg >= 50 ? 'Credit' : ($avg >= 35 ? 'Simple Pass' : 'Needs Support')));
                                $badgeClass = $avg >= 75 ? 'bg-success' : ($avg >= 65 ? 'bg-primary' : ($avg >= 50 ? 'bg-info' : ($avg >= 35 ? 'bg-warning text-dark' : 'bg-danger')));
                            ?>
                            <tr>
                                <td class="text-center fw-bold">
                                    <?php if ($rank === 1): ?>
                                        <span class="badge rounded-pill bg-warning text-dark px-2 py-1"><i class="bi bi-trophy-fill me-1"></i>#1</span>
                                    <?php elseif ($rank === 2): ?>
                                        <span class="badge rounded-pill bg-secondary text-white px-2 py-1">#2</span>
                                    <?php elseif ($rank === 3): ?>
                                        <span class="badge rounded-pill bg-dark text-white px-2 py-1">#3</span>
                                    <?php else: ?>
                                        <span class="text-muted">#<?= $rank ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= htmlspecialchars($r['admission_number']) ?></code></td>
                                <td class="fw-semibold"><?= htmlspecialchars($r['full_name']) ?></td>
                                <td class="text-center"><?= (int)$r['exams_taken'] ?></td>
                                <td class="text-center fw-bold fs-6 <?= $avg >= 50 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($avg, 1) ?>%
                                </td>
                                <td class="text-center text-success fw-semibold"><?= number_format((float)$r['best_score'], 1) ?>%</td>
                                <td class="text-center text-muted"><?= number_format((float)$r['worst_score'], 1) ?>%</td>
                                <td class="text-center small">
                                    <span class="text-success fw-semibold"><?= (int)$r['pass_count'] ?>P</span> /
                                    <span class="text-danger fw-semibold"><?= (int)$r['fail_count'] ?>F</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $badgeClass ?>"><?= $rating ?></span>
                                </td>
                            </tr>
                        <?php $rank++; endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php $content = ob_get_clean(); ?>
<?php require_once VIEW_PATH . '/layouts/app.php'; ?>

