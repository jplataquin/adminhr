<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center p-4">
                            <div class="mb-3 text-primary">
                                <i class="bi bi-people-fill fs-1"></i>
                            </div>
                            <h5 class="card-title mb-3">User Management</h5>
                            <p class="card-text text-muted small">Manage system users, create new accounts, and reset passwords.</p>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-primary w-100 mt-3">
                                Go to Users
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- More cards can be added here -->
            </div>
        </div>
    </div>
</x-app-layout>
