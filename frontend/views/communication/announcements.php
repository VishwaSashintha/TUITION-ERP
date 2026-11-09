<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Notice Board & Announcements</h2>
        <p class="text-muted mb-0">Broadcast messages, exam alerts, and schedule changes to students, parents, and teachers.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal">
            <i class="bi bi-megaphone-fill me-1"></i> Post Announcement
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <?php if (empty($announcements)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-megaphone fs-1 d-block mb-2 opacity-50"></i>
                    <h6>No announcements published on the notice board yet.</h6>
                    <p class="small mb-3">Broadcast institute updates directly to students, parents, or staff.</p>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal">
                        Post First Announcement
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($announcements as $ann): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-1 text-dark"><?= e($ann['title']) ?></h5>
                                <?php
                                $audBadge = match($ann['target_audience']) {
                                    'students' => 'bg-info-subtle text-info border border-info-subtle',
                                    'parents'  => 'bg-warning-subtle text-warning border border-warning-subtle',
                                    'teachers' => 'bg-success-subtle text-success border border-success-subtle',
                                    default    => 'bg-primary-subtle text-primary border border-primary-subtle'
                                };
                                ?>
                                <span class="badge <?= $audBadge ?> text-capitalize px-3 py-1">
                                    Audience: <?= e($ann['target_audience']) ?>
                                </span>
                            </div>

                            <p class="text-secondary mb-3" style="white-space: pre-line;"><?= e($ann['content']) ?></p>

                            <div class="d-flex flex-wrap align-items-center justify-content-between pt-3 border-top small text-muted">
                                <div class="d-flex align-items-center gap-3">
                                    <span><i class="bi bi-person me-1"></i><?= e($ann['author_name']) ?></span>
                                    <span><i class="bi bi-clock me-1"></i><?= date('d M Y, h:i A', strtotime($ann['published_at'])) ?></span>
                                </div>
                                <?php if ($ann['batch_name']): ?>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-mortarboard me-1"></i><?= e($ann['batch_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Notice Board Tips Side Column -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Broadcasting Channels</h6>
            </div>
            <div class="card-body p-3 small text-muted">
                <p>Announcements posted here are immediately visible on:</p>
                <ul class="ps-3 mb-0">
                    <li class="mb-2"><strong>Student Portal:</strong> Displayed on student dashboards.</li>
                    <li class="mb-2"><strong>Parent Portal:</strong> Visible to parents for child tracking.</li>
                    <li class="mb-2"><strong>Teacher Dashboard:</strong> Instant alerts for academic staff.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- New Announcement Modal -->
<div class="modal fade" id="newAnnouncementModal" tabindex="-1" aria-labelledby="newAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="newAnnouncementModalLabel"><i class="bi bi-megaphone me-2 text-primary"></i>Post Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('communication/announcements') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Special Holiday Revision Seminar" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Target Audience <span class="text-danger">*</span></label>
                        <select name="target_audience" class="form-select" required>
                            <option value="all">All (Students, Parents & Staff)</option>
                            <option value="students">Students Only</option>
                            <option value="parents">Parents Only</option>
                            <option value="teachers">Teachers & Staff Only</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Specific Batch (Optional)</label>
                        <select name="batch_id" class="form-select">
                            <option value="">-- All Batches across institute --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">Announcement Content <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Write announcement details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Publish Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

