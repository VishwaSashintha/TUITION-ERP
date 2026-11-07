<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">System Administration & Health Overview</h4>
            <span class="badge badge-tp-purple px-2 py-1">Super Administrator</span>
        </div>
        <p class="text-muted mb-0">Platform-wide control centre: infrastructure health, tenant instances, security tokens, and system audit logs.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('platform/tenants') ?>" class="btn btn-primary-custom" style="background-color: var(--tp-purple); border-color: var(--tp-purple);">
            <i class="bi bi-person-gear"></i> Institute Admin Setup
        </a>
        <a href="<?= url('settings/backups') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-database-down"></i> Backups
        </a>
    </div>
</div>

<!-- ─── PLATFORM-WIDE KPI CARDS (Exact 6-Color Palette & Poppins 700) ─────── -->
<div class="row g-3 mb-4">
    <!-- Total Institutes (Purple: #9985FB) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-purple">
                <i class="bi bi-building"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($totalTenants ?? 1) ?></div>
                <div class="stat-label">Total Institutes</div>
                <small class="text-muted" style="font-size: 0.75rem;"><?= number_format($activeTenants ?? 1) ?> active deployments</small>
            </div>
        </div>
    </div>

    <!-- Total Students Across Institutes (Green: #4BA95F) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green"><?= number_format($totalAllStudents ?? 0) ?></div>
                <div class="stat-label">Platform Students</div>
                <small class="text-muted" style="font-size: 0.75rem;">Enrolled across instances</small>
            </div>
        </div>
    </div>

    <!-- Total Platform Staff & Users (Sky Blue: #77DDFA) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-sky-blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($totalPlatformUsers ?? 0) ?></div>
                <div class="stat-label">Registered Users</div>
                <small class="text-muted" style="font-size: 0.75rem;"><?= number_format($totalAllTeachers ?? 0) ?> teachers provisioned</small>
            </div>
        </div>
    </div>

    <!-- Active JWT Refresh Tokens (Coral: #FF908D) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-coral"><?= number_format($activeTokens) ?></div>
                <div class="stat-label">Active JWT Sessions</div>
                <small class="text-muted" style="font-size: 0.75rem;">Persisted refresh tokens</small>
            </div>
        </div>
    </div>
</div>

<!-- ─── SYSTEM ENVIRONMENT & DATABASE STATUS ────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-green px-2 py-1"><i class="bi bi-cpu"></i></span>
                    <h6 class="fw-bold mb-0">Runtime & Server Environment</h6>
                </div>
                <span class="badge badge-tp-green">System Online</span>
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
                            <td class="text-muted fw-semibold">Database Schema:</td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= $totalTables ?> Tables Active</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">JWT Authentication:</td>
                            <td><span class="badge badge-tp-purple font-monospace"><?= e($systemInfo['jwt_auth']) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Server OS:</td>
                            <td><?= e($systemInfo['server_os']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Memory Usage / Peak:</td>
                            <td class="font-monospace fw-number"><?= e($systemInfo['memory_usage']) ?> / <?= e($systemInfo['memory_peak']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Storage Directory:</td>
                            <td>
                                <span class="badge badge-tp-green">
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
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-purple px-2 py-1"><i class="bi bi-gear-wide-connected"></i></span>
                    <h6 class="fw-bold mb-0">Active Installation Profile</h6>
                </div>
                <a href="<?= url('platform/tenants') ?>" class="btn btn-sm btn-link text-decoration-none">Manage Admin &rarr;</a>
            </div>
            <div class="p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-semibold" style="width: 40%;">Institute Name:</td>
                            <td class="fw-bold"><?= e($activeInstitute['name'] ?? 'Apex Higher Education Institute') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Installation Code:</td>
                            <td><span class="badge bg-light text-primary border font-monospace"><?= e($activeInstitute['code'] ?? 'apex') ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Admin Email:</td>
                            <td><a href="mailto:<?= e($activeInstitute['email'] ?? '') ?>"><?= e($activeInstitute['email'] ?? 'N/A') ?></a></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Telephone:</td>
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
                            <td><span class="badge badge-tp-green text-capitalize"><?= e($activeInstitute['status'] ?? 'active') ?></span></td>
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
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-tp-black px-2 py-1"><i class="bi bi-journal-text"></i></span>
            <h6 class="fw-bold mb-0">Platform Audit Trail</h6>
        </div>
        <span class="badge bg-light text-muted border"><?= number_format($totalAudits) ?> Total Events</span>
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
                            <td><span class="badge badge-tp-black font-monospace"><?= e($a['action']) ?></span></td>
                            <td class="small text-muted font-monospace"><?= e($a['entity_type'] ?? '—') ?> #<?= e($a['entity_id'] ?? '—') ?></td>
                            <td class="small"><?= e($a['message'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
