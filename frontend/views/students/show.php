<div class="mb-4">
    <a href="<?= url('students') ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Student Directory</a>
</div>

<!-- ─── STUDENT HEADER PROFILE CARD ────────────────────────────────────────── -->
<div class="card-custom p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 64px; height: 64px;">
                <?= strtoupper(substr($student['first_name'], 0, 1)) ?>
            </div>
            <div>
                <h4 class="fw-bold mb-1"><?= e($student['full_name']) ?></h4>
                <div class="d-flex flex-wrap align-items-center gap-2 text-muted" style="font-size: 0.85rem;">
                    <span class="badge bg-light text-dark border font-monospace fw-bold"><?= e($student['admission_number']) ?></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-building me-1"></i><?= e($student['school'] ?: 'School Unspecified') ?></span>
                    <span>&bull;</span>
                    <span class="badge badge-custom badge-<?= $student['status'] === 'active' ? 'success' : 'danger' ?>">
                        <?= ucfirst($student['status']) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url("students/{$student['id']}/id-card") ?>" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-qr-code me-1"></i> Print Student ID
            </a>
            <a href="<?= url("fees/invoices/create?student_id={$student['id']}") ?>" class="btn btn-primary-custom">
                <i class="bi bi-receipt me-1"></i> Issue Invoice
            </a>
        </div>
    </div>

    <hr class="my-3 text-muted">

    <div class="row g-3" style="font-size: 0.875rem;">
        <div class="col-md-3">
            <div class="text-muted">Contact Phone:</div>
            <div class="fw-semibold"><?= e($student['phone'] ?: '—') ?></div>
        </div>
        <div class="col-md-3">
            <div class="text-muted">Grade / Level:</div>
            <div class="fw-semibold"><?= e($student['grade_level'] ?: '—') ?></div>
        </div>
        <div class="col-md-3">
            <div class="text-muted">Attendance Rate:</div>
            <div class="fw-bold text-success"><?= $attSummary['percentage'] ?>% (<?= $attSummary['present'] ?>/<?= $attSummary['total'] ?> classes)</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted">Emergency Contact:</div>
            <div class="fw-semibold"><?= e($student['emergency_contact_name']) ?> (<?= e($student['emergency_contact_phone']) ?>)</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- ─── LEFT: ACADEMIC BATCHES & EXAM MARKS ──────────────────────────────── -->
    <div class="col-lg-7">
        <!-- Enrolled Batches -->
        <div class="card-custom mb-3">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-collection me-2 text-primary"></i> Enrolled Batches</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Batch Name</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($enrollments)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Not currently enrolled in any batches.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($enrollments as $enr): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($enr['batch_name']) ?></td>
                                    <td><?= e($enr['subject_name']) ?></td>
                                    <td><?= e($enr['teacher_name']) ?></td>
                                    <td class="fw-semibold text-primary"><?= format_currency($enr['monthly_fee']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Exam Performance -->
        <div class="card-custom">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-journal-check me-2 text-primary"></i> Examination Results</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Examination</th>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Rank</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($examMarks)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No examination results recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($examMarks as $em): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($em['exam_name']) ?></td>
                                    <td><?= e($em['subject_name']) ?></td>
                                    <td class="fw-semibold"><?= (float)$em['marks_obtained'] ?> / <?= (float)$em['max_marks'] ?></td>
                                    <td>
                                        <span class="badge badge-custom badge-<?= in_array($em['grade'], ['A', 'B'], true) ? 'success' : 'warning' ?>">
                                            Grade <?= e($em['grade']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $em['rank_in_batch'] ? 'Rank #' . $em['rank_in_batch'] : '—' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ─── RIGHT: INVOICES & PAYMENTS ────────────────────────────────────── -->
    <div class="col-lg-5">
        <!-- Invoices Card -->
        <div class="card-custom mb-3">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i> Fee Invoices</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Billed</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No invoices generated yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold font-monospace"><?= e($inv['fee_period']) ?></div>
                                        <small class="text-muted"><?= e($inv['invoice_number']) ?></small>
                                    </td>
                                    <td><?= format_currency($inv['total_amount']) ?></td>
                                    <td class="fw-bold text-danger"><?= format_currency($inv['balance_amount']) ?></td>
                                    <td>
                                        <span class="badge badge-custom badge-<?= $inv['status'] === 'paid' ? 'success' : ($inv['status'] === 'partially_paid' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $inv['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payments Collected -->
        <div class="card-custom">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-cash-coin me-2 text-success"></i> Payment Receipts</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No payments recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace"><?= e($pay['receipt_number']) ?></span>
                                    </td>
                                    <td><?= format_date($pay['payment_date']) ?></td>
                                    <td class="fw-bold text-success"><?= format_currency($pay['amount']) ?></td>
                                    <td>
                                        <a href="<?= url("fees/receipts/{$pay['id']}") ?>" target="_blank" class="btn btn-sm btn-light border py-0 px-2" title="Print Receipt">
                                            <i class="bi bi-printer"></i>
                                        </a>
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

