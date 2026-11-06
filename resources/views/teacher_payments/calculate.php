<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Calculate Teacher Payroll</h2>
        <p class="text-muted mb-0">Select an active payment period to automatically compute teacher compensations.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="<?= url('teacher-payments') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Payroll
        </a>
    </div>
</div>

<!-- Period Selection Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('teacher-payments/calculate') ?>" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="fw-bold small mb-0"><i class="bi bi-calendar-range me-1 text-primary"></i> Select Period:</label>
            </div>
            <div class="col-md-4">
                <select name="period_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($periods as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $periodId == $p['id'] ? 'selected' : '' ?>>
                            <?= e($p['name']) ?> (<?= date('d M', strtotime($p['start_date'])) ?> — <?= date('d M Y', strtotime($p['end_date'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto text-muted small ms-auto">
                Automatic calculation engine evaluates sessions, student counts, and actual collections.
            </div>
        </form>
    </div>
</div>

<!-- Computed Calculations List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="fw-bold mb-0">Compensation Calculation Results</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Teacher</th>
                        <th>Compensation Model</th>
                        <th class="text-center">Sessions Held</th>
                        <th class="text-center">Students</th>
                        <th class="text-end">Batch Revenue</th>
                        <th class="text-end">Base Rate / %</th>
                        <th class="text-end">Net Computed (LKR)</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($calculatedList)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-2 d-block mb-2 opacity-50"></i>
                                No active teachers found to calculate for this period.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($calculatedList as $calc): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark"><?= e($calc['teacher_name']) ?></td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border text-capitalize">
                                        <?= str_replace('_', ' ', $calc['compensation_model']) ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $calc['total_sessions'] ?></td>
                                <td class="text-center"><?= $calc['total_students'] ?></td>
                                <td class="text-end">Rs. <?= number_format($calc['eligible_revenue'], 2) ?></td>
                                <td class="text-end">
                                    <?php if ($calc['compensation_model'] === 'revenue_share'): ?>
                                        <?= $calc['percentage'] ?>%
                                    <?php else: ?>
                                        Rs. <?= number_format($calc['rate'], 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold text-success fs-6">
                                    Rs. <?= number_format($calc['net_payable'], 2) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <form action="<?= url('teacher-payments/save-calculation') ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="teacher_id" value="<?= $calc['teacher_id'] ?>">
                                        <input type="hidden" name="period_id" value="<?= $calc['period_id'] ?>">
                                        <input type="hidden" name="adjustments" value="0.00">
                                        <input type="hidden" name="deductions" value="0.00">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="bi bi-check-circle me-1"></i> Approve & Save
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

