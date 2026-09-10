<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Departments') }}
            </h2>
            <a href="{{ route('admin.departments.create') }}" class="btn btn-info btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Add New Department
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}">Master Data</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Departments</li>
                </ol>
            </nav>

            <!-- Success Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- List Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4" style="width: 80px;">ID</th>
                                    <th>Division</th>
                                    <th>Department Code</th>
                                    <th>Department Name</th>
                                    <th>Created At</th>
                                    <th class="text-end px-4" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($departments as $department)
                                    <tr>
                                        <td class="px-4 text-white-50">{{ $department->id }}</td>
                                        <td>
                                            <span class="badge bg-success font-monospace">{{ $department->division->code }}</span>
                                            <span class="small text-white-50 ms-1 d-none d-md-inline">{{ $department->division->name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info font-monospace">{{ $department->code }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-white">{{ $department->name }}</strong>
                                        </td>
                                        <td class="small text-white-50">
                                            {{ $department->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="text-end px-4">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('admin.departments.edit', $department->id) }}" class="btn btn-sm btn-outline-info py-1 px-2">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <form method="POST" action="{{ route('admin.departments.destroy', $department->id) }}" onsubmit="return confirm('Are you sure you want to delete this department? This will cascade to all positions!');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-white-50">
                                            <div class="fs-4 mb-2"><i class="bi bi-info-circle"></i></div>
                                            No departments are configured yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($departments->hasPages())
                        <div class="card-footer bg-transparent border-0 py-3">
                            {{ $departments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
