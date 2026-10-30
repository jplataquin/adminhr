<x-ledger-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Update Logs: ') }} {{ $ledger->name }}
            </h2>
            <a href="{{ url('/ledger/'.$ledger->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Ledger
            </a>
        </div>
    </x-slot>

    <div>
        <!-- Record Summary Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <small class="text-white-50 text-uppercase d-block fw-bold">Account</small>
                            <span class="fs-6 fw-bold text-white">{{ $account ? $account->name : 'N/A' }}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-white-50 text-uppercase d-block fw-bold">Ledger Name</small>
                            <span class="fs-5 fw-bold text-white">{{ $ledger->name }}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-white-50 text-uppercase d-block fw-bold">Current Status</small>
                            <span class="badge {{ $ledger->status == 'APRV' ? 'bg-success' : ($ledger->status == 'PEND' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ $ledger->status }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-white-50 text-uppercase d-block fw-bold">Unit</small>
                            <span class="text-white">{{ $ledger->unit }}</span>
                        </div>
                        <div class="col-md-2 text-md-end">
                            <small class="text-white-50 text-uppercase d-block fw-bold">Total Logs</small>
                            <span class="badge bg-primary fs-6">{{ $logs->total() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs List Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-clock-history me-2 text-info"></i>Audit Update History
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4" style="width: 220px;">Timestamp</th>
                                    <th style="width: 200px;">Updated By</th>
                                    <th>Changes (Old vs New)</th>
                                    <th class="text-end px-4" style="width: 180px;">Old Values (JSON)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="px-4">
                                            <div class="fw-bold text-white">{{ $log->created_at->format('M d, Y h:i:s A') }}</div>
                                            <small class="text-white-50">{{ $log->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if($log->user)
                                                <div class="fw-semibold text-white">{{ $log->user->name }}</div>
                                                <small class="text-white-50">{{ $log->user->email }}</small>
                                            @else
                                                <span class="text-white-50 fst-italic">System / Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($log->old_values))
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach($log->old_values as $field => $oldValue)
                                                        @php
                                                            $newValue = $log->new_values[$field] ?? null;
                                                        @endphp
                                                        <div class="small">
                                                            <strong class="text-info">{{ ucwords(str_replace('_', ' ', $field)) }}:</strong>
                                                            <span class="text-danger text-decoration-line-through me-1">{{ is_array($oldValue) ? json_encode($oldValue) : ($oldValue ?? 'null') }}</span>
                                                            <i class="bi bi-arrow-right text-white-50 mx-1"></i>
                                                            <span class="text-success fw-semibold">{{ is_array($newValue) ? json_encode($newValue) : ($newValue ?? 'null') }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-white-50 small fst-italic">No attribute diff recorded</span>
                                            @endif
                                        </td>
                                        <td class="text-end px-4">
                                            <button class="btn btn-sm btn-outline-secondary py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#json-log-{{ $log->id }}" aria-expanded="false">
                                                <i class="bi bi-code-slash me-1"></i> View JSON
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="collapse bg-dark" id="json-log-{{ $log->id }}">
                                        <td colspan="4" class="p-3">
                                            <div class="card bg-body-tertiary border-0">
                                                <div class="card-body p-3">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <h6 class="small fw-bold text-uppercase text-danger mb-2">
                                                                <i class="bi bi-file-earmark-minus me-1"></i>Old Values (JSON)
                                                            </h6>
                                                            <pre class="bg-dark text-light p-3 rounded small mb-0 font-monospace overflow-auto" style="max-height: 200px;"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="small fw-bold text-uppercase text-success mb-2">
                                                                <i class="bi bi-file-earmark-plus me-1"></i>New Values (JSON)
                                                            </h6>
                                                            <pre class="bg-dark text-light p-3 rounded small mb-0 font-monospace overflow-auto" style="max-height: 200px;"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-white-50">
                                            <div class="fs-3 mb-2"><i class="bi bi-clock-history"></i></div>
                                            No update logs have been recorded for this ledger yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($logs->hasPages())
                        <div class="card-footer bg-transparent border-0 py-3">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
    </div>
</x-ledger-layout>
