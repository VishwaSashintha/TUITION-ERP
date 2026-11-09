// TuitionPro ERP — Client Application Scripts
// Modern UI/UX Interactive Handlers & Roboto Chart.js Defaults

document.addEventListener('DOMContentLoaded', () => {
    // ─── Set Chart.js Defaults to Roboto ─────────────────────────────
    if (window.Chart) {
        Chart.defaults.font.family = "'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#6B7280';
        Chart.defaults.borderColor = '#F3F4F6';
        if (Chart.defaults.plugins && Chart.defaults.plugins.tooltip) {
            Chart.defaults.plugins.tooltip.titleFont = {
                family: "'Roboto', sans-serif",
                weight: '600',
                size: 13
            };
            Chart.defaults.plugins.tooltip.bodyFont = {
                family: "'Roboto', sans-serif",
                weight: '400',
                size: 12
            };
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 8;
            Chart.defaults.plugins.tooltip.boxPadding = 4;
        }
    }

    // ─── Sidebar Mobile Toggle ───────────────────────────────────────
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

    // ─── Auto-dismiss Flash Alerts ───────────────────────────────────
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // ─── Form Submission Indicator ───────────────────────────────────
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

    // ─── Instant Client-Side Table Filter ────────────────────────────
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

function confirmAction(message, onConfirm) {
    if (confirm(message)) {
        if (typeof onConfirm === 'function') {
            onConfirm();
        } else if (typeof onConfirm === 'string') {
            window.location.href = onConfirm;
        }
    }
}
