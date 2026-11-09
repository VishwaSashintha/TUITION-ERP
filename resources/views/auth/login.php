<h4 class="fw-bold mb-2 text-center text-tp-black">Sign In to Your Account</h4>
<p class="text-muted text-center mb-4" style="font-size: 0.85rem;">Enter your credentials to access <?= e(institute_name()) ?></p>

<form action="<?= url('login') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="login" class="form-label-custom">Email or Username <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="login" name="login" value="<?= e(old('login', '')) ?>" placeholder="name@institute.lk or username" required autofocus>
        </div>
    </div>

    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label-custom mb-0">Password <span class="text-danger">*</span></label>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 justify-content-center py-2 mb-3">
        <i class="bi bi-box-arrow-in-right"></i> Sign In
    </button>
</form>

<hr class="my-3 text-muted">

<!-- Account Hierarchy Test Logins -->
<div class="mb-2">
    <div class="text-muted fw-bold text-center mb-2" style="font-size: 0.725rem; text-transform: uppercase; letter-spacing: 0.05em;">
        Account Hierarchy Test Accounts
    </div>
    <div class="d-flex flex-wrap gap-1 justify-content-center">
        <button type="button" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem;" onclick="fillLogin('superadmin@tuitionpro.lk', 'Password@123')">
            <i class="bi bi-shield-lock me-1"></i>Super Admin
        </button>
        <button type="button" class="btn btn-sm btn-secondary" style="font-size: 0.75rem;" onclick="fillLogin('apex.owner@tuitionpro.lk', 'Password@123')">
            <i class="bi bi-person-badge me-1"></i>Institute Admin
        </button>
        <button type="button" class="btn btn-sm btn-secondary" style="font-size: 0.75rem;" onclick="fillLogin('apex.reception@tuitionpro.lk', 'Password@123')">
            <i class="bi bi-headset me-1"></i>Receptionist
        </button>
        <button type="button" class="btn btn-sm btn-secondary" style="font-size: 0.75rem;" onclick="fillLogin('apex.fernando@tuitionpro.lk', 'Password@123')">
            <i class="bi bi-person-video3 me-1"></i>Teacher
        </button>
        <button type="button" class="btn btn-sm btn-secondary" style="font-size: 0.75rem;" onclick="fillLogin('apex.student1@tuitionpro.lk', 'Password@123')">
            <i class="bi bi-mortarboard me-1"></i>Student
        </button>
        <button type="button" class="btn btn-sm btn-secondary" style="font-size: 0.75rem;" onclick="fillLogin('apex.parent1@tuitionpro.lk', 'Password@123')">
            <i class="bi bi-people me-1"></i>Parent
        </button>
    </div>
</div>

<script>
function fillLogin(login, pwd) {
    document.getElementById('login').value = login;
    document.getElementById('password').value = pwd;
}
</script>
