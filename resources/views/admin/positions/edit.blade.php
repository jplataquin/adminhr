<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Edit Position') }}
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
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
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

                            <form method="POST" action="{{ route('admin.positions.update', $position->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label for="division_id" class="form-label small font-weight-bold">Division <span class="text-danger">*</span></label>
                                    <select class="form-select" id="division_id" name="division_id" required autofocus>
                                        <option value="">-- Select Division --</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}" {{ old('division_id', $position->department->division_id) == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small text-muted">Select the division this position belongs to.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="department_id" class="form-label small font-weight-bold">Department <span class="text-danger">*</span></label>
                                    <select class="form-select" id="department_id" name="department_id" required>
                                        <option value="">-- Select Department --</option>
                                        @foreach ($divisions as $division)
                                            @foreach ($division->departments as $department)
                                                <option value="{{ $department->id }}" data-division-id="{{ $division->id }}" {{ old('department_id', $position->department_id) == $department->id ? 'selected' : '' }} style="display: none;">
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    <div class="form-text small text-muted">Select the department this position belongs to.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="name" class="form-label small font-weight-bold">Position Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $position->name) }}" required placeholder="e.g. Admin/HR Division Manager, IT Department Head">
                                    <div class="form-text small text-muted">Enter a descriptive title for this position.</div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                    <button type="submit" class="btn btn-warning text-dark btn-sm font-weight-bold">Update Position</button>
                                </div>
                            </form>

                            <script type="text/javascript">
                                document.addEventListener('DOMContentLoaded', function() {
                                    const divisionSelect = document.getElementById('division_id');
                                    const departmentSelect = document.getElementById('department_id');
                                    const departmentOptions = departmentSelect.querySelectorAll('option');

                                    function updateDepartments(divisionId) {
                                        if (divisionId === '') {
                                            departmentSelect.value = '';
                                            departmentSelect.disabled = true;
                                            departmentOptions.forEach(opt => {
                                                if (opt.value !== '') {
                                                    opt.style.display = 'none';
                                                }
                                            });
                                        } else {
                                            departmentSelect.disabled = false;
                                            departmentOptions.forEach(opt => {
                                                if (opt.value !== '') {
                                                    if (opt.getAttribute('data-division-id') == divisionId) {
                                                        opt.style.display = 'block';
                                                    } else {
                                                        opt.style.display = 'none';
                                                    }
                                                }
                                            });
                                        }
                                    }

                                    divisionSelect.addEventListener('change', function() {
                                        departmentSelect.value = '';
                                        updateDepartments(this.value);
                                    });

                                    // Initial trigger on edit load
                                    if (divisionSelect.value !== '') {
                                        updateDepartments(divisionSelect.value);
                                        const selectedDept = "{{ old('department_id', $position->department_id) }}";
                                        if (selectedDept !== '') {
                                            departmentSelect.value = selectedDept;
                                        }
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
