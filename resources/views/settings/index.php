<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Institute Settings</h2>
        <p class="text-muted mb-0">Configure your institute profile, contact details, currency, and operational timezone.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('settings/branches') ?>" class="btn btn-outline-primary">
            <i class="bi bi-buildings me-1"></i> Branches
        </a>
        <a href="<?= url('settings/users') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-people me-1"></i> Staff Users
        </a>
        <a href="<?= url('settings/backups') ?>" class="btn btn-outline-dark">
            <i class="bi bi-database-down me-1"></i> Backups
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-gear-fill me-2 text-primary"></i>Institute Profile</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('settings') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Institute Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= e($tenant['name'] ?? '') ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Institute Code (Subdomain)</label>
                            <input type="text" class="form-control font-monospace bg-light" value="<?= e($tenant['code'] ?? '') ?>" readonly>
                            <small class="text-muted">Tenant identifier cannot be changed.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Official Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= e($tenant['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Contact Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($tenant['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Timezone</label>
                            <input type="text" name="timezone" class="form-control" value="<?= e($tenant['timezone'] ?? 'Asia/Colombo') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Operating Currency</label>
                            <input type="text" class="form-control bg-light" value="LKR (Rs.)" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Subscription Status</label>
                            <div>
                                <span class="badge bg-success text-capitalize px-3 py-2"><?= e($tenant['status'] ?? 'active') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Physical Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= e($tenant['address'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

