<!-- Welcome Banner -->
<div class="card border-0 shadow-sm mb-4 bg-primary text-white">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar bg-white text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 64px; height: 64px;">
                <?= strtoupper(substr($student['full_name'] ?? 'ST', 0, 2)) ?>
            </div>
            <div>
                <span class="badge bg-white text-primary mb-1">STUDENT PORTAL</span>
                <h3 class="fw-bold mb-0"><?= e($student['full_name'] ?? 'Student') ?></h3>
                <small class="opacity-75 font-monospace">Admission: <?= e($student['admission_number'] ?? 'N/A') ?> | Branch: <?= e($student['branch_name'] ?? 'Main') ?></small>
            </div>
        </div>
        <div class="d-flex gap-3">
            <div class="text-center px-4 py-2 bg-white bg-opacity-10 rounded-3">
                <small class="d-block opacity-75">Attendance Turnout</small>
                <h4 class="fw-bold mb-0"><?= $attRate ?>%</h4>
            </div>
            <div class="text-center px-4 py-2 bg-white bg-opacity-10 rounded-3">
                <small class="d-block opacity-75">Active Batches</small>
                <h4 class="fw-bold mb-0"><?= count($batches) ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Batches & Results -->
    <div class="col-lg-8">
        <!-- My Enrolled Batches -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-mortarboard me-2 text-primary"></i>My Enrolled Batches</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Batch Name</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th class="text-end pe-3">Monthly Fee</th>
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
                                        <td class="ps-3 fw-bold text-primary"><?= e($b['name']) ?></td>
                                        <td><?= e($b['subject_name']) ?></td>
                                        <td><i class="bi bi-person me-1 text-muted"></i><?= e($b['teacher_name']) ?></td>
                                        <td class="text-end pe-3 fw-bold">Rs. <?= number_format((float)$b['monthly_fee'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Academic Performance & Results -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Recent Examination Results</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Exam Title</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th class="text-center">Marks</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center pe-3">Class Rank</th>
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
                                        <td class="ps-3 fw-bold"><?= e($r['exam_name']) ?></td>
                                        <td><?= e($r['subject_name']) ?></td>
                                        <td><?= date('d M Y', strtotime($r['exam_date'])) ?></td>
                                        <td class="text-center font-monospace fw-bold">
                                            <?= $r['is_absent'] ? '<span class="text-danger">ABSENT</span>' : number_format((float)$r['marks_obtained'], 1) . ' / ' . (int)$r['max_marks'] ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $r['grade'] === 'A' ? 'bg-success' : ($r['grade'] === 'F' ? 'bg-danger' : 'bg-primary') ?> fs-6 font-monospace">
                                                <?= e($r['grade']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center pe-3">
                                            <?php if ($r['rank_in_batch']): ?>
                                                <span class="badge bg-warning text-dark">#<?= $r['rank_in_batch'] ?></span>
                                            <?php else: ?>
                                                -
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

        <!-- Fee Statements & Invoices -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-success"></i>Tuition Invoices & Payment Status</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Invoice #</th>
                                <th>Period</th>
                                <th>Due Date</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Balance</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No invoices generated.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td class="ps-3 font-monospace fw-bold"><?= e($inv['invoice_number']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= e($inv['fee_period']) ?></span></td>
                                        <td class="small"><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
                                        <td class="text-end">Rs. <?= number_format((float)$inv['total_amount'], 2) ?></td>
                                        <td class="text-end fw-bold <?= (float)$inv['balance_amount'] > 0 ? 'text-danger' : 'text-muted' ?>">
                                            Rs. <?= number_format((float)$inv['balance_amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $inv['status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?> text-capitalize">
                                                <?= str_replace('_', ' ', $inv['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Learning Materials & Lecture Notes -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Class Study Materials</h5>
                <span class="badge bg-light text-dark border"><?= count($materials ?? []) ?> Items</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Material Title</th>
                                <th>Batch & Subject</th>
                                <th>Type</th>
                                <th class="text-end pe-3">Access</th>
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
                                        <td class="ps-3 fw-bold text-dark">
                                            <i class="bi bi-file-earmark-text me-2 text-primary"></i><?= e($m['title']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= e($m['batch_name']) ?></span>
                                            <small class="text-muted d-block"><?= e($m['subject_name']) ?></small>
                                        </td>
                                        <td><span class="badge bg-info-subtle text-info border text-uppercase"><?= e($m['material_type']) ?></span></td>
                                        <td class="text-end pe-3">
                                            <?php if (!empty($m['file_path'])): ?>
                                                <a href="<?= url('academic/materials/download/' . $m['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            <?php elseif (!empty($m['external_link'])): ?>
                                                <a href="<?= e($m['external_link']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-box-arrow-up-right"></i> Link
                                                </a>
                                            <?php else: ?>
                                                -
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
    </div>

    <!-- Right Column: Assignments & Announcements -->
    <div class="col-lg-4">
        <!-- Assignments Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-warning"></i>Homework & Assignments</h5>
                <span class="badge bg-light text-dark border"><?= count($assignments ?? []) ?></span>
            </div>
            <div class="card-body p-3">
                <?php if (empty($assignments)): ?>
                    <p class="text-muted small text-center py-4 mb-0">No active assignments assigned.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($assignments as $asn): ?>
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-warning">
                                <h6 class="fw-bold mb-1 text-dark"><?= e($asn['title']) ?></h6>
                                <div class="small text-muted mb-2">
                                    <span class="badge bg-secondary-subtle text-dark"><?= e($asn['batch_name']) ?></span>
                                    <span class="ms-1">Max Marks: <?= (int)$asn['max_marks'] ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-danger fw-bold"><i class="bi bi-clock me-1"></i>Due: <?= date('d M', strtotime($asn['due_date'])) ?></span>
                                    <?php if (!empty($asn['attachment_path'])): ?>
                                        <a href="<?= url('academic/assignments/download/' . $asn['id']) ?>" class="btn btn-xs btn-outline-dark py-1 px-2">
                                            <i class="bi bi-paperclip"></i> Brief
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-megaphone me-2 text-primary"></i>Notice Board</h5>
            </div>
            <div class="card-body p-3">
                <?php if (empty($announcements)): ?>
                    <p class="text-muted small text-center py-4 mb-0">No active announcements.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($announcements as $an): ?>
                            <div class="p-3 bg-light rounded-3">
                                <h6 class="fw-bold mb-1 text-dark"><?= e($an['title']) ?></h6>
                                <p class="small text-secondary mb-2" style="white-space: pre-line;"><?= e($an['content']) ?></p>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('d M Y', strtotime($an['published_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

