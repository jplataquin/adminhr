<x-app-layout>
    <div id="employee_record_display" class="card shadow-sm border-0 m-4">

        <div class="card-header bg-body py-3">
            <h3 class="h5 mb-0">
                Employee Record
            </h3>
        </div>

        <div class="card-header bg-body border-top-0 p-0">
            <x-tab-controls tab_scope=".tabs">
                <ul class="nav nav-tabs px-3 border-bottom-0">
                    <li class="nav-item">
                        <c-tab default target="#employee_data_display_view" class="nav-link active">Data</c-tab>
                    </li>
                    <li class="nav-item">
                        <c-tab target="#employee_ledgers_display_view" class="nav-link">
                            Ledgers
                            @if(isset($ledgers) && $ledgers->count() > 0)
                                <span class="badge bg-secondary ms-1">{{ $ledgers->count() }}</span>
                            @endif
                        </c-tab>
                    </li>
                    <li class="nav-item">
                        <c-tab target="#employee_options_display_view" class="nav-link">Options</c-tab>
                    </li>
                </ul>
            </x-tab-controls>
        </div>

        <div class="tabs" id="employee_data_display_view">
            {!! $employee_data_display_view !!}
        </div>

        <div class="tabs d-none p-4" id="employee_ledgers_display_view">
            @if(isset($ledger_account) && $ledger_account)
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-body-tertiary rounded border">
                    <div>
                        <div class="text-muted small">Linked Account Ledger</div>
                        <div class="fw-bold fs-6">{{ $ledger_account->name }}</div>
                        @if($ledger_account->type_category)
                            <span class="badge bg-info text-dark mt-1">{{ $ledger_account->type_category }}</span>
                        @endif
                    </div>
                    <div>
                        <a href="/ledger/account/{{ $ledger_account->id }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-folder2-open me-1"></i> Open Account
                        </a>
                    </div>
                </div>
            @endif

            @if(isset($ledgers) && $ledgers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="employee-ledgers-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Ledger Name</th>
                                <th>Ledger Type</th>
                                <th>Account</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ledgers as $ledger)
                                <tr class="cursor-pointer" onclick="if(event.ctrlKey || event.metaKey){ window.open('/ledger/{{ $ledger->id }}', '_blank'); } else { window.location.href = '/ledger/{{ $ledger->id }}'; }">
                                    <td>
                                        <a href="/ledger/{{ $ledger->id }}" class="fw-semibold text-decoration-none text-light" onclick="event.stopPropagation();">
                                            {{ $ledger->name }}
                                        </a>
                                        @if($ledger->description)
                                            <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $ledger->description }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ledger->LedgerType)
                                            <span class="badge bg-secondary">{{ $ledger->LedgerType->text }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ledger->Account)
                                            <a href="/ledger/account/{{ $ledger->Account->id }}" class="text-decoration-none text-muted small" onclick="event.stopPropagation();">
                                                {{ $ledger->Account->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $ledger->unit }}</td>
                                    <td>
                                        @php
                                            $statusClass = match($ledger->status) {
                                                'APRV' => 'bg-success',
                                                'PEND' => 'bg-warning text-dark',
                                                'REJC' => 'bg-danger',
                                                'CLSD' => 'bg-secondary',
                                                default => 'bg-info text-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $ledger->status }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-primary">
                                        {{ $ledger->total_amount_formatted }}
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <a href="/ledger/{{ $ledger->id }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted" id="employee-ledgers-empty">
                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                    <p class="mb-0">No ledgers found for this employee.</p>
                    @if(isset($ledger_account) && $ledger_account)
                        <a href="/ledger/account/{{ $ledger_account->id }}" class="btn btn-sm btn-outline-primary mt-3">
                            <i class="bi bi-plus-circle me-1"></i> Add Ledger in Account
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <div class="tabs d-none p-4" id="employee_options_display_view">
            <div class="card bg-body-tertiary">
                <div class="card-body">
                    <h5 class="card-title">Administrative Actions</h5>
                    <div class="mt-3">
                        <x-primary-button id="generate_id_btn">Generate ID</x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import Technologia from '/technologia.js';
        import {$q} from '/adarna.js';

        const employee_record_display = $q('#employee_record_display').first();
        const generate_id_btn         = $q('#generate_id_btn').first();
        
        Technologia.init(employee_record_display);

        generate_id_btn.onclick = (e)=>{
            window.$tab('/employee/template_id/{{$employee->id}}','Company ID');
        }

    </script>
</x-app-layout>
