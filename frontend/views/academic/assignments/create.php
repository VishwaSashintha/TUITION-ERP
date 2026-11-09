<div class="mb-4">
    <a href="<?= url('academic/assignments') ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Assignments</a>
    <h4 class="fw-bold mt-2 mb-1">Create New Assignment</h4>
    <p class="text-muted mb-0">Publish a coursework brief, tutorial problem set, or practical worksheet with submission deadlines.</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="<?= url('academic/assignments/store') ?>" method="POST" enctype="multipart/form-data" class="card-custom p-4">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label-custom">Assignment Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control form-control-custom" placeholder="e.g. Tutorial Sheet 04 — Coordinate Geometry & Conics" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label-custom">Target Class Batch <span class="text-danger">*</span></label>
                    <select name="batch_id" class="form-select form-control-custom" required>
                        <option value="">Select Class Batch</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label-custom">Submission Due Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="due_date" class="form-control form-control-custom" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label-custom">Maximum Marks <span class="text-danger">*</span></label>
                    <input type="number" step="0.5" name="max_marks" class="form-control form-control-custom" value="100" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label-custom">Worksheet / Brief Attachment (Optional)</label>
                    <input type="file" name="attachment" class="form-control form-control-custom">
                    <small class="text-muted">PDF, DOCX, ZIP (Max: 10 MB)</small>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label-custom">Instructions & Problem Statement <span class="text-danger">*</span></label>
                <textarea name="instructions" class="form-control form-control-custom" rows="5" placeholder="Detail the questions, problems, or submission requirements for students..." required></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= url('academic/assignments') ?>" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-check2-circle me-1"></i> Publish Assignment
                </button>
            </div>
        </form>
    </div>
</div>

