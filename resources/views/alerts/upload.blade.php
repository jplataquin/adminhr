<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Bulk Upload Alerts') }}
            </h2>
            <a href="{{ route('alerts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Alerts
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('alerts.index') }}">Alerts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Bulk Upload</li>
                </ol>
            </nav>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm border-0 mb-4" role="alert">
                    <div class="fw-bold mb-2">Please correct the following errors:</div>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="mb-0 text-white"><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload Excel/CSV File</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('alerts.preview') }}" method="POST" enctype="multipart/form-data" class="py-3">
                                @csrf
                                <div class="mb-4">
                                    <label for="file" class="form-label text-white-50 small fw-semibold">Choose Spreadsheet File</label>
                                    <input type="file" name="file" id="file" class="form-control" required accept=".xlsx,.xls,.csv">
                                    <div class="form-text text-muted small mt-2">
                                        Supported formats: Microsoft Excel (.xlsx, .xls) and Comma-Separated Values (.csv).
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-eye me-1"></i> Preview and Validate Entries
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="mb-0 text-white"><i class="bi bi-info-circle me-2"></i>Instructions & File Format</h5>
                        </div>
                        <div class="card-body small text-white-50">
                            <p class="mb-3">To ensure a successful bulk upload, please prepare your Excel or CSV file according to the following layout constraints:</p>
                            
                            <table class="table table-bordered table-sm text-white-50 mb-4">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Column Header</th>
                                        <th>Required</th>
                                        <th>Rules / Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-white fw-semibold">Title</td>
                                        <td>Yes</td>
                                        <td>The alert/item name. <strong>Must be unique</strong> across the system.</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white fw-semibold">Document Type</td>
                                        <td>Yes</td>
                                        <td>e.g. Visa, Passport, License, Insurance, etc.</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white fw-semibold">Employee ID</td>
                                        <td>No</td>
                                        <td>Database ID of the linked employee. Leave blank for General alerts.</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white fw-semibold">Expiry Date</td>
                                        <td>Yes</td>
                                        <td>The item's expiration date (Format: <code>YYYY-MM-DD</code>).</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white fw-semibold">Alert Days Before</td>
                                        <td>No</td>
                                        <td>Number of days before expiry to trigger warning status (defaults to 30).</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white fw-semibold">Description</td>
                                        <td>No</td>
                                        <td>Optional contextual notes for the alert.</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white fw-semibold">Reference</td>
                                        <td>No</td>
                                        <td>Optional serial number, passport number, transaction ID, etc. (max 250 chars).</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="d-flex align-items-center bg-light-subtle p-3 rounded border">
                                <div class="fs-3 text-primary me-3">
                                    <i class="bi bi-cloud-arrow-down-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-white fw-semibold">Need a template?</h6>
                                    <p class="mb-2 text-muted small">Download our pre-formatted CSV template directly with sample records to get started immediately.</p>
                                    <a href="{{ route('alerts.sample') }}" class="btn btn-sm btn-outline-primary py-1">
                                        <i class="bi bi-download me-1"></i> Download CSV Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>