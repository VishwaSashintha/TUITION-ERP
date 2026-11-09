<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-tp-black"><i class="bi bi-person-gear text-tp-green me-2"></i>Institute Admin & Installation Setup</h4>
        <p class="text-muted mb-0">Super Admin control to configure the active institute profile and manage Institute Administrator accounts.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('platform/dashboard') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> System Overview
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAdminModal">
            <i class="bi bi-person-plus-fill"></i> Create Institute Admin
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Active Institute Profile Card -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0 text-tp-black"><i class="bi bi-building me-2 text-tp-green"></i>Installation Profile</h6>
                <span class="badge badge-success text-capitalize"><?= e($institute['status'] ?? 'active') ?></span>
            </div>
            <div class="card-body">
                <form action="<?= url('platform/institute/update') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="institute_id" value="<?= $institute['id'] ?? 1 ?>">

                    <div class="mb-3">
                        <label class="form-label">Institute Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= e($institute['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">System Identifier Code</label>
                        <input type="text" class="form-control bg-light font-monospace" value="<?= e($institute['code'] ?? '') ?>" readonly>
                        <small class="text-muted">Unique code configured in environment (.env).</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Official Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= e($institute['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contact Telephone</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($institute['phone'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Physical Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= e($institute['address'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check2-circle"></i> Save Installation Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Institute Admin Accounts List -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0 text-tp-black"><i class="bi bi-shield-lock me-2 text-tp-green"></i>Institute Administrators & Directors</h6>
                <span class="badge badge-dark"><?= count($admins) ?> Accounts</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Administrator</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No Institute Administrators assigned yet. Click "Create Institute Admin" to set up an account.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-tp-black"><?= e($admin['name']) ?></div>
                                        <small class="text-muted font-monospace"><i class="bi bi-envelope me-1"></i><?= e($admin['email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-purple">
                                            <?= e($admin['role_display']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $admin['status'] === 'active' ? 'success' : 'dark' ?>">
                                            <?= ucfirst($admin['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="openResetModal(<?= $admin['id'] ?>, '<?= e($admin['name']) ?>', '<?= e($admin['email']) ?>')">
                                            <i class="bi bi-key-fill me-1 text-tp-orange"></i>Reset Password
                                        </button>
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

<!-- Modal: Create Institute Admin -->
<div class="modal fade" id="newAdminModal" tabindex="-1" aria-labelledby="newAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-tp-black" id="newAdminModalLabel"><i class="bi bi-person-plus-fill me-2 text-tp-green"></i>Create Institute Administrator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('platform/tenants') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Dr. K. Perera" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="director@apexeducation.lk" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Telephone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+94 77 123 4567">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="8" placeholder="Minimum 8 characters" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-tp-black" id="resetPasswordModalLabel"><i class="bi bi-key-fill me-2 text-tp-orange"></i>Reset Admin Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('platform/institute-admin/reset') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" id="reset_user_id" value="">
                <div class="modal-body p-4">
                    <div class="alert alert-warning small mb-3">
                        <i class="bi bi-shield-exclamation me-1"></i>
                        Resetting the password will immediately revoke all active JWT refresh tokens and force the administrator to sign in again with the new credentials.
                    </div>
                    <p class="mb-2">Admin Account: <strong id="reset_user_name"></strong> (<span id="reset_user_email" class="font-monospace text-muted"></span>)</p>
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control" minlength="8" placeholder="Minimum 8 characters" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent-custom">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResetModal(userId, name, email) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('reset_user_name').textContent = name;
    document.getElementById('reset_user_email').textContent = email;
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}
</script>
