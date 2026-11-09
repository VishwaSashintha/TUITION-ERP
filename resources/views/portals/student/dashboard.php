<!-- Student Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 text-white bg-tp-green" style="width: 56px; height: 56px;">
            <?= strtoupper(substr($student['full_name'] ?? 'ST', 0, 2)) ?>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-tp-black"><?= e($student['full_name'] ?? 'Student') ?></h4>
                <span class="badge badge-purple px-2 py-1">Student Portal</span>
            </div>
            <small class="text-muted font-monospace">Admission: <?= e($student['admission_number'] ?? 'N/A') ?> &bull; Branch: <?= e($student['branch_name'] ?? 'Main') ?></small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('communication/announcements') ?>" class="btn btn-secondary">
            <i class="bi bi-megaphone"></i> Notice Board
        </a>
    </div>
</div>

<!-- ─── STUDENT STAT KPI CARDS (Standard 4 per row | Tabular Figures) ────── -->
<div class="row g-3 mb-4">
    <!-- Active Batches (Purple) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-purple">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($batches) ?></div>
                <div class="stat-label">Enrolled Batches</div>
                <small class="text-muted" style="font-size: 0.75rem;">Active courses</small>
            </div>
        </div>
    </div>

    <!-- Attendance Rate % (Green) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green"><?= number_format((float)$attRate, 1) ?>%</div>
                <div class="stat-label">Attendance Rate</div>
                <small class="text-muted" style="font-size: 0.75rem;">Session turnout</small>
            </div>
        </div>
    </div>

    <!-- Active Assignments (Orange) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-orange">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($assignments ?? []) ?></div>
                <div class="stat-label">Assignments Due</div>
                <small class="text-muted" style="font-size: 0.75rem;">Pending tasks</small>
            </div>
        </div>
    </div>

    <!-- Study Materials (Sky Blue) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-sky-blue">
                <i class="bi bi-file-earmark-pdf-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-sky-blue"><?= count($materials ?? []) ?></div>
                <div class="stat-label">Study Materials</div>
                <small class="text-muted" style="font-size: 0.75rem;">Downloadable notes</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Batches & Results -->
    <div class="col-lg-8">
        <!-- My Enrolled Batches -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-purple px-2 py-1"><i class="bi bi-mortarboard"></i></span>
                    <h6 class="fw-bold mb-0">My Enrolled Classes & Batches</h6>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Batch Name</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th class="text-end">Monthly Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($batches)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">You are not currently enrolled in any active batches.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($batches as $b): ?>
                                <tr>
                                    <td class="fw-bold text-tp-black"><?= e($b['name']) ?></td>
                                    <td><?= e($b['subject_name']) ?></td>
                                    <td><i class="bi bi-person me-1 text-muted"></i><?= e($b['teacher_name']) ?></td>
                                    <td class="text-end fw-number fw-bold"><?= format_currency($b['monthly_fee']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Academic Performance & Results -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-orange px-2 py-1"><i class="bi bi-trophy-fill"></i></span>
                    <h6 class="fw-bold mb-0">Recent Examination Results</h6>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Exam Title</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th class="text-center">Marks</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Class Rank</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No published examination results available yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td class="fw-bold text-tp-black"><?= e($r['exam_name']) ?></td>
                                    <td><?= e($r['subject_name']) ?></td>
                                    <td class="text-muted" style="font-size: 0.825rem;"><?= format_date($r['exam_date']) ?></td>
                                    <td class="text-center fw-number fw-bold">
                                        <?= $r['is_absent'] ? '<span class="text-danger">ABSENT</span>' : number_format((float)$r['marks_obtained'], 1) . ' / ' . (int)$r['max_marks'] ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $r['grade'] === 'A' ? 'success' : ($r['grade'] === 'F' ? 'danger' : 'info') ?> font-monospace">
                                            <?= e($r['grade']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($r['rank_in_batch']): ?>
                                            <span class="badge badge-warning fw-number">#<?= $r['rank_in_batch'] ?></span>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Class Study Materials -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-sky-blue px-2 py-1"><i class="bi bi-file-earmark-pdf"></i></span>
                    <h6 class="fw-bold mb-0">Learning Materials & Notes</h6>
                </div>
                <span class="badge badge-dark"><?= count($materials ?? []) ?> Items</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Material Title</th>
                            <th>Batch & Subject</th>
                            <th>Type</th>
                            <th class="text-end">Access</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materials)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No study materials published for your batches yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($materials as $m): ?>
                                <tr>
                                    <td class="fw-bold text-tp-black">
                                        <i class="bi bi-file-earmark-text me-2 text-tp-purple"></i><?= e($m['title']) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-dark"><?= e($m['batch_name']) ?></span>
                                        <small class="text-muted d-block"><?= e($m['subject_name']) ?></small>
                                    </td>
                                    <td><span class="badge badge-info text-uppercase"><?= e($m['material_type']) ?></span></td>
                                    <td class="text-end">
                                        <?php if (!empty($m['file_path'])): ?>
                                            <a href="<?= url('academic/materials/download/' . $m['id']) ?>" class="btn btn-sm btn-secondary" style="font-size: 0.75rem; padding: 2px 10px;">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        <?php elseif (!empty($m['external_link'])): ?>
                                            <a href="<?= e($m['external_link']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-secondary" style="font-size: 0.75rem; padding: 2px 10px;">
                                                <i class="bi bi-box-arrow-up-right"></i> Link
                                            </a>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Assignments & Invoices -->
    <div class="col-lg-4">
        <!-- Assignments Card -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-orange px-2 py-1"><i class="bi bi-pencil-square"></i></span>
                    <h6 class="fw-bold mb-0">Assignments & Tasks</h6>
                </div>
                <span class="badge badge-dark"><?= count($assignments ?? []) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($assignments)): ?>
                    <p class="text-muted text-center py-4 mb-0">No active assignments assigned.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($assignments as $asn): ?>
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-warning">
                                <h6 class="fw-bold mb-1 text-tp-black"><?= e($asn['title']) ?></h6>
                                <div class="small text-muted mb-2">
                                    <span class="badge badge-dark"><?= e($asn['batch_name']) ?></span>
                                    <span class="ms-1">Max Marks: <?= (int)$asn['max_marks'] ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-tp-coral fw-bold"><i class="bi bi-clock me-1"></i>Due: <?= format_date($asn['due_date']) ?></span>
                                    <?php if (!empty($asn['attachment_path'])): ?>
                                        <a href="<?= url('academic/assignments/download/' . $asn['id']) ?>" class="btn btn-sm btn-secondary py-1 px-2" style="font-size: 0.7rem;">
                                            <i class="bi bi-paperclip"></i> Task File
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fee Invoices for this Student -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-green px-2 py-1"><i class="bi bi-receipt"></i></span>
                    <h6 class="fw-bold mb-0">My Tuition Invoices</h6>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($invoices)): ?>
                    <p class="text-muted text-center py-3 mb-0">No invoices issued.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($invoices as $inv): ?>
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-monospace fw-bold" style="font-size: 0.85rem;"><?= e($inv['invoice_number']) ?></span>
                                    <span class="badge badge-<?= $inv['status'] === 'paid' ? 'success' : 'warning' ?> text-capitalize" style="font-size: 0.7rem;">
                                        <?= str_replace('_', ' ', $inv['status']) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted">Due: <?= format_date($inv['due_date']) ?></small>
                                    <span class="fw-number fw-bold <?= (float)$inv['balance_amount'] > 0 ? 'text-tp-coral' : 'text-tp-green' ?>">
                                        <?= format_currency($inv['balance_amount']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notice Board -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-black px-2 py-1"><i class="bi bi-megaphone-fill"></i></span>
                    <h6 class="fw-bold mb-0">Notice Board</h6>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($announcements)): ?>
                    <p class="text-muted text-center py-3 mb-0">No active notices.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($announcements as $an): ?>
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="fw-bold mb-1 text-tp-black" style="font-size: 0.85rem;"><?= e($an['title']) ?></div>
                                <p class="text-muted mb-1 text-truncate" style="font-size: 0.8rem;"><?= e(strip_tags($an['content'])) ?></p>
                                <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i><?= format_date($an['published_at']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
