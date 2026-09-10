<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Add Position') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}">Master Data</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.positions.index') }}">Positions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add New</li>
                </ol>
            </nav>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <!-- Validation Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.positions.store') }}">
                                @csrf

                                <div class="mb-4">
                                    <label for="department_id" class="form-label small font-weight-bold">Department <span class="text-danger">*</span></label>
                                    <select class="form-select" id="department_id" name="department_id" required autofocus>
                                        <option value="">-- Select Department --</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                [{{ $department->division->code }}] {{ $department->code }} - {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small text-muted">Select the department this position belongs to.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="code" class="form-label small font-weight-bold">Position Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control font-monospace text-uppercase" id="code" name="code" value="{{ old('code') }}" required placeholder="e.g. ADHRDM, ADHRST, ITHEAD" maxlength="7">
                                    <div class="form-text small text-muted">A short globally unique code identifier for the position (up to 7 characters).</div>
                                </div>

                                <div class="mb-4">
                                    <label for="name" class="form-label small font-weight-bold">Position Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Admin/HR Division Manager, IT Department Head">
                                    <div class="form-text small text-muted">Enter a descriptive title for this position.</div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                    <button type="submit" class="btn btn-warning text-dark btn-sm font-weight-bold">Create Position</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
