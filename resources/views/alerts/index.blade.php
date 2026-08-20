<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Alerts Dashboard') }}
            </h2>
            <a href="{{ route('alerts.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Register New Alert
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <!-- Session Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-danger text-white h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Expired Items</h6>
                                <h2 class="display-6 font-weight-bold mb-0">{{ $expiredCount }}</h2>
                            </div>
                            <div class="fs-1">
                                <i class="bi bi-exclamation-octagon-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Warning Threshold</h6>
                                <h2 class="display-6 font-weight-bold mb-0">{{ $warningCount }}</h2>
                            </div>
                            <div class="fs-1">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-success text-white h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Active Tracking</h6>
                                <h2 class="display-6 font-weight-bold mb-0">{{ $activeCount }}</h2>
                            </div>
                            <div class="fs-1">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('alerts.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label small text-muted">Search Text</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ $search }}" placeholder="Search title, description, or employee...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label small text-muted">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="Active" {{ $statusFilter === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Warning" {{ $statusFilter === 'Warning' ? 'selected' : '' }}>Warning</option>
                                <option value="Expired" {{ $statusFilter === 'Expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="document_type" class="form-label small text-muted">Document Type</label>
                            <select class="form-select form-select-sm" id="document_type" name="document_type">
                                <option value="">All Types</option>
                                @foreach ($documentTypes as $type)
                                    <option value="{{ $type }}" {{ $typeFilter === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="bi bi-funnel-fill"></i> Filter
                            </button>
                            <a href="{{ route('alerts.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">Title</th>
                                    <th>Document Type</th>
                                    <th>Linked Employee</th>
                                    <th>Expiry Date</th>
                                    <th>Threshold (Days)</th>
                                    <th>Status</th>
                                    <th class="text-end px-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alerts as $alert)
                                    <tr>
                                        <td class="px-4">
                                            <div class="font-weight-bold text-dark">{{ $alert->title }}</div>
                                            @if ($alert->description)
                                                <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">{{ $alert->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $alert->document_type }}</span>
                                        </td>
                                        <td>
                                            @if ($alert->employee)
                                                <a href="/employee/{{ $alert->employee->id }}" class="text-decoration-none font-weight-semibold">
                                                    {{ $alert->employee->firstname }} {{ $alert->employee->lastname }}
                                                </a>
                                            @else
                                                <span class="text-muted small">None (General)</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small {{ $alert->status === 'Expired' ? 'text-danger font-weight-bold' : '' }}">
                                                {{ $alert->expiry_date->format('M d, Y') }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                ({{ $alert->expiry_date->diffForHumans() }})
                                            </div>
                                        </td>
                                        <td>
                                            <span class="small">{{ $alert->alert_days_before }} days</span>
                                        </td>
                                        <td>
                                            @if ($alert->status === 'Expired')
                                                <span class="badge bg-danger">Expired</span>
                                            @elseif ($alert->status === 'Warning')
                                                <span class="badge bg-warning text-dark">Warning</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td class="text-end px-4">
                                            <div class="d-flex justify-content-end gap-1">
                                                @if ($alert->status === 'Expired' || $alert->status === 'Warning')
                                                    <button type="button" class="btn btn-sm btn-outline-success py-1 px-2" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#renewModal" 
                                                            data-bs-id="{{ $alert->id }}" 
                                                            data-bs-title="{{ $alert->title }}" 
                                                            data-bs-days="{{ $alert->alert_days_before }}">
                                                        <i class="bi bi-arrow-repeat"></i> Renew
                                                    </button>
                                                @endif
                                                <a href="{{ route('alerts.edit', $alert->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('alerts.destroy', $alert->id) }}" onsubmit="return confirm('Are you sure you want to delete this alert?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <div class="fs-4 mb-2"><i class="bi bi-info-circle"></i></div>
                                            No alerts matching the criteria were found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($alerts->hasPages())
                        <div class="card-footer bg-white border-0 py-3">
                            {{ $alerts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Renewal Modal -->
    <div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="renewForm" method="POST" action="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold" id="renewModalLabel">Renew Item / Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <p class="text-muted">Setting a new future expiry date will reset the alert tracking to Active state.</p>
                        <div class="p-3 bg-light rounded mb-4">
                            <span class="small text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Document Name</span>
                            <strong id="renewAlertTitle" class="text-dark"></strong>
                        </div>
                        
                        <div class="mb-3">
                            <label for="renew_expiry_date" class="form-label small font-weight-bold">New Expiry Date</label>
                            <input type="date" class="form-control" id="renew_expiry_date" name="expiry_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <div class="form-text small text-muted">Select a new date in the future.</div>
                        </div>

                        <div class="mb-3">
                            <label for="renew_alert_days_before" class="form-label small font-weight-bold">Warning Threshold (Days Before)</label>
                            <input type="number" class="form-control" id="renew_alert_days_before" name="alert_days_before" min="0" required value="30">
                            <div class="form-text small text-muted">Days prior to expiry when alert state switches to Warning.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Commit Renewal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var renewModal = document.getElementById('renewModal');
            if (renewModal) {
                renewModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var id = button.getAttribute('data-bs-id');
                    var title = button.getAttribute('data-bs-title');
                    var days = button.getAttribute('data-bs-days');

                    var form = renewModal.querySelector('#renewForm');
                    form.action = '/alerts/' + id + '/renew';

                    var titleEl = renewModal.querySelector('#renewAlertTitle');
                    titleEl.textContent = title;

                    var daysInput = renewModal.querySelector('#renew_alert_days_before');
                    daysInput.value = days;
                    
                    var dateInput = renewModal.querySelector('#renew_expiry_date');
                    dateInput.value = ''; // Reset date value
                });
            }
        });
    </script>
</x-app-layout>
