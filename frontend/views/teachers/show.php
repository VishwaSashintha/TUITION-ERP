<div class="mb-4">
    <a href="<?= url('teachers') ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Teacher Directory</a>
</div>

<div class="card-custom p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 64px; height: 64px;">
                <?= strtoupper(substr($teacher['first_name'], 0, 1)) ?>
            </div>
            <div>
                <h4 class="fw-bold mb-1"><?= e($teacher['full_name']) ?></h4>
                <div class="d-flex flex-wrap align-items-center gap-2 text-muted" style="font-size: 0.85rem;">
                    <span class="badge bg-light text-dark border font-monospace fw-bold"><?= e($teacher['employee_id']) ?></span>
                    <span>&bull;</span>
                    <span><?= e($teacher['specialization'] ?: 'General Subject') ?></span>
                    <span>&bull;</span>
                    <span class="badge badge-custom badge-<?= $teacher['status'] === 'active' ? 'success' : 'danger' ?>">
                        <?= ucfirst($teacher['status']) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url("teacher-payments?teacher_id={$teacher['id']}") ?>" class="btn btn-primary-custom">
                <i class="bi bi-calculator me-1"></i> Calculate Compensation
            </a>
        </div>
    </div>

    <hr class="my-3 text-muted">

    <div class="row g-3" style="font-size: 0.875rem;">
        <div class="col-md-3">
            <div class="text-muted">Phone Number:</div>
            <div class="fw-semibold"><?= e($teacher['phone']) ?></div>
        </div>
        <div class="col-md-3">
            <div class="text-muted">Email:</div>
            <div class="fw-semibold"><?= e($teacher['email']) ?></div>
        </div>
        <div class="col-md-3">
            <div class="text-muted">Compensation Model:</div>
            <div class="fw-bold text-primary text-capitalize">
                <?= str_replace('_', ' ', $teacher['compensation_model']) ?>
                <?php if ($teacher['compensation_model'] === 'revenue_share'): ?>
                    (<?= (float)$teacher['default_percentage'] ?>%)
                <?php else: ?>
                    (<?= format_currency($teacher['default_rate']) ?>)
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-muted">Active Batches:</div>
            <div class="fw-semibold"><?= count($batches) ?> Assigned</div>
        </div>
    </div>
</div>

<!-- ─── ASSIGNED BATCHES & PAYMENT HISTORY ─────────────────────────────────── -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card-custom">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-collection me-2 text-primary"></i> Assigned Batches & Classes</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Batch Name</th>
                            <th>Subject</th>
                            <th>Branch</th>
                            <th>Class Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($batches)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No classes currently assigned.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($batches as $b): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($b['name']) ?></td>
                                    <td><?= e($b['subject_name']) ?></td>
                                    <td><?= e($b['branch_name'] ?? 'Main') ?></td>
                                    <td class="fw-semibold text-primary"><?= format_currency($b['monthly_fee']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-custom">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2 text-success"></i> Disbursed Payment Vouchers</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Voucher</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vouchers)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No payment vouchers processed yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vouchers as $v): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border font-monospace"><?= e($v['voucher_number']) ?></span></td>
                                    <td><?= format_date($v['payment_date']) ?></td>
                                    <td class="fw-bold text-success"><?= format_currency($v['amount']) ?></td>
                                    <td><span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;"><?= e($v['payment_method']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

