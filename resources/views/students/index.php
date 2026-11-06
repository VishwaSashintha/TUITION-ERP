<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Student Directory</h4>
        <p class="text-muted mb-0">Total <?= number_format($total) ?> registered students in institute</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('students/export/csv') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
        <a href="<?= url('students/create') ?>" class="btn btn-primary-custom">
            <i class="bi bi-person-plus-fill me-1"></i> New Admission
        </a>
    </div>
</div>

<!-- ─── FILTER & SEARCH BAR ────────────────────────────────────────────────── -->
<div class="card-custom p-3 mb-4">
    <form action="<?= url('students') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="term" class="form-control form-control-custom border-start-0" placeholder="Search by name, ID, or phone..." value="<?= e($filters['term'] ?? '') ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="branch_id" class="form-select form-control-custom">
                <option value="">All Branches</option>
                <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($filters['branch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                        <?= e($b['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-control-custom">
                <option value="">All Statuses</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="graduated" <?= ($filters['status'] ?? '') === 'graduated' ? 'selected' : '' ?>>Graduated</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-custom w-100 justify-content-center">
                Filter
            </button>
            <?php if (!empty($filters['term']) || !empty($filters['branch_id']) || !empty($filters['status'])): ?>
                <a href="<?= url('students') ?>" class="btn btn-light border" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- ─── STUDENT DATA TABLE ─────────────────────────────────────────────────── -->
<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>School & Grade</th>
                    <th>Phone</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                            No students found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace fw-bold">
                                    <?= e($s['admission_number']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($s['full_name']) ?></div>
                                <small class="text-muted"><?= e($s['email'] ?: 'No email') ?></small>
                            </td>
                            <td>
                                <div><?= e($s['school'] ?: '—') ?></div>
                                <small class="badge bg-light text-muted border"><?= e($s['grade_level'] ?: 'Grade N/A') ?></small>
                            </td>
                            <td>
                                <a href="tel:<?= e($s['phone']) ?>" class="text-muted">
                                    <i class="bi bi-telephone me-1"></i><?= e($s['phone'] ?: '—') ?>
                                </a>
                            </td>
                            <td><?= e($s['branch_name'] ?? 'Main') ?></td>
                            <td>
                                <span class="badge badge-custom badge-<?= $s['status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($s['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url("students/{$s['id']}") ?>" class="btn btn-sm btn-light border me-1" title="View Profile">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="<?= url("students/{$s['id']}/id-card") ?>" target="_blank" class="btn btn-sm btn-light border text-primary" title="Print ID Card">
                                    <i class="bi bi-qr-code"></i> ID
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($lastPage > 1): ?>
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">Page <?= $page ?> of <?= $lastPage ?></small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= url('students?page=' . ($page - 1)) ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('students?page=' . $i) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $lastPage): ?>
                        <li class="page-item"><a class="page-link" href="<?= url('students?page=' . ($page + 1)) ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

