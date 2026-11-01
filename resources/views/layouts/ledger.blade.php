<x-app-layout :containerClass="'container-fluid px-lg-4'">
    @isset($header)
        <x-slot name="header">
            <div id="ledger-header-slot">
                {{ $header }}
            </div>
        </x-slot>
    @endisset

    <!-- Preload TomSelect CSS so it is ready across dynamic views -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <!-- Ledger Workspace with Resizable Sidebar -->
    <div class="d-flex flex-column flex-md-row gap-0 py-3 position-relative" id="ledger-workspace">
        
        <!-- Mobile Sidebar Navigation Toggle -->
        <div class="d-md-none w-100 mb-2" id="ledger-sidebar-mobile-toggle-wrapper">
            <button type="button" 
                    id="ledger-sidebar-mobile-toggle"
                    class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between py-2 px-3 shadow-sm"
                    aria-expanded="false" 
                    aria-controls="ledger-sidebar-container">
                <span class="d-flex align-items-center gap-2 fw-semibold">
                    <i class="bi bi-layout-sidebar-inset text-primary"></i>
                    <span>Ledger Navigation</span>
                </span>
                <span class="badge bg-secondary-subtle text-secondary-emphasis border rounded-pill px-2 py-1 small d-flex align-items-center gap-1">
                    <span id="ledger-sidebar-mobile-status">Show</span>
                    <i class="bi bi-chevron-down ms-1" id="ledger-sidebar-mobile-chevron"></i>
                </span>
            </button>
        </div>

        <!-- Mobile Sidebar Backdrop Overlay -->
        <div id="ledger-sidebar-backdrop" class="d-md-none" aria-hidden="true"></div>

        <!-- Persistent Resizable Sidebar Container -->
        <aside id="ledger-sidebar-container" class="d-none d-md-block position-relative flex-shrink-0" style="width: 320px; min-width: 240px; max-width: 650px;">
            <script>
                (function() {
                    if (window.innerWidth >= 768) {
                        try {
                            var w = localStorage.getItem('ledger_sidebar_width');
                            if (w) {
                                var p = parseInt(w, 10);
                                if (p >= 220 && p <= window.innerWidth * 0.7) {
                                    document.getElementById('ledger-sidebar-container').style.width = p + 'px';
                                }
                            }
                        } catch(e) {}
                    }
                })();
            </script>
            <x-ledger-sidebar />
        </aside>

        <!-- Draggable Resizer Splitter (desktop only) -->
        <div id="ledger-sidebar-resizer" class="d-none d-md-flex align-items-center justify-content-center" 
             role="separator" 
             aria-orientation="vertical" 
             aria-label="Resize sidebar" 
             tabindex="0"
             title="Drag to resize sidebar (double-click to reset)">
            <div class="resizer-line"></div>
        </div>

        <!-- Main Content Pane -->
        @php
            $sidebarHelper = app(\App\View\Components\LedgerSidebar::class);
            $activeAccountId = $sidebarHelper->activeAccountId;
            $activeLedgerId = $sidebarHelper->activeLedgerId;
        @endphp
        <main id="ledger-main-pane" class="flex-grow-1 ps-md-3" style="min-width: 0;">
            <!-- Loading Indicator Bar -->
            <div id="ledger-pane-loader" class="progress mb-2 d-none" style="height: 3px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%;"></div>
            </div>

            <!-- Dynamic Content Container -->
            <div id="ledger-content-container"
                 data-account-id="{{ $activeAccountId ?? '' }}"
                 data-ledger-id="{{ $activeLedgerId ?? '' }}">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Ledger Workspace Engine: Resizable Splitter & Dynamic SPA Navigator -->
    <script>
        (function() {
            // 1. Resizable Sidebar Handling
            const workspace = document.getElementById('ledger-workspace');
            const sidebar = document.getElementById('ledger-sidebar-container');
            const resizer = document.getElementById('ledger-sidebar-resizer');

            const MIN_WIDTH = 240;
            const DEFAULT_WIDTH = 320;

            function applySavedWidth() {
                if (!sidebar) return;
                if (window.innerWidth < 768) {
                    sidebar.style.width = '';
                    return;
                }
                try {
                    const saved = localStorage.getItem('ledger_sidebar_width');
                    if (saved) {
                        const parsed = parseInt(saved, 10);
                        const maxW = Math.min(750, Math.floor(window.innerWidth * 0.65));
                        if (parsed >= MIN_WIDTH && parsed <= maxW) {
                            sidebar.style.width = parsed + 'px';
                            return;
                        }
                    }
                } catch(e) {}
                sidebar.style.width = DEFAULT_WIDTH + 'px';
            }

            if (resizer && sidebar) {
                applySavedWidth();
                window.addEventListener('resize', () => {
                    if (window.innerWidth < 768) {
                        sidebar.style.width = '';
                    } else {
                        if (typeof hideTimeout !== 'undefined' && hideTimeout) {
                            clearTimeout(hideTimeout);
                            hideTimeout = null;
                        }
                        sidebar.classList.remove('show', 'is-hiding');
                        const bd = document.getElementById('ledger-sidebar-backdrop');
                        if (bd) bd.classList.remove('show');
                        applySavedWidth();
                    }
                });

                let isDragging = false;
                let startX = 0;
                let startWidth = 0;

                function onPointerDown(e) {
                    if (e.pointerType === 'mouse' && e.button !== 0) return;
                    if (window.innerWidth < 768) return;

                    isDragging = true;
                    startX = e.clientX;
                    startWidth = sidebar.getBoundingClientRect().width;
                    resizer.classList.add('is-resizing');
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';

                    if (resizer.setPointerCapture) {
                        try { resizer.setPointerCapture(e.pointerId); } catch(err){}
                    }
                    e.preventDefault();
                }

                function onPointerMove(e) {
                    if (!isDragging) return;
                    const deltaX = e.clientX - startX;
                    let newWidth = startWidth + deltaX;
                    const maxW = Math.min(750, Math.floor(window.innerWidth * 0.65));
                    if (newWidth < MIN_WIDTH) newWidth = MIN_WIDTH;
                    if (newWidth > maxW) newWidth = maxW;

                    sidebar.style.width = newWidth + 'px';
                }

                function onPointerUp(e) {
                    if (!isDragging) return;
                    isDragging = false;
                    resizer.classList.remove('is-resizing');
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';

                    if (resizer.releasePointerCapture && e.pointerId) {
                        try { resizer.releasePointerCapture(e.pointerId); } catch(err){}
                    }

                    try {
                        localStorage.setItem('ledger_sidebar_width', sidebar.style.width);
                    } catch(err){}
                }

                resizer.addEventListener('pointerdown', onPointerDown);
                resizer.addEventListener('pointermove', onPointerMove);
                resizer.addEventListener('pointerup', onPointerUp);
                resizer.addEventListener('pointercancel', onPointerUp);

                // Double click to reset to default width
                resizer.addEventListener('dblclick', () => {
                    sidebar.style.width = DEFAULT_WIDTH + 'px';
                    try { localStorage.removeItem('ledger_sidebar_width'); } catch(err){}
                });

                // Keyboard support for accessibility
                resizer.addEventListener('keydown', (e) => {
                    if (window.innerWidth < 768) return;
                    let currentWidth = parseInt(sidebar.style.width || DEFAULT_WIDTH, 10);
                    const maxW = Math.min(750, Math.floor(window.innerWidth * 0.65));

                    if (e.key === 'ArrowLeft') {
                        currentWidth = Math.max(MIN_WIDTH, currentWidth - 20);
                        sidebar.style.width = currentWidth + 'px';
                        try { localStorage.setItem('ledger_sidebar_width', sidebar.style.width); } catch(err){}
                        e.preventDefault();
                    } else if (e.key === 'ArrowRight') {
                        currentWidth = Math.min(maxW, currentWidth + 20);
                        sidebar.style.width = currentWidth + 'px';
                        try { localStorage.setItem('ledger_sidebar_width', sidebar.style.width); } catch(err){}
                        e.preventDefault();
                    } else if (e.key === 'Home') {
                        sidebar.style.width = DEFAULT_WIDTH + 'px';
                        try { localStorage.removeItem('ledger_sidebar_width'); } catch(err){}
                        e.preventDefault();
                    }
                });
            }

            // 2. Mobile Sidebar Navigation Toggle & Swipe Handling
            const mobileToggleBtn = document.getElementById('ledger-sidebar-mobile-toggle');
            const mobileStatus = document.getElementById('ledger-sidebar-mobile-status');
            const mobileChevron = document.getElementById('ledger-sidebar-mobile-chevron');
            const backdrop = document.getElementById('ledger-sidebar-backdrop');
            let hideTimeout = null;

            function setMobileSidebarState(isOpen) {
                if (!sidebar) return;

                if (hideTimeout) {
                    clearTimeout(hideTimeout);
                    hideTimeout = null;
                }

                if (isOpen) {
                    sidebar.classList.remove('is-hiding');
                    sidebar.classList.add('show');
                    if (backdrop) {
                        backdrop.classList.add('show');
                    }
                    if (mobileToggleBtn) {
                        mobileToggleBtn.setAttribute('aria-expanded', 'true');
                        mobileToggleBtn.classList.add('active');
                    }
                    if (mobileStatus) mobileStatus.textContent = 'Hide';
                    if (mobileChevron) {
                        mobileChevron.classList.remove('bi-chevron-down');
                        mobileChevron.classList.add('bi-chevron-up');
                    }
                } else {
                    if (mobileToggleBtn) {
                        mobileToggleBtn.setAttribute('aria-expanded', 'false');
                        mobileToggleBtn.classList.remove('active');
                    }
                    if (mobileStatus) mobileStatus.textContent = 'Show';
                    if (mobileChevron) {
                        mobileChevron.classList.remove('bi-chevron-up');
                        mobileChevron.classList.add('bi-chevron-down');
                    }
                    if (backdrop) {
                        backdrop.classList.remove('show');
                    }

                    if (sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        sidebar.classList.add('is-hiding');
                        hideTimeout = setTimeout(() => {
                            sidebar.classList.remove('is-hiding');
                            hideTimeout = null;
                        }, 240);
                    } else {
                        sidebar.classList.remove('is-hiding');
                    }
                }
            }

            window.$toggleLedgerMobileSidebar = function() {
                if (!sidebar) return;
                const isCurrentlyOpen = sidebar.classList.contains('show');
                setMobileSidebarState(!isCurrentlyOpen);
            };

            window.$closeLedgerMobileSidebar = function() {
                setMobileSidebarState(false);
            };

            window.$openLedgerMobileSidebar = function() {
                setMobileSidebarState(true);
            };

            if (mobileToggleBtn) {
                mobileToggleBtn.addEventListener('click', () => {
                    window.$toggleLedgerMobileSidebar();
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', () => {
                    window.$closeLedgerMobileSidebar();
                });
            }

            // Touch swipe gesture support for mobile view
            let sidebarTouchStartX = 0;
            let sidebarTouchStartY = 0;
            let sidebarTouchStartTime = 0;

            if (sidebar) {
                sidebar.addEventListener('touchstart', (e) => {
                    if (window.innerWidth >= 768 || e.touches.length !== 1) return;
                    sidebarTouchStartX = e.touches[0].clientX;
                    sidebarTouchStartY = e.touches[0].clientY;
                    sidebarTouchStartTime = Date.now();
                }, { passive: true });

                sidebar.addEventListener('touchend', (e) => {
                    if (window.innerWidth >= 768 || !sidebar.classList.contains('show')) return;
                    const deltaX = e.changedTouches[0].clientX - sidebarTouchStartX;
                    const deltaY = e.changedTouches[0].clientY - sidebarTouchStartY;
                    const duration = Date.now() - sidebarTouchStartTime;

                    // Swiping to the right dismisses / hides sidebar
                    if (deltaX > 45 && Math.abs(deltaX) > Math.abs(deltaY) * 1.2 && duration < 600) {
                        window.$closeLedgerMobileSidebar();
                    }
                }, { passive: true });
            }

            if (workspace) {
                let workspaceTouchStartX = 0;
                let workspaceTouchStartY = 0;
                let workspaceTouchStartTime = 0;

                workspace.addEventListener('touchstart', (e) => {
                    if (window.innerWidth >= 768 || e.touches.length !== 1) return;
                    workspaceTouchStartX = e.touches[0].clientX;
                    workspaceTouchStartY = e.touches[0].clientY;
                    workspaceTouchStartTime = Date.now();
                }, { passive: true });

                workspace.addEventListener('touchend', (e) => {
                    if (window.innerWidth >= 768 || sidebar.classList.contains('show')) return;
                    const deltaX = e.changedTouches[0].clientX - workspaceTouchStartX;
                    const deltaY = e.changedTouches[0].clientY - workspaceTouchStartY;
                    const duration = Date.now() - workspaceTouchStartTime;

                    // Swiping from the left edge towards the right opens sidebar
                    if (workspaceTouchStartX <= 40 && deltaX > 45 && Math.abs(deltaX) > Math.abs(deltaY) * 1.2 && duration < 600) {
                        window.$openLedgerMobileSidebar();
                    }
                }, { passive: true });
            }

            // 3. SPA Navigation without Page Refresh
            window.$ledgerNavigate = async function(url, accountId = null, ledgerId = null, pushHistory = true) {
                if (!url) return;

                if (window.innerWidth < 768 && window.$closeLedgerMobileSidebar) {
                    window.$closeLedgerMobileSidebar();
                }

                const urlObj = new URL(url, window.location.origin);
                const path = urlObj.pathname;

                // Resolve account/ledger IDs from URL if omitted
                if (accountId === null && ledgerId === null) {
                    const accMatch = path.match(/\/ledger\/account\/(\d+)/);
                    const ledMatch = path.match(/\/ledger\/(\d+)/);
                    if (accMatch) {
                        accountId = parseInt(accMatch[1], 10);
                    } else if (ledMatch) {
                        ledgerId = parseInt(ledMatch[1], 10);
                    } else if (path === '/ledger/accounts') {
                        accountId = null;
                        ledgerId = null;
                    }
                }

                // Update sidebar UI state immediately
                window.dispatchEvent(new CustomEvent('ledger-sidebar-set-active', {
                    detail: { accountId, ledgerId, url: path }
                }));

                // Close drawer modal if open
                if (window.$drawerModal && window.$drawerModal.isOpen) {
                    window.$drawerModal.close();
                }

                const loader = document.getElementById('ledger-pane-loader');
                const content = document.getElementById('ledger-content-container');
                if (loader) loader.classList.remove('d-none');
                if (content) content.style.opacity = '0.5';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        window.location.href = url;
                        return;
                    }

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newContent = doc.getElementById('ledger-content-container');
                    if (!newContent) {
                        window.location.href = url;
                        return;
                    }

                    // Extract server-resolved IDs from response (e.g. for entry, logs, or ledger)
                    const resolvedAccountId = newContent.dataset.accountId ? parseInt(newContent.dataset.accountId, 10) : null;
                    const resolvedLedgerId = newContent.dataset.ledgerId ? parseInt(newContent.dataset.ledgerId, 10) : null;

                    window.dispatchEvent(new CustomEvent('ledger-sidebar-set-active', {
                        detail: { 
                            accountId: resolvedAccountId !== null ? resolvedAccountId : accountId, 
                            ledgerId: resolvedLedgerId !== null ? resolvedLedgerId : ledgerId, 
                            url: path 
                        }
                    }));

                    if (doc.title) {
                        document.title = doc.title;
                    }

                    const newHeaderSlot = doc.getElementById('ledger-header-slot');
                    const curHeaderSlot = document.getElementById('ledger-header-slot');
                    if (newHeaderSlot && curHeaderSlot) {
                        curHeaderSlot.innerHTML = newHeaderSlot.innerHTML;
                    } else {
                        const newHeader = doc.querySelector('header');
                        const curHeader = document.querySelector('header');
                        if (newHeader && curHeader) {
                            curHeader.innerHTML = newHeader.innerHTML;
                        }
                    }

                    if (pushHistory) {
                        history.pushState({ url, accountId, ledgerId }, '', url);
                    }

                    content.innerHTML = newContent.innerHTML;

                    // Execute scripts in document order
                    const scripts = Array.from(content.querySelectorAll('script'));
                    for (const oldScript of scripts) {
                        const newScript = document.createElement('script');
                        for (const attr of Array.from(oldScript.attributes)) {
                            newScript.setAttribute(attr.name, attr.value);
                        }
                        newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    }

                    if (window.Alpine) {
                        try { window.Alpine.initTree(content); } catch(err){}
                    }

                    window.scrollTo({ top: 0, behavior: 'smooth' });

                } catch (err) {
                    console.error('Ledger navigation error:', err);
                    window.location.href = url;
                } finally {
                    if (loader) loader.classList.add('d-none');
                    if (content) content.style.opacity = '1';
                }
            };

            window.$ledgerReload = function() {
                if (window.$ledgerNavigate) {
                    window.$ledgerNavigate(window.location.href, null, null, false);
                } else {
                    window.location.reload();
                }
            };

            // Browser Back / Forward navigation support
            window.addEventListener('popstate', (e) => {
                if (location.pathname.startsWith('/ledger')) {
                    const state = e.state || {};
                    window.$ledgerNavigate(location.pathname + location.search, state.accountId ?? null, state.ledgerId ?? null, false);
                }
            });

            // Intercept internal clicks within ledger workspace
            if (workspace) {
                workspace.addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (!link) return;
                    if (link.target === '_blank' || link.hasAttribute('download')) return;
                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

                    try {
                        const targetUrl = new URL(link.href, window.location.origin);
                        if (targetUrl.origin === window.location.origin && 
                            targetUrl.pathname.startsWith('/ledger') && 
                            !targetUrl.pathname.includes('/print')) {
                            e.preventDefault();
                            window.$ledgerNavigate(link.href);
                        }
                    } catch(err){}
                });
            }
        })();
    </script>
</x-app-layout>
