<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Staff & User Accounts</h2>
        <p class="text-muted mb-0">Role-based access control, staff provisioning, and credential management.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('settings') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Settings
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUserModal">
            <i class="bi bi-person-plus me-1"></i> Add Staff Account
        </button>
    </div>
</div>

<!-- Users Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Email Address</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Phone</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                        <?= strtoupper(substr($u['name'], 0, 2)) ?>
                                    </div>
                                    <div><?= e($u['name']) ?></div>
                                </div>
                            </td>
                            <td class="font-monospace small"><?= e($u['email']) ?></td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border text-capitalize">
                                    <?= e($u['role_display'] ?? ucfirst(str_replace('_', ' ', $u['role_name']))) ?>
                                </span>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= e($u['branch_name'] ?? 'All Branches') ?></span></td>
                            <td class="small text-muted"><?= e($u['phone'] ?? '-') ?></td>
                            <td class="text-center">
                                <span class="badge bg-success text-capitalize"><?= e($u['status']) ?></span>
                            </td>
                            <td class="pe-3 small text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="newUserModalLabel"><i class="bi bi-person-plus me-2 text-primary"></i>Create Staff Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('settings/users') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Kasun Fernando" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="kasun@tuition.lk" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+94 77 123 4567">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Initial Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>

                    <div class="row g-2 mb-0">
                        <div class="col-6">
                            <label class="form-label small fw-bold">System Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= e($r['display_name'] ?? ucfirst(str_replace('_', ' ', $r['name']))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Assigned Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">-- All Branches --</option>
                                <?php foreach ($branches as $br): ?>
                                    <option value="<?= $br['id'] ?>"><?= e($br['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

