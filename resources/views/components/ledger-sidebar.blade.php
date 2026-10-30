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
    toggleAccount(id) {
        this.expandedAccounts[id] = !this.expandedAccounts[id];
    },
    expandAll() {
        @foreach($accounts as $account)
            this.expandedAccounts[{{ $account->id }}] = true;
        @endforeach
    },
    collapseAll() {
        @foreach($accounts as $account)
            this.expandedAccounts[{{ $account->id }}] = false;
        @endforeach
    },
    matchesSearch(name, ledgers) {
        if (!this.search.trim()) return true;
        const q = this.search.toLowerCase();
        if (name.toLowerCase().includes(q)) return true;
        return ledgers.some(l => l.name.toLowerCase().includes(q));
    },
    ledgerMatches(ledgerName) {
        if (!this.search.trim()) return true;
        return ledgerName.toLowerCase().includes(this.search.toLowerCase());
    },
    navigate(url, accountId, ledgerId) {
        this.activeAccountId = accountId;
        this.activeLedgerId = ledgerId;
        if (accountId) {
            this.expandedAccounts[accountId] = true;
        }
        this.$nextTick(() => {
            this.scrollToActiveNode();
        });
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
        <a href="{{ url('/ledger/accounts') }}" 
           @click.prevent="navigate('{{ url('/ledger/accounts') }}', null, null)"
           class="btn btn-sm btn-outline-primary py-1 px-2" 
           title="View all accounts">
            <i class="bi bi-grid-fill"></i> All
        </a>
    </div>

    <!-- Search & Quick Filter -->
    <div class="p-2 border-bottom bg-body-tertiary">
        <div class="input-group input-group-sm mb-2">
            <span class="input-group-text bg-body border-end-0">
                <i class="bi bi-search text-white-50"></i>
            </span>
            <input type="text" class="form-control bg-body border-start-0" placeholder="Filter accounts & ledgers..." x-model="search">
            <button class="btn btn-outline-secondary" type="button" x-show="search" @click="search = ''" style="display: none;">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="d-flex justify-content-between small text-white-50 px-1">
            <button type="button" class="btn btn-link btn-sm text-white-50 p-0 text-decoration-none" @click="expandAll()">
                <i class="bi bi-arrows-expand me-1"></i>Expand
            </button>
            <button type="button" class="btn btn-link btn-sm text-white-50 p-0 text-decoration-none" @click="collapseAll()">
                <i class="bi bi-arrows-collapse me-1"></i>Collapse
            </button>
        </div>
    </div>

    <!-- Accounts & Ledgers Hierarchy -->
    <div class="card-body p-0" id="ledger-sidebar-scroll" style="overflow-y: auto; flex: 1;">
        <div class="list-group list-group-flush">
            @forelse($accounts as $account)
                @php
                    $isAccountActive = $activeAccountId === $account->id;
                    $ledgersJson = $account->Ledgers->map(fn($l) => ['id' => $l->id, 'name' => $l->name])->toJson();
                @endphp
                <div class="border-bottom" x-show="matchesSearch('{{ addslashes($account->name) }}', {{ $ledgersJson }})" id="sidebar-account-{{ $account->id }}">
                    <!-- Account Header Row -->
                    <div class="d-flex align-items-center justify-content-between p-2 hover-bg"
                         :class="activeAccountId === {{ $account->id }} ? 'bg-primary-subtle text-primary border-start border-3 border-primary' : ''"
                         style="transition: background-color 0.15s ease;"
                         @click="navigate('{{ url('/ledger/account/' . $account->id) }}', {{ $account->id }}, null)">
                        <button type="button" class="btn btn-sm p-0 me-1 text-white-50 border-0" @click.stop="toggleAccount({{ $account->id }})">
                            <i class="bi" :class="expandedAccounts[{{ $account->id }}] ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                        </button>

                        <a href="{{ url('/ledger/account/' . $account->id) }}"
                           @click.prevent.stop="navigate('{{ url('/ledger/account/' . $account->id) }}', {{ $account->id }}, null)"
                           class="text-decoration-none flex-grow-1 text-truncate me-2"
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
                            <div class="d-flex align-items-center justify-content-between py-1 pe-2 ps-2 ledger-sidebar-item"
                                 :class="activeLedgerId === {{ $ledger->id }} ? 'bg-primary text-white rounded me-2' : ''"
                                 x-show="ledgerMatches('{{ addslashes($ledger->name) }}')"
                                 id="sidebar-ledger-{{ $ledger->id }}"
                                 @click="navigate('{{ url('/ledger/' . $ledger->id) }}', {{ $account->id }}, {{ $ledger->id }})">
                                <a href="{{ url('/ledger/' . $ledger->id) }}"
                                   @click.prevent.stop="navigate('{{ url('/ledger/' . $ledger->id) }}', {{ $account->id }}, {{ $ledger->id }})"
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
            @empty
                <div class="text-center py-4 text-white-50 small">
                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                    No ledger accounts found.
                </div>
            @endforelse
        </div>
    </div>
</div>
