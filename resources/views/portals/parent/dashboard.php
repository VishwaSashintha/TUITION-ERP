<!-- Parent Header -->
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">Parent & Guardian Portal</h4>
            <span class="badge badge-tp-purple px-2 py-1">Guardian View</span>
        </div>
        <p class="text-muted mb-0">Monitor your linked child's attendance turnout, fee dues, exam scores, and learning progress.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <?php if (!empty($children) && count($children) > 1): ?>
            <div class="d-inline-flex align-items-center gap-2">
                <label class="small fw-bold text-muted mb-0">Switch Child:</label>
                <div class="dropdown">
                    <button class="btn btn-outline-dark btn-sm dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person me-1"></i> <?= e($activeChild['full_name'] ?? 'Child') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border">
                        <?php foreach ($children as $c): ?>
                            <li>
                                <a class="dropdown-item <?= (int)$c['id'] === (int)$selectedChildId ? 'active' : '' ?>" href="<?= url('portal/parent?child_id=' . $c['id']) ?>">
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

<?php if (empty($children)): ?>
    <!-- Empty State: No Linked Children -->
    <div class="card-custom p-5 text-center my-4">
        <div class="stat-icon tp-purple mx-auto mb-3" style="width: 64px; height: 64px; font-size: 2rem;">
            <i class="bi bi-person-x-fill"></i>
        </div>
        <h5 class="fw-bold mb-2">No Students Linked to Your Account</h5>
        <p class="text-muted mb-4" style="max-width: 480px; margin: 0 auto;">
            We could not find any active student profiles linked to this parent login. Please contact your tuition institute's reception desk to link your student admission records.
        </p>
        <div>
            <a href="tel:0112345678" class="btn btn-primary-custom" style="background-color: var(--tp-purple); border-color: var(--tp-purple);">
                <i class="bi bi-telephone"></i> Contact Institute Office
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Child Summary Profile Banner -->
    <div class="card-custom p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 text-white bg-tp-purple" style="width: 56px; height: 56px;">
                    <?= strtoupper(substr($activeChild['full_name'] ?? 'CH', 0, 2)) ?>
                </div>
                <div>
                    <span class="badge badge-tp-purple mb-1">STUDENT PROFILE</span>
                    <h5 class="fw-bold mb-0 text-dark"><?= e($activeChild['full_name'] ?? 'Student') ?></h5>
                    <small class="text-muted font-monospace">Admission: <?= e($activeChild['admission_number'] ?? 'N/A') ?> &bull; Branch: <?= e($activeChild['branch_name'] ?? 'Main Branch') ?></small>
                </div>
            </div>

            <!-- Header Quick Stats -->
            <div class="d-flex gap-3">
                <div class="p-3 bg-light rounded-3 text-center" style="min-width: 140px;">
                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Attendance</small>
                    <div class="fw-number fw-bold fs-5 text-tp-green"><?= number_format((float)$attRate, 1) ?>%</div>
                </div>
                <div class="p-3 bg-light rounded-3 text-center" style="min-width: 140px;">
                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Fee Dues</small>
                    <div class="fw-number fw-bold fs-5 <?= (float)$totalBalance > 0 ? 'text-tp-coral' : 'text-tp-green' ?>">
                        <?= format_currency($totalBalance) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── PARENT KPI CARDS (Exact 6-Color Palette & Poppins 700) ────────── -->
    <div class="row g-3 mb-4">
        <!-- Linked Children Count (Purple: #9985FB) -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon tp-purple">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-value"><?= count($children) ?></div>
                    <div class="stat-label">Linked Children</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Under your guardianship</small>
                </div>
            </div>
        </div>

        <!-- Attendance Rate % (Green: #4BA95F) -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon tp-green">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div>
                    <div class="stat-value text-tp-green"><?= number_format((float)$attRate, 1) ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Verified class turnout</small>
                </div>
            </div>
        </div>

        <!-- Outstanding Fee Balance (Coral: #FF908D) -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon tp-coral">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="stat-value <?= (float)$totalBalance > 0 ? 'text-tp-coral' : 'text-tp-green' ?>">
                        <?= format_currency($totalBalance) ?>
                    </div>
                    <div class="stat-label">Total Fee Due</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Pending fee balance</small>
                </div>
            </div>
        </div>

        <!-- Study Materials (Sky Blue: #77DDFA) -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon tp-sky-blue">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                </div>
                <div>
                    <div class="stat-value text-tp-sky-blue"><?= count($materials ?? []) ?></div>
                    <div class="stat-label">Study Materials</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Available downloads</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Exams & Invoices -->
        <div class="col-lg-8">
            <!-- Examination Marks & Rankings -->
            <div class="card-custom mb-4">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-tp-orange px-2 py-1"><i class="bi bi-award-fill"></i></span>
                        <h6 class="fw-bold mb-0">Examination Marks & Performance</h6>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center">Batch Rank</th>
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
                                        <td class="fw-bold text-dark"><?= e($r['exam_name']) ?></td>
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
                                                <span class="badge badge-tp-orange fw-number">#<?= $r['rank_in_batch'] ?></span>
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

            <!-- Fee Statements & Payments -->
            <div class="card-custom mb-4">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-tp-green px-2 py-1"><i class="bi bi-wallet2"></i></span>
                        <h6 class="fw-bold mb-0">Tuition Invoices & Payment Ledger</h6>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Period</th>
                                <th>Due Date</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end">Balance Due</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No fee records found for this student.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td class="font-monospace fw-bold"><?= e($inv['invoice_number']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= e($inv['fee_period']) ?></span></td>
                                        <td class="text-muted" style="font-size: 0.825rem;"><?= format_date($inv['due_date']) ?></td>
                                        <td class="text-end fw-number"><?= format_currency($inv['total_amount']) ?></td>
                                        <td class="text-end fw-number fw-bold <?= (float)$inv['balance_amount'] > 0 ? 'text-tp-coral' : 'text-tp-green' ?>">
                                            <?= format_currency($inv['balance_amount']) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-<?= $inv['status'] === 'paid' ? 'success' : 'warning' ?> text-capitalize">
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

            <!-- Class Study Materials -->
            <div class="card-custom">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-tp-sky-blue px-2 py-1"><i class="bi bi-file-earmark-pdf"></i></span>
                        <h6 class="fw-bold mb-0">Class Study Materials & Notes</h6>
                    </div>
                    <span class="badge bg-light text-dark border"><?= count($materials ?? []) ?> Items</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Class & Subject</th>
                                <th>Type</th>
                                <th class="text-end">Download</th>
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
                                        <td class="fw-bold text-dark"><?= e($m['title']) ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= e($m['batch_name']) ?></span>
                                            <small class="text-muted d-block"><?= e($m['subject_name']) ?></small>
                                        </td>
                                        <td><span class="badge badge-tp-sky-blue text-uppercase"><?= e($m['material_type']) ?></span></td>
                                        <td class="text-end">
                                            <?php if (!empty($m['file_path'])): ?>
                                                <a href="<?= url('academic/materials/download/' . $m['id']) ?>" class="btn btn-sm btn-outline-dark" style="font-size: 0.75rem;">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            <?php elseif (!empty($m['external_link'])): ?>
                                                <a href="<?= e($m['external_link']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" style="font-size: 0.75rem;">
                                                    <i class="bi bi-link-45deg"></i> Link
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

        <!-- Right Column: Homework & Announcements -->
        <div class="col-lg-4">
            <!-- Homework & Coursework Card -->
            <div class="card-custom mb-4">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-tp-orange px-2 py-1"><i class="bi bi-journal-text"></i></span>
                        <h6 class="fw-bold mb-0">Homework & Tasks</h6>
                    </div>
                    <span class="badge bg-light text-dark border"><?= count($assignments ?? []) ?></span>
                </div>
                <div class="p-3">
                    <?php if (empty($assignments)): ?>
                        <p class="text-muted text-center py-4 mb-0">No active homework assigned.</p>
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
                                        <span class="text-danger fw-bold"><i class="bi bi-clock me-1"></i>Due: <?= format_date($asn['due_date']) ?></span>
                                        <?php if (!empty($asn['attachment_path'])): ?>
                                            <a href="<?= url('academic/assignments/download/' . $asn['id']) ?>" class="btn btn-sm btn-outline-dark py-1 px-2" style="font-size: 0.7rem;">
                                                <i class="bi bi-download"></i> Task File
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Announcements -->
            <div class="card-custom">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-tp-black px-2 py-1"><i class="bi bi-bell-fill"></i></span>
                        <h6 class="fw-bold mb-0">Institute Announcements</h6>
                    </div>
                </div>
                <div class="p-3">
                    <?php if (empty($announcements)): ?>
                        <p class="text-muted text-center py-3 mb-0">No announcements for parents at this time.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($announcements as $an): ?>
                                <div class="list-group-item px-0 py-2 border-bottom">
                                    <div class="fw-bold mb-1 text-dark" style="font-size: 0.85rem;"><?= e($an['title']) ?></div>
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
<?php endif; ?>
