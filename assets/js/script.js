/**
 * FarmersBD — Main JavaScript
 */

'use strict';

// ── DOMContentLoaded ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initHeroCarousel();
    initSmoothScroll();
    initNewsletterForm();
    initLazyImages();
    autoHideAlerts();
    initImagePlaceholders();
    initGlobalSearch();
});

// ── Hero Carousel ─────────────────────────────────────────────
function initHeroCarousel() {
    const el = document.getElementById('heroCarousel');
    if (!el) return;
    const carousel = bootstrap.Carousel.getOrCreateInstance(el, {
        interval: 3500,
        ride: 'carousel',
        touch: true,
        pause: false
    });
    carousel.cycle();
}

// ── Smooth Scroll for anchor links ────────────────────────────
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const href = a.getAttribute('href');
            if (!href || href === '#' || a.hasAttribute('data-bs-toggle')) return;
            try {
                const target = document.querySelector(href);
                if (!target) return;
                e.preventDefault();
                const offset = document.getElementById('mainNavbar')?.offsetHeight ?? 68;
                window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - offset, behavior: 'smooth' });
            } catch (err) {
                // Ignore invalid selectors safely
            }
        });
    });
}

// ── URL resolution helper ────────────────────────────────────
function resolveUrl(urlStr, fallback) {
    if (!urlStr) return fallback;
    if (urlStr.startsWith('http://') || urlStr.startsWith('https://')) {
        try {
            const parsed = new URL(urlStr);
            if (parsed.hostname !== window.location.hostname) {
                return window.location.origin + parsed.pathname;
            }
        } catch (e) {
            return fallback;
        }
    }
    return urlStr;
}

// ── Newsletter AJAX Form ──────────────────────────────────────
function initNewsletterForm() {
    document.querySelectorAll('#footer-newsletter-form, #newsletter-form').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const btn   = form.querySelector('[type="submit"]');
            const email = form.querySelector('[name="email"]')?.value.trim();
            const csrf  = form.querySelector('[name="_csrf_token"]')?.value;

            if (!email) return;

            const original = btn.textContent;
            btn.disabled    = true;
            btn.textContent = 'অপেক্ষা করুন…';

            try {
                const targetUrl = resolveUrl(window.FARMERSBD?.urls?.newsletter, '/contact/newsletter-subscribe.php');
                const res = await fetch(targetUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    body:    `email=${encodeURIComponent(email)}&_csrf_token=${encodeURIComponent(csrf)}`,
                });
                const data = await res.json();
                showToast(data.message || 'সম্পন্ন হয়েছে।', data.success ? 'success' : 'danger');
                if (data.success) form.reset();
            } catch {
                showToast('একটি ত্রুটি ঘটেছে। পুনরায় চেষ্টা করুন।', 'danger');
            } finally {
                btn.disabled    = false;
                btn.textContent = original;
            }
        });
    });
}

// ── Lazy Load Images ──────────────────────────────────────────
function initLazyImages() {
    if (!('IntersectionObserver' in window)) return;
    const obs = new IntersectionObserver((entries, o) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const img = entry.target;
            if (img.dataset.src) img.src = img.dataset.src;
            img.classList.remove('lazy');
            o.unobserve(img);
        });
    }, { rootMargin: '100px' });
    document.querySelectorAll('img.lazy').forEach(img => obs.observe(img));
}

// ── Auto-hide alerts ──────────────────────────────────────────
function autoHideAlerts() {
    document.querySelectorAll('.alert.auto-dismiss').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 450);
        }, 5000);
    });
}

// ── Broken image placeholder ──────────────────────────────────
function initImagePlaceholders() {
    document.querySelectorAll('img:not([data-no-placeholder])').forEach(img => {
        img.addEventListener('error', function() {
            if (!this.src.includes('placeholder')) {
                this.src = FARMERSBD.urls.placeholder;
            }
        });
    });
}

