<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Database Backup & Disaster Recovery</h2>
        <p class="text-muted mb-0">Generate, download, and manage full relational database SQL backups for offline safekeeping.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('settings') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Settings
        </a>
        <form action="<?= url('settings/backups/create') ?>" method="POST" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-dark fw-bold">
                <i class="bi bi-database-down me-1"></i> Create Backup Now
            </button>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-archive me-2 text-primary"></i>Stored Backup Archives</h5>
                <span class="badge bg-light text-dark border"><?= count($backups) ?> Archives</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Filename</th>
                                <th>File Size</th>
                                <th>Generated At</th>
                                <th class="text-center">Integrity</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($backups)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-database-x fs-1 d-block mb-2 opacity-50"></i>
                                        <h6>No offline backup archives generated yet.</h6>
                                        <p class="small mb-3">Click 'Create Backup Now' to produce an immediate snapshot of your database.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($backups as $b): ?>
                                    <tr>
                                        <td class="ps-3 font-monospace fw-bold text-primary">
                                            <i class="bi bi-file-earmark-code me-2"></i><?= e($b['filename']) ?>
                                        </td>
                                        <td><?= $b['size'] ?></td>
                                        <td><?= date('d M Y, h:i A', strtotime($b['date'])) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-shield-check me-1"></i> Verified
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-2">
                                                <a href="<?= url('settings/backups/download?file=' . urlencode($b['filename'])) ?>" class="btn btn-sm btn-outline-primary" title="Download Backup Archive">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                                <form action="<?= url('settings/backups/restore') ?>" method="POST" class="d-inline" onsubmit="return confirm('WARNING: Restoring will overwrite existing database records with the contents of <?= e($b['filename']) ?>. Do you want to proceed?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="filename" value="<?= e($b['filename']) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Restore this Backup">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                    </button>
                                                </form>
                                            </div>
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

    <!-- Recovery Notes -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-shield-shaded me-2 text-success"></i>Disaster Recovery Protocol</h6>
            </div>
            <div class="card-body p-3 small text-muted">
                <p>Every backup contains:</p>
                <ul class="ps-3 mb-3">
                    <li>Full DDL schema with foreign key constraints</li>
                    <li>Multi-tenant institute configuration</li>
                    <li>Financial journal lines and chart of accounts</li>
                    <li>Student attendance and exam marks</li>
                </ul>
                <div class="alert alert-warning py-2 px-3 small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i> To restore on XAMPP:
                    <div class="font-monospace small mt-1">mysql -u root tuitionpro &lt; backup.sql</div>
                </div>
            </div>
        </div>
    </div>
</div>

