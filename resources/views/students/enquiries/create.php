<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--primary-dark);">Register Student Enquiry</h3>
                <p class="text-muted mb-0 small">Capture prospective student information, subject interests, and initial contact preferences.</p>
            </div>
            <a href="<?= url('students/enquiries') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Enquiries
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="<?= url('students/enquiries/store') ?>">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="e.g. Nimal" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="e.g. Silva" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="e.g. 0771234567" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. nimal@example.com">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Grade / Standard</label>
                            <select name="grade" class="form-select">
                                <option value="">Select Grade</option>
                                <option value="6">Grade 6</option>
                                <option value="7">Grade 7</option>
                                <option value="8">Grade 8</option>
                                <option value="9">Grade 9</option>
                                <option value="10">Grade 10 (O/L)</option>
                                <option value="11">Grade 11 (O/L)</option>
                                <option value="12">Grade 12 (A/L)</option>
                                <option value="13">Grade 13 (A/L)</option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Subjects / Course Interest</label>
                            <input type="text" name="course_interest" class="form-control" placeholder="e.g. A/L Combined Mathematics, Physics">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Lead Source</label>
                            <select name="source" class="form-select">
                                <option value="walk_in">Walk-in Visit</option>
                                <option value="phone">Phone Inquiry</option>
                                <option value="social_media">Social Media (Facebook / Instagram)</option>
                                <option value="website">Website Form</option>
                                <option value="referral">Friend / Student Referral</option>
                                <option value="advertisement">Flyer / Banner / Advertisement</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Next Follow-Up Date</label>
                            <input type="date" name="next_followup_date" class="form-control" value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Initial Discussion Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Notes from conversation, batch preferences, special queries..."></textarea>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <a href="<?= url('students/enquiries') ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Enquiry
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
