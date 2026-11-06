<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Classes & Batches</h4>
        <p class="text-muted mb-0">Active tuition classes, seat capacity limits, and teacher assignments</p>
    </div>
    <a href="<?= url('academic/batches/create') ?>" class="btn btn-primary-custom">
        <i class="bi bi-plus-circle me-1"></i> Create Batch
    </a>
</div>

<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Batch Name & Subject</th>
                    <th>Teacher</th>
                    <th>Monthly Fee</th>
                    <th>Seat Capacity</th>
                    <th>Branch</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No classes or batches registered yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($batches as $b): ?>
                        <?php 
                        $enrolled = (int)$b['enrolled_count'];
                        $capacity = (int)$b['max_capacity'];
                        $pct = $capacity > 0 ? min(100, round(($enrolled / $capacity) * 100)) : 0;
                        ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace fw-bold"><?= e($b['code']) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($b['name']) ?></div>
                                <small class="text-muted"><?= e($b['subject_name']) ?> &bull; <?= e($b['grade_level'] ?: 'A/L') ?></small>
                            </td>
                            <td><?= e($b['teacher_name']) ?></td>
                            <td class="fw-bold text-primary"><?= format_currency($b['monthly_fee']) ?></td>
                            <td style="min-width: 140px;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-bold" style="font-size: 0.75rem;"><?= $enrolled ?> / <?= $capacity ?> Seats</small>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?= $pct ?>%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?= $pct >= 90 ? 'danger' : ($pct >= 75 ? 'warning' : 'primary') ?>" style="width: <?= $pct ?>%;"></div>
                                </div>
                            </td>
                            <td><?= e($b['branch_name'] ?? 'Main') ?></td>
                            <td class="text-end">
                                <a href="<?= url("academic/batches/{$b['id']}") ?>" class="btn btn-sm btn-light border">
                                    <i class="bi bi-people me-1"></i> Roster
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

