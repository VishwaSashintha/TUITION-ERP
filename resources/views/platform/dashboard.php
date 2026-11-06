<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill text-primary me-2"></i>System Administration & Health Overview</h4>
        <p class="text-muted mb-0">Developer and operational control centre for the active installation.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('platform/tenants') ?>" class="btn btn-primary-custom">
            <i class="bi bi-person-gear"></i> Institute Admin Setup
        </a>
        <a href="<?= url('settings/backups') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-database-down"></i> Backups
        </a>
    </div>
</div>

<!-- ─── STAT CARDS ──────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-building"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size: 1.25rem;"><?= e($activeInstitute['name'] ?? 'Apex Education') ?></div>
                <div class="stat-label">Active Institute (Code: <span class="font-monospace fw-bold"><?= e($activeInstitute['code'] ?? 'apex') ?></span>)</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon accent">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($activeInstitute['student_count'] ?? 0) ?></div>
                <div class="stat-label">Enrolled Students</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format(($activeInstitute['user_count'] ?? 0) + ($activeInstitute['teacher_count'] ?? 0)) ?></div>
                <div class="stat-label">Total Staff & Teachers</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($activeTokens) ?></div>
                <div class="stat-label">Active JWT Refresh Tokens</div>
            </div>
        </div>
    </div>
</div>

<!-- ─── SYSTEM ENVIRONMENT & DATABASE STATUS ────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-cpu me-2 text-primary"></i>Runtime & Server Environment</h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle">Online</span>
            </div>
            <div class="p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-semibold" style="width: 40%;">PHP Version:</td>
                            <td class="fw-bold font-monospace"><?= e($systemInfo['php_version']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Database Engine:</td>
                            <td class="fw-bold font-monospace">MySQL <?= e($systemInfo['mysql_version']) ?> (InnoDB)</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Database Tables:</td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= $totalTables ?> Tables Active</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">JWT Authentication:</td>
                            <td><span class="badge bg-primary-subtle text-primary border font-monospace"><?= e($systemInfo['jwt_auth']) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Server OS:</td>
                            <td><?= e($systemInfo['server_os']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Memory Allocated / Peak:</td>
                            <td class="font-monospace"><?= e($systemInfo['memory_usage']) ?> / <?= e($systemInfo['memory_peak']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Storage Directory:</td>
                            <td>
                                <span class="badge bg-success-subtle text-success border">
                                    <i class="bi bi-check-circle me-1"></i><?= e($systemInfo['storage_status']) ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-gear-wide-connected me-2 text-primary"></i>Installation Parameters</h6>
                <a href="<?= url('platform/tenants') ?>" class="btn btn-sm btn-link p-0">Manage Admin & Profile &rarr;</a>
            </div>
            <div class="p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-semibold" style="width: 40%;">Institute Name:</td>
                            <td class="fw-bold"><?= e($activeInstitute['name'] ?? 'Apex Higher Education Institute') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">System Code:</td>
                            <td><span class="badge bg-light text-primary border font-monospace"><?= e($activeInstitute['code'] ?? 'apex') ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Official Email:</td>
                            <td><a href="mailto:<?= e($activeInstitute['email'] ?? '') ?>"><?= e($activeInstitute['email'] ?? 'N/A') ?></a></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Contact Telephone:</td>
                            <td><?= e($activeInstitute['phone'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Physical Address:</td>
                            <td class="small"><?= e($activeInstitute['address'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Active Branches:</td>
                            <td><span class="badge bg-light text-dark border"><?= $activeInstitute['branch_count'] ?? 1 ?> Branch</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Installation Status:</td>
                            <td><span class="badge bg-success text-capitalize"><?= e($activeInstitute['status'] ?? 'active') ?></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ─── RECENT SYSTEM AUDIT TRAIL ───────────────────────────────────────────── -->
<div class="card-custom">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Recent System Audit Trail</h6>
        <span class="badge bg-light text-muted border"><?= number_format($totalAudits) ?> Total Logged Events</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentAudits)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No audit events recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentAudits as $a): ?>
                        <tr>
                            <td class="text-muted small" style="white-space: nowrap;"><?= format_datetime($a['created_at']) ?></td>
                            <td class="fw-semibold"><?= e($a['user_name']) ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= e($a['action']) ?></span></td>
                            <td class="small text-muted font-monospace"><?= e($a['entity_type'] ?? '—') ?> #<?= e($a['entity_id'] ?? '—') ?></td>
                            <td class="small"><?= e($a['message'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
