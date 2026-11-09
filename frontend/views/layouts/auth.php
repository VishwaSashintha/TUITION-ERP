<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sign In') ?> — TuitionPro ERP</title>
    
    <!-- Fonts & Bootstrap 5: Roboto Strictly Only -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <style>
        body {
            background-color: var(--bg-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-container {
            width: 100%;
            max-width: 440px;
        }
        .auth-card {
            background: #FFFFFF;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(12, 12, 12, 0.05);
            border: 1px solid var(--border-color);
            padding: 2.25rem 2rem;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="text-center mb-4">
        <img src="<?= institute_logo() ?>" alt="<?= e(institute_name()) ?>" height="44" class="mb-2 img-fluid">
        <p class="text-muted mb-0" style="font-size: 0.85rem; font-weight: 500;"><?= e(institute_name()) ?></p>
    </div>

    <!-- Flash Alerts -->
    <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div><?= e($msg) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?= e($msg) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="auth-card">
        <?= $content ?>
    </div>

    <div class="text-center mt-4 text-muted" style="font-size: 0.8rem;">
        Sri Lankan Tuition Institute Management &bull; LKR Currency &bull; Asia/Colombo
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
