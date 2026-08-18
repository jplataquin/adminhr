<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Master Data') }}
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Master Data</li>
                </ol>
            </nav>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="container">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5 text-center">
                    <div class="mb-4 text-secondary">
                        <i class="bi bi-database-fill fs-1"></i>
                    </div>
                    <h4 class="card-title mb-3">Master Data Management</h4>
                    <p class="card-text text-muted max-w-md mx-auto">
                        This section will host administrative interfaces for configuring system-wide master data. Currently under construction.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
