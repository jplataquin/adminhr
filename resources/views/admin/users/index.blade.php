<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('User Management') }}
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- User List -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">System Users</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if ($user->is_admin)
                                                    <span class="badge bg-danger">Admin</span>
                                                @else
                                                    <span class="badge bg-secondary">User</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Are you sure you want to reset the password for {{ $user->name }}?')">
                                                        Reset Password
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($users->hasPages())
                            <div class="card-footer bg-white py-3">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Create User Form -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Create New User</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.users.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <x-input-label for="name" :value="__('Name')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 d-block w-100" :value="old('name')" required autofocus />
                                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                </div>

                                <div class="mb-3">
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 d-block w-100" :value="old('email')" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="is_admin" class="form-check-input" id="is_admin" value="1">
                                    <label class="form-check-label" for="is_admin">Grant Admin Access</label>
                                </div>

                                <div class="d-grid">
                                    <x-primary-button>
                                        {{ __('Create User') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
