<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--primary-dark);">
            Enquiry #<?= $enquiry['id'] ?> — <?= e($enquiry['first_name'] . ' ' . $enquiry['last_name']) ?>
        </h3>
        <p class="text-muted mb-0 small">Registered on <?= e($enquiry['created_at']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($enquiry['status'] !== 'admitted'): ?>
            <form method="POST" action="<?= url('students/enquiries/convert') ?>" onsubmit="return confirm('Convert this enquiry into an official student admission?');">
                <?= csrf_field() ?>
                <input type="hidden" name="enquiry_id" value="<?= $enquiry['id'] ?>">
                <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-person-check-fill"></i> Convert to Student Admission
                </button>
            </form>
        <?php else: ?>
            <a href="<?= url("students/{$enquiry['student_pk']}") ?>" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-person-badge"></i> View Student Profile (<?= e($enquiry['admission_number']) ?>)
            </a>
        <?php endif; ?>
        <a href="<?= url('students/enquiries') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Enquiry Profile Card -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Prospect Profile</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 40%;">Full Name:</td>
                            <td class="fw-bold"><?= e($enquiry['first_name'] . ' ' . $enquiry['last_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone Number:</td>
                            <td>
                                <a href="tel:<?= e($enquiry['phone']) ?>" class="fw-bold text-decoration-none">
                                    <i class="bi bi-telephone-fill me-1"></i><?= e($enquiry['phone']) ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td><?= e($enquiry['email'] ?: '—') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Grade:</td>
                            <td><?= e($enquiry['grade'] ? "Grade {$enquiry['grade']}" : '—') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Course Interest:</td>
                            <td><span class="badge bg-light text-dark border"><?= e($enquiry['course_interest'] ?: 'General') ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Lead Source:</td>
                            <td class="text-capitalize"><?= str_replace('_', ' ', e($enquiry['source'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Current Status:</td>
                            <td>
                                <?php if ($enquiry['status'] === 'new'): ?>
                                    <span class="badge bg-primary">New</span>
                                <?php elseif ($enquiry['status'] === 'follow_up'): ?>
                                    <span class="badge bg-warning text-dark">In Follow-Up</span>
                                <?php elseif ($enquiry['status'] === 'admitted'): ?>
                                    <span class="badge bg-success">Admitted</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= e(ucfirst($enquiry['status'])) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Next Follow-Up:</td>
                            <td class="fw-bold text-warning"><?= e($enquiry['next_followup_date'] ?: 'Not scheduled') ?></td>
                        </tr>
                    </tbody>
                </table>

                <?php if (!empty($enquiry['notes'])): ?>
                    <div class="mt-3 p-3 bg-light rounded">
                        <span class="small fw-bold text-muted d-block mb-1">Registration Notes:</span>
                        <p class="small mb-0"><?= nl2br(e($enquiry['notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add Follow-Up Interaction Form -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Record Follow-Up Interaction</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('students/enquiries/followup') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="enquiry_id" value="<?= $enquiry['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Contact Channel</label>
                        <select name="channel" class="form-select">
                            <option value="call">Phone Call</option>
                            <option value="whatsapp">WhatsApp Message</option>
                            <option value="in_person">In-Person Campus Visit</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Prospect Reaction / Outcome</label>
                        <select name="outcome" class="form-select">
                            <option value="positive">Positive / Very Interested</option>
                            <option value="neutral">Neutral / Deciding</option>
                            <option value="scheduled_visit">Scheduled Campus Visit</option>
                            <option value="unreachable">Unreachable / No Answer</option>
                            <option value="not_interested">Not Interested / Closed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Interaction Notes <span class="text-danger">*</span></label>
                        <textarea name="response_notes" rows="3" class="form-control" placeholder="What was discussed? Next steps agreed?" required></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Update Status</label>
                            <select name="new_status" class="form-select">
                                <option value="follow_up" selected>In Follow-Up</option>
                                <option value="contacted">Contacted</option>
                                <option value="closed">Closed / Not Interested</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Next Follow-Up</label>
                            <input type="date" name="next_followup_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-chat-left-dots me-1"></i> Log Follow-Up
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Follow-Up Timeline / History -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Interaction & Follow-Up History</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($followups)): ?>
                    <div class="timeline">
                        <?php foreach ($followups as $f): ?>
                            <div class="border-start border-3 border-primary ps-3 pb-4 position-relative">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-light text-dark border text-uppercase">
                                        <i class="bi bi-telephone me-1"></i><?= e($f['channel']) ?>
                                    </span>
                                    <span class="small text-muted"><?= e($f['contact_date']) ?></span>
                                </div>
                                <div class="small fw-semibold text-secondary mb-1">
                                    Logged by <?= e($f['contacted_by_name']) ?> &bull; 
                                    <span class="text-capitalize"><?= str_replace('_', ' ', e($f['outcome'])) ?></span>
                                </div>
                                <p class="mb-0 text-dark"><?= nl2br(e($f['response_notes'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                        No follow-up interactions logged yet. Use the form to record contact.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
