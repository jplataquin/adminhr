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
           
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Master Data</li>
                </ol>
            </nav>
            
            <div class="row">
                <div class="col-md-4 mb-4">
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

                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100 bg-light text-muted">
                        <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-3 text-secondary">
                                    <i class="bi bi-lock-fill fs-1"></i>
                                </div>
                                <h5 class="card-title mb-2">General Configurations</h5>
                                <p class="card-text small">System configuration settings are currently locked.</p>
                            </div>
                            <div class="mt-4">
                                <button class="btn btn-outline-secondary btn-sm w-100" disabled>Under Construction</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
