<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — System Error | TuitionPro ERP</title>
    <!-- Fonts: Roboto Strictly Only -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #F7F8FA; font-family: 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; }
        .error-card { background: #ffffff; border-radius: 14px; box-shadow: 0 4px 20px rgba(12, 12, 12, 0.06); border: 1px solid #E5E7EB; padding: 48px; max-width: 500px; text-align: center; }
        .error-code { font-size: 72px; font-weight: 700; color: #FEA339; line-height: 1; margin-bottom: 16px; font-variant-numeric: tabular-nums; }
        .btn-primary-custom { background-color: #4BA95F; border-color: #4BA95F; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: background 0.15s ease; }
        .btn-primary-custom:hover { background-color: #3E8E4F; border-color: #3E8E4F; color: white; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">500</div>
        <h4 class="fw-bold mb-3" style="color: #0C0C0C;">Internal Server Error</h4>
        <p class="text-muted mb-4">Something went wrong while processing your request. Our technical team has been notified.</p>
        <a href="<?= url('dashboard') ?>" class="btn-primary-custom">
            <i class="bi bi-arrow-left"></i> Return to Dashboard
        </a>
    </div>
</body>
</html>
