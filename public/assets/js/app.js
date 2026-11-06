/**
 * TuitionPro ERP — Client Application Scripts
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            if (sidebarBackdrop) {
                sidebarBackdrop.classList.toggle('d-none');
            }
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarBackdrop.classList.add('d-none');
        });
    }

    // 2. Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // 3. Prevent Double Form Submission
    const forms = document.querySelectorAll('form:not(.no-disable)');
    forms.forEach(form => {
        form.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.dataset.originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing...';
            }
        });
    });

    // 4. Client-side Table Filter helper
    const searchInputs = document.querySelectorAll('[data-table-filter]');
    searchInputs.forEach(input => {
        const tableId = input.dataset.tableFilter;
        const table = document.getElementById(tableId);
        if (table) {
            input.addEventListener('keyup', () => {
                const term = input.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }
    });
});

/**
 * Confirm action dialog helper
 */
function confirmAction(message, onConfirm) {
    if (confirm(message)) {
        if (typeof onConfirm === 'function') {
            onConfirm();
        } else if (typeof onConfirm === 'string') {
            window.location.href = onConfirm;
        }
    }
}

