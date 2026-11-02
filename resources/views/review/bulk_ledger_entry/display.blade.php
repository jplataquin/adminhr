<x-app-layout>
    <div id="pageDoc" class="card shadow-sm border-0 m-4">
        <div class="card-header bg-body py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <h3 class="h5 mb-0">Review Bulk Ledger Entry #{{ $bulk_ledger_entry->id }}</h3>
                @php
                    $statusClass = match($bulk_ledger_entry->status) {
                        'APRV' => 'bg-success',
                        'REJC' => 'bg-danger',
                        default => 'bg-warning text-dark',
                    };
                @endphp
                <span class="badge {{ $statusClass }} fs-6">{{ $bulk_ledger_entry->status }}</span>
            </div>
            <a href="/review/bulk-ledger-entries" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Review List
            </a>
        </div>

        <div class="card-body p-4">
            <!-- Metadata card -->
            <div class="row g-3 p-3 bg-body-tertiary rounded border mb-4">
                <div class="col-md-6">
                    <div class="text-muted small">Description</div>
                    <div class="fw-semibold">{{ $bulk_ledger_entry->description ?: '(No description)' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Total Batch Amount</div>
                    <div class="fw-bold fs-5 text-primary">{{ $bulk_ledger_entry->total_amount_formatted }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Total Entries</div>
                    <div class="fw-bold fs-5">{{ $bulk_ledger_entry->Entries->count() }} items</div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Submitted By</div>
                    <div>{{ $bulk_ledger_entry->CreatedByUser->name ?? 'Unknown' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Submitted At</div>
                    <div>{{ $bulk_ledger_entry->created_at->format('M d, Y h:i A') }}</div>
                </div>
                @if($bulk_ledger_entry->status === 'APRV')
                    <div class="col-md-3">
                        <div class="text-muted small">Approved By</div>
                        <div>{{ $bulk_ledger_entry->ApprovedByUser->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Approved At</div>
                        <div>{{ $bulk_ledger_entry->approved_at ? \Carbon\Carbon::parse($bulk_ledger_entry->approved_at)->format('M d, Y h:i A') : '-' }}</div>
                    </div>
                @elseif($bulk_ledger_entry->status === 'REJC')
                    <div class="col-md-3">
                        <div class="text-muted small">Rejected By</div>
                        <div>{{ $bulk_ledger_entry->RejectedByUser->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Rejected At</div>
                        <div>{{ $bulk_ledger_entry->rejected_at ? \Carbon\Carbon::parse($bulk_ledger_entry->rejected_at)->format('M d, Y h:i A') : '-' }}</div>
                    </div>
                @endif
            </div>

            <!-- Ledger Entries Table -->
            <h5 class="h6 mb-3">Entries to be Processed ({{ $bulk_ledger_entry->Entries->count() }})</h5>

            <div class="table-responsive mb-4">
                <table class="table table-hover table-bordered align-middle mb-0" id="review-entries-table">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Account Ledger</th>
                            <th>Ledger</th>
                            <th>Type</th>
                            <th>Tag</th>
                            <th>Particular</th>
                            <th class="text-center">Date</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Amount</th>
                            <th class="text-end">Subtotal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bulk_ledger_entry->Entries as $entry)
                            @php
                                $subtotal = $entry->quantity * $entry->unit_amount;
                                $entryStatusClass = match($entry->status) {
                                    'APRV' => 'bg-success',
                                    'REJC' => 'bg-danger',
                                    default => 'bg-warning text-dark',
                                };
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($entry->Ledger && $entry->Ledger->Account)
                                        <a href="/ledger/account/{{ $entry->Ledger->Account->id }}" class="text-decoration-none fw-semibold">
                                            {{ $entry->Ledger->Account->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($entry->Ledger)
                                        <a href="/ledger/{{ $entry->Ledger->id }}" class="text-decoration-none fw-semibold">
                                            {{ $entry->Ledger->name }}
                                        </a>
                                        @if($entry->Ledger->LedgerType)
                                            <span class="badge bg-secondary ms-1 small">{{ $entry->Ledger->LedgerType->text }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($entry->type === 'CRED')
                                        <span class="badge bg-success-subtle text-success border border-success">Credit (+)</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger">Debit (-)</span>
                                    @endif
                                </td>
                                <td>{{ $entry->tag }}</td>
                                <td>{{ $entry->particular }}</td>
                                <td class="text-center">{{ $entry->date }}</td>
                                <td class="text-end">{{ number_format($entry->quantity, 2) }}</td>
                                <td class="text-end">{{ number_format($entry->unit_amount, 2) }}</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($subtotal, 2) }}</td>
                                <td>
                                    <span class="badge {{ $entryStatusClass }}">{{ $entry->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">No ledger entries associated.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="9" class="text-end fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold text-primary">{{ $bulk_ledger_entry->total_amount_formatted }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Review Actions Card Footer -->
            <div class="card-footer bg-body py-3 border rounded">
                <x-review-controls :record="$bulk_ledger_entry"></x-review-controls>
            </div>
        </div>
    </div>

    <script type="module">
        import {$_POST, $ui, $url} from '/adarna.js';

        const recordId = {{ $bulk_ledger_entry->id }};

        controls.onApproveClick = () => {
            if (!confirm('Are you sure you want to approve this bulk ledger entry and activate all its entries?')) {
                return;
            }

            $_POST('/api/review/bulk-ledger-entry/approve', {
                id: recordId
            }).then(reply => {
                if (reply.status <= 0) {
                    $ui.showError(reply);
                    return;
                }
                alert(reply.message || 'Approved successfully.');
                window.location.reload();
            });
        };

        controls.onRejectClick = () => {
            if (!confirm('Are you sure you want to reject this bulk ledger entry?')) {
                return;
            }

            $_POST('/api/review/bulk-ledger-entry/reject', {
                id: recordId
            }).then(reply => {
                if (reply.status <= 0) {
                    $ui.showError(reply);
                    return;
                }
                alert(reply.message || 'Rejected successfully.');
                window.location.reload();
            });
        };

        controls.onCancelClick = () => {
            $url('/review/bulk-ledger-entries');
        };
    </script>
</x-app-layout>
