<!-- Parent Header -->
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Parent & Guardian Portal</h2>
        <p class="text-muted mb-0">Monitor your child's tuition attendance, fee dues, examination performance, and progress.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <?php if (!empty($children) && count($children) > 1): ?>
            <div class="d-inline-flex align-items-center gap-2">
                <label class="small fw-bold text-muted mb-0">Active Child:</label>
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person me-1"></i> <?= e($activeChild['full_name'] ?? 'Child') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <?php foreach ($children as $c): ?>
                            <li>
                                <a class="dropdown-item <?= $c['id'] == $selectedChildId ? 'active' : '' ?>" href="<?= url('portal/parent?child_id=' . $c['id']) ?>">
                                    <?= e($c['full_name']) ?> (<?= e($c['admission_number']) ?>)
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Child Summary Banner -->
<div class="card border-0 shadow-sm mb-4 bg-light">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 60px; height: 60px;">
                <?= strtoupper(substr($activeChild['full_name'] ?? 'CH', 0, 2)) ?>
            </div>
            <div>
                <span class="badge bg-primary mb-1">LINKED STUDENT</span>
                <h4 class="fw-bold mb-0 text-dark"><?= e($activeChild['full_name'] ?? 'Child') ?></h4>
                <small class="text-muted font-monospace">Admission: <?= e($activeChild['admission_number'] ?? 'N/A') ?> | Branch: <?= e($activeChild['branch_name'] ?? 'Main') ?></small>
            </div>
        </div>

        <div class="d-flex gap-3">
            <div class="p-3 bg-white rounded-3 shadow-sm text-center" style="min-width: 140px;">
                <small class="text-muted d-block">Attendance</small>
                <h4 class="fw-bold mb-0 text-success"><?= $attRate ?>%</h4>
            </div>
            <div class="p-3 bg-white rounded-3 shadow-sm text-center" style="min-width: 140px;">
                <small class="text-muted d-block">Pending Dues</small>
                <h4 class="fw-bold mb-0 <?= $totalBalance > 0 ? 'text-danger' : 'text-success' ?>">
                    Rs. <?= number_format($totalBalance, 2) ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Exams & Fees -->
    <div class="col-lg-8">
        <!-- Examination Marks & Rankings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-award-fill me-2 text-warning"></i>Recent Test & Exam Results</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Exam</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center pe-3">Batch Rank</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($results)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No examination results published yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($results as $r): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark"><?= e($r['exam_name']) ?></td>
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

        <!-- Fee Statements & Payments -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2 text-success"></i>Tuition Invoices & Payment Ledger</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Invoice #</th>
                                <th>Period</th>
                                <th>Due Date</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end">Balance Due</th>
                                <th class="text-center pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No fee records found.</td>
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
                                        <td class="text-center pe-3">
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

        <!-- Class Materials & Syllabus Notes -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Class Study Materials & Notes</h5>
                <span class="badge bg-light text-dark border"><?= count($materials ?? []) ?> Items</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Title</th>
                                <th>Class & Subject</th>
                                <th>Type</th>
                                <th class="text-end pe-3">Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($materials)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No study materials shared for this student yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($materials as $m): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark"><?= e($m['title']) ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= e($m['batch_name']) ?></span>
                                            <small class="text-muted d-block"><?= e($m['subject_name']) ?></small>
                                        </td>
                                        <td><span class="badge bg-info-subtle text-info border text-uppercase"><?= e($m['material_type']) ?></span></td>
                                        <td class="text-end pe-3">
                                            <?php if (!empty($m['file_path'])): ?>
                                                <a href="<?= url('academic/materials/download/' . $m['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download"></i> Get File
                                                </a>
                                            <?php elseif (!empty($m['external_link'])): ?>
                                                <a href="<?= e($m['external_link']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-link-45deg"></i> Link
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

    <!-- Right Column: Institute Notice Board & Homework -->
    <div class="col-lg-4">
        <!-- Homework & Coursework Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-warning"></i>Homework & Tasks</h5>
                <span class="badge bg-light text-dark border"><?= count($assignments ?? []) ?></span>
            </div>
            <div class="card-body p-3">
                <?php if (empty($assignments)): ?>
                    <p class="text-muted small text-center py-4 mb-0">No active homework assigned.</p>
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
                                            <i class="bi bi-download"></i> Task
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-bell-fill me-2 text-primary"></i>Institute Announcements</h5>
            </div>
            <div class="card-body p-3">
                <?php if (empty($announcements)): ?>
                    <p class="text-muted small text-center py-4 mb-0">No announcements for parents at this time.</p>
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

