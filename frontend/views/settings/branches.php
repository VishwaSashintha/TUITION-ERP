<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Institute Branches</h2>
        <p class="text-muted mb-0">Multi-branch management with branch-specific attendance, rooms, and academic isolation.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('settings') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Settings
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBranchModal">
            <i class="bi bi-plus-circle me-1"></i> Add Branch
        </button>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($branches as $br): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-light text-primary border font-monospace mb-2"><?= e($br['code']) ?></span>
                            <h5 class="fw-bold mb-0 text-dark"><?= e($br['name']) ?></h5>
                        </div>
                        <?php if (!empty($br['is_main'])): ?>
                            <span class="badge bg-primary px-3 py-1">Head Office</span>
                        <?php endif; ?>
                    </div>

                    <div class="small text-muted mb-3">
                        <i class="bi bi-geo-alt me-1 text-primary"></i> <?= e($br['address'] ?? 'Sri Lanka') ?><br>
                        <i class="bi bi-telephone me-1 text-primary"></i> <?= e($br['phone'] ?? 'N/A') ?><br>
                        <i class="bi bi-envelope me-1 text-primary"></i> <?= e($br['email'] ?? 'N/A') ?>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top small">
                        <div>
                            <span class="text-muted d-block">Students</span>
                            <strong class="fs-6"><?= $br['student_count'] ?></strong>
                        </div>
                        <div class="text-end">
                            <span class="text-muted d-block">Active Batches</span>
                            <strong class="fs-6"><?= $br['batch_count'] ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Add Branch Modal -->
<div class="modal fade" id="newBranchModal" tabindex="-1" aria-labelledby="newBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="newBranchModalLabel"><i class="bi bi-building me-2 text-primary"></i>Add Institute Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('settings/branches') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Kandy Branch" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Branch Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="e.g. KDY" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+94 81 222 3344">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="kandy@tuition.lk">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Street address"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

