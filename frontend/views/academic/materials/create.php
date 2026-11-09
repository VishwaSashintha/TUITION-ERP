<div class="mb-4">
    <a href="<?= url('academic/materials') ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Materials</a>
    <h4 class="fw-bold mt-2 mb-1">Upload Learning Material</h4>
    <p class="text-muted mb-0">Share lecture notes, revision packs, tutorial sheets, or external video lectures with enrolled students.</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="<?= url('academic/materials/store') ?>" method="POST" enctype="multipart/form-data" class="card-custom p-4">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label-custom">Material Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control form-control-custom" placeholder="e.g. 2026 Combined Maths — Integration Revision Notes" required>
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
                    <label class="form-label-custom">Material Type <span class="text-danger">*</span></label>
                    <select name="material_type" class="form-select form-control-custom" required>
                        <option value="pdf">PDF Document</option>
                        <option value="document">Word / Excel / Office Document</option>
                        <option value="image">Image / Diagram</option>
                        <option value="video_link">Video Lesson URL (YouTube / Drive)</option>
                        <option value="external_link">External Web Link</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Upload File Attachment</label>
                <input type="file" name="file" class="form-control form-control-custom">
                <small class="text-muted">Supported formats: PDF, DOCX, PPTX, XLSX, PNG, JPG, ZIP (Max: 10 MB)</small>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">External Link (Optional)</label>
                <input type="url" name="external_link" class="form-control form-control-custom" placeholder="https://youtube.com/... or https://drive.google.com/...">
                <small class="text-muted">Required if material type is Video Link or External Link.</small>
            </div>

            <div class="mb-4">
                <label class="form-label-custom">Description & Student Instructions</label>
                <textarea name="description" class="form-control form-control-custom" rows="3" placeholder="Provide any additional reading instructions or context for students..."></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= url('academic/materials') ?>" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload & Publish
                </button>
            </div>
        </form>
    </div>
</div>

