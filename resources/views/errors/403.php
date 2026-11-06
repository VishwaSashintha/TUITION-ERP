<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied | TuitionPro ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #F7F6FB; font-family: system-ui, -apple-system, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(108, 78, 227, 0.08); padding: 48px; max-width: 520px; text-align: center; }
        .error-code { font-size: 80px; font-weight: 800; color: #dc3545; line-height: 1; margin-bottom: 16px; }
        .btn-primary-custom { background-color: #6C4EE3; border-color: #6C4EE3; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary-custom:hover { background-color: #5638C4; border-color: #5638C4; color: white; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">403</div>
        <h4 class="fw-bold mb-3">Access Forbidden</h4>
        <p class="text-muted mb-4">You do not possess the necessary privileges or institute permissions to view this resource.</p>
        <a href="<?= url('dashboard') ?>" class="btn btn-primary-custom">
            <i class="bi bi-arrow-left"></i> Return to Dashboard
        </a>
    </div>
</body>
</html>

