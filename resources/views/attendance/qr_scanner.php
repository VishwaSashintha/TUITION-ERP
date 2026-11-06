<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Instant QR Code Scanner</h2>
        <p class="text-muted mb-0">High-speed contactless attendance check-in for student identity cards and badges.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?= url('attendance/take' . ($sessionId ? '?session_id=' . $sessionId : '')) ?>" class="btn btn-outline-primary">
            <i class="bi bi-table me-1"></i> Manual Roster Sheet
        </a>
        <a href="<?= url('attendance/history') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i> Logs
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Scanner Column -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-camera me-2 text-primary"></i>Live Scanner</h5>
            </div>
            <div class="card-body p-4 text-center">
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold">Active Class Session <span class="text-danger">*</span></label>
                    <select id="activeSessionSelect" class="form-select">
                        <?php if (empty($sessions)): ?>
                            <option value="">-- No sessions scheduled for today --</option>
                        <?php else: ?>
                            <?php foreach ($sessions as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $sessionId == $s['id'] ? 'selected' : '' ?>>
                                    <?= date('h:i A', strtotime($s['start_time'])) ?> — <?= e($s['batch_name']) ?> (<?= e($s['teacher_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Camera Preview / Viewfinder -->
                <div class="position-relative bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center mx-auto mb-3" style="min-height: 260px; max-width: 360px; border: 3px solid #6C4EE3;">
                    <div id="reader" style="width: 100%;"></div>
                    <div id="scannerOverlay" class="text-white text-center p-3">
                        <i class="bi bi-qr-code fs-1 text-primary d-block mb-2 animate-pulse"></i>
                        <p class="small mb-1 fw-bold">Scanner Active</p>
                        <small class="text-muted d-block">Hold student QR card in front of camera or use USB barcode scanner.</small>
                    </div>
                </div>

                <!-- Manual / USB Barcode Input Box -->
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" id="manualCodeInput" class="form-control font-monospace" placeholder="Scan or enter Admission No (e.g. APX-2026-0001)" autofocus>
                    <button class="btn btn-primary" type="button" id="manualScanBtn">Check In</button>
                </div>
                <small class="text-muted d-block mt-2">USB Barcode Readers automatically submit on scan (Enter key supported).</small>
            </div>
        </div>
    </div>

    <!-- Live Result & Feed Column -->
    <div class="col-lg-7">
        <!-- Result Alert Box -->
        <div id="scanResultBox" class="card border-0 shadow-sm mb-4 d-none">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3">
                    <div id="resultIcon" class="avatar rounded-circle d-flex align-items-center justify-content-center text-white fs-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 id="resultStudentName" class="fw-bold mb-0">Student Name</h5>
                            <span id="resultTime" class="badge bg-light text-dark font-monospace border">Time</span>
                        </div>
                        <div class="small text-muted mt-1">
                            Admission No: <strong id="resultStudentCode" class="font-monospace text-dark"></strong> |
                            Batch: <span id="resultBatchName"></span>
                        </div>
                        <div id="resultMessage" class="small mt-1 fw-bold"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Scans Live List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i>Recent Scans (Current Session)</h5>
                <span id="scanCountBadge" class="badge bg-primary rounded-pill">0 Recorded</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">Time</th>
                                <th>Admission No</th>
                                <th>Student Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="scanFeedBody">
                            <tr id="emptyFeedRow">
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-upc-scan fs-3 d-block mb-1 opacity-50"></i>
                                    Waiting for first student scan...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio Feedback (Web Audio API Synthesizer) -->
<script>
let scanCount = 0;

function playBeep(success = true) {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        if (success) {
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
            gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.15);
        } else {
            osc.frequency.setValueAtTime(220, audioCtx.currentTime); // A3
            gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.3);
        }
    } catch (e) {
        // audio context not allowed without prior user interaction
    }
}

async function processScan(code) {
    if (!code) return;
    const sessionSelect = document.getElementById('activeSessionSelect');
    const sessionId = sessionSelect.value;

    if (!sessionId) {
        alert('Please select an active class session before scanning.');
        return;
    }

    try {
        const res = await fetch('<?= url("attendance/scan-qr") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
            },
            body: new URLSearchParams({
                session_id: sessionId,
                admission_number: code,
                _csrf_token: '<?= csrf_token() ?>'
            })
        });

        const data = await res.json();
        const box = document.getElementById('scanResultBox');
        box.classList.remove('d-none');

        const emptyRow = document.getElementById('emptyFeedRow');
        if (emptyRow) emptyRow.remove();

        if (data.success) {
            playBeep(true);
            const isDup = data.already_marked;
            box.className = isDup ? 'card border-0 shadow-sm mb-4 border-start border-warning border-4' : 'card border-0 shadow-sm mb-4 border-start border-success border-4';
            document.getElementById('resultIcon').className = isDup ? 'avatar rounded-circle d-flex align-items-center justify-content-center text-white fs-3 bg-warning' : 'avatar rounded-circle d-flex align-items-center justify-content-center text-white fs-3 bg-success';
            document.getElementById('resultStudentName').textContent = data.student.name;
            document.getElementById('resultStudentCode').textContent = data.student.admission_number;
            document.getElementById('resultBatchName').textContent = data.student.batch;
            document.getElementById('resultTime').textContent = data.student.check_in_time;
            
            const msgEl = document.getElementById('resultMessage');
            msgEl.textContent = data.message;
            msgEl.className = isDup ? 'small mt-1 text-warning fw-bold' : 'small mt-1 text-success fw-bold';

            // Prepend row to table
            scanCount++;
            document.getElementById('scanCountBadge').textContent = scanCount + ' Recorded';
            const tbody = document.getElementById('scanFeedBody');
            const row = document.createElement('tr');
            row.className = 'table-success animate-fade-in';
            row.innerHTML = `
                <td class="ps-3 font-monospace small">${data.student.check_in_time}</td>
                <td><span class="badge bg-light text-dark border font-monospace">${data.student.admission_number}</span></td>
                <td class="fw-bold">${data.student.name}</td>
                <td><span class="badge ${isDup ? 'bg-warning text-dark' : 'bg-success'}">PRESENT</span></td>
            `;
            tbody.insertBefore(row, tbody.firstChild);
        } else {
            playBeep(false);
            box.className = 'card border-0 shadow-sm mb-4 border-start border-danger border-4';
            document.getElementById('resultIcon').className = 'avatar rounded-circle d-flex align-items-center justify-content-center text-white fs-3 bg-danger';
            document.getElementById('resultStudentName').textContent = 'Scan Error';
            document.getElementById('resultStudentCode').textContent = code;
            document.getElementById('resultBatchName').textContent = '-';
            document.getElementById('resultTime').textContent = new Date().toLocaleTimeString();
            const msgEl = document.getElementById('resultMessage');
            msgEl.textContent = data.message;
            msgEl.className = 'small mt-1 text-danger fw-bold';
        }
    } catch (err) {
        console.error(err);
    }
}

document.getElementById('manualScanBtn').addEventListener('click', () => {
    const input = document.getElementById('manualCodeInput');
    const val = input.value.trim();
    if (val) {
        processScan(val);
        input.value = '';
        input.focus();
    }
});

document.getElementById('manualCodeInput').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('manualScanBtn').click();
    }
});
</script>

