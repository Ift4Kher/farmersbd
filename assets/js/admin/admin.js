/**
 * FarmersBD — Admin Panel JavaScript (Tailwind Edition)
 * Handles: sidebar, modals, confirms, image preview, status toggles, toast alerts
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initConfirm();
    initImagePreview();
    initTableSearch();
    initStatusToggles();
    initModalClose();
    initTypographyPreview();
    initAutoAlerts();
    initDragDrop();
});

// ── Sidebar Toggle ─────────────────────────────────────────────
function initSidebar() {
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar   = document.getElementById('adminSidebar');
    const overlay   = document.getElementById('sidebarOverlay');
    if (!sidebar) return;

    function openSidebar() {
        sidebar.classList.add('sidebar-open');
        overlay?.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('sidebar-open');
        overlay?.classList.remove('show');
        document.body.style.overflow = '';
    }

    toggleBtn?.addEventListener('click', () => {
        sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });

    overlay?.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('sidebar-open');
            overlay?.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    // Keyboard: Escape closes sidebar
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeSidebar();
    });
}

// ── Confirm Dialogs ─────────────────────────────────────────────
function initConfirm() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            const msg = el.dataset.confirm || 'আপনি কি নিশ্চিত?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    document.querySelectorAll('form[data-confirm-form]').forEach(form => {
        form.addEventListener('submit', e => {
            const msg = form.dataset.confirmForm || 'আপনি কি নিশ্চিত?';
            if (!confirm(msg)) e.preventDefault();
        });
    });
}

// ── Image Preview ───────────────────────────────────────────────
function initImagePreview() {
    document.querySelectorAll('[data-preview-for]').forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;
            const img = document.getElementById(input.dataset.previewFor);
            if (!img) return;
            const reader = new FileReader();
            reader.onload = e => {
                img.src = e.target.result;
                img.classList.remove('hidden');
                img.parentElement?.querySelector('.upload-placeholder')?.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    });
}

// ── Table Client-Side Search ─────────────────────────────────────
function initTableSearch() {
    const searchInput = document.getElementById('table-search');
    const tbody       = document.querySelector('.adm-table tbody');
    if (!searchInput || !tbody) return;

    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        tbody.querySelectorAll('tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = (!q || text.includes(q)) ? '' : 'none';
        });
    });
}

// ── AJAX Status Toggles ──────────────────────────────────────────
function initStatusToggles() {
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', async () => {
            const { url, id, field = 'is_active' } = toggle.dataset;
            const value = toggle.checked ? 1 : 0;
            const csrf  = document.querySelector('meta[name="csrf-admin"]')?.content || '';

            try {
                const res = await fetch(url, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body:    `id=${id}&field=${field}&value=${value}&_csrf_token=${encodeURIComponent(csrf)}`,
                });
                const data = await res.json();
                if (!data.success) {
                    toggle.checked = !toggle.checked;
                    showToast(data.message || 'আপডেট ব্যর্থ হয়েছে।', 'error');
                } else {
                    showToast('সফলভাবে আপডেট হয়েছে।', 'success');
                }
            } catch {
                toggle.checked = !toggle.checked;
                showToast('নেটওয়ার্ক ত্রুটি।', 'error');
            }
        });
    });
}

// ── Modal Close ──────────────────────────────────────────────────
function initModalClose() {
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.adm-modal-overlay');
            if (modal) modal.classList.remove('show');
        });
    });

    // Close on overlay click
    document.querySelectorAll('.adm-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) overlay.classList.remove('show');
        });
    });
}

// ── Open Modal ───────────────────────────────────────────────────
window.openModal = function(id) {
    document.getElementById(id)?.classList.add('show');
};

window.closeModal = function(id) {
    document.getElementById(id)?.classList.remove('show');
};

// ── Toast Notification ───────────────────────────────────────────
window.showToast = function(msg, type = 'success') {
    const colors = {
        success: 'bg-emerald-500',
        error:   'bg-red-500',
        warning: 'bg-amber-500',
        info:    'bg-sky-500',
    };
    const container = getOrCreateToastContainer();
    const toast = document.createElement('div');
    const color = colors[type] || colors.success;
    toast.className = `${color} text-white text-sm font-medium px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 transform transition-all duration-300 translate-y-4 opacity-0`;
    toast.innerHTML = `<i class="bi bi-check-circle-fill"></i><span>${msg}</span>`;
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-4', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
};

function getOrCreateToastContainer() {
    let el = document.getElementById('adm-toast-container');
    if (!el) {
        el = document.createElement('div');
        el.id = 'adm-toast-container';
        el.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2';
        document.body.appendChild(el);
    }
    return el;
}

// ── Auto-dismiss flash alerts ────────────────────────────────────
function initAutoAlerts() {
    document.querySelectorAll('.adm-alert[data-auto-dismiss]').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.4s';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });
}

// ── Typography Live Preview ──────────────────────────────────────
function initTypographyPreview() {
    document.querySelectorAll('[data-css-prop]').forEach(input => {
        input.addEventListener('input', () => {
            const prop = input.dataset.cssProp;
            const val  = input.value.trim();
            if (prop && val) document.documentElement.style.setProperty(prop, val);
        });
    });
}

// ── Drag & Drop Upload ───────────────────────────────────────────
function initDragDrop() {
    document.querySelectorAll('.adm-upload-zone').forEach(zone => {
        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.classList.add('dragover');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('dragover');
            const input = zone.querySelector('input[type="file"]');
            if (input && e.dataTransfer.files.length > 0) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
        zone.addEventListener('click', () => {
            zone.querySelector('input[type="file"]')?.click();
        });
    });
}

// ── Sortable table columns ───────────────────────────────────────
document.querySelectorAll('th[data-sort]').forEach(th => {
    th.style.cursor = 'pointer';
    th.addEventListener('click', () => {
        const table = th.closest('table');
        const tbody = table?.querySelector('tbody');
        if (!tbody) return;
        const col = [...th.parentElement.children].indexOf(th);
        const asc = th.dataset.order !== 'asc';
        th.dataset.order = asc ? 'asc' : 'desc';
        const rows = [...tbody.querySelectorAll('tr')];
        rows.sort((a, b) => {
            const av = a.cells[col]?.textContent.trim() || '';
            const bv = b.cells[col]?.textContent.trim() || '';
            return asc ? av.localeCompare(bv, 'bn') : bv.localeCompare(av, 'bn');
        });
        rows.forEach(r => tbody.appendChild(r));

        // Update sort indicators
        th.closest('tr').querySelectorAll('th[data-sort]').forEach(t => {
            t.querySelector('.sort-icon')?.remove();
        });
        const icon = document.createElement('i');
        icon.className = `sort-icon bi bi-chevron-${asc ? 'up' : 'down'} ms-1`;
        th.appendChild(icon);
    });
});

// ── Notification badge refresh ───────────────────────────────────
async function refreshNotifBadge() {
    try {
        const res = await fetch('/farmersbd/admin/api/notifications-count.php');
        if (!res.ok) return;
        const data = await res.json();
        const badge = document.getElementById('notif-count');
        if (badge && data.count > 0) {
            badge.textContent = data.count > 99 ? '99+' : data.count;
            badge.style.display = 'inline-flex';
        } else if (badge) {
            badge.style.display = 'none';
        }
    } catch { /* silent */ }
}

// Refresh every 60 seconds
setInterval(refreshNotifBadge, 60000);
