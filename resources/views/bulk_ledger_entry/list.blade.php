<x-app-layout>
    <div id="pageDoc" class="card shadow-sm border-0 m-4">
        <div class="card-header bg-body py-3 d-flex align-items-center justify-content-between">
            <h3 class="h5 mb-0">Bulk Ledger Entries</h3>
            <a href="/bulk-ledger-entry/create" class="btn btn-primary btn-sm" id="createBulkBtn">
                <i class="bi bi-plus-lg me-1"></i> Create Bulk Entry
            </a>
        </div>

        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <input type="text" id="search" class="form-control" placeholder="Search by description...">
                </div>
                <div class="col-md-4">
                    <select id="status_filter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="PEND">Pending (PEND)</option>
                        <option value="APRV">Approved (APRV)</option>
                        <option value="REJC">Rejected (REJC)</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="bulk-ledger-entries-table">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Entries</th>
                            <th class="text-end">Total Amount</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="list">
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3" id="pagination-controls">
                <div class="text-muted small" id="pagination-info"></div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" id="prev-page" disabled>Previous</button>
                    <button class="btn btn-outline-secondary" id="next-page" disabled>Next</button>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import {$_GET, $el, $url} from '/adarna.js';

        let currentPage = 1;
        const limit = 10;
        let totalCount = 0;

        const listEl = document.getElementById('list');
        const searchInput = document.getElementById('search');
        const statusFilter = document.getElementById('status_filter');
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const paginationInfo = document.getElementById('pagination-info');

        function loadData() {
            listEl.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Loading...</td></tr>';

            const params = new URLSearchParams({
                page: currentPage,
                limit: limit,
                query: searchInput.value.trim(),
                status: statusFilter.value,
            });

            $_GET('/api/bulk-ledger-entries?' + params.toString()).then(reply => {
                if (reply.status <= 0) {
                    listEl.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Failed to load data.</td></tr>';
                    return;
                }

                const rows = reply.data.rows || [];
                totalCount = reply.data.total_count || 0;

                if (rows.length === 0) {
                    listEl.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No bulk ledger entries found.</td></tr>';
                } else {
                    listEl.innerHTML = '';
                    rows.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.className = 'cursor-pointer';
                        tr.onclick = (e) => {
                            const url = '/bulk-ledger-entry/' + item.id;
                            if (e.ctrlKey || e.metaKey) {
                                window.open(url, '_blank');
                            } else {
                                $url(url);
                            }
                        };

                        const statusBadge = item.status === 'APRV' 
                            ? '<span class="badge bg-success">APRV</span>'
                            : (item.status === 'REJC' 
                                ? '<span class="badge bg-danger">REJC</span>'
                                : '<span class="badge bg-warning text-dark">PEND</span>');

                        tr.innerHTML = `
                            <td><strong>#${item.id}</strong></td>
                            <td>${item.description ? item.description : '<span class="text-muted">(No description)</span>'}</td>
                            <td>${statusBadge}</td>
                            <td><span class="badge bg-secondary">${item.entries_count || 0} entries</span></td>
                            <td class="text-end fw-bold text-primary">${item.total_amount_formatted}</td>
                            <td>${item.created_by_user ? item.created_by_user.name : '-'}</td>
                            <td class="text-muted small">${new Date(item.created_at).toLocaleString()}</td>
                            <td class="text-center" onclick="event.stopPropagation();">
                                <a href="/bulk-ledger-entry/${item.id}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        `;
                        listEl.appendChild(tr);
                    });
                }

                updatePagination();
            });
        }

        function updatePagination() {
            const totalPages = Math.ceil(totalCount / limit) || 1;
            paginationInfo.textContent = `Showing page ${currentPage} of ${totalPages} (${totalCount} total)`;
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= totalPages;
        }

        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                loadData();
            }
        };

        nextBtn.onclick = () => {
            const totalPages = Math.ceil(totalCount / limit);
            if (currentPage < totalPages) {
                currentPage++;
                loadData();
            }
        };

        let searchTimeout;
        searchInput.oninput = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadData();
            }, 300);
        };

        statusFilter.onchange = () => {
            currentPage = 1;
            loadData();
        };

        loadData();
    </script>
</x-app-layout>
