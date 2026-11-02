<x-ledger-layout>
    <div id="pageDoc" class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-body py-3 d-flex align-items-center justify-content-between">
            <h3 class="h5 mb-0">
                <i class="bi bi-search me-2 text-primary"></i>Advanced Ledger Search
            </h3>
            <div>
                <a href="{{ route('ledger') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back to Accounts
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- Filter Panel -->
            <div class="card bg-body-tertiary border-0 mb-4 shadow-sm">
                <div class="card-body p-3">
                    <form id="searchForm" class="row g-3">
                        <!-- Query (Ledger Name) -->
                        <div class="col-md-4">
                            <label for="filter_query" class="form-label small fw-bold">Ledger Name</label>
                            <input type="text" class="form-control form-control-sm" id="filter_query" placeholder="Search ledger name...">
                        </div>

                        <!-- Ledger Type Filter -->
                        <div class="col-md-4">
                            <label for="filter_ledger_type" class="form-label small fw-bold">Ledger Type</label>
                            <select class="form-select form-select-sm" id="filter_ledger_type">
                                <option value="">All Ledger Types</option>
                                @foreach($ledger_types as $type)
                                    <option value="{{ $type->id }}">{{ $type->text }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Account Category Type Filter -->
                        <div class="col-md-4">
                            <label for="filter_type_category" class="form-label small fw-bold">Account Category Type</label>
                            <select class="form-select form-select-sm" id="filter_type_category">
                                <option value="">All Categories</option>
                                @foreach($account_categories as $cat)
                                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Employee Name Filter -->
                        <div class="col-md-6">
                            <label for="filter_employee_name" class="form-label small fw-bold">Employee Name</label>
                            <input type="text" class="form-control form-control-sm" id="filter_employee_name" placeholder="Search by employee name...">
                        </div>

                        <!-- No Linked Employee Switch -->
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="filter_no_employee">
                                <label class="form-check-label small fw-bold" for="filter_no_employee">
                                    Records with no linked Employee
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-12 text-end border-top pt-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="resetBtn">
                                <i class="bi bi-x-circle me-1"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm" id="applySearchBtn">
                                <i class="bi bi-search me-1"></i>Search Ledgers
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Section -->
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small">Results</span>
                <span class="badge bg-secondary-subtle text-secondary small" id="resultCountBadge">Loading...</span>
            </div>

            <div id="list" class="mt-2"></div>

            <div class="text-center mt-4 pt-3 border-top">
                <button class="btn btn-secondary btn-sm" id="showMoreBtn">Show More</button>
            </div>
        </div>
    </div>

    <script type="module">
        import {$el, Template} from '/adarna.js';

        const t = new Template();

        let page = 1;
        let order = 'DESC';
        let orderBy = 'id';
        let loading = false;

        const searchForm = document.getElementById('searchForm');
        const filterQuery = document.getElementById('filter_query');
        const filterLedgerType = document.getElementById('filter_ledger_type');
        const filterTypeCategory = document.getElementById('filter_type_category');
        const filterEmployeeName = document.getElementById('filter_employee_name');
        const filterNoEmployee = document.getElementById('filter_no_employee');
        const resetBtn = document.getElementById('resetBtn');
        const showMoreBtn = document.getElementById('showMoreBtn');
        const listContainer = document.getElementById('list');
        const resultCountBadge = document.getElementById('resultCountBadge');

        // Toggle employee name input based on no-employee switch
        filterNoEmployee.addEventListener('change', () => {
            if (filterNoEmployee.checked) {
                filterEmployeeName.value = '';
                filterEmployeeName.disabled = true;
            } else {
                filterEmployeeName.disabled = false;
            }
        });

        function reinitalize() {
            page = 1;
            order = 'DESC';
            orderBy = 'id';
            $el.clear(listContainer);
            showMoreBtn.classList.remove('d-none');
        }

        function renderRows(data) {
            if (page === 1 && data.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-5 text-secondary">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-white-50"></i>
                        <p class="mb-0">No ledgers found matching the criteria.</p>
                    </div>
                `;
                showMoreBtn.classList.add('d-none');
                resultCountBadge.innerText = '0 records';
                return;
            }

            data.map(item => {
                let employeeDisplay = 'No linked employee';
                if (item.account && item.account.employee) {
                    employeeDisplay = item.account.employee.firstname + ' ' + item.account.employee.lastname;
                }

                let row = t.div({class: 'card mb-2 cursor-pointer shadow-sm hover-shadow'}, () => {
                    t.div({class: 'card-body p-3 d-flex justify-content-between align-items-center'}, () => {
                        t.div({class: 'min-w-0 flex-grow-1 me-3'}, () => {
                            t.div({class: 'd-flex align-items-center gap-2 mb-1'}, () => {
                                t.h5({class: "h6 mb-0 text-truncate text-white fw-bold"}, item.name);
                                if (item.ledger_type && item.ledger_type.text) {
                                    t.span({class: 'badge bg-primary-subtle text-primary border border-primary-subtle'}, item.ledger_type.text);
                                }
                                t.span({class: 'badge ' + (item.status === 'APRV' ? 'bg-success' : 'bg-warning text-dark')}, item.status);
                            });

                            t.div({class: 'small text-secondary d-flex flex-wrap gap-3'}, () => {
                                t.span({}, (el) => {
                                    el.innerHTML = '<i class="bi bi-folder2 me-1 text-info"></i>' + (item.account ? item.account.name : 'Unknown Account');
                                });

                                if (item.account && item.account.type_category) {
                                    t.span({}, (el) => {
                                        el.innerHTML = '<i class="bi bi-tag me-1 text-warning"></i>' + item.account.type_category;
                                    });
                                }

                                t.span({}, (el) => {
                                    el.innerHTML = '<i class="bi bi-person me-1 ' + (item.account && item.account.employee ? 'text-success' : 'text-danger') + '"></i>' + employeeDisplay;
                                });
                            });
                        });

                        t.div({class: 'text-end flex-shrink-0'}, () => {
                            const formattedAmount = item.total_amount_formatted !== undefined
                                ? item.total_amount_formatted
                                : (window.$numberFormat ? window.$numberFormat(item.total_amount || 0, 2) : Number(item.total_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                            
                            t.div({class: 'small text-white-50'}, 'Total Amount:');
                            t.div({class: 'fw-bold fs-6 text-primary'}, formattedAmount);
                        });
                    });
                });

                row.onclick = () => {
                    if (window.$ledgerNavigate) {
                        window.$ledgerNavigate('/ledger/' + item.id, item.ledger_account_id, item.id);
                    } else {
                        window.location.href = '/ledger/' + item.id;
                    }
                };

                $el.append(row).to(listContainer);
            });
        }

        function showData() {
            if (loading) return;
            loading = true;

            const params = {
                page: page,
                order: order,
                order_by: orderBy,
                limit: 15,
                query: filterQuery.value.trim(),
                ledger_type_id: filterLedgerType.value,
                type_category: filterTypeCategory.value,
                employee_name: filterEmployeeName.value.trim(),
                no_employee: filterNoEmployee.checked ? 1 : 0
            };

            $_GET('/api/ledgers', params).then(reply => {
                loading = false;

                if (reply.status <= 0) {
                    $ui.showError(reply);
                    return false;
                }

                if (reply.data.rows.length === 0 && page > 1) {
                    showMoreBtn.classList.add('d-none');
                    return false;
                }

                if (reply.data.rows.length < 15) {
                    showMoreBtn.classList.add('d-none');
                } else {
                    showMoreBtn.classList.remove('d-none');
                }

                renderRows(reply.data.rows);
                page++;

                if (page === 2) {
                    resultCountBadge.innerText = reply.data.rows.length + ' records shown';
                }
            }).catch(err => {
                loading = false;
                console.error(err);
            });
        }

        searchForm.onsubmit = (e) => {
            e.preventDefault();
            reinitalize();
            showData();
        };

        resetBtn.onclick = () => {
            searchForm.reset();
            filterEmployeeName.disabled = false;
            reinitalize();
            showData();
        };

        showMoreBtn.onclick = () => {
            showData();
        };

        reinitalize();
        showData();
    </script>
</x-ledger-layout>
