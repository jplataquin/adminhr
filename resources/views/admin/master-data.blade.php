<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Master Data') }}
            </h2>
            
        </div>
    </x-slot>

    <div class="py-12">
        <div class="container">
           
            <nav class="mb-4" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Master Data</li>
                </ol>
            </nav>
            
            <div class="row">
                <!-- Alert Document Types Card -->
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-3 text-primary">
                                    <i class="bi bi-file-earmark-text-fill fs-1"></i>
                                </div>
                                <h5 class="card-title mb-2">Alert Document Types</h5>
                                <p class="card-text text-muted small">Configure the master list of document and item types allowed for tracking within the Expiry Alerts module.</p>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.alert-document-types.index') }}" class="btn btn-primary btn-sm w-100">
                                    Manage Types
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divisions Card -->
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-3 text-success">
                                    <i class="bi bi-diagram-3-fill fs-1"></i>
                                </div>
                                <h5 class="card-title mb-2">Divisions</h5>
                                <p class="card-text text-muted small">Configure high-level organizational divisions (e.g., Construction, Accounting & Finance, Admin/HR).</p>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.divisions.index') }}" class="btn btn-success btn-sm w-100">
                                    Manage Divisions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departments Card -->
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-3 text-info">
                                    <i class="bi bi-building fs-1"></i>
                                </div>
                                <h5 class="card-title mb-2">Departments</h5>
                                <p class="card-text text-muted small">Configure organizational departments nested within divisions (e.g., Repair & Maintenance under Equipment).</p>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.departments.index') }}" class="btn btn-info btn-sm w-100">
                                    Manage Departments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Positions Card -->
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-3 text-warning">
                                    <i class="bi bi-person-workspace fs-1"></i>
                                </div>
                                <h5 class="card-title mb-2">Positions</h5>
                                <p class="card-text text-muted small">Configure job titles and positions nested within departments (e.g., Welder 1, Accountant).</p>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.positions.index') }}" class="btn btn-warning btn-sm w-100">
                                    Manage Positions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            
            </div>
        </div>
    </div>
</x-app-layout>
