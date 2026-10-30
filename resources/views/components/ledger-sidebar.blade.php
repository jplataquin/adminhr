<div class="card border-0 shadow-sm sticky-top" style="top: 1rem; max-height: calc(100vh - 2rem); overflow: hidden; display: flex; flex-direction: column;" x-data="{
    search: '',
    expandedAccounts: {
        @foreach($accounts as $account)
            {{ $account->id }}: {{ $activeAccountId === $account->id ? 'true' : 'false' }},
        @endforeach
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
    }
}">
    <!-- Header -->
    <div class="card-header bg-body py-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-journal-bookmark-fill text-primary fs-5"></i>
            <div>
                <h6 class="mb-0 fw-bold">Ledger Explorer</h6>
                <small class="text-white-50">{{ $accounts->count() }} Accounts</small>
            </div>
        </div>
        <a href="{{ url('/ledger/accounts') }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="View all accounts">
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
    <div class="card-body p-0" style="overflow-y: auto; flex: 1;">
        <div class="list-group list-group-flush">
            @forelse($accounts as $account)
                @php
                    $isAccountActive = $activeAccountId === $account->id;
                    $ledgersJson = $account->Ledgers->map(fn($l) => ['id' => $l->id, 'name' => $l->name])->toJson();
                @endphp
                <div class="border-bottom" x-show="matchesSearch('{{ addslashes($account->name) }}', {{ $ledgersJson }})">
                    <!-- Account Header Row -->
                    <div class="d-flex align-items-center justify-content-between p-2 {{ $isAccountActive ? 'bg-primary-subtle text-primary border-start border-3 border-primary' : 'hover-bg' }}"
                         style="transition: background-color 0.15s ease;">
                        <button type="button" class="btn btn-sm p-0 me-1 text-white-50 border-0" @click="toggleAccount({{ $account->id }})">
                            <i class="bi" :class="expandedAccounts[{{ $account->id }}] ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                        </button>

                        <a href="{{ url('/ledger/account/' . $account->id) }}"
                           class="text-decoration-none flex-grow-1 text-truncate me-2 {{ $isAccountActive ? 'fw-bold text-primary' : 'text-body' }}"
                           title="{{ $account->name }}">
                            <i class="bi bi-folder2{{ $isAccountActive ? '-open' : '' }} me-1 text-warning"></i>
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
                            <div class="d-flex align-items-center justify-content-between py-1 pe-2 ps-2 {{ $isLedgerActive ? 'bg-primary text-white rounded me-2' : '' }}"
                                 x-show="ledgerMatches('{{ addslashes($ledger->name) }}')">
                                <a href="{{ url('/ledger/' . $ledger->id) }}"
                                   class="text-decoration-none text-truncate flex-grow-1 small {{ $isLedgerActive ? 'text-white fw-bold' : 'text-white-50' }}"
                                   title="{{ $ledger->name }}">
                                    <i class="bi bi-journal-text me-1 {{ $isLedgerActive ? 'text-white' : 'text-info' }}"></i>
                                    <span>{{ $ledger->name }}</span>
                                </a>

                                <span class="badge rounded-pill {{ $ledger->status == 'APRV' ? ($isLedgerActive ? 'bg-white text-dark' : 'bg-success') : ($isLedgerActive ? 'bg-white text-dark' : 'bg-warning text-dark') }}" style="font-size: 0.6rem;">
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
