<x-app-layout>
    <div id="pageDoc" class="container-fluid py-4" style="max-width: 1400px;" x-data="bulkLedgerForm(@js($accounts))">
        <!-- Page Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h3 class="h4 fw-bold mb-1">
                    <i class="bi bi-collection me-2 text-primary"></i>Create Bulk Ledger Entry
                </h3>
                <p class="text-muted small mb-0">Organize and batch-submit multiple ledger entries nested hierarchically by Account and Ledger.</p>
            </div>
            <a href="/bulk-ledger-entries" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <!-- Batch Description Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <label for="bulk_description" class="form-label fw-semibold">
                    <i class="bi bi-chat-left-text me-1 text-secondary"></i> Batch Description / Notes
                </label>
                <textarea id="bulk_description" x-model="description" class="form-control" rows="2" placeholder="Describe the purpose of this bulk batch (e.g., Monthly Department Allowances, Vendor Disbursements)..."></textarea>
            </div>
        </div>

        <!-- Account Ledgers Section Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="fw-bold mb-0">Account Ledgers</h5>
                <small class="text-muted">Structure entries under Account Ledgers, then Ledgers, then individual Entry items.</small>
            </div>
            <button type="button" @click="addAccount()" class="btn btn-primary btn-sm px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Add Account Ledger
            </button>
        </div>

        <!-- Accounts Container -->
        <div class="d-flex flex-column gap-4">
            <template x-for="(acc, accIndex) in accounts" :key="acc.uid">
                <!-- Single Account Card -->
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <!-- Account Card Header -->
                    <div class="card-header bg-primary bg-opacity-10 border-0 py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width: 320px; max-width: 650px;">
                            <span class="badge bg-primary px-3 py-2 text-uppercase fs-7 shadow-xs">
                                <i class="bi bi-wallet2 me-1"></i> Account Ledger
                            </span>
                            <!-- Searchable Account Ledger Dropdown -->
                            <div class="flex-grow-1 position-relative" @click.away="acc.searchOpen = false">
                                <button type="button" 
                                        class="form-select form-select-sm fw-semibold shadow-sm text-start text-truncate bg-body"
                                        :class="!acc.account_id ? 'text-secondary' : ''"
                                        @click="acc.searchOpen = !acc.searchOpen; if (acc.searchOpen) { $nextTick(() => $el.nextElementSibling.querySelector('input')?.focus()); }">
                                    <span x-text="getSelectedAccountLabel(acc)"></span>
                                </button>

                                <div x-show="acc.searchOpen" 
                                     x-transition
                                     class="dropdown-menu show p-2 position-absolute shadow-lg w-100" 
                                     style="z-index: 1050; max-height: 280px; left: 0; right: 0; top: 100%; display: none;">
                                    
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text bg-body-tertiary border-end-0">
                                            <i class="bi bi-search text-secondary"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control form-control-sm border-start-0" 
                                               placeholder="Search account or category..." 
                                               x-model="acc.searchTerm"
                                               @click.stop
                                               @keydown.escape="acc.searchOpen = false">
                                        <button type="button" 
                                                class="btn btn-outline-secondary btn-sm" 
                                                x-show="(acc.searchTerm || '').length > 0" 
                                                @click="acc.searchTerm = ''">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>

                                    <div class="list-group list-group-flush overflow-auto" style="max-height: 200px;">
                                        <button type="button" 
                                                class="list-group-item list-group-item-action py-1 px-2 border-0 text-start text-truncate small text-secondary fst-italic"
                                                :class="!acc.account_id ? 'active text-primary fw-bold' : ''"
                                                @click="selectAccount(acc, null)">
                                            -- Select Account Ledger * --
                                        </button>
                                        <template x-for="a in getFilteredAccounts(acc)" :key="a.id">
                                            <button type="button" 
                                                    class="list-group-item list-group-item-action py-1 px-2 border-0 text-start text-truncate small"
                                                    :class="acc.account_id === a.id ? 'active bg-primary text-white fw-bold' : ''"
                                                    @click="selectAccount(acc, a)">
                                                <span x-text="formatAccountLabel(a)"></span>
                                            </button>
                                        </template>
                                        <div x-show="getFilteredAccounts(acc).length === 0" class="text-muted text-center py-2 small">
                                            No matching accounts found
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="text-end">
                                <span class="small text-muted d-block">Account Subtotal</span>
                                <strong class="fs-6 text-primary" x-text="formatCurrency(calculateAccountTotal(acc))"></strong>
                            </div>
                            <button type="button" @click="removeAccount(accIndex)" class="btn btn-outline-danger btn-sm px-2" title="Delete Account Ledger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Account Card Body -->
                    <div class="card-body p-4">
                        <div class="mb-3 d-flex align-items-center justify-content-between">
                            <span class="fw-semibold text-secondary small text-uppercase tracking-wider">
                                <i class="bi bi-journals me-1"></i> Ledgers under this Account
                            </span>
                            <button type="button" @click="addLedger(acc)" class="btn btn-outline-primary btn-sm" :disabled="!acc.account_id">
                                <i class="bi bi-plus-circle me-1"></i> Add Ledger
                            </button>
                        </div>

                        <!-- Ledgers Container -->
                        <div class="d-flex flex-column gap-3">
                            <template x-for="(ledger, ledgerIndex) in acc.ledgers" :key="ledger.uid">
                                <!-- Single Ledger Card -->
                                <div class="card border border-2 border-light-subtle rounded-3 bg-body-tertiary">
                                    <div class="card-header bg-body border-bottom border-light-subtle py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 280px; max-width: 500px;">
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                                <i class="bi bi-book me-1"></i> Ledger
                                            </span>
                                            <div class="flex-grow-1">
                                                <select class="form-select form-select-sm" x-model.number="ledger.ledger_id" :disabled="!acc.account_id">
                                                    <option value="">-- Select Ledger * --</option>
                                                    <template x-for="l in getAvailableLedgers(acc.account_id)" :key="l.id">
                                                        <option :value="l.id" x-text="l.name + (l.ledger_type ? ' (' + l.ledger_type.text + ')' : '')" :selected="l.id === ledger.ledger_id"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-3">
                                            <span class="small text-muted">
                                                Ledger Subtotal: <strong class="text-success" x-text="formatCurrency(calculateLedgerSubtotal(ledger))"></strong>
                                            </span>
                                            <button type="button" @click="removeLedger(acc, ledgerIndex)" class="btn btn-outline-danger btn-sm p-1 px-2" title="Delete Ledger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Ledger Card Body: Individual Entries -->
                                    <div class="card-body p-3">
                                        <div class="d-flex flex-column gap-3 mb-3">
                                            <template x-for="(entry, entryIndex) in ledger.entries" :key="entry.uid">
                                                <!-- Single Entry Div -->
                                                <div class="card border shadow-xs bg-body rounded-3 p-3">
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-2 col-sm-6">
                                                            <label class="form-label form-label-sm small fw-semibold text-secondary mb-1">Type *</label>
                                                            <select class="form-select form-select-sm" x-model="entry.type">
                                                                <option value="CRED">Credit (+)</option>
                                                                <option value="DEBI">Debit (-)</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-2 col-sm-6">
                                                            <label class="form-label form-label-sm small fw-semibold text-secondary mb-1">Tag *</label>
                                                            <select class="form-select form-select-sm" x-model="entry.tag">
                                                                <option value="REGU">Regular</option>
                                                                <option value="FLOA">Floating</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-3 col-sm-12">
                                                            <label class="form-label form-label-sm small fw-semibold text-secondary mb-1">Particular *</label>
                                                            <input type="text" class="form-control form-control-sm" placeholder="Particular details" x-model="entry.particular" required>
                                                        </div>

                                                        <div class="col-md-1 col-sm-4">
                                                            <label class="form-label form-label-sm small fw-semibold text-secondary mb-1">Qty *</label>
                                                            <input type="number" step="0.01" min="0.01" class="form-control form-control-sm text-end" x-model.number="entry.quantity" required>
                                                        </div>

                                                        <div class="col-md-2 col-sm-4">
                                                            <label class="form-label form-label-sm small fw-semibold text-secondary mb-1">Unit Amount *</label>
                                                            <input type="number" step="0.01" min="0.01" class="form-control form-control-sm text-end" x-model.number="entry.unit_amount" required>
                                                        </div>

                                                        <div class="col-md-2 col-sm-4">
                                                            <label class="form-label form-label-sm small fw-semibold text-secondary mb-1">Date *</label>
                                                            <input type="date" class="form-control form-control-sm" x-model="entry.date" required>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                                        <span class="small text-muted">
                                                            Item Subtotal: <strong class="text-body" x-text="formatCurrency((entry.quantity || 0) * (entry.unit_amount || 0))"></strong>
                                                        </span>
                                                        <button type="button" @click="removeEntry(ledger, entryIndex)" class="btn btn-link text-danger p-0 text-decoration-none small">
                                                            <i class="bi bi-trash me-1"></i> Remove Entry
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Empty state for Entries -->
                                            <div x-show="ledger.entries.length === 0" class="text-center py-3 text-muted small bg-body rounded border" style="border-style: dashed !important;">
                                                <i class="bi bi-pencil-square me-1"></i> No entries added yet to this ledger.
                                            </div>
                                        </div>

                                        <button type="button" @click="addEntry(ledger)" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-plus me-1"></i> Add Entry
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty state for Ledgers -->
                            <div x-show="acc.ledgers.length === 0" class="text-center py-4 text-muted small bg-body rounded border" style="border-style: dashed !important;">
                                <i class="bi bi-journal-x fs-5 d-block mb-1 text-secondary"></i>
                                No ledgers added under this account yet. Click "Add Ledger" above to start.
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty state for Accounts -->
            <div x-show="accounts.length === 0" class="card border-0 shadow-sm p-5 text-center text-muted">
                <i class="bi bi-wallet2 fs-2 text-secondary mb-2"></i>
                <h6 class="fw-bold">No Account Ledgers Added</h6>
                <p class="small mb-3">Begin by adding an account ledger to structure your entries.</p>
                <div>
                    <button type="button" @click="addAccount()" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-plus-circle me-1"></i> Add Account Ledger
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating Bottom Summary Bar -->
        <div class="card border-0 shadow-lg mt-4 bg-body sticky-bottom" style="z-index: 10;">
            <div class="card-body p-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div>
                        <span class="text-muted small d-block">Accounts</span>
                        <span class="fw-bold fs-6" x-text="accounts.length"></span>
                    </div>
                    <div class="border-start ps-3">
                        <span class="text-muted small d-block">Ledgers</span>
                        <span class="fw-bold fs-6" x-text="totalLedgersCount"></span>
                    </div>
                    <div class="border-start ps-3">
                        <span class="text-muted small d-block">Total Entries</span>
                        <span class="fw-bold fs-6" x-text="totalEntriesCount"></span>
                    </div>
                    <div class="border-start ps-3">
                        <span class="text-muted small d-block">Total Batch Amount</span>
                        <span class="fw-bold fs-5 text-primary" x-text="formatCurrency(grandTotal)"></span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="/bulk-ledger-entries" class="btn btn-secondary px-3">Cancel</a>
                    <button type="button" class="btn btn-primary px-4 shadow-sm" @click="submitBatch()" :disabled="isSubmitting">
                        <span x-show="isSubmitting" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        <i x-show="!isSubmitting" class="bi bi-send me-1"></i> Submit for Approval
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bulkLedgerForm', (initialAccounts) => ({
                accountsData: initialAccounts || [],
                description: '',
                isSubmitting: false,
                accounts: [],

                init() {
                    this.addAccount();
                },

                get todayStr() {
                    return new Date().toISOString().split('T')[0];
                },

                generateUid() {
                    return 'uid_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
                },

                addAccount() {
                    const acc = {
                        uid: this.generateUid(),
                        account_id: '',
                        searchOpen: false,
                        searchTerm: '',
                        ledgers: []
                    };
                    this.accounts.push(acc);
                    this.addLedger(acc);
                },

                removeAccount(index) {
                    this.accounts.splice(index, 1);
                },

                formatAccountLabel(a) {
                    if (!a) return '';
                    const cat = a.type_category || a.category_type || 'Uncategorized';
                    return `${a.name} [${cat}]`;
                },

                getSelectedAccountLabel(acc) {
                    if (!acc.account_id) return '-- Select Account Ledger * --';
                    const found = this.accountsData.find(a => a.id === acc.account_id);
                    return found ? this.formatAccountLabel(found) : '-- Select Account Ledger * --';
                },

                getFilteredAccounts(acc) {
                    const term = (acc.searchTerm || '').toLowerCase().trim();
                    if (!term) return this.accountsData;
                    return this.accountsData.filter(a => {
                        const label = this.formatAccountLabel(a).toLowerCase();
                        return label.includes(term);
                    });
                },

                selectAccount(acc, accountObj) {
                    acc.account_id = accountObj ? accountObj.id : '';
                    acc.searchOpen = false;
                    acc.searchTerm = '';
                    this.onAccountChange(acc);
                },

                onAccountChange(acc) {
                    const availableLedgers = this.getAvailableLedgers(acc.account_id);
                    const validIds = availableLedgers.map(l => l.id);
                    acc.ledgers.forEach(ledger => {
                        if (!validIds.includes(ledger.ledger_id)) {
                            ledger.ledger_id = '';
                        }
                    });
                },

                getAvailableLedgers(accountId) {
                    if (!accountId) return [];
                    const acc = this.accountsData.find(a => a.id === parseInt(accountId));
                    return (acc && acc.ledgers) ? acc.ledgers : [];
                },

                addLedger(acc) {
                    const ledger = {
                        uid: this.generateUid(),
                        ledger_id: '',
                        entries: []
                    };
                    acc.ledgers.push(ledger);
                    this.addEntry(ledger);
                },

                removeLedger(acc, index) {
                    acc.ledgers.splice(index, 1);
                },

                addEntry(ledger) {
                    ledger.entries.push({
                        uid: this.generateUid(),
                        type: 'CRED',
                        tag: 'REGU',
                        particular: '',
                        quantity: 1,
                        unit_amount: 0.00,
                        date: this.todayStr
                    });
                },

                removeEntry(ledger, index) {
                    ledger.entries.splice(index, 1);
                },

                formatCurrency(amount) {
                    const val = parseFloat(amount) || 0;
                    return val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                calculateLedgerSubtotal(ledger) {
                    return ledger.entries.reduce((sum, e) => {
                        return sum + ((parseFloat(e.quantity) || 0) * (parseFloat(e.unit_amount) || 0));
                    }, 0);
                },

                calculateAccountTotal(acc) {
                    return acc.ledgers.reduce((sum, l) => {
                        return sum + this.calculateLedgerSubtotal(l);
                    }, 0);
                },

                get grandTotal() {
                    return this.accounts.reduce((sum, a) => {
                        return sum + this.calculateAccountTotal(a);
                    }, 0);
                },

                get totalLedgersCount() {
                    return this.accounts.reduce((sum, a) => sum + a.ledgers.length, 0);
                },

                get totalEntriesCount() {
                    return this.accounts.reduce((sum, a) => {
                        return sum + a.ledgers.reduce((lSum, l) => lSum + l.entries.length, 0);
                    }, 0);
                },

                submitBatch() {
                    if (this.totalEntriesCount === 0) {
                        alert('Please add at least one ledger entry.');
                        return;
                    }

                    const flattenedEntries = [];
                    let isValid = true;
                    let errorMessage = '';

                    for (const acc of this.accounts) {
                        if (!acc.account_id) {
                            isValid = false;
                            errorMessage = 'Please select an Account Ledger for all added account blocks.';
                            break;
                        }
                        for (const ledger of acc.ledgers) {
                            if (!ledger.ledger_id) {
                                isValid = false;
                                errorMessage = 'Please select a Ledger for all added ledger blocks.';
                                break;
                            }
                            for (const entry of ledger.entries) {
                                const particular = (entry.particular || '').trim();
                                const qty = parseFloat(entry.quantity);
                                const unitAmount = parseFloat(entry.unit_amount);
                                const date = entry.date;

                                if (!particular || isNaN(qty) || qty <= 0 || isNaN(unitAmount) || unitAmount <= 0 || !date) {
                                    isValid = false;
                                    errorMessage = 'Please ensure all entry fields (Particular, Quantity > 0, Unit Amount > 0, Date) are filled out.';
                                    break;
                                }

                                flattenedEntries.push({
                                    ledger_account_id: parseInt(acc.account_id),
                                    ledger_id: parseInt(ledger.ledger_id),
                                    type: entry.type,
                                    tag: entry.tag,
                                    particular: particular,
                                    quantity: qty,
                                    unit_amount: unitAmount,
                                    date: date
                                });
                            }
                            if (!isValid) break;
                        }
                        if (!isValid) break;
                    }

                    if (!isValid) {
                        alert(errorMessage);
                        return;
                    }

                    this.isSubmitting = true;

                    $_POST('/api/bulk-ledger-entry/create', {
                        description: this.description.trim(),
                        entries: flattenedEntries
                    }).then(reply => {
                        this.isSubmitting = false;

                        if (reply.status <= 0) {
                            $ui.showError(reply);
                            return;
                        }

                        $url('/bulk-ledger-entry/' + reply.data.id);
                    }).catch(err => {
                        this.isSubmitting = false;
                        alert('An unexpected error occurred while saving.');
                    });
                }
            }));
        });
    </script>
</x-app-layout>
