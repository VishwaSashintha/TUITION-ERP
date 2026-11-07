<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--primary-dark);">Student Enquiries & Admissions</h3>
        <p class="text-muted mb-0 small">Track prospective student inquiries, follow-up interactions, and conversion to active enrollments.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('students/enquiries/create') ?>" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-person-plus-fill"></i> Log New Enquiry
        </a>
        <a href="<?= url('students') ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="bi bi-people"></i> Student Directory
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--secondary) !important;">
            <div class="card-body">
                <span class="text-muted small text-uppercase fw-semibold">Total Inquiries</span>
                <h2 class="kpi-number text-dark mt-2 mb-0"><?= number_format((int)($counts['total'] ?? 0)) ?></h2>
                <div class="small text-muted mt-1">All recorded leads</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--accent-orange) !important;">
            <div class="card-body">
                <span class="text-muted small text-uppercase fw-semibold">Follow-Ups Due Today</span>
                <h2 class="kpi-number text-warning mt-2 mb-0"><?= number_format((int)($counts['due_today'] ?? 0)) ?></h2>
                <div class="small text-muted mt-1">Requires contact today</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--accent-purple) !important;">
            <div class="card-body">
                <span class="text-muted small text-uppercase fw-semibold">In Follow-Up Pipeline</span>
                <h2 class="kpi-number text-info mt-2 mb-0"><?= number_format((int)($counts['count_follow_up'] ?? 0)) ?></h2>
                <div class="small text-muted mt-1">Active discussions</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid var(--primary-brand) !important;">
            <div class="card-body">
                <span class="text-muted small text-uppercase fw-semibold">Converted / Admitted</span>
                <h2 class="kpi-number text-success mt-2 mb-0"><?= number_format((int)($counts['count_admitted'] ?? 0)) ?></h2>
                <div class="small text-muted mt-1">Successful admissions</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('students/enquiries') ?>" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Search Name, Phone, Course</label>
                <input type="text" name="term" value="<?= e($filters['term'] ?? '') ?>" class="form-control" placeholder="Search by name, phone, course...">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="new" <?= ($filters['status'] ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="contacted" <?= ($filters['status'] ?? '') === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                    <option value="follow_up" <?= ($filters['status'] ?? '') === 'follow_up' ? 'selected' : '' ?>>In Follow-Up</option>
                    <option value="admitted" <?= ($filters['status'] ?? '') === 'admitted' ? 'selected' : '' ?>>Admitted</option>
                    <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed / Lost</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Lead Source</label>
                <select name="source" class="form-select">
                    <option value="">All Sources</option>
                    <option value="walk_in" <?= ($filters['source'] ?? '') === 'walk_in' ? 'selected' : '' ?>>Walk-in</option>
                    <option value="phone" <?= ($filters['source'] ?? '') === 'phone' ? 'selected' : '' ?>>Phone Call</option>
                    <option value="social_media" <?= ($filters['source'] ?? '') === 'social_media' ? 'selected' : '' ?>>Social Media</option>
                    <option value="website" <?= ($filters['source'] ?? '') === 'website' ? 'selected' : '' ?>>Website</option>
                    <option value="referral" <?= ($filters['source'] ?? '') === 'referral' ? 'selected' : '' ?>>Referral</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                <a href="<?= url('students/enquiries') ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Enquiries Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="fw-bold mb-0" style="color: var(--primary-dark);">Enquiries Directory</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Prospective Student</th>
                    <th>Phone / Contact</th>
                    <th>Grade / Subject Interest</th>
                    <th>Lead Source</th>
                    <th>Status</th>
                    <th>Next Follow-up</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($enquiries)): ?>
                    <?php foreach ($enquiries as $enq): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark">#<?= $enq['id'] ?></span></td>
                            <td>
                                <a href="<?= url("students/enquiries/{$enq['id']}") ?>" class="fw-bold text-decoration-none text-dark">
                                    <?= e($enq['first_name'] . ' ' . $enq['last_name']) ?>
                                </a>
                                <?php if (!empty($enq['email'])): ?>
                                    <div class="text-muted small"><?= e($enq['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="tel:<?= e($enq['phone']) ?>" class="text-decoration-none">
                                    <i class="bi bi-telephone-fill text-muted me-1"></i><?= e($enq['phone']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border"><?= e($enq['course_interest'] ?: 'General') ?></span>
                                <?php if (!empty($enq['grade'])): ?>
                                    <span class="badge bg-info text-dark">Grade <?= e($enq['grade']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="small text-muted text-capitalize"><?= str_replace('_', ' ', e($enq['source'])) ?></span>
                            </td>
                            <td>
                                <?php if ($enq['status'] === 'new'): ?>
                                    <span class="badge bg-primary">New</span>
                                <?php elseif ($enq['status'] === 'follow_up'): ?>
                                    <span class="badge bg-warning text-dark">In Follow-Up</span>
                                <?php elseif ($enq['status'] === 'contacted'): ?>
                                    <span class="badge bg-info text-dark">Contacted</span>
                                <?php elseif ($enq['status'] === 'admitted'): ?>
                                    <span class="badge bg-success">Admitted (<?= e($enq['converted_adm_no']) ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= e(ucfirst($enq['status'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($enq['next_followup_date'])): ?>
                                    <?php $isToday = ($enq['next_followup_date'] === date('Y-m-d')); ?>
                                    <span class="<?= $isToday ? 'badge bg-danger' : 'text-muted small' ?>">
                                        <?= e($enq['next_followup_date']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url("students/enquiries/{$enq['id']}") ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> View & Follow-Up
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No enquiries found matching your criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
