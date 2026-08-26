<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0 text-white">
                {{ __('Preview Alerts Upload') }}
            </h2>
            <a href="{{ route('alerts.upload') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Upload
            </a>
        </div>
    </x-slot>

    <div class="py-4" x-data="previewUpload">
        <div class="container-fluid">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('alerts.index') }}">Alerts</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('alerts.upload') }}">Bulk Upload</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Preview</li>
                </ol>
            </nav>

            <!-- Status Info -->
            <div class="alert alert-info shadow-sm border-0 mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span class="fw-semibold">Preview Mode:</span> Review, edit, or delete items before saving them to the database. 
                    <span class="badge bg-primary ms-2"><span x-text="alerts.length"></span> items parsed</span>
                </div>
                <div x-show="hasErrors()" class="badge bg-danger p-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Uniqueness or validation errors found! Please resolve before saving.
                </div>
            </div>

            <!-- Server Error Messages (if any bypasses client-side) -->
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm border-0 mb-4" role="alert">
                    <div class="fw-bold mb-2">Server Validation failed:</div>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Tabular list and editor -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="px-3" style="width: 50px;">#</th>
                                    <th style="min-width: 250px;">Item Name (Title) <span class="text-danger">*</span></th>
                                    <th style="width: 200px;">Document Type <span class="text-danger">*</span></th>
                                    <th style="width: 220px;">Linked Employee</th>
                                    <th style="width: 170px;">Expiry Date <span class="text-danger">*</span></th>
                                    <th style="width: 110px;">Threshold (Days)</th>
                                    <th>Description</th>
                                    <th class="text-center" style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(alert, index) in alerts" :key="index">
                                    <tr :class="{'table-danger-subtle': !isTitleValid(alert.title, index) || !alert.document_type || !alert.expiry_date}">
                                        <td class="px-3 text-muted fw-semibold" x-text="index + 1"></td>
                                        
                                        <!-- Title Column -->
                                        <td>
                                            <input type="text" x-model="alert.title" class="form-control form-control-sm border-secondary text-white bg-dark" placeholder="Enter item name..." required>
                                            
                                            <!-- Errors -->
                                            <div x-show="!alert.title || alert.title.trim() === ''" class="text-danger small mt-1">
                                                <i class="bi bi-x-circle me-1"></i> Title is required.
                                            </div>
                                            <div x-show="alert.title && alert.title.trim() !== '' && isDbDuplicate(alert.title)" class="text-danger small mt-1">
                                                <i class="bi bi-exclamation-circle me-1"></i> Already exists in database. Must be unique.
                                            </div>
                                            <div x-show="alert.title && alert.title.trim() !== '' && isRowDuplicate(alert.title, index)" class="text-warning small mt-1">
                                                <i class="bi bi-exclamation-triangle me-1"></i> Duplicate title in uploaded list.
                                            </div>
                                        </td>

                                        <!-- Document Type Column -->
                                        <td>
                                            <select x-model="alert.document_type" class="form-select form-select-sm border-secondary text-white bg-dark" required>
                                                <option value="">-- Select Type --</option>
                                                <template x-for="type in documentTypes" :key="type">
                                                    <option :value="type" x-text="type" :selected="alert.document_type === type"></option>
                                                </template>
                                                <!-- If it is custom and not in list, add it as option -->
                                                <option x-show="alert.document_type && !documentTypes.includes(alert.document_type)" :value="alert.document_type" x-text="alert.document_type" selected></option>
                                            </select>
                                            <div x-show="!alert.document_type" class="text-danger small mt-1">
                                                <i class="bi bi-x-circle me-1"></i> Required.
                                            </div>
                                        </td>

                                        <!-- Employee Column -->
                                        <td>
                                            <select x-model="alert.employee_id" class="form-select form-select-sm border-secondary text-white bg-dark">
                                                <option value="">None (General Alert)</option>
                                                <template x-for="emp in employees" :key="emp.id">
                                                    <option :value="emp.id" x-text="emp.lastname + ', ' + emp.firstname" :selected="alert.employee_id == emp.id"></option>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- Expiry Date Column -->
                                        <td>
                                            <input type="date" x-model="alert.expiry_date" class="form-control form-control-sm border-secondary text-white bg-dark" required>
                                            <div x-show="!alert.expiry_date" class="text-danger small mt-1">
                                                <i class="bi bi-x-circle me-1"></i> Required.
                                            </div>
                                        </td>

                                        <!-- Alert Days Before Column -->
                                        <td>
                                            <input type="number" x-model.number="alert.alert_days_before" min="0" class="form-control form-control-sm border-secondary text-white bg-dark" required>
                                        </td>

                                        <!-- Description Column -->
                                        <td>
                                            <input type="text" x-model="alert.description" class="form-control form-control-sm border-secondary text-white bg-dark" placeholder="Optional notes...">
                                        </td>

                                        <!-- Actions Column -->
                                        <td class="text-center">
                                            <button type="button" @click="deleteRow(index)" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Row">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Empty State -->
                                <tr x-show="alerts.length === 0">
                                    <td colspan="8" class="text-center py-5 text-muted bg-dark text-white-50">
                                        <div class="fs-3 mb-2"><i class="bi bi-trash-fill"></i></div>
                                        All entries have been deleted.
                                        <div class="mt-3">
                                            <a href="{{ route('alerts.upload') }}" class="btn btn-primary btn-sm">
                                                Upload Another File
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Form for submission -->
            <form action="{{ route('alerts.bulk-store') }}" method="POST" @submit="onSubmit($event)">
                @csrf
                <input type="hidden" name="alerts" :value="JSON.stringify(alerts)">

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('alerts.upload') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Cancel & Back
                    </a>

                    <div class="d-flex gap-2">
                        <button type="button" @click="alerts = []" class="btn btn-outline-danger" x-show="alerts.length > 0">
                            <i class="bi bi-trash me-1"></i> Clear All Rows
                        </button>

                        <button type="submit" class="btn btn-success px-4" :disabled="hasErrors() || alerts.length === 0">
                            <i class="bi bi-cloud-arrow-down-fill me-1"></i> Save <span x-text="alerts.length"></span> Alerts
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alpine.js Page script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('previewUpload', () => ({
                alerts: @json($parsedAlerts),
                employees: @json($employees),
                documentTypes: @json($documentTypes),
                existingTitles: @json($existingTitles),

                deleteRow(index) {
                    if (confirm('Are you sure you want to delete this row?')) {
                        this.alerts.splice(index, 1);
                    }
                },

                isDbDuplicate(title) {
                    if (!title) return false;
                    return this.existingTitles.map(t => t.toLowerCase().trim())
                                             .includes(title.toLowerCase().trim());
                },

                isRowDuplicate(title, index) {
                    if (!title) return false;
                    const cleanTitle = title.toLowerCase().trim();
                    for (let i = 0; i < this.alerts.length; i++) {
                        if (i !== index && this.alerts[i].title && this.alerts[i].title.toLowerCase().trim() === cleanTitle) {
                            return true;
                        }
                    }
                    return false;
                },

                isTitleValid(title, index) {
                    if (!title || title.trim() === '') return false;
                    if (this.isDbDuplicate(title)) return false;
                    if (this.isRowDuplicate(title, index)) return false;
                    return true;
                },

                hasErrors() {
                    for (let i = 0; i < this.alerts.length; i++) {
                        const alert = this.alerts[i];
                        if (!alert.title || alert.title.trim() === '') return true;
                        if (!this.isTitleValid(alert.title, i)) return true;
                        if (!alert.document_type || alert.document_type.trim() === '') return true;
                        if (!alert.expiry_date || alert.expiry_date.trim() === '') return true;
                    }
                    return false;
                },

                onSubmit(e) {
                    if (this.hasErrors()) {
                        e.preventDefault();
                        alert('Please resolve all validation and duplicate title errors before saving.');
                        return false;
                    }
                    if (this.alerts.length === 0) {
                        e.preventDefault();
                        alert('No alerts to save.');
                        return false;
                    }
                }
            }));
        });
    </script>
</x-app-layout>
