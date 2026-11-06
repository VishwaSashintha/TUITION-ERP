<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student ID Card — <?= e($student['full_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .id-card-wrapper {
            width: 320px;
            height: 480px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
            border: 2px solid #6C4EE3;
        }
        .id-header {
            background: linear-gradient(135deg, #6C4EE3, #5638C4);
            color: #ffffff;
            padding: 16px;
            text-align: center;
        }
        .inst-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .inst-sub {
            font-size: 10px;
            opacity: 0.85;
            margin-top: 2px;
        }
        .id-body {
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        .student-photo {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: #ECE8FD;
            border: 3px solid #6C4EE3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 800;
            color: #6C4EE3;
            margin-bottom: 12px;
        }
        .student-name {
            font-size: 16px;
            font-weight: 800;
            color: #1E1B4B;
            text-align: center;
            margin-bottom: 4px;
        }
        .admission-badge {
            background: #6C4EE3;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
        }
        .info-grid {
            width: 100%;
            font-size: 11px;
            line-height: 1.6;
            color: #475569;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
            margin-bottom: 12px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
        }
        .info-label { font-weight: 600; color: #64748B; }
        .info-val { font-weight: 700; color: #1E1B4B; }
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: auto;
        }
        .qr-placeholder {
            width: 80px;
            height: 80px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #6C4EE3;
        }
        .id-footer {
            background: #F7F6FB;
            border-top: 1px solid #e2e8f0;
            padding: 8px;
            text-align: center;
            font-size: 9px;
            color: #64748B;
        }
        .print-btn {
            margin-top: 20px;
            background: #6C4EE3;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
        }
        @media print {
            body { background: white; padding: 0; }
            .print-btn { display: none; }
            .id-card-wrapper { box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="id-card-wrapper">
    <div class="id-header">
        <div class="inst-title"><?= e(institute_name()) ?></div>
        <div class="inst-sub"><?= e($branch['name'] ?? 'Main Branch') ?> &bull; Student Pass</div>
    </div>

    <div class="id-body">
        <div class="student-photo">
            <?= strtoupper(substr($student['first_name'], 0, 1)) ?>
        </div>
        <div class="student-name"><?= e($student['full_name']) ?></div>
        <div class="admission-badge"><?= e($student['admission_number']) ?></div>

        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Grade / Stream:</span>
                <span class="info-val"><?= e($student['grade_level'] ?: 'A/L') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">School:</span>
                <span class="info-val"><?= e($student['school'] ?: 'General') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Emergency Contact:</span>
                <span class="info-val"><?= e($student['emergency_contact_phone'] ?: 'N/A') ?></span>
            </div>
        </div>

        <div class="qr-section">
            <!-- Simulated QR code displaying student admission number for instant camera check-in -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= urlencode($student['admission_number']) ?>" alt="QR Code" width="80" height="80">
            <small style="font-size: 8px; color: #94a3b8; margin-top: 4px;">SCAN FOR ATTENDANCE</small>
        </div>
    </div>

    <div class="id-footer">
        Valid for Academic Year <?= date('Y') ?> &bull; Property of Institute
    </div>
</div>

<button class="print-btn" onclick="window.print()">
    <i class="bi bi-printer-fill me-1"></i> Print Student ID Card
</button>

</body>
</html>

