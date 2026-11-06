<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Teacher Payroll & Compensations</h2>
        <p class="text-muted mb-0">Automated multi-model salary, per-session, and revenue-share commission calculations.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('teacher-payments/calculate') ?>" class="btn btn-primary">
            <i class="bi bi-calculator me-1"></i> Calculate Period Payroll
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-success-subtle text-success p-3 me-3 fs-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Total Disbursed to Teachers</h6>
                    <h3 class="fw-bold mb-0 text-success">Rs. <?= number_format((float)($summary['total_disbursed'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3 fs-3">
                    <i class="bi bi-file-earmark-check"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Total Payment Vouchers Issued</h6>
                    <h3 class="fw-bold mb-0 text-primary"><?= (int)($summary['total_vouchers'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calculations & Vouchers Tabs -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <ul class="nav nav-tabs card-header-tabs" id="payrollTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold text-dark" id="calcs-tab" data-bs-toggle="tab" data-bs-target="#calcs" type="button">
                    <i class="bi bi-calculator me-1"></i> Payroll Calculations (<?= count($calculations) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-dark" id="vouchers-tab" data-bs-toggle="tab" data-bs-target="#vouchers" type="button">
                    <i class="bi bi-receipt me-1"></i> Disbursed Vouchers (<?= count($vouchers) ?>)
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="payrollTabContent">
            <!-- Calculations Tab -->
            <div class="tab-pane fade show active" id="calcs" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Teacher</th>
                                <th>Period</th>
                                <th>Model</th>
                                <th class="text-center">Sessions</th>
                                <th class="text-center">Students</th>
                                <th class="text-end">Eligible Rev.</th>
                                <th class="text-end">Net Payable</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($calculations)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="bi bi-calculator fs-1 d-block mb-2 opacity-50"></i>
                                        <h6>No payroll calculations generated yet.</h6>
                                        <p class="small mb-3">Run an automated calculation for an active payment period.</p>
                                        <a href="<?= url('teacher-payments/calculate') ?>" class="btn btn-sm btn-primary">Generate Calculation</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($calculations as $c): ?>
                                    <tr>
                                        <td class="ps-3 text-muted"><?= $c['id'] ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= e($c['teacher_name']) ?></div>
                                            <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($c['teacher_phone']) ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= e($c['period_name']) ?></span></td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border text-capitalize">
                                                <?= str_replace('_', ' ', $c['compensation_model']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center"><?= $c['total_sessions'] ?></td>
                                        <td class="text-center"><?= $c['total_students'] ?></td>
                                        <td class="text-end">Rs. <?= number_format((float)$c['eligible_revenue'], 2) ?></td>
                                        <td class="text-end fw-bold text-success fs-6">
                                            Rs. <?= number_format((float)$c['net_payable'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $stBadge = match($c['status']) {
                                                'paid'     => 'bg-success',
                                                'approved' => 'bg-primary',
                                                default    => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $stBadge ?> text-capitalize"><?= $c['status'] ?></span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?= url('teacher-payments/statement?id=' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-text me-1"></i> Payslip
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vouchers Tab -->
            <div class="tab-pane fade" id="vouchers" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Voucher #</th>
                                <th>Date</th>
                                <th>Teacher</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th class="text-end">Amount</th>
                                <th class="pe-3">Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vouchers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        No disbursed payment vouchers recorded.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($vouchers as $v): ?>
                                    <tr>
                                        <td class="ps-3 font-monospace fw-bold text-primary"><?= e($v['voucher_number']) ?></td>
                                        <td><?= date('d M Y', strtotime($v['payment_date'])) ?></td>
                                        <td class="fw-bold"><?= e($v['teacher_name']) ?></td>
                                        <td><span class="badge bg-light text-dark border text-capitalize"><?= str_replace('_', ' ', $v['payment_method']) ?></span></td>
                                        <td class="font-monospace small text-muted"><?= e($v['reference_number'] ?? '-') ?></td>
                                        <td class="text-end fw-bold text-success">Rs. <?= number_format((float)$v['amount'], 2) ?></td>
                                        <td class="pe-3 small text-muted"><?= e($v['paid_by_name'] ?? 'System') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

