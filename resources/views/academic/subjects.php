<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Academic Subjects</h4>
        <p class="text-muted mb-0">Configure subjects, grades, and academic curriculums</p>
    </div>
    <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#newSubjectModal">
        <i class="bi bi-plus-circle me-1"></i> Add Subject
    </button>
</div>

<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>Grade / Stream</th>
                    <th>Active Batches</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No academic subjects configured yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subjects as $s): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace fw-bold"><?= e($s['code']) ?></span>
                            </td>
                            <td class="fw-bold text-dark"><?= e($s['name']) ?></td>
                            <td><span class="badge bg-light text-muted border"><?= e($s['grade_level'] ?: 'All Grades') ?></span></td>
                            <td>
                                <span class="badge bg-light text-primary border fw-bold"><?= $s['batches_count'] ?> Batches</span>
                            </td>
                            <td class="text-muted" style="font-size: 0.85rem;"><?= e($s['description'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal to Add Subject -->
<div class="modal fade" id="newSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= url('academic/subjects/store') ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Academic Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label-custom">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-custom" placeholder="e.g. Combined Mathematics" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Subject Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control form-control-custom" placeholder="e.g. CMATH_AL" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Grade / Academic Level</label>
                    <input type="text" name="grade_level" class="form-control form-control-custom" placeholder="e.g. Grade 12-13 (A/L)">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Description</label>
                    <textarea name="description" class="form-control form-control-custom" rows="2" placeholder="Subject details..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary-custom">Save Subject</button>
            </div>
        </form>
    </div>
</div>

