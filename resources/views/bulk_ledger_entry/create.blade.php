<x-app-layout>
    <div id="pageDoc" class="card shadow-sm border-0 m-4">
        <div class="card-header bg-body py-3 d-flex align-items-center justify-content-between">
            <h3 class="h5 mb-0">Create Bulk Ledger Entry</h3>
            <a href="/bulk-ledger-entries" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <div class="card-body p-4">
            <div class="mb-4">
                <label for="bulk_description" class="form-label fw-semibold">Batch Description</label>
                <textarea id="bulk_description" class="form-control" rows="2" placeholder="Describe the purpose of this bulk ledger batch (e.g., Monthly Department Allowances, Vendor Disbursements)..."></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="h6 mb-0">Ledger Entry Items</h5>
                <button type="button" class="btn btn-success btn-sm" id="add-row-btn">
                    <i class="bi bi-plus-circle me-1"></i> Add Entry Row
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="entries-table">
                    <thead class="table-dark">
                        <tr>
                            <th style="min-width: 180px;">Account Ledger *</th>
                            <th style="min-width: 180px;">Ledger *</th>
                            <th style="width: 120px;">Type *</th>
                            <th style="width: 110px;">Tag *</th>
                            <th style="min-width: 180px;">Particular *</th>
                            <th style="width: 100px;">Qty *</th>
                            <th style="width: 120px;">Unit Amount *</th>
                            <th style="width: 130px;">Date *</th>
                            <th style="width: 120px;" class="text-end">Subtotal</th>
                            <th style="width: 50px;" class="text-center">Del</th>
                        </tr>
                    </thead>
                    <tbody id="entries-body">
                        <!-- Dynamic rows will be inserted here -->
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="8" class="text-end fw-bold">Total Batch Amount:</td>
                            <td class="text-end fw-bold text-primary" id="batch-grand-total">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="/bulk-ledger-entries" class="btn btn-secondary">Cancel</a>
                <button type="button" class="btn btn-primary px-4" id="submit-bulk-btn">
                    <i class="bi bi-send me-1"></i> Submit for Approval
                </button>
            </div>
        </div>
    </div>

    <script type="module">
        import {$_POST, $ui, $url} from '/adarna.js';

        const accountsData = @json($accounts);
        const entriesBody = document.getElementById('entries-body');
        const addRowBtn = document.getElementById('add-row-btn');
        const submitBtn = document.getElementById('submit-bulk-btn');
        const descriptionInput = document.getElementById('bulk_description');
        const grandTotalEl = document.getElementById('batch-grand-total');

        const todayStr = new Date().toISOString().split('T')[0];
        let rowCounter = 0;

        function addEntryRow() {
            rowCounter++;
            const rowId = 'row-' + rowCounter;

            const tr = document.createElement('tr');
            tr.id = rowId;

            // Build Account options
            let accountOptions = '<option value="">-- Select Account --</option>';
            accountsData.forEach(acc => {
                accountOptions += `<option value="${acc.id}">${acc.name}</option>`;
            });

            tr.innerHTML = `
                <td>
                    <select class="form-select form-select-sm account-select" required>
                        ${accountOptions}
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm ledger-select" required disabled>
                        <option value="">-- Select Ledger --</option>
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm type-select" required>
                        <option value="CRED">Credit (+)</option>
                        <option value="DEBI">Debit (-)</option>
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm tag-select" required>
                        <option value="REGU">Regular</option>
                        <option value="FLOA">Floating</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm particular-input" placeholder="Particular details" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" value="1" class="form-control form-control-sm quantity-input" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" value="0.00" class="form-control form-control-sm amount-input" required>
                </td>
                <td>
                    <input type="date" value="${todayStr}" class="form-control form-control-sm date-input" required>
                </td>
                <td class="text-end fw-semibold subtotal-cell">0.00</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="Remove row">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            entriesBody.appendChild(tr);

            const accountSelect = tr.querySelector('.account-select');
            const ledgerSelect = tr.querySelector('.ledger-select');
            const quantityInput = tr.querySelector('.quantity-input');
            const amountInput = tr.querySelector('.amount-input');
            const subtotalCell = tr.querySelector('.subtotal-cell');
            const deleteBtn = tr.querySelector('.delete-row-btn');

            accountSelect.onchange = () => {
                const selectedAccId = parseInt(accountSelect.value);
                const selectedAcc = accountsData.find(a => a.id === selectedAccId);

                ledgerSelect.innerHTML = '<option value="">-- Select Ledger --</option>';
                if (selectedAcc && selectedAcc.ledgers && selectedAcc.ledgers.length > 0) {
                    selectedAcc.ledgers.forEach(l => {
                        const typeInfo = l.ledger_type ? ` (${l.ledger_type.text})` : '';
                        ledgerSelect.innerHTML += `<option value="${l.id}">${l.name}${typeInfo}</option>`;
                    });
                    ledgerSelect.disabled = false;
                } else {
                    ledgerSelect.disabled = true;
                }
            };

            function updateRowSubtotal() {
                const q = parseFloat(quantityInput.value) || 0;
                const a = parseFloat(amountInput.value) || 0;
                const sub = q * a;
                subtotalCell.textContent = sub.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                updateGrandTotal();
            }

            quantityInput.oninput = updateRowSubtotal;
            amountInput.oninput = updateRowSubtotal;

            deleteBtn.onclick = () => {
                tr.remove();
                updateGrandTotal();
            };
        }

        function updateGrandTotal() {
            let total = 0;
            const rows = entriesBody.querySelectorAll('tr');
            rows.forEach(tr => {
                const q = parseFloat(tr.querySelector('.quantity-input')?.value) || 0;
                const a = parseFloat(tr.querySelector('.amount-input')?.value) || 0;
                total += (q * a);
            });
            grandTotalEl.textContent = total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        addRowBtn.onclick = () => {
            addEntryRow();
        };

        submitBtn.onclick = () => {
            const rows = entriesBody.querySelectorAll('tr');
            if (rows.length === 0) {
                alert('Please add at least one ledger entry row.');
                return;
            }

            const entries = [];
            let isValid = true;

            rows.forEach((tr, index) => {
                const accountId = tr.querySelector('.account-select').value;
                const ledgerId = tr.querySelector('.ledger-select').value;
                const type = tr.querySelector('.type-select').value;
                const tag = tr.querySelector('.tag-select').value;
                const particular = tr.querySelector('.particular-input').value.trim();
                const quantity = parseFloat(tr.querySelector('.quantity-input').value);
                const unitAmount = parseFloat(tr.querySelector('.amount-input').value);
                const date = tr.querySelector('.date-input').value;

                if (!accountId || !ledgerId || !particular || isNaN(quantity) || isNaN(unitAmount) || !date) {
                    isValid = false;
                    tr.classList.add('table-danger');
                } else {
                    tr.classList.remove('table-danger');
                    entries.push({
                        ledger_account_id: parseInt(accountId),
                        ledger_id: parseInt(ledgerId),
                        type: type,
                        tag: tag,
                        particular: particular,
                        quantity: quantity,
                        unit_amount: unitAmount,
                        date: date
                    });
                }
            });

            if (!isValid) {
                alert('Please fill out all required fields marked with * in highlighted rows.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting...';

            $_POST('/api/bulk-ledger-entry/create', {
                description: descriptionInput.value.trim(),
                entries: entries
            }).then(reply => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send me-1"></i> Submit for Approval';

                if (reply.status <= 0) {
                    $ui.showError(reply);
                    return;
                }

                $url('/bulk-ledger-entry/' + reply.data.id);
            }).catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send me-1"></i> Submit for Approval';
                alert('An unexpected error occurred while saving.');
            });
        };

        // Add 1 initial row
        addEntryRow();
    </script>
</x-app-layout>
