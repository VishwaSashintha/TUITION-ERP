<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Assignments & Homework</h4>
        <p class="text-muted mb-0">Create coursework, post tutorial problems, set submission deadlines, and grade student submissions.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/assignments/create') ?>" class="btn btn-primary-custom">
            <i class="bi bi-plus-circle-fill"></i> New Assignment
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card-custom mb-4 p-3">
    <form method="GET" action="<?= url('academic/assignments') ?>" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control form-control-custom" placeholder="Search assignment title..." value="<?= e($filters['search'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <select name="batch_id" class="form-select form-control-custom">
                <option value="">All Class Batches</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($filters['batch_id'] ?? 0) == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary-custom flex-grow-1"><i class="bi bi-search"></i> Filter</button>
            <a href="<?= url('academic/assignments') ?>" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Assignments Table -->
<div class="card-custom">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-card-checklist me-2 text-primary"></i>Active Coursework</h6>
        <span class="badge bg-light text-muted border"><?= count($assignments) ?> Assignments</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Batch & Subject</th>
                    <th>Due Date</th>
                    <th>Max Marks</th>
                    <th>Roster</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                            <h6>No assignments scheduled yet.</h6>
                            <p class="small mb-3">Create homework or tutorial problem sets for your classes.</p>
                            <a href="<?= url('academic/assignments/create') ?>" class="btn btn-sm btn-primary-custom">Create First Assignment</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td>
                                <a href="<?= url('academic/assignments/' . $a['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                    <?= e($a['title']) ?>
                                </a>
                                <div class="small text-muted font-monospace"><i class="bi bi-person me-1"></i><?= e($a['teacher_name'] ?? 'Faculty') ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($a['batch_name']) ?></div>
                                <small class="text-muted"><?= e($a['subject_name']) ?></small>
                            </td>
                            <td>
                                <?php
                                $isOverdue = strtotime($a['due_date']) < time();
                                ?>
                                <span class="badge bg-<?= $isOverdue ? 'danger-subtle text-danger' : 'warning-subtle text-warning' ?> border">
                                    <i class="bi bi-calendar3 me-1"></i><?= date('d M Y, h:i A', strtotime($a['due_date'])) ?>
                                </span>
                            </td>
                            <td class="fw-bold text-primary"><?= number_format((float)$a['max_marks'], 0) ?> pts</td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= $a['total_students'] ?> Students</span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('academic/assignments/' . $a['id']) ?>" class="btn btn-sm btn-light border me-1">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <?php if (!empty($a['attachment_path'])): ?>
                                    <a href="<?= url('academic/assignments/download/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary" title="Download Worksheet">
                                        <i class="bi bi-download"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

