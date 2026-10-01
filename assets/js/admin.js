/**
 * FarmersBD — Admin Panel JavaScript
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initDarkMode();
    initNotificationDropdown();
    initSidebarToggle();
    initConfirmDialogs();
    initImagePreview();
    initDataTables();
    initStatusToggles();
    initAdminSearch();
    initAutoAlerts();
    initTypographyPreview();
});

// ── Notification Dropdown Handler ──────────────────────────────
function initNotificationDropdown() {
    const notifBtn = document.getElementById('notifDropdownBtn');
    const notifMenu = document.getElementById('notifDropdownMenu');
    if (!notifBtn || !notifMenu) return;

    notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isShown = notifMenu.classList.contains('show');
        document.querySelectorAll('.dropdown-menu.show').forEach(m => {
            if (m !== notifMenu) m.classList.remove('show');
        });
        if (!isShown) {
            notifMenu.classList.add('show');
            notifBtn.setAttribute('aria-expanded', 'true');
        } else {
            notifMenu.classList.remove('show');
            notifBtn.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('click', (e) => {
        if (!notifMenu.contains(e.target) && !notifBtn.contains(e.target)) {
            notifMenu.classList.remove('show');
            notifBtn.setAttribute('aria-expanded', 'false');
        }
    });
}


// ── Dark Mode Persistence ─────────────────────────────────────
function initDarkMode() {
    if (localStorage.getItem('farmersbd_admin_theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }

    const darkModeButtons = document.querySelectorAll('[title="ডার্ক মোড"], .dark-mode-toggle');
    darkModeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('farmersbd_admin_theme', isDark ? 'dark' : 'light');
        });
    });
}


// ── Sidebar Toggle (Mobile & Responsive) ──────────────────────
function initSidebarToggle() {
    const toggleBtn = document.getElementById('admin-sidebar-toggle') || document.getElementById('sidebar-toggle');
    const sidebar   = document.getElementById('adminSidebar');
    const overlay   = document.getElementById('sidebar-overlay') || document.getElementById('sidebarOverlay');
    if (!toggleBtn || !sidebar) return;

    function openSidebar() {
        sidebar.classList.add('show', 'sidebar-open');
        if (overlay) {
            overlay.classList.remove('d-none', 'hidden');
            overlay.classList.add('show');
        }
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('show', 'sidebar-open');
        if (overlay) {
            overlay.classList.remove('show');
            overlay.classList.add('d-none', 'hidden');
        }
        document.body.style.overflow = '';
    }

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.contains('show') || sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });

    overlay?.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (window.innerWidth > 991) {
            closeSidebar();
        }
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeSidebar();
    });
}

// ── Confirm Dialogs ───────────────────────────────────────────
function initConfirmDialogs() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            const message = el.dataset.confirm || 'আপনি কি নিশ্চিত?';
            if (!confirm(message)) e.preventDefault();
        });
    });

    document.querySelectorAll('form[data-confirm-form]').forEach(form => {
        form.addEventListener('submit', e => {
            const message = form.dataset.confirmForm || 'আপনি কি নিশ্চিত?';
            if (!confirm(message)) e.preventDefault();
        });
    });
}

// ── Image Preview on File Select ──────────────────────────────
function initImagePreview() {
    document.querySelectorAll('[data-preview-for]').forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;
            const targetId = input.dataset.previewFor;
            const img = document.getElementById(targetId);
            if (!img) return;
            const reader = new FileReader();
            reader.onload = e => { 
                img.src = e.target.result; 
                img.classList.remove('d-none', 'hidden'); 
            };
            reader.readAsDataURL(file);
        });
    });
}

// ── Simple Client-Side Search Filter ─────────────────────────
function initAdminSearch() {
    const searchInput = document.getElementById('table-search');
    const tbody       = document.querySelector('.admin-table tbody, .adm-table tbody');
    if (!searchInput || !tbody) return;

    searchInput.addEventListener('input', () => {
        const q = searchInput.value.toLowerCase().trim();
        tbody.querySelectorAll('tr').forEach(row => {
            row.style.display = (!q || row.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });
}

// ── Client-side sortable tables ──────────────────────────────
function initDataTables() {
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', () => {
            const table   = th.closest('table');
            const tbody   = table?.querySelector('tbody');
            if (!tbody) return;
            const col     = [...th.parentElement.children].indexOf(th);
            const asc     = th.dataset.order !== 'asc';
            th.dataset.order = asc ? 'asc' : 'desc';

            const rows = [...tbody.querySelectorAll('tr')];
            rows.sort((a, b) => {
                const av = a.cells[col]?.textContent.trim() || '';
                const bv = b.cells[col]?.textContent.trim() || '';
                return asc ? av.localeCompare(bv, 'bn') : bv.localeCompare(av, 'bn');
            });
            rows.forEach(r => tbody.appendChild(r));
        });
    });
}

// ── AJAX Status Toggles ───────────────────────────────────────
function initStatusToggles() {
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', async () => {
            const url      = toggle.dataset.url;
            const id       = toggle.dataset.id;
            const field    = toggle.dataset.field || 'is_active';
            const value    = toggle.checked ? 1 : 0;
            const csrf     = document.querySelector('meta[name="csrf-admin"]')?.content || '';

            try {
                const res = await fetch(url, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
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

// ── Toast Notification System ─────────────────────────────────
window.showToast = function(msg, type = 'success') {
    let container = document.getElementById('admin-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'admin-toast-container';
        container.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#f59e0b');
    toast.style.cssText = `background:${bgColor};color:#fff;padding:12px 20px;border-radius:10px;font-size:0.875rem;font-weight:500;box-shadow:0 10px 25px rgba(0,0,0,0.15);transition:all 0.3s ease;transform:translateY(20px);opacity:0;display:flex;align-items:center;gap:8px;`;
    toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'}"></i><span>${msg}</span>`;
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
};

// ── Auto-dismiss Flash Alerts ────────────────────────────────
function initAutoAlerts() {
    document.querySelectorAll('.alert[data-auto-dismiss], .adm-alert[data-auto-dismiss]').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.4s ease';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });
}

// ── Typography Live Preview ───────────────────────────────────
function initTypographyPreview() {
    document.querySelectorAll('[data-css-prop]').forEach(input => {
        input.addEventListener('input', () => {
            const prop = input.dataset.cssProp;
            const val  = input.value.trim();
            if (prop && val) document.documentElement.style.setProperty(prop, val);
        });
    });
}

