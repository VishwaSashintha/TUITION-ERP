<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-tp-black">Academic Subjects</h4>
        <p class="text-muted mb-0">Configure subjects, grades, and academic curriculums</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSubjectModal">
        <i class="bi bi-plus-circle me-1"></i> Add Subject
    </button>
</div>

<div class="card">
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
                                <span class="badge badge-dark font-monospace fw-bold"><?= e($s['code']) ?></span>
                            </td>
                            <td class="fw-bold text-tp-black"><?= e($s['name']) ?></td>
                            <td><span class="badge badge-dark"><?= e($s['grade_level'] ?: 'All Grades') ?></span></td>
                            <td>
                                <span class="badge badge-purple fw-bold fw-number"><?= $s['batches_count'] ?> Batches</span>
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
        <form action="<?= url('academic/subjects/store') ?>" method="POST" class="modal-content" style="border-radius: 12px; border: 1px solid var(--border-color);">
            <?= csrf_field() ?>
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-tp-black">Add Academic Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Combined Mathematics" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. MATH-AL">
                    <small class="text-muted">Auto-generated if left blank</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Grade Level / Academic Stream</label>
                    <input type="text" name="grade_level" class="form-control" placeholder="e.g. A/L, O/L, Grade 12">
                </div>
                <div class="mb-0">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Syllabus scope or brief subject overview..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Subject</button>
            </div>
        </form>
    </div>
</div>
