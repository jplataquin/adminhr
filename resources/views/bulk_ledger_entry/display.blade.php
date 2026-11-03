<x-app-layout>
    <style>
        /* Screen enhancements */
        .ble-account-box {
            transition: border-color 0.15s ease-in-out;
        }

        .ble-entry-box {
            background-color: var(--bs-body-bg);
            border-color: var(--bs-border-color-translucent);
        }

        /* Print Styles for A4 Paper in Black & White */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }

            /* Hide website navigation, buttons, and chrome */
            nav, 
            header, 
            footer, 
            .navbar, 
            .sidebar, 
            #sidebar,
            .drawer_modal, 
            .drawer_modal_background, 
            .d-print-none, 
            .btn {
                display: none !important;
            }

            /* Reset all colors to pure black & white */
            *, *::before, *::after {
                background-color: transparent !important;
                color: #000 !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            body {
                background: #fff !important;
                color: #000 !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
                font-size: 9pt !important;
                line-height: 1.25 !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .min-vh-100, main, .container, .container-fluid, #pageDoc {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                background: transparent !important;
            }

            /* Document Print Header */
            .print-header {
                display: block !important;
                border-bottom: 2px solid #000 !important;
                margin-bottom: 8px !important;
                padding-bottom: 6px !important;
            }

            /* Black & White Metadata Box */
            .ble-metadata-box {
                border: 1px solid #000 !important;
                border-radius: 4px !important;
                background-color: #fafafa !important;
                margin-bottom: 8px !important;
                padding: 6px 10px !important;
                page-break-inside: avoid;
            }

            /* Nested Divs: Level 1 - Account Ledger Box */
            .ble-account-box {
                border: 1.5px solid #000 !important;
                border-radius: 4px !important;
                margin-bottom: 8px !important;
                page-break-inside: avoid;
            }

            .ble-account-header {
                background-color: #eee !important;
                border-bottom: 1.5px solid #000 !important;
                padding: 4px 8px !important;
            }

            .ble-account-body {
                padding: 6px !important;
            }

            /* Nested Divs: Level 2 - Ledger Box */
            .ble-ledger-box {
                border: 1px solid #444 !important;
                border-radius: 3px !important;
                margin-bottom: 6px !important;
                page-break-inside: avoid;
            }

            .ble-ledger-header {
                background-color: #f7f7f7 !important;
                border-bottom: 1px solid #444 !important;
                padding: 3px 6px !important;
            }

            .ble-ledger-body {
                padding: 6px !important;
            }

            /* Nested Divs: Level 3 - Ledger Entry Box */
            .ble-entry-box {
                border: 0.5px solid #888 !important;
                border-radius: 2px !important;
                background-color: #fff !important;
                margin-bottom: 4px !important;
                padding: 3px 6px !important;
                font-size: 8.5pt !important;
                page-break-inside: avoid;
            }

            .ble-entry-subtotal-bar {
                border-top: 1px solid #000 !important;
                background-color: #f7f7f7 !important;
                padding: 3px 6px !important;
                font-weight: 700 !important;
            }

            /* Grand Total Box */
            .ble-grand-total-box {
                border: 1.5px solid #000 !important;
                border-radius: 4px !important;
                background-color: #f4f4f4 !important;
                padding: 6px 10px !important;
                page-break-inside: avoid;
            }

            /* Badges: bordered clean monochrome */
            .badge {
                border: 1px solid #000 !important;
                background: #fff !important;
                color: #000 !important;
                padding: 1px 4px !important;
                font-size: 7.5pt !important;
                font-weight: 600 !important;
            }

            .ble-amount {
                color: #000 !important;
                font-weight: 700 !important;
            }

            /* Force page break avoidance */
            .ble-account-box, .ble-ledger-box, .ble-entry-box, .ble-grand-total-box {
                page-break-inside: avoid;
            }
        }
    </style>

    <div id="pageDoc" class="card shadow-sm border-0 m-4">
        <!-- Print-only Document Title Header -->
        <div class="print-header d-none">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 0.5px;">ADMINHR - Bulk Ledger Entry</h4>
                    <span class="small text-muted">Document Batch #{{ $bulk_ledger_entry->id }} &bull; Printed: {{ now()->format('M d, Y h:i A') }}</span>
                </div>
                <div class="text-end">
                    <span class="border border-dark px-2 py-1 fw-bold text-uppercase small">Status: {{ $bulk_ledger_entry->status }}</span>
                </div>
            </div>
        </div>

        <!-- Page Header (Screen) -->
        <div class="card-header bg-body py-3 d-flex align-items-center justify-content-between d-print-none">
            <div class="d-flex align-items-center gap-2">
                <h3 class="h5 mb-0">Bulk Ledger Entry #{{ $bulk_ledger_entry->id }}</h3>
                @php
                    $statusClass = match($bulk_ledger_entry->status) {
                        'APRV' => 'bg-success',
                        'REJC' => 'bg-danger',
                        default => 'bg-warning text-dark',
                    };
                @endphp
                <span class="badge {{ $statusClass }} fs-6">{{ $bulk_ledger_entry->status }}</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
                @if($bulk_ledger_entry->status === 'PEND')
                    <button type="button" class="btn btn-warning btn-sm" id="reviewLinkBtn">
                        <i class="bi bi-clipboard me-1"></i> Copy Review Link
                    </button>
                @endif
                <a href="/bulk-ledger-entries" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Metadata card -->
            <div class="row g-3 p-3 bg-body-tertiary rounded border mb-4 ble-metadata-box">
                <div class="col-md-6 col-sm-12">
                    <div class="text-muted small">Batch Description / Notes</div>
                    <div class="fw-semibold">{{ $bulk_ledger_entry->description ?: '(No description)' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-muted small">Total Batch Amount</div>
                    <div class="fw-bold fs-5 text-primary ble-amount">{{ $bulk_ledger_entry->total_amount_formatted }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-muted small">Total Entries</div>
                    <div class="fw-bold fs-5">{{ $bulk_ledger_entry->Entries->count() }} items</div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="text-muted small">Created By</div>
                    <div>{{ $bulk_ledger_entry->CreatedByUser->name ?? 'Unknown' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-muted small">Created At</div>
                    <div>{{ $bulk_ledger_entry->created_at->format('M d, Y h:i A') }}</div>
                </div>
                @if($bulk_ledger_entry->status === 'APRV')
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Approved By</div>
                        <div>{{ $bulk_ledger_entry->ApprovedByUser->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Approved At</div>
                        <div>{{ $bulk_ledger_entry->approved_at ? \Carbon\Carbon::parse($bulk_ledger_entry->approved_at)->format('M d, Y h:i A') : '-' }}</div>
                    </div>
                @elseif($bulk_ledger_entry->status === 'REJC')
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Rejected By</div>
                        <div>{{ $bulk_ledger_entry->RejectedByUser->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="text-muted small">Rejected At</div>
                        <div>{{ $bulk_ledger_entry->rejected_at ? \Carbon\Carbon::parse($bulk_ledger_entry->rejected_at)->format('M d, Y h:i A') : '-' }}</div>
                    </div>
                @endif
            </div>

            @php
                // Hierarchical grouping: Ledger Account -> Ledger -> Ledger Entries
                $groupedAccounts = [];
                foreach ($bulk_ledger_entry->Entries as $entry) {
                    $accId = $entry->Ledger?->Account?->id ?? 0;
                    $accName = $entry->Ledger?->Account?->name ?? 'Unassigned Account';
                    $accCategory = $entry->Ledger?->Account?->type_category ?? $entry->Ledger?->Account?->category_type ?? null;

                    $ledgerId = $entry->ledger_id ?? 0;
                    $ledgerName = $entry->Ledger?->name ?? 'Unassigned Ledger';
                    $ledgerType = $entry->Ledger?->LedgerType?->text ?? null;

                    if (!isset($groupedAccounts[$accId])) {
                        $groupedAccounts[$accId] = [
                            'id'       => $accId,
                            'name'     => $accName,
                            'category' => $accCategory,
                            'ledgers'  => [],
                            'total'    => 0,
                            'count'    => 0,
                        ];
                    }

                    if (!isset($groupedAccounts[$accId]['ledgers'][$ledgerId])) {
                        $groupedAccounts[$accId]['ledgers'][$ledgerId] = [
                            'id'       => $ledgerId,
                            'name'     => $ledgerName,
                            'type'     => $ledgerType,
                            'entries'  => [],
                            'subtotal' => 0,
                        ];
                    }

                    $subtotal = (float) ($entry->quantity * $entry->unit_amount);
                    $groupedAccounts[$accId]['ledgers'][$ledgerId]['entries'][] = $entry;
                    $groupedAccounts[$accId]['ledgers'][$ledgerId]['subtotal'] += $subtotal;
                    $groupedAccounts[$accId]['total'] += $subtotal;
                    $groupedAccounts[$accId]['count']++;
                }
            @endphp

            <!-- Section Title -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="h6 fw-bold mb-0">
                    <i class="bi bi-diagram-3 me-1 d-print-none text-primary"></i> Entries Grouped by Account & Ledger
                </h5>
                <span class="text-muted small">
                    {{ count($groupedAccounts) }} {{ count($groupedAccounts) === 1 ? 'Account' : 'Accounts' }},
                    {{ $bulk_ledger_entry->Entries->pluck('ledger_id')->unique()->count() }} {{ $bulk_ledger_entry->Entries->pluck('ledger_id')->unique()->count() === 1 ? 'Ledger' : 'Ledgers' }},
                    {{ $bulk_ledger_entry->Entries->count() }} {{ $bulk_ledger_entry->Entries->count() === 1 ? 'Entry' : 'Entries' }}
                </span>
            </div>

            <!-- Nested Divs Structure: Ledger Account -> Ledger -> Ledger Entries -->
            <div class="d-flex flex-column gap-3 mb-4 nested-entries-container">
                @forelse($groupedAccounts as $accId => $accountGroup)
                    <!-- Level 1: Ledger Account Box (div) -->
                    <div class="card border rounded-3 overflow-hidden ble-account-box">
                        <!-- Account Header (div) -->
                        <div class="card-header bg-primary bg-opacity-10 py-2 px-3 d-flex justify-content-between align-items-center ble-account-header">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-primary text-uppercase px-2 py-1 ble-account-badge">
                                    <i class="bi bi-wallet2 me-1 d-print-none"></i>Account Ledger
                                </span>
                                <strong class="fs-6">{{ $accountGroup['name'] }}</strong>
                                @if($accountGroup['category'])
                                    <span class="badge bg-secondary-subtle text-secondary border small">[{{ $accountGroup['category'] }}]</span>
                                @endif
                                <span class="text-muted small">({{ count($accountGroup['ledgers']) }} {{ count($accountGroup['ledgers']) === 1 ? 'ledger' : 'ledgers' }}, {{ $accountGroup['count'] }} {{ $accountGroup['count'] === 1 ? 'entry' : 'entries' }})</span>
                            </div>
                            <div class="text-end">
                                <span class="small text-muted me-1">Account Subtotal:</span>
                                <strong class="text-primary ble-amount">{{ number_format($accountGroup['total'], 2) }}</strong>
                            </div>
                        </div>

                        <!-- Account Body containing Level 2 Ledgers (div) -->
                        <div class="card-body p-3 d-flex flex-column gap-3 ble-account-body">
                            @foreach($accountGroup['ledgers'] as $ledgerId => $ledgerGroup)
                                <!-- Level 2: Ledger Box (div) -->
                                <div class="card border border-light-subtle rounded-3 bg-body-tertiary overflow-hidden ble-ledger-box">
                                    <div class="card-header bg-body py-2 px-3 d-flex justify-content-between align-items-center ble-ledger-header border-bottom">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 ble-ledger-badge">
                                                <i class="bi bi-book me-1 d-print-none"></i>Ledger
                                            </span>
                                            <strong class="text-body">{{ $ledgerGroup['name'] }}</strong>
                                            @if($ledgerGroup['type'])
                                                <span class="badge bg-secondary text-white small">({{ $ledgerGroup['type'] }})</span>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <span class="small text-muted me-1">Ledger Subtotal:</span>
                                            <strong class="text-success ble-amount">{{ number_format($ledgerGroup['subtotal'], 2) }}</strong>
                                        </div>
                                    </div>

                                    <!-- Level 3: Ledger Entries (Nested Divs) -->
                                    <div class="p-3 d-flex flex-column gap-2 ble-ledger-body">
                                        @foreach($ledgerGroup['entries'] as $entry)
                                            @php
                                                $subtotal = $entry->quantity * $entry->unit_amount;
                                                $entryStatusClass = match($entry->status) {
                                                    'APRV' => 'bg-success',
                                                    'REJC' => 'bg-danger',
                                                    default => 'bg-warning text-dark',
                                                };
                                            @endphp
                                            <!-- Single Ledger Entry Card (Nested Div) -->
                                            <div class="card border bg-body rounded-2 p-2 p-md-3 ble-entry-box">
                                                <!-- Entry Top Row: Badges, Date, and Subtotal -->
                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span class="badge bg-secondary-subtle text-secondary border small px-2 py-1 ble-entry-num">#{{ $loop->iteration }}</span>
                                                        @if($entry->type === 'CRED')
                                                            <span class="badge bg-success-subtle text-success border border-success small ble-entry-badge">Credit (+)</span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger border border-danger small ble-entry-badge">Debit (-)</span>
                                                        @endif
                                                        <span class="badge bg-secondary-subtle text-body border small ble-entry-badge">{{ $entry->tag }}</span>
                                                        <span class="badge {{ $entryStatusClass }} small ble-entry-badge">{{ $entry->status }}</span>
                                                        <span class="small text-muted ms-1">
                                                            <i class="bi bi-calendar3 me-1 d-print-none"></i>{{ $entry->date }}
                                                        </span>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="small text-muted me-1">Item Subtotal:</span>
                                                        <strong class="text-primary fs-6 ble-amount">{{ number_format($subtotal, 2) }}</strong>
                                                    </div>
                                                </div>

                                                <!-- Entry Bottom Row: Particular, Qty, and Unit Amount -->
                                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-1 border-top border-light-subtle">
                                                    <div class="fw-semibold text-body ble-particular">
                                                        {{ $entry->particular }}
                                                    </div>
                                                    <div class="small text-muted ble-calc-details">
                                                        <span class="me-3">Qty: <strong class="text-body">{{ number_format($entry->quantity, 2) }}</strong></span>
                                                        <span>Unit Amount: <strong class="text-body">{{ number_format($entry->unit_amount, 2) }}</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Ledger Subtotal Summary Bar -->
                                        <div class="d-flex justify-content-between align-items-center p-2 rounded bg-body border ble-entry-subtotal-bar mt-1">
                                            <span class="small fw-semibold text-muted">Subtotal for {{ $ledgerGroup['name'] }}:</span>
                                            <strong class="text-success ble-amount">{{ number_format($ledgerGroup['subtotal'], 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="card border p-4 text-center text-muted">
                        No ledger entries associated with this batch.
                    </div>
                @endforelse
            </div>

            <!-- Grand Total Summary Box -->
            <div class="card border rounded-3 p-3 bg-body ble-grand-total-box">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <strong class="fs-6">Grand Total Batch Summary</strong>
                        <div class="text-muted small">
                            {{ count($groupedAccounts) }} {{ count($groupedAccounts) === 1 ? 'Account Ledger' : 'Account Ledgers' }} &bull;
                            {{ $bulk_ledger_entry->Entries->pluck('ledger_id')->unique()->count() }} {{ $bulk_ledger_entry->Entries->pluck('ledger_id')->unique()->count() === 1 ? 'Ledger' : 'Ledgers' }} &bull;
                            {{ $bulk_ledger_entry->Entries->count() }} {{ $bulk_ledger_entry->Entries->count() === 1 ? 'Total Entry' : 'Total Entries' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small d-block">Batch Total Amount</span>
                        <strong class="fs-4 text-primary ble-amount">{{ $bulk_ledger_entry->total_amount_formatted }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script for Copy Review Link -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reviewLinkBtn = document.getElementById('reviewLinkBtn');
            if (reviewLinkBtn) {
                reviewLinkBtn.addEventListener('click', async () => {
                    const reviewUrl = '{{ url("/review/bulk-ledger-entry/" . $bulk_ledger_entry->id) }}';
                    let copied = false;

                    if (window.$copyToClipboard) {
                        try {
                            copied = await window.$copyToClipboard(reviewUrl);
                        } catch (e) {
                            copied = false;
                        }
                    } else if (navigator.clipboard && navigator.clipboard.writeText) {
                        try {
                            await navigator.clipboard.writeText(reviewUrl);
                            copied = true;
                        } catch (e) {
                            copied = false;
                        }
                    }

                    if (copied) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Copied!',
                                text: 'Review Link for Bulk Ledger Entry #{{ $bulk_ledger_entry->id }} has been copied to your clipboard.',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Review Link for Bulk Ledger Entry #{{ $bulk_ledger_entry->id }} copied to clipboard!');
                        }
                    } else {
                        prompt('Copy this review link:', reviewUrl);
                    }
                });
            }
        });
    </script>
</x-app-layout>
