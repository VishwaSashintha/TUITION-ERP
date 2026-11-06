<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-book-half text-primary me-2"></i>Learning Materials & Courseware</h4>
        <p class="text-muted mb-0">Upload and manage lecture notes, worksheets, past papers, and video lessons.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/materials/create') ?>" class="btn btn-primary-custom">
            <i class="bi bi-cloud-upload-fill"></i> Upload Material
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card-custom mb-4 p-3">
    <form method="GET" action="<?= url('academic/materials') ?>" class="row g-2 align-items-center">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-custom" placeholder="Search by title or topic..." value="<?= e($filters['search'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <select name="batch_id" class="form-select form-control-custom">
                <option value="">All Class Batches</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($filters['batch_id'] ?? 0) == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="subject_id" class="form-select form-control-custom">
                <option value="">All Subjects</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($filters['subject_id'] ?? 0) == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary-custom flex-grow-1"><i class="bi bi-search"></i> Filter</button>
            <a href="<?= url('academic/materials') ?>" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Materials List -->
<div class="card-custom">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-collection me-2 text-primary"></i>Available Study Materials</h6>
        <span class="badge bg-light text-muted border"><?= count($materials) ?> Items</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Title & Description</th>
                    <th>Batch & Subject</th>
                    <th>Type</th>
                    <th>Uploaded By</th>
                    <th class="text-end">Resource</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($materials)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 opacity-50"></i>
                            <h6>No learning materials found.</h6>
                            <p class="small mb-3">Upload lecture notes, past paper tutorials, or revision guides.</p>
                            <a href="<?= url('academic/materials/create') ?>" class="btn btn-sm btn-primary-custom">Upload First Material</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($materials as $m): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= e($m['title']) ?></div>
                                <?php if (!empty($m['description'])): ?>
                                    <small class="text-muted d-block"><?= e($m['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($m['batch_name']) ?></div>
                                <small class="text-muted"><?= e($m['subject_name']) ?></small>
                            </td>
                            <td>
                                <?php
                                $typeBadge = match($m['material_type']) {
                                    'pdf'           => 'bg-danger-subtle text-danger border-danger-subtle',
                                    'document'      => 'bg-primary-subtle text-primary border-primary-subtle',
                                    'video_link'    => 'bg-warning-subtle text-warning border-warning-subtle',
                                    'external_link' => 'bg-info-subtle text-info border-info-subtle',
                                    default         => 'bg-light text-dark border',
                                };
                                ?>
                                <span class="badge <?= $typeBadge ?> text-uppercase font-monospace" style="font-size: 0.725rem;">
                                    <?= e($m['material_type']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= e($m['teacher_name'] ?? 'Faculty') ?></div>
                                <small class="text-muted font-monospace"><?= date('d M Y', strtotime($m['created_at'])) ?></small>
                            </td>
                            <td class="text-end">
                                <?php if (!empty($m['file_path'])): ?>
                                    <a href="<?= url('academic/materials/download/' . $m['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i> Download
                                    </a>
                                <?php elseif (!empty($m['external_link'])): ?>
                                    <a href="<?= e($m['external_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Open Link
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">No file</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

