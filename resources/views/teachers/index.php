<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-tp-black">Teacher Directory</h4>
        <p class="text-muted mb-0">Manage teaching faculty, compensation agreements, and class allocations</p>
    </div>
    <a href="<?= url('teachers/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> Register Teacher
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Teacher ID</th>
                    <th>Full Name</th>
                    <th>Specialization</th>
                    <th>Phone & Email</th>
                    <th>Compensation Model</th>
                    <th>Batches</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-person-video3 fs-2 d-block mb-2"></i>
                            No teachers registered yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td>
                                <span class="badge badge-dark font-monospace fw-bold">
                                    <?= e($t['employee_id']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-tp-black"><?= e($t['full_name']) ?></div>
                                <small class="text-muted"><?= e($t['qualifications'] ?: 'No qualifications entered') ?></small>
                            </td>
                            <td>
                                <span class="badge badge-dark"><?= e($t['specialization'] ?: 'General') ?></span>
                            </td>
                            <td>
                                <div><a href="tel:<?= e($t['phone']) ?>" class="text-tp-green"><?= e($t['phone']) ?></a></div>
                                <small class="text-muted"><?= e($t['email']) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-info text-capitalize">
                                    <?= str_replace('_', ' ', $t['compensation_model']) ?>
                                    <?php if ($t['compensation_model'] === 'revenue_share'): ?>
                                        (<?= (float)$t['default_percentage'] ?>%)
                                    <?php elseif ($t['default_rate'] > 0): ?>
                                        (<?= format_currency($t['default_rate']) ?>)
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-purple fw-bold fw-number">
                                    <?= $t['active_batches_count'] ?> Batches
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= $t['status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($t['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url("teachers/{$t['id']}") ?>" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-eye me-1"></i> Profile
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
