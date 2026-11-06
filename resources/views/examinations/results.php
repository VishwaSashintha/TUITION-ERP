<div class="row align-items-center mb-4 d-print-none">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Results & Rankings — <?= e($exam['name']) ?></h2>
        <p class="text-muted mb-0">Class evaluation summary, grade distributions, and student ranking merit list.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <?php if ($exam['status'] !== 'published'): ?>
            <form action="<?= url('examinations/publish') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $exam['id'] ?>">
                <button type="submit" class="btn btn-success fw-bold">
                    <i class="bi bi-send-check me-1"></i> Publish to Portals
                </button>
            </form>
        <?php else: ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 d-flex align-items-center">
                <i class="bi bi-check-all fs-5 me-1"></i> Published to Student & Parent Portals
            </span>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Sheet
        </button>
        <a href="<?= url('examinations') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Exams
        </a>
    </div>
</div>

<!-- Performance Summary Cards -->
<?php
$totalCands = (int)($summary['total_candidates'] ?? 0);
$presentCount = (int)($summary['present_count'] ?? 0);
$passCount = (int)($summary['pass_count'] ?? 0);
$passRate = $presentCount > 0 ? round(($passCount / $presentCount) * 100, 1) : 0;
$avgMarks = round((float)($summary['average_marks'] ?? 0), 1);
$highMarks = round((float)($summary['highest_marks'] ?? 0), 1);
?>
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3 fs-4">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Total Candidates</h6>
                    <h4 class="fw-bold mb-0"><?= $totalCands ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-success-subtle text-success p-3 me-3 fs-4">
                    <i class="bi bi-percent"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Class Pass Rate</h6>
                    <h4 class="fw-bold mb-0 text-success"><?= $passRate ?>%</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-info-subtle text-info p-3 me-3 fs-4">
                    <i class="bi bi-calculator"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Class Average</h6>
                    <h4 class="fw-bold mb-0 text-info"><?= $avgMarks ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3 fs-4">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Top Score</h6>
                    <h4 class="fw-bold mb-0 text-warning"><?= $highMarks ?> / <?= (int)$exam['max_marks'] ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grade Distribution Pills -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <strong class="text-dark small"><i class="bi bi-pie-chart-fill me-1 text-primary"></i> Grade Breakdown:</strong>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-success fs-6 px-3 py-2">A (75%+): <strong><?= (int)($summary['grade_a'] ?? 0) ?></strong></span>
            <span class="badge bg-primary fs-6 px-3 py-2">B (65-74%): <strong><?= (int)($summary['grade_b'] ?? 0) ?></strong></span>
            <span class="badge bg-info fs-6 px-3 py-2">C (55-64%): <strong><?= (int)($summary['grade_c'] ?? 0) ?></strong></span>
            <span class="badge bg-warning text-dark fs-6 px-3 py-2">S (40-54%): <strong><?= (int)($summary['grade_s'] ?? 0) ?></strong></span>
            <span class="badge bg-danger fs-6 px-3 py-2">F (<40%): <strong><?= (int)($summary['grade_f'] ?? 0) ?></strong></span>
        </div>
    </div>
</div>

<!-- Results & Merit List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Official Class Merit Sheet</h5>
        <a href="<?= url('examinations/marks-entry?id=' . $exam['id']) ?>" class="btn btn-sm btn-outline-primary d-print-none">
            <i class="bi bi-pencil me-1"></i> Edit Marks
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-center" style="width: 80px;">Rank</th>
                        <th>Student</th>
                        <th>Admission No</th>
                        <th class="text-center">Score</th>
                        <th class="text-center">Percentage</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No marks have been entered for this examination yet.
                                <div class="mt-2">
                                    <a href="<?= url('examinations/marks-entry?id=' . $exam['id']) ?>" class="btn btn-sm btn-primary">Enter Marks Now</a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td class="ps-3 text-center">
                                    <?php if ($r['is_absent']): ?>
                                        <span class="text-muted">-</span>
                                    <?php elseif ($r['rank_in_batch'] == 1): ?>
                                        <span class="badge bg-warning text-dark px-2 py-1 fs-6"><i class="bi bi-trophy-fill me-1"></i>#1</span>
                                    <?php elseif ($r['rank_in_batch'] == 2): ?>
                                        <span class="badge bg-secondary-subtle text-dark border px-2 py-1 fs-6">#2</span>
                                    <?php elseif ($r['rank_in_batch'] == 3): ?>
                                        <span class="badge bg-warning-subtle text-dark border px-2 py-1 fs-6">#3</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">#<?= $r['rank_in_batch'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark"><?= e($r['student_name']) ?></td>
                                <td><span class="badge bg-light text-dark font-monospace border"><?= e($r['admission_number']) ?></span></td>
                                <td class="text-center font-monospace fw-bold">
                                    <?= $r['is_absent'] ? '<span class="text-danger">ABSENT</span>' : number_format((float)$r['marks_obtained'], 1) ?>
                                </td>
                                <td class="text-center font-monospace">
                                    <?= $r['is_absent'] ? '-' : number_format((float)$r['percentage'], 1) . '%' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['is_absent']): ?>
                                        <span class="badge bg-danger">AB</span>
                                    <?php else: ?>
                                        <span class="badge <?= $r['grade'] === 'A' ? 'bg-success' : ($r['grade'] === 'F' ? 'bg-danger' : 'bg-primary') ?> fs-6 font-monospace">
                                            <?= e($r['grade']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $statBadge = match($r['status']) {
                                        'pass'   => 'bg-success',
                                        'fail'   => 'bg-danger',
                                        default  => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statBadge ?> text-capitalize"><?= $r['status'] ?></span>
                                </td>
                                <td class="pe-3 small text-muted"><?= e($r['remarks'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

