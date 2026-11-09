<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h4 class="fw-bold mb-1 text-tp-black">Teacher Payroll & Compensations</h4>
        <p class="text-muted mb-0">Automated multi-model salary, per-session, and revenue-share commission calculations.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('teacher-payments/calculate') ?>" class="btn btn-primary">
            <i class="bi bi-calculator me-1"></i> Calculate Period Payroll
        </a>
    </div>
</div>

<!-- KPI Cards (Tabular Figures | Standard Palette) -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green">Rs. <?= number_format((float)($summary['total_disbursed'] ?? 0), 2) ?></div>
                <div class="stat-label">Total Disbursed to Teachers</div>
                <small class="text-muted" style="font-size: 0.75rem;">Settled faculty vouchers</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon tp-purple">
                <i class="bi bi-file-earmark-check"></i>
            </div>
            <div>
                <div class="stat-value"><?= (int)($summary['total_vouchers'] ?? 0) ?></div>
                <div class="stat-label">Total Payment Vouchers Issued</div>
                <small class="text-muted" style="font-size: 0.75rem;">Official payroll vouchers</small>
            </div>
        </div>
    </div>
</div>

<!-- Calculations & Vouchers Tabs -->
<div class="card">
    <div class="card-header bg-white py-3 border-bottom">
        <ul class="nav nav-tabs card-header-tabs" id="payrollTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold text-tp-black" id="calcs-tab" data-bs-toggle="tab" data-bs-target="#calcs" type="button">
                    <i class="bi bi-calculator me-1"></i> Payroll Calculations (<?= count($calculations) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-tp-black" id="vouchers-tab" data-bs-toggle="tab" data-bs-target="#vouchers" type="button">
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
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Teacher</th>
                                <th>Period</th>
                                <th>Model</th>
                                <th class="text-center">Sessions</th>
                                <th class="text-center">Students</th>
                                <th class="text-end">Eligible Rev.</th>
                                <th class="text-end">Net Payable</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Action</th>
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
                                        <td class="text-muted fw-number"><?= $c['id'] ?></td>
                                        <td>
                                            <div class="fw-bold text-tp-black"><?= e($c['teacher_name']) ?></div>
                                            <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($c['teacher_phone']) ?></small>
                                        </td>
                                        <td><span class="badge badge-dark"><?= e($c['period_name']) ?></span></td>
                                        <td>
                                            <span class="badge badge-info text-capitalize">
                                                <?= str_replace('_', ' ', $c['compensation_model']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-number"><?= $c['total_sessions'] ?></td>
                                        <td class="text-center fw-number"><?= $c['total_students'] ?></td>
                                        <td class="text-end fw-number">Rs. <?= number_format((float)$c['eligible_revenue'], 2) ?></td>
                                        <td class="text-end fw-bold text-tp-green fs-6 fw-number">
                                            Rs. <?= number_format((float)$c['net_payable'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $stBadge = match($c['status']) {
                                                'paid'     => 'badge-success',
                                                'approved' => 'badge-purple',
                                                default    => 'badge-dark'
                                            };
                                            ?>
                                            <span class="badge <?= $stBadge ?> text-capitalize"><?= $c['status'] ?></span>
                                        </td>
                                        <td class="text-end">
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
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Voucher #</th>
                                <th>Date</th>
                                <th>Teacher</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th class="text-end">Amount</th>
                                <th>Processed By</th>
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
                                        <td class="font-monospace fw-bold text-tp-green"><?= e($v['voucher_number']) ?></td>
                                        <td class="font-monospace small"><?= date('d M Y', strtotime($v['payment_date'])) ?></td>
                                        <td class="fw-bold text-tp-black"><?= e($v['teacher_name']) ?></td>
                                        <td><span class="badge badge-dark text-capitalize"><?= str_replace('_', ' ', $v['payment_method']) ?></span></td>
                                        <td class="font-monospace small text-muted"><?= e($v['reference_number'] ?? '-') ?></td>
                                        <td class="text-end fw-bold text-tp-green fw-number">Rs. <?= number_format((float)$v['amount'], 2) ?></td>
                                        <td class="small text-muted"><?= e($v['paid_by_name'] ?? 'System') ?></td>
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