// ── Toast notifications ──────────────────────────────────────
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `alert alert-${type} d-flex align-items-center shadow`;
    toast.style.cssText = 'min-width:280px;max-width:360px;padding:0.75rem 1rem;border-radius:10px;animation:fadeInRight 0.3s ease;';
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        <span>${escapeHtml(message)}</span>
        <button type="button" class="btn-close ms-auto btn-sm" style="font-size:0.7rem;"></button>
    `;
    toast.querySelector('.btn-close').addEventListener('click', () => toast.remove());
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 4500);
}

// ── Utility: HTML escape ──────────────────────────────────────
function escapeHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

// ── Helper: Highlight matching query terms ─────────────────────
function highlightMatch(text, query) {
    if (!text || !query) return escapeHtml(text || '');
    const escapedText = escapeHtml(text);
    const escapedQuery = escapeHtml(query.trim());
    if (!escapedQuery) return escapedText;
    const regex = new RegExp(`(${escapedQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return escapedText.replace(regex, '<mark class="search-highlight">$1</mark>');
}

// ── Global Search Modal Handler (Suggestive Searchbar) ────────
function initGlobalSearch() {
    const searchModal   = document.getElementById('navSearchModal');
    if (!searchModal) return;

    const searchInput   = document.getElementById('globalSearchInput');
    const searchForm    = document.getElementById('globalSearchForm');
    const clearBtn      = document.getElementById('clearSearchBtn');
    const catFilters    = document.querySelectorAll('.search-cat-filter');
    const liveArea      = document.getElementById('searchLiveResults');
    const listArea      = document.getElementById('searchResultsList');
    const countBadge    = document.getElementById('searchResultCount');
    const popularArea   = document.getElementById('popularSearchTags');
    const pillsSection  = document.getElementById('searchKeywordPillsSection');
    const pillsContainer= document.getElementById('searchKeywordPills');

    let selectedNavIndex = -1;

    // Keyboard shortcut (Ctrl + K / Cmd + K) to toggle modal
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            const bsModal = bootstrap.Modal.getOrCreateInstance(searchModal);
            if (searchModal.classList.contains('show')) {
                bsModal.hide();
            } else {
                bsModal.show();
            }
        }
    });

    // Auto-focus input when modal opens
    searchModal.addEventListener('shown.bs.modal', () => {
        searchInput?.focus();
    });

    // Reset selection helper
    function clearKeyboardSelection() {
        selectedNavIndex = -1;
        document.querySelectorAll('.search-item-selected').forEach(el => el.classList.remove('search-item-selected'));
    }

    // Clear search input button
    if (clearBtn && searchInput) {
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            clearBtn.classList.add('d-none');
            if (liveArea) liveArea.classList.add('d-none');
            if (popularArea) popularArea.classList.remove('d-none');
            if (listArea) listArea.innerHTML = '';
            if (pillsSection) pillsSection.classList.add('d-none');
            if (pillsContainer) pillsContainer.innerHTML = '';
            clearKeyboardSelection();
            searchInput.focus();
        });
    }

    // Category filter button click
    catFilters.forEach(btn => {
        btn.addEventListener('click', () => {
            catFilters.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            if (searchForm && btn.dataset.action) {
                searchForm.action = btn.dataset.action;
            }
            if (searchInput && btn.dataset.placeholder) {
                searchInput.placeholder = btn.dataset.placeholder;
                if (searchInput.value.trim().length >= 2) {
                    searchInput.dispatchEvent(new Event('input'));
                }
                searchInput.focus();
            }
        });
    });

    // Keyboard Navigation (ArrowUp, ArrowDown, Enter, Esc)
    searchInput?.addEventListener('keydown', (e) => {
        const items = listArea ? Array.from(listArea.querySelectorAll('.banner-result-card')) : [];
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedNavIndex++;
            if (selectedNavIndex >= items.length) selectedNavIndex = 0;
            updateItemSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedNavIndex--;
            if (selectedNavIndex < 0) selectedNavIndex = items.length - 1;
            updateItemSelection(items);
        } else if (e.key === 'Enter') {
            if (selectedNavIndex >= 0 && items[selectedNavIndex]) {
                e.preventDefault();
                items[selectedNavIndex].click();
            }
        } else if (e.key === 'Escape') {
            if (liveArea && !liveArea.classList.contains('d-none')) {
                liveArea.classList.add('d-none');
                if (popularArea) popularArea.classList.remove('d-none');
                clearKeyboardSelection();
            }
        }
    });

    function updateItemSelection(items) {
        items.forEach((item, index) => {
            if (index === selectedNavIndex) {
                item.classList.add('search-item-selected');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('search-item-selected');
            }
        });
    }

    // Live suggestic search on input with debounce
    let debounceTimer = null;
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        clearKeyboardSelection();

        const query = searchInput.value.trim();

        if (clearBtn) {
            if (query.length > 0) {
                clearBtn.classList.remove('d-none');
            } else {
                clearBtn.classList.add('d-none');
            }
        }

        if (query.length < 1) {
            if (liveArea) liveArea.classList.add('d-none');
            if (popularArea) popularArea.classList.remove('d-none');
            if (listArea) listArea.innerHTML = '';
            if (pillsSection) pillsSection.classList.add('d-none');
            if (pillsContainer) pillsContainer.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(async () => {
            if (listArea) {
                listArea.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-info me-2" role="status"></div>
                        <span>অনুসন্ধান ও পরামর্শ তৈরি করা হচ্ছে...</span>
                    </div>
                `;
                if (liveArea) liveArea.classList.remove('d-none');
                if (popularArea) popularArea.classList.add('d-none');
            }

            try {
                const searchApiUrl = (window.FARMERSBD?.urls?.searchApi || '/farmersbd/api/search.php') + '?q=' + encodeURIComponent(query);
                const res = await fetch(searchApiUrl);
                const data = await res.json();

                if (!data.success || !data.results || data.results.length === 0) {
                    if (pillsSection) pillsSection.classList.add('d-none');
                    if (listArea) {
                        listArea.innerHTML = `
                            <div class="p-3 text-center text-muted bg-white border border-info-subtle rounded-4">
                                <i class="bi bi-search me-1 text-info fs-5"></i> "${escapeHtml(query)}" এর জন্য কোনো ফলাফল পাওয়া যায়নি।
                            </div>
                        `;
                    }
                    if (countBadge) countBadge.textContent = '০ টি ফলাফল';
                    return;
                }

                // Render Suggestion Pills
                if (data.suggestions && data.suggestions.length > 0 && pillsSection && pillsContainer) {
                    let pillsHtml = '';
                    data.suggestions.forEach(sugg => {
                        pillsHtml += `
                            <button type="button" class="search-suggestion-pill" data-suggestion="${escapeHtml(sugg)}">
                                <i class="bi bi-search text-info me-1"></i>
                                <span>${highlightMatch(sugg, query)}</span>
                            </button>
                        `;
                    });
                    pillsContainer.innerHTML = pillsHtml;
                    pillsSection.classList.remove('d-none');

                    // Bind pill click handlers
                    pillsContainer.querySelectorAll('.search-suggestion-pill').forEach(pill => {
                        pill.addEventListener('click', () => {
                            const term = pill.dataset.suggestion;
                            if (term) {
                                searchInput.value = term;
                                searchInput.dispatchEvent(new Event('input'));
                                searchInput.focus();
                            }
                        });
                    });
                } else if (pillsSection) {
                    pillsSection.classList.add('d-none');
                }

                // Render Results
                if (countBadge) countBadge.textContent = `${data.results.length} টি ফলাফল`;

                let html = '';
                data.results.forEach(item => {
                    let fallbackIcon = 'capsule';
                    if (item.type === 'disease') fallbackIcon = 'virus';
                    else if (item.type === 'blog') fallbackIcon = 'journal-text';
                    else if (item.type === 'fish') fallbackIcon = 'water';

                    const iconOrImg = item.image 
                        ? `<img src="${escapeHtml(item.image)}" class="flat-result-thumb me-3" alt="${escapeHtml(item.title)}">` 
                        : `<div class="flat-result-icon-fallback me-3"><i class="bi bi-${fallbackIcon}"></i></div>`;

                    html += `
                        <a href="${escapeHtml(item.url)}" class="banner-result-card">
                            <div class="d-flex align-items-center overflow-hidden">
                                ${iconOrImg}
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge ${escapeHtml(item.badge_class)} rounded-pill" style="font-size:0.675rem;">
                                            ${escapeHtml(item.type_label)}
                                        </span>
                                    </div>
                                    <div class="fw-bold text-dark text-truncate" style="font-size:0.925rem;">
                                        ${highlightMatch(item.title, query)}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                                ${item.extra ? `<span class="fw-bold text-info small">${escapeHtml(item.extra)}</span>` : ''}
                                <i class="bi bi-chevron-right text-info small"></i>
                            </div>
                        </a>
                    `;
                });
                if (listArea) listArea.innerHTML = html;
            } catch (err) {
                console.error('Live search error:', err);
            }
        }, 250);
    });
}

// ── Global config (set by PHP pages) ─────────────────────────
window.FARMERSBD = window.FARMERSBD || {
    urls: {
        newsletter: './contact/newsletter-subscribe.php',
        cartAdd:    './cart/add.php',
        cartUpdate: './cart/update.php',
        cartRemove: './cart/remove.php',
        cartClear:  './cart/clear.php',
        placeholder:'./assets/images/general/placeholder.jpg',
        searchApi:  './api/search.php',
    }
};
