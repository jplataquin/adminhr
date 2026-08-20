<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Edit Alert') }}
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('alerts.index') }}">Alerts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <!-- Display validation errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('alerts.update', $alert->id) }}">
                                @csrf
                                @method('PUT')

                                <!-- Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label small font-weight-bold">Title / Item Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $alert->title) }}" required placeholder="e.g. Driver's License Renewal, Server SSL Certificate">
                                    <div class="form-text small text-muted">A clear, descriptive name of what is being tracked.</div>
                                </div>

                                <div class="row mb-3">
                                    <!-- Document Type (Dropdown) -->
                                    <div class="col-md-6">
                                        <label for="document_type" class="form-label small font-weight-bold">Document / Item Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="document_type" name="document_type" required>
                                            <option value="">-- Select Type --</option>
                                            @foreach ($documentTypes as $type)
                                                <option value="{{ $type }}" {{ old('document_type', $alert->document_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text small text-muted">Select from the configured document/item types.</div>
                                    </div>

                                    <!-- Employee Link (Optional) -->
                                    <div class="col-md-6">
                                        <label for="employee_id" class="form-label small font-weight-bold">Linked Employee <span class="text-muted">(Optional)</span></label>
                                        <select class="form-select" id="employee_id" name="employee_id">
                                            <option value="">-- None (General Alert) --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->id }}" {{ old('employee_id', $alert->employee_id) == $emp->id ? 'selected' : '' }}>
                                                    {{ $emp->lastname }}, {{ $emp->firstname }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text small text-muted">Link this alert to a specific employee record if applicable.</div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <!-- Expiry Date -->
                                    <div class="col-md-6">
                                        <label for="expiry_date" class="form-label small font-weight-bold">Expiry Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $alert->expiry_date->format('Y-m-d')) }}" required>
                                        <div class="form-text small text-muted">The final expiration date of the document or item.</div>
                                    </div>

                                    <!-- Alert Days Before -->
                                    <div class="col-md-6">
                                        <label for="alert_days_before" class="form-label small font-weight-bold">Warning Threshold (Days Before) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="alert_days_before" name="alert_days_before" value="{{ old('alert_days_before', $alert->alert_days_before) }}" min="0" required>
                                        <div class="form-text small text-muted">Number of days before expiry to trigger a Warning state.</div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="description" class="form-label small font-weight-bold">Description / Additional Notes</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter any extra details, instructions, or renewal reference links...">{{ old('description', $alert->description) }}</textarea>
                                </div>

                                <!-- Form Actions -->
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('alerts.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                    <button type="submit" class="btn btn-primary btn-sm">Update Alert</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
