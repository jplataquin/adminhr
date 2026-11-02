<div class="card border-0 shadow-sm sticky-top" style="top: 1rem; max-height: calc(100vh - 2rem); overflow: hidden; display: flex; flex-direction: column;" x-data="{
    search: '',
    activeAccountId: {{ $activeAccountId !== null ? $activeAccountId : 'null' }},
    activeLedgerId: {{ $activeLedgerId !== null ? $activeLedgerId : 'null' }},
    ledgerAccountMap: {
        @foreach($accounts as $account)
            @foreach($account->Ledgers as $ledger)
                {{ $ledger->id }}: {{ $account->id }},
            @endforeach
        @endforeach
    },
    accountCategoryMap: {
        @foreach($accounts as $account)
            @php $cat = trim((string)$account->type_category) !== '' ? trim((string)$account->type_category) : 'Uncategorized'; @endphp
            {{ $account->id }}: '{{ addslashes($cat) }}',
        @endforeach
    },
    expandedCategories: {
        @foreach($groupedAccounts as $catName => $catAccounts)
            '{{ addslashes($catName) }}': {{ ($activeCategoryName === $catName || ($activeAccountId && $catAccounts->contains('id', $activeAccountId))) ? 'true' : 'false' }},
        @endforeach
    },
    expandedAccounts: {
        @foreach($accounts as $account)
            {{ $account->id }}: {{ $activeAccountId === $account->id ? 'true' : 'false' }},
        @endforeach
    },
    init() {
        this.$nextTick(() => {
            this.scrollToActiveNode();
        });
        setTimeout(() => {
            this.scrollToActiveNode();
        }, 200);
    },
    toggleCategory(name) {
        this.expandedCategories[name] = !this.expandedCategories[name];
    },
    toggleAccount(id) {
        this.expandedAccounts[id] = !this.expandedAccounts[id];
    },
    expandAll() {
        @foreach($groupedAccounts as $catName => $catAccounts)
            this.expandedCategories['{{ addslashes($catName) }}'] = true;
        @endforeach
        @foreach($accounts as $account)
            this.expandedAccounts[{{ $account->id }}] = true;
        @endforeach
    },
    collapseAll() {
        @foreach($groupedAccounts as $catName => $catAccounts)
            this.expandedCategories['{{ addslashes($catName) }}'] = false;
        @endforeach
        @foreach($accounts as $account)
            this.expandedAccounts[{{ $account->id }}] = false;
        @endforeach
    },
    categoryMatchesSearch(catName, accountsList) {
        if (!this.search.trim()) return true;
        const q = this.search.toLowerCase();
        if (catName.toLowerCase().includes(q)) return true;
        return accountsList.some(acc => {
            if (acc.name && acc.name.toLowerCase().includes(q)) return true;
            return (acc.ledgers || []).some(l => l.name && l.name.toLowerCase().includes(q));
        });
    },
    matchesSearch(name, ledgers, catName = '') {
        if (!this.search.trim()) return true;
        const q = this.search.toLowerCase();
        if (catName && catName.toLowerCase().includes(q)) return true;
        if (name.toLowerCase().includes(q)) return true;
        return ledgers.some(l => l.name.toLowerCase().includes(q));
    },
    ledgerMatches(ledgerName) {
        if (!this.search.trim()) return true;
        return ledgerName.toLowerCase().includes(this.search.toLowerCase());
    },
    navigate(url, accountId, ledgerId, event = null) {
        if (event && (event.ctrlKey || event.metaKey)) {
            window.open(url, '_blank');
            return;
        }
        this.activeAccountId = accountId;
        this.activeLedgerId = ledgerId;
        if (accountId) {
            this.expandedAccounts[accountId] = true;
            const cat = this.accountCategoryMap[accountId];
            if (cat) this.expandedCategories[cat] = true;
        }
        this.$nextTick(() => {
            this.scrollToActiveNode();
        });
        if (window.innerWidth < 768 && window.$closeLedgerMobileSidebar) {
            window.$closeLedgerMobileSidebar();
        }
        if (window.$ledgerNavigate) {
            window.$ledgerNavigate(url, accountId, ledgerId);
        } else {
            window.location.href = url;
        }
    },
    handleExternalNavigate(detail) {
        if (detail.accountId !== undefined && detail.accountId !== null) {
            this.activeAccountId = detail.accountId;
        } else if (detail.ledgerId && this.ledgerAccountMap[detail.ledgerId]) {
            this.activeAccountId = this.ledgerAccountMap[detail.ledgerId];
        } else if (detail.url === '/ledger/accounts') {
            this.activeAccountId = null;
        }
        if (detail.ledgerId !== undefined) {
            this.activeLedgerId = detail.ledgerId;
        }
        if (this.activeAccountId) {
            this.expandedAccounts[this.activeAccountId] = true;
            const cat = this.accountCategoryMap[this.activeAccountId];
            if (cat) this.expandedCategories[cat] = true;
        }
        this.$nextTick(() => {
            this.scrollToActiveNode();
        });
        setTimeout(() => {
            this.scrollToActiveNode();
        }, 150);
    },
    scrollToActiveNode() {
        const container = document.getElementById('ledger-sidebar-scroll');
        if (!container) return;

        let targetEl = null;
        if (this.activeLedgerId) {
            targetEl = document.getElementById('sidebar-ledger-' + this.activeLedgerId);
        } else if (this.activeAccountId) {
            targetEl = document.getElementById('sidebar-account-' + this.activeAccountId);
        }

        if (!targetEl) {
            if (!this.activeAccountId && !this.activeLedgerId) {
                container.scrollTo({ top: 0, behavior: 'smooth' });
            }
            return;
        }

        const containerRect = container.getBoundingClientRect();
        const targetRect = targetEl.getBoundingClientRect();

        // Check if target is out of view
        const isAbove = targetRect.top < containerRect.top + 10;
        const isBelow = targetRect.bottom > containerRect.bottom - 10;

        if (isAbove || isBelow) {
            const offsetTop = targetEl.offsetTop - container.offsetTop;
            const targetScrollTop = offsetTop - (container.clientHeight / 2) + (targetEl.clientHeight / 2);

            container.scrollTo({
                top: Math.max(0, targetScrollTop),
                behavior: 'smooth'
            });
        }

        // Apply subtle pulse animation to draw focus
        targetEl.classList.remove('ledger-node-pulse');
        void targetEl.offsetWidth;
        targetEl.classList.add('ledger-node-pulse');
    }
}" @ledger-sidebar-set-active.window="handleExternalNavigate($event.detail)">
    <!-- Header -->
    <div class="card-header bg-body py-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-journal-bookmark-fill text-primary fs-5"></i>
            <div>
                <h6 class="mb-0 fw-bold">Ledger Explorer</h6>
                <small class="text-white-50">{{ $accounts->count() }} Accounts</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ url('/ledger/accounts') }}" 
               @click="if ($event.ctrlKey || $event.metaKey) { return; } $event.preventDefault(); navigate('{{ url('/ledger/accounts') }}', null, null, $event)"
               class="btn btn-sm btn-outline-primary py-1 px-2" 
               title="View all accounts">
                <i class="bi bi-grid-fill"></i> All
            </a>
            <button type="button" 
                    class="btn btn-sm btn-outline-secondary py-1 px-2 d-md-none" 
                    id="ledger-sidebar-mobile-close"
                    title="Hide sidebar"
                    @click="if (window.$closeLedgerMobileSidebar) window.$closeLedgerMobileSidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Search & Quick Filter -->
    <div class="p-2 border-bottom bg-body-tertiary">
        <div class="input-group input-group-sm mb-2">
            <span class="input-group-text bg-body border-end-0">
                <i class="bi bi-search text-white-50"></i>
            </span>
            <input type="text" class="form-control bg-body border-start-0" placeholder="Filter accounts, categories & ledgers..." x-model="search">
            <button class="btn btn-outline-secondary" type="button" x-show="search" @click="search = ''" style="display: none;">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="d-flex justify-content-between small text-white-50 px-1">
            <button type="button" class="btn btn-link btn-sm text-white-50 p-0 text-decoration-none" @click="expandAll()">
                <i class="bi bi-arrows-expand me-1"></i>Expand All
            </button>
            <button type="button" class="btn btn-link btn-sm text-white-50 p-0 text-decoration-none" @click="collapseAll()">
                <i class="bi bi-arrows-collapse me-1"></i>Collapse All
            </button>
        </div>
    </div>

    <!-- Accounts & Ledgers Grouped by Category Type Hierarchy -->
    <div class="card-body p-0" id="ledger-sidebar-scroll" style="overflow-y: auto; flex: 1;">
        <div class="list-group list-group-flush">
            @forelse($groupedAccounts as $categoryName => $catAccounts)
                @php
                    $catAccountsSummary = $catAccounts->map(fn($acc) => [
                        'id' => $acc->id,
                        'name' => $acc->name,
                        'ledgers' => $acc->Ledgers->map(fn($l) => ['id' => $l->id, 'name' => $l->name])->values()
                    ])->values()->toJson();
                    $categorySlug = \Illuminate\Support\Str::slug($categoryName);
                @endphp
                <div class="border-bottom" x-show="categoryMatchesSearch('{{ addslashes($categoryName) }}', {{ $catAccountsSummary }})" id="sidebar-category-{{ $categorySlug }}">
                    <!-- Category Header Row (Parent Node) -->
                    <div class="d-flex align-items-center justify-content-between p-2 bg-body-tertiary cursor-pointer hover-bg border-start border-3 border-primary-subtle"
                         style="transition: background-color 0.15s ease;"
                         @click="toggleCategory('{{ addslashes($categoryName) }}')">
                        <div class="d-flex align-items-center flex-grow-1 text-truncate me-2">
                            <button type="button" class="btn btn-sm p-0 me-2 text-white-50 border-0" @click.stop="toggleCategory('{{ addslashes($categoryName) }}')">
                                <i class="bi" :class="expandedCategories['{{ addslashes($categoryName) }}'] ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                            </button>
                            <i class="bi bi-tags-fill text-primary me-2"></i>
                            <span class="fw-bold small text-truncate text-body text-uppercase" title="{{ $categoryName }}">
                                {{ $categoryName }}
                            </span>
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border rounded-pill" style="font-size: 0.65rem;" title="{{ $catAccounts->count() }} accounts">
                                {{ $catAccounts->count() }}
                            </span>
                        </div>
                    </div>

                    <!-- Accounts List under this Category -->
                    <div x-show="expandedCategories['{{ addslashes($categoryName) }}']" x-transition style="display: none;" class="bg-body">
                        @foreach($catAccounts as $account)
                            @php
                                $isAccountActive = $activeAccountId === $account->id;
                                $ledgersJson = $account->Ledgers->map(fn($l) => ['id' => $l->id, 'name' => $l->name])->toJson();
                            @endphp
                            <div class="border-bottom border-secondary-subtle ps-2" x-show="matchesSearch('{{ addslashes($account->name) }}', {{ $ledgersJson }}, '{{ addslashes($categoryName) }}')" id="sidebar-account-{{ $account->id }}">
                                <!-- Account Header Row -->
                                <div class="d-flex align-items-center justify-content-between py-2 px-2 hover-bg cursor-pointer"
                                     :class="activeAccountId === {{ $account->id }} ? 'bg-primary-subtle text-primary border-start border-3 border-primary' : ''"
                                     style="transition: background-color 0.15s ease;"
                                     @click="navigate('{{ url('/ledger/account/' . $account->id) }}', {{ $account->id }}, null, $event)">
                                    <button type="button" class="btn btn-sm p-0 me-1 text-white-50 border-0" @click.stop="toggleAccount({{ $account->id }})">
                                        <i class="bi" :class="expandedAccounts[{{ $account->id }}] ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                                    </button>

                                    <a href="{{ url('/ledger/account/' . $account->id) }}"
                                       @click.prevent.stop="navigate('{{ url('/ledger/account/' . $account->id) }}', {{ $account->id }}, null, $event)"
                                       class="text-decoration-none flex-grow-1 text-truncate me-2 small"
                                       :class="activeAccountId === {{ $account->id }} ? 'fw-bold text-primary' : 'text-body'"
                                       title="{{ $account->name }}">
                                        <i class="bi me-1 text-warning" :class="activeAccountId === {{ $account->id }} ? 'bi-folder2-open' : 'bi-folder2'"></i>
                                        <span>{{ $account->name }}</span>
                                    </a>

                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge rounded-pill {{ $account->status == 'APRV' ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size: 0.65rem;">
                                            {{ $account->status }}
                                        </span>
                                        <span class="badge bg-secondary rounded-pill" style="font-size: 0.65rem;" title="{{ $account->Ledgers->count() }} ledgers">
                                            {{ $account->Ledgers->count() }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Ledgers Sub-list -->
                                <div x-show="expandedAccounts[{{ $account->id }}]" x-transition style="display: none;" class="bg-body-tertiary py-1 ps-3 border-top">
                                    @forelse($account->Ledgers as $ledger)
                                        @php
                                            $isLedgerActive = $activeLedgerId === $ledger->id;
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-between py-1 pe-2 ps-2 ledger-sidebar-item cursor-pointer"
                                             :class="activeLedgerId === {{ $ledger->id }} ? 'bg-primary text-white rounded me-2' : ''"
                                             x-show="ledgerMatches('{{ addslashes($ledger->name) }}')"
                                             id="sidebar-ledger-{{ $ledger->id }}"
                                             @click="navigate('{{ url('/ledger/' . $ledger->id) }}', {{ $account->id }}, {{ $ledger->id }}, $event)">
                                            <a href="{{ url('/ledger/' . $ledger->id) }}"
                                               @click.prevent.stop="navigate('{{ url('/ledger/' . $ledger->id) }}', {{ $account->id }}, {{ $ledger->id }}, $event)"
                                               class="text-decoration-none text-truncate flex-grow-1 small"
                                               :class="activeLedgerId === {{ $ledger->id }} ? 'text-white fw-bold' : 'text-white-50'"
                                               title="{{ $ledger->name }}">
                                                <i class="bi bi-journal-text me-1" :class="activeLedgerId === {{ $ledger->id }} ? 'text-white' : 'text-info'"></i>
                                                <span>{{ $ledger->name }}</span>
                                            </a>

                                            <span class="badge rounded-pill"
                                                  :class="activeLedgerId === {{ $ledger->id }} ? 'bg-white text-dark' : '{{ $ledger->status == 'APRV' ? 'bg-success' : 'bg-warning text-dark' }}'"
                                                  style="font-size: 0.6rem;">
                                                {{ $ledger->status }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="text-white-50 small py-1 ps-2 fst-italic">
                                            No ledgers yet
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-white-50 small">
                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                    No ledger accounts found.
                </div>
            @endforelse
        </div>
    </div>
</div>
